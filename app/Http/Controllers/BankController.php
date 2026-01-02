<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\Partner;
use App\Services\CodaParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::with(['statements' => function ($query) {
            $query->latest('statement_date')->limit(5);
        }])->get();

        $recentTransactions = BankTransaction::with(['bankAccount', 'invoice', 'partner'])
            ->latest('value_date')
            ->limit(20)
            ->get();

        // Calculate total balance from account accessors (current_balance is computed from last statement)
        $totalBalance = $accounts->sum(fn ($account) => $account->current_balance ?? 0);

        $stats = [
            'total_balance' => $totalBalance,
            'unreconciled' => BankTransaction::where('reconciliation_status', 'pending')->count(),
            'this_month_in' => BankTransaction::where('amount', '>', 0)
                ->whereMonth('value_date', now()->month)
                ->whereYear('value_date', now()->year)
                ->sum('amount'),
            'this_month_out' => abs(BankTransaction::where('amount', '<', 0)
                ->whereMonth('value_date', now()->month)
                ->whereYear('value_date', now()->year)
                ->sum('amount')),
        ];

        return view('bank.index', compact('accounts', 'recentTransactions', 'stats'));
    }

    public function accounts()
    {
        $accounts = BankAccount::withCount('transactions')
            ->withSum('transactions', 'amount')
            ->get();

        return view('bank.accounts', compact('accounts'));
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'iban' => 'required|string|max:34|unique:bank_accounts,iban',
            'bic' => 'nullable|string|max:11',
            'bank_name' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'opening_balance' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
        ]);

        // Remove fields not in the model
        unset($validated['opening_balance'], $validated['opening_date'], $validated['currency']);

        BankAccount::create($validated);

        return redirect()
            ->route('bank.accounts')
            ->with('success', 'Compte bancaire ajouté avec succès.');
    }

    public function showImport()
    {
        $accounts = BankAccount::all();

        return view('bank.import', compact('accounts'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,coda|max:5120',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        try {
            $parser = new CodaParserService();
            $result = $parser->parse($content);

            DB::beginTransaction();

            // Find or create bank account
            $account = null;
            if ($request->filled('bank_account_id')) {
                $account = BankAccount::find($request->bank_account_id);
            } elseif (!empty($result['account_iban'])) {
                $account = BankAccount::where('iban', $result['account_iban'])->first();
                if (!$account) {
                    $account = BankAccount::create([
                        'iban' => $result['account_iban'],
                        'bic' => $result['account_bic'] ?? null,
                        'name' => 'Compte ' . substr($result['account_iban'], -4),
                    ]);
                }
            }

            if (!$account) {
                throw new \Exception('Impossible de déterminer le compte bancaire.');
            }

            // Create statement
            $statement = BankStatement::create([
                'bank_account_id' => $account->id,
                'statement_number' => $result['statement_number'],
                'statement_date' => $result['statement_date'],
                'opening_balance' => $result['old_balance'],
                'closing_balance' => $result['new_balance'],
                'total_credit' => $result['total_credit'] ?? 0,
                'total_debit' => $result['total_debit'] ?? 0,
                'transaction_count' => count($result['transactions']),
                'raw_content' => $content,
            ]);

            // Import transactions
            $imported = 0;
            $duplicates = 0;

            foreach ($result['transactions'] as $trans) {
                // Check for duplicates
                $exists = BankTransaction::where('bank_account_id', $account->id)
                    ->where('value_date', $trans['value_date'])
                    ->where('amount', $trans['amount'])
                    ->where('reference', $trans['reference'] ?? null)
                    ->exists();

                if ($exists) {
                    $duplicates++;
                    continue;
                }

                // Try to match partner
                $partnerId = null;
                if (!empty($trans['counterparty_account'])) {
                    $partner = Partner::where('iban', $trans['counterparty_account'])->first();
                    $partnerId = $partner?->id;
                }

                // Try to match invoice by structured communication
                $invoiceId = null;
                if (!empty($trans['structured_communication'])) {
                    $invoice = Invoice::where('structured_communication', $trans['structured_communication'])->first();
                    $invoiceId = $invoice?->id;
                }

                BankTransaction::create([
                    'bank_account_id' => $account->id,
                    'bank_statement_id' => $statement->id,
                    'transaction_date' => $trans['transaction_date'],
                    'value_date' => $trans['value_date'],
                    'amount' => $trans['amount'],
                    'currency' => $trans['currency'] ?? 'EUR',
                    'communication' => $trans['description'] ?? '',
                    'bank_reference' => $trans['reference'] ?? null,
                    'counterparty_name' => $trans['counterparty_name'] ?? null,
                    'counterparty_account' => $trans['counterparty_account'] ?? null,
                    'structured_communication' => $trans['structured_communication'] ?? null,
                    'matched_partner_id' => $partnerId,
                    'matched_invoice_id' => $invoiceId,
                    'reconciliation_status' => $invoiceId ? 'matched' : 'pending',
                ]);

                $imported++;

                // Auto-reconcile invoice if matched
                if ($invoiceId && $invoice) {
                    $this->reconcileInvoice($invoice, $trans['amount'], $trans['value_date']);
                }
            }

            DB::commit();

            return redirect()
                ->route('bank.index')
                ->with('success', "Import réussi : {$imported} transactions importées" . ($duplicates > 0 ? ", {$duplicates} doublons ignorés" : ""));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    public function reconciliation()
    {
        $unreconciled = BankTransaction::with(['bankAccount', 'partner'])
            ->where('reconciliation_status', 'pending')
            ->orderBy('value_date', 'desc')
            ->paginate(20);

        $openInvoices = Invoice::whereIn('status', ['sent', 'overdue'])
            ->with('partner')
            ->orderBy('due_date')
            ->get();

        return view('bank.reconciliation', compact('unreconciled', 'openInvoices'));
    }

    public function matchTransaction(Request $request, BankTransaction $transaction)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'partner_id' => 'nullable|exists:partners,id',
            'action' => 'required|in:match_invoice,match_partner,ignore,manual',
        ]);

        switch ($validated['action']) {
            case 'match_invoice':
                $invoice = Invoice::findOrFail($validated['invoice_id']);
                $transaction->update([
                    'matched_invoice_id' => $invoice->id,
                    'matched_partner_id' => $invoice->partner_id,
                    'reconciliation_status' => 'matched',
                ]);
                $this->reconcileInvoice($invoice, $transaction->amount, $transaction->value_date);
                break;

            case 'match_partner':
                $transaction->update([
                    'matched_partner_id' => $validated['partner_id'],
                    'reconciliation_status' => 'partial',
                ]);
                break;

            case 'ignore':
                $transaction->update(['reconciliation_status' => 'ignored']);
                break;

            case 'manual':
                // Will be handled by accounting entry
                $transaction->update(['reconciliation_status' => 'manual']);
                break;
        }

        return back()->with('success', 'Transaction réconciliée.');
    }

    protected function reconcileInvoice(Invoice $invoice, float $amount, $date): void
    {
        $newPaid = $invoice->amount_paid + abs($amount);
        $status = $newPaid >= $invoice->total_incl_vat ? 'paid' : $invoice->status;

        $invoice->update([
            'amount_paid' => $newPaid,
            'status' => $status,
            'paid_at' => $status === 'paid' ? $date : $invoice->paid_at,
        ]);
    }
}
