<?php

namespace App\Services\Integrations;

use App\Models\Company;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Open Banking Service (PSD2) for Belgian Banks
 *
 * Supports: BNP Paribas Fortis, KBC, Belfius, ING Belgium, Argenta
 */
class OpenBankingService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.open_banking.base_url', 'https://api.openbanking.be');
        $this->clientId = config('services.open_banking.client_id');
        $this->clientSecret = config('services.open_banking.client_secret');
    }

    /**
     * Get authorization URL for bank connection
     */
    public function getAuthorizationUrl(string $bankCode, string $redirectUri, string $state): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'accounts transactions balances',
            'state' => $state,
            'bank' => $bankCode,
        ]);

        return "{$this->baseUrl}/authorize?{$params}";
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        try {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post("{$this->baseUrl}/token", [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception("Token exchange failed: {$response->body()}");
        } catch (\Exception $e) {
            Log::error('Open Banking token exchange failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        try {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post("{$this->baseUrl}/token", [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception("Token refresh failed: {$response->body()}");
        } catch (\Exception $e) {
            Log::error('Open Banking token refresh failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get all connected bank accounts
     */
    public function getAccounts(string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/accounts");

            if ($response->successful()) {
                return $response->json()['accounts'] ?? [];
            }

            throw new \Exception("Failed to fetch accounts: {$response->body()}");
        } catch (\Exception $e) {
            Log::error('Failed to fetch bank accounts', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get account balance
     */
    public function getAccountBalance(string $accessToken, string $accountId): ?array
    {
        try {
            $cacheKey = "bank_balance_{$accountId}";

            return Cache::remember($cacheKey, 300, function () use ($accessToken, $accountId) {
                $response = Http::withToken($accessToken)
                    ->get("{$this->baseUrl}/accounts/{$accountId}/balances");

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'balance' => $data['balances'][0]['amount']['value'] ?? 0,
                        'currency' => $data['balances'][0]['amount']['currency'] ?? 'EUR',
                        'last_updated' => now(),
                    ];
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Failed to fetch account balance', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get account transactions
     */
    public function getTransactions(
        string $accessToken,
        string $accountId,
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null
    ): array {
        try {
            $params = [];

            if ($dateFrom) {
                $params['dateFrom'] = $dateFrom->format('Y-m-d');
            }

            if ($dateTo) {
                $params['dateTo'] = $dateTo->format('Y-m-d');
            }

            $url = "{$this->baseUrl}/accounts/{$accountId}/transactions";
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get($url);

            if ($response->successful()) {
                return $response->json()['transactions']['booked'] ?? [];
            }

            throw new \Exception("Failed to fetch transactions: {$response->body()}");
        } catch (\Exception $e) {
            Log::error('Failed to fetch transactions', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Import transactions into database
     */
    public function importTransactions(
        BankAccount $bankAccount,
        string $accessToken,
        ?Carbon $dateFrom = null
    ): int {
        $dateFrom = $dateFrom ?? now()->subDays(90);
        $dateTo = now();

        $transactions = $this->getTransactions(
            $accessToken,
            $bankAccount->external_account_id,
            $dateFrom,
            $dateTo
        );

        $imported = 0;

        foreach ($transactions as $transaction) {
            $transactionId = $transaction['transactionId'] ?? $transaction['entryReference'] ?? null;

            if (!$transactionId) {
                continue;
            }

            // Check if already imported
            $exists = BankTransaction::where('bank_account_id', $bankAccount->id)
                ->where('external_transaction_id', $transactionId)
                ->exists();

            if ($exists) {
                continue;
            }

            // Create transaction
            BankTransaction::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'company_id' => $bankAccount->company_id,
                'bank_account_id' => $bankAccount->id,
                'external_transaction_id' => $transactionId,
                'transaction_date' => Carbon::parse($transaction['bookingDate'] ?? $transaction['valueDate']),
                'value_date' => Carbon::parse($transaction['valueDate'] ?? $transaction['bookingDate']),
                'amount' => (float) ($transaction['transactionAmount']['amount'] ?? 0),
                'currency' => $transaction['transactionAmount']['currency'] ?? 'EUR',
                'description' => $transaction['remittanceInformationUnstructured'] ??
                                $transaction['additionalInformation'] ?? '',
                'counterparty_name' => $transaction['creditorName'] ??
                                      $transaction['debtorName'] ?? null,
                'counterparty_account' => $transaction['creditorAccount']['iban'] ??
                                         $transaction['debtorAccount']['iban'] ?? null,
                'type' => $this->determineTransactionType($transaction),
                'status' => 'booked',
                'reconciled_at' => null,
            ]);

            $imported++;
        }

        Log::info("Imported {$imported} transactions for bank account {$bankAccount->id}");

        return $imported;
    }

    /**
     * Determine transaction type
     */
    protected function determineTransactionType(array $transaction): string
    {
        $amount = (float) ($transaction['transactionAmount']['amount'] ?? 0);

        if ($amount > 0) {
            return 'credit';
        } elseif ($amount < 0) {
            return 'debit';
        }

        return 'unknown';
    }

    /**
     * Sync all connected bank accounts
     */
    public function syncAllAccounts(string $companyId): array
    {
        $company = Company::find($companyId);

        if (!$company || !$company->open_banking_access_token) {
            return [
                'success' => false,
                'message' => 'No Open Banking connection found',
            ];
        }

        $accessToken = $company->open_banking_access_token;

        // Refresh token if needed
        if ($company->open_banking_token_expires_at &&
            Carbon::parse($company->open_banking_token_expires_at)->isPast()) {

            $tokenData = $this->refreshAccessToken($company->open_banking_refresh_token);

            $company->update([
                'open_banking_access_token' => $tokenData['access_token'],
                'open_banking_refresh_token' => $tokenData['refresh_token'] ?? $company->open_banking_refresh_token,
                'open_banking_token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
            ]);

            $accessToken = $tokenData['access_token'];
        }

        // Get all accounts
        $externalAccounts = $this->getAccounts($accessToken);
        $syncedAccounts = 0;
        $totalTransactions = 0;

        foreach ($externalAccounts as $externalAccount) {
            // Find or create bank account
            $bankAccount = BankAccount::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'external_account_id' => $externalAccount['resourceId'] ?? $externalAccount['id'],
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'account_name' => $externalAccount['name'] ?? 'Bank Account',
                    'account_number' => $externalAccount['iban'] ?? null,
                    'bank_name' => $externalAccount['servicer']['bic'] ?? 'Unknown',
                    'currency' => $externalAccount['currency'] ?? 'EUR',
                    'type' => 'checking',
                    'is_active' => true,
                ]
            );

            // Update balance
            $balance = $this->getAccountBalance($accessToken, $bankAccount->external_account_id);
            if ($balance) {
                $bankAccount->update([
                    'current_balance' => $balance['balance'],
                    'balance_updated_at' => $balance['last_updated'],
                ]);
            }

            // Import transactions
            $imported = $this->importTransactions($bankAccount, $accessToken);
            $totalTransactions += $imported;
            $syncedAccounts++;
        }

        return [
            'success' => true,
            'synced_accounts' => $syncedAccounts,
            'imported_transactions' => $totalTransactions,
        ];
    }

    /**
     * Disconnect Open Banking
     */
    public function disconnect(string $companyId): bool
    {
        $company = Company::find($companyId);

        if (!$company) {
            return false;
        }

        $company->update([
            'open_banking_access_token' => null,
            'open_banking_refresh_token' => null,
            'open_banking_token_expires_at' => null,
        ]);

        // Optionally revoke tokens on bank side
        try {
            Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->post("{$this->baseUrl}/revoke", [
                    'token' => $company->open_banking_access_token,
                ]);
        } catch (\Exception $e) {
            Log::warning('Failed to revoke Open Banking token', [
                'error' => $e->getMessage(),
            ]);
        }

        return true;
    }

    /**
     * Get supported banks
     */
    public function getSupportedBanks(): array
    {
        return [
            [
                'code' => 'bnp_be',
                'name' => 'BNP Paribas Fortis',
                'country' => 'BE',
                'logo' => '/images/banks/bnp.png',
            ],
            [
                'code' => 'kbc_be',
                'name' => 'KBC Bank',
                'country' => 'BE',
                'logo' => '/images/banks/kbc.png',
            ],
            [
                'code' => 'belfius_be',
                'name' => 'Belfius',
                'country' => 'BE',
                'logo' => '/images/banks/belfius.png',
            ],
            [
                'code' => 'ing_be',
                'name' => 'ING Belgium',
                'country' => 'BE',
                'logo' => '/images/banks/ing.png',
            ],
            [
                'code' => 'argenta_be',
                'name' => 'Argenta',
                'country' => 'BE',
                'logo' => '/images/banks/argenta.png',
            ],
        ];
    }
}
