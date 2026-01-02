<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountingController extends Controller
{
    public function index()
    {
        $currentYear = FiscalYear::current()->first();

        // Quick stats
        $stats = [
            'total_entries' => JournalEntry::when($currentYear, fn($q) => $q->whereBetween('entry_date', [$currentYear->start_date, $currentYear->end_date]))->count(),
            'total_debit' => JournalEntryLine::whereHas('journalEntry', fn($q) =>
                $q->when($currentYear, fn($q2) => $q2->whereBetween('entry_date', [$currentYear->start_date, $currentYear->end_date]))
            )->sum('debit'),
            'total_credit' => JournalEntryLine::whereHas('journalEntry', fn($q) =>
                $q->when($currentYear, fn($q2) => $q2->whereBetween('entry_date', [$currentYear->start_date, $currentYear->end_date]))
            )->sum('credit'),
            'unbalanced' => JournalEntry::whereRaw('
                (SELECT SUM(debit) FROM journal_entry_lines WHERE journal_entry_id = journal_entries.id) !=
                (SELECT SUM(credit) FROM journal_entry_lines WHERE journal_entry_id = journal_entries.id)
            ')->count(),
        ];

        $recentEntries = JournalEntry::with(['journal', 'lines.account'])
            ->latest('entry_date')
            ->limit(10)
            ->get();

        $journals = Journal::withCount('entries')->get();

        return view('accounting.index', compact('stats', 'recentEntries', 'journals', 'currentYear'));
    }

    public function chartOfAccounts()
    {
        $accounts = ChartOfAccount::with('parent')
            ->orderBy('account_number')
            ->get()
            ->groupBy(fn($account) => substr($account->account_number, 0, 1));

        return view('accounting.chart', compact('accounts'));
    }

    public function journals()
    {
        $journals = Journal::withCount('entries')
            ->withSum('entries', 'total_amount')
            ->get();

        return view('accounting.journals', compact('journals'));
    }

    public function entries(Request $request)
    {
        $query = JournalEntry::with(['journal', 'lines.account']);

        // Filters
        if ($request->filled('journal')) {
            $query->where('journal_id', $request->journal);
        }

        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $entries = $query->latest('entry_date')->paginate(20)->withQueryString();
        $journals = Journal::all();

        return view('accounting.entries', compact('entries', 'journals'));
    }

    public function createEntry()
    {
        $journals = Journal::all();
        $accounts = ChartOfAccount::postable()
            ->orderBy('account_number')
            ->get();

        $nextNumber = JournalEntry::generateEntryNumber();

        return view('accounting.entries.create', compact('journals', 'accounts', 'nextNumber'));
    }

    public function storeEntry(Request $request)
    {
        $validated = $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:100',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        // Validate balance
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()
                ->withErrors(['lines' => 'L\'écriture n\'est pas équilibrée. Débit: ' . number_format($totalDebit, 2) . ' - Crédit: ' . number_format($totalCredit, 2)])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $entry = JournalEntry::create([
                'journal_id' => $validated['journal_id'],
                'entry_number' => JournalEntry::generateEntryNumber($validated['journal_id']),
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'reference' => $validated['reference'] ?? null,
                'total_amount' => $totalDebit,
                'status' => 'draft',
            ]);

            foreach ($validated['lines'] as $index => $line) {
                if (($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $line['account_id'],
                        'line_number' => $index + 1,
                        'description' => $line['description'] ?? null,
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('accounting.entries')
                ->with('success', 'Écriture comptable créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function balance(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $accounts = ChartOfAccount::postable()
            ->orderBy('account_number')
            ->get()
            ->map(function ($account) use ($date) {
                $movements = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn($q) => $q->where('entry_date', '<=', $date))
                    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                    ->first();

                $account->total_debit = $movements->total_debit ?? 0;
                $account->total_credit = $movements->total_credit ?? 0;
                $account->balance = $account->total_debit - $account->total_credit;

                return $account;
            })
            ->filter(fn($account) => $account->balance != 0)
            ->groupBy(fn($account) => substr($account->account_number, 0, 1));

        $totals = [
            'debit' => $accounts->flatten()->sum('total_debit'),
            'credit' => $accounts->flatten()->sum('total_credit'),
        ];

        return view('accounting.balance', compact('accounts', 'totals', 'date'));
    }

    public function ledger(Request $request)
    {
        $accountId = $request->get('account');
        $dateFrom = $request->get('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $accounts = ChartOfAccount::postable()->orderBy('account_number')->get();
        $selectedAccount = $accountId ? ChartOfAccount::find($accountId) : null;

        $movements = collect();
        $openingBalance = 0;

        if ($selectedAccount) {
            // Calculate opening balance
            $openingBalance = JournalEntryLine::where('account_id', $accountId)
                ->whereHas('journalEntry', fn($q) => $q->where('entry_date', '<', $dateFrom))
                ->selectRaw('SUM(debit) - SUM(credit) as balance')
                ->value('balance') ?? 0;

            // Get movements
            $movements = JournalEntryLine::with(['journalEntry.journal'])
                ->where('account_id', $accountId)
                ->whereHas('journalEntry', fn($q) =>
                    $q->whereBetween('entry_date', [$dateFrom, $dateTo])
                )
                ->orderBy('id')
                ->get()
                ->map(function ($line) use (&$runningBalance, $openingBalance) {
                    static $runningBalance = null;
                    if ($runningBalance === null) {
                        $runningBalance = $openingBalance;
                    }
                    $runningBalance += $line->debit - $line->credit;
                    $line->running_balance = $runningBalance;
                    return $line;
                });
        }

        return view('accounting.ledger', compact('accounts', 'selectedAccount', 'movements', 'openingBalance', 'dateFrom', 'dateTo'));
    }
}
