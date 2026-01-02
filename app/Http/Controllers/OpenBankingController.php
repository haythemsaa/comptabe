<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankConnection;
use App\Services\OpenBanking\PSD2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class OpenBankingController extends Controller
{
    public function __construct(
        protected PSD2Service $psd2Service
    ) {}

    /**
     * Dashboard Open Banking
     */
    public function index()
    {
        $companyId = Auth::user()->current_company_id;

        $connections = BankConnection::where('company_id', $companyId)
            ->with('accounts')
            ->get();

        $supportedBanks = $this->psd2Service->getSupportedBanks();

        $healthCheck = $this->psd2Service->healthCheck($companyId);

        // Stats globales
        $totalBalance = BankAccount::where('company_id', $companyId)
            ->where('status', 'active')
            ->sum('current_balance');

        $accountsCount = BankAccount::where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        return view('openbanking.index', compact(
            'connections',
            'supportedBanks',
            'healthCheck',
            'totalBalance',
            'accountsCount'
        ));
    }

    /**
     * Afficher les banques disponibles
     */
    public function banks()
    {
        $supportedBanks = $this->psd2Service->getSupportedBanks();

        return view('openbanking.banks', compact('supportedBanks'));
    }

    /**
     * Initier la connexion à une banque
     */
    public function connect(Request $request, string $bankId)
    {
        try {
            $companyId = Auth::user()->current_company_id;
            $authUrl = $this->psd2Service->getAuthorizationUrl($bankId, $companyId);

            return redirect()->away($authUrl);
        } catch (Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Callback OAuth de la banque
     */
    public function callback(Request $request)
    {
        try {
            if ($request->has('error')) {
                throw new Exception($request->get('error_description', 'Autorisation refusée'));
            }

            $code = $request->get('code');
            $state = $request->get('state');

            if (!$code || !$state) {
                throw new Exception('Paramètres de callback invalides');
            }

            $connection = $this->psd2Service->handleCallback($code, $state);

            return redirect()->route('openbanking.index')
                ->with('success', "Connexion à {$connection->bank_name} établie avec succès!");
        } catch (Exception $e) {
            return redirect()->route('openbanking.index')
                ->with('error', 'Erreur de connexion: ' . $e->getMessage());
        }
    }

    /**
     * Synchroniser tous les comptes d'une connexion
     */
    public function syncAccounts(BankConnection $connection)
    {
        $this->authorize('update', $connection);

        try {
            $accounts = $this->psd2Service->syncAccounts($connection);

            return back()->with('success', count($accounts) . ' compte(s) synchronisé(s).');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur de synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Synchroniser les transactions d'un compte
     */
    public function syncTransactions(Request $request, BankAccount $account)
    {
        $this->authorize('update', $account);

        try {
            $dateFrom = $request->filled('date_from')
                ? \Carbon\Carbon::parse($request->date_from)
                : null;

            $dateTo = $request->filled('date_to')
                ? \Carbon\Carbon::parse($request->date_to)
                : null;

            $transactions = $this->psd2Service->syncTransactions($account, $dateFrom, $dateTo);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'imported' => count($transactions),
                    'message' => count($transactions) . ' transaction(s) importée(s).',
                ]);
            }

            return back()->with('success', count($transactions) . ' transaction(s) importée(s).');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Synchroniser tous les comptes
     */
    public function syncAll()
    {
        $companyId = Auth::user()->current_company_id;
        $connections = BankConnection::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $totalAccounts = 0;
        $totalTransactions = 0;
        $errors = [];

        foreach ($connections as $connection) {
            try {
                $accounts = $this->psd2Service->syncAccounts($connection);
                $totalAccounts += count($accounts);

                foreach ($accounts as $account) {
                    try {
                        $transactions = $this->psd2Service->syncTransactions($account);
                        $totalTransactions += count($transactions);
                    } catch (Exception $e) {
                        $errors[] = "{$account->iban}: {$e->getMessage()}";
                    }
                }
            } catch (Exception $e) {
                $errors[] = "{$connection->bank_name}: {$e->getMessage()}";
            }
        }

        $message = "{$totalAccounts} compte(s) et {$totalTransactions} transaction(s) synchronisé(s).";
        if (!empty($errors)) {
            $message .= ' Erreurs: ' . implode(', ', array_slice($errors, 0, 3));
        }

        return back()->with(empty($errors) ? 'success' : 'warning', $message);
    }

    /**
     * Déconnecter une banque
     */
    public function disconnect(BankConnection $connection)
    {
        $this->authorize('delete', $connection);

        try {
            $this->psd2Service->revokeConnection($connection);

            return back()->with('success', "Connexion à {$connection->bank_name} révoquée.");
        } catch (Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Détail d'un compte
     */
    public function accountDetail(BankAccount $account)
    {
        $this->authorize('view', $account);

        $transactions = $account->transactions()
            ->orderBy('date', 'desc')
            ->paginate(50);

        return view('openbanking.account', compact('account', 'transactions'));
    }

    /**
     * Initier un paiement
     */
    public function initiatePayment(Request $request, BankConnection $connection)
    {
        $this->authorize('update', $connection);

        $validated = $request->validate([
            'debtor_iban' => 'required|string',
            'creditor_iban' => 'required|string',
            'creditor_name' => 'required|string|max:140',
            'amount' => 'required|numeric|min:0.01',
            'communication' => 'nullable|string|max:140',
            'structured_communication' => 'nullable|string|regex:/^\d{3}\/\d{4}\/\d{5}$/',
        ]);

        try {
            $result = $this->psd2Service->initiatePayment($connection, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'payment_id' => $result['paymentId'] ?? null,
                    'status' => $result['transactionStatus'] ?? 'pending',
                    'message' => 'Paiement initié. Veuillez confirmer sur l\'app de votre banque.',
                ]);
            }

            return back()->with('success', 'Paiement initié. Confirmez sur votre app bancaire.');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * API: Statut de santé des connexions
     */
    public function healthStatus()
    {
        $companyId = Auth::user()->current_company_id;
        $health = $this->psd2Service->healthCheck($companyId);

        return response()->json($health);
    }

    /**
     * Renouveler une connexion expirée
     */
    public function renew(BankConnection $connection)
    {
        $this->authorize('update', $connection);

        try {
            $companyId = Auth::user()->current_company_id;
            $authUrl = $this->psd2Service->getAuthorizationUrl($connection->bank_id, $companyId);

            return redirect()->away($authUrl);
        } catch (Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}
