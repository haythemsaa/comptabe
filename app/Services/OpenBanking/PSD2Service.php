<?php

namespace App\Services\OpenBanking;

use App\Models\BankAccount;
use App\Models\BankConnection;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Service d'intégration Open Banking PSD2 pour les banques belges
 *
 * Supporte les principales banques belges via leurs APIs PSD2:
 * - KBC/CBC
 * - BNP Paribas Fortis
 * - ING Belgium
 * - Belfius
 * - Argenta
 * - AXA Bank
 * - Crelan
 */
class PSD2Service
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $redirectUri;

    // Configuration des banques belges
    protected array $bankConfigs = [
        'kbc' => [
            'name' => 'KBC',
            'bic' => 'KREDBEBB',
            'auth_url' => 'https://openbanking.kbc.be/oauth/authorize',
            'token_url' => 'https://openbanking.kbc.be/oauth/token',
            'api_url' => 'https://openbanking.kbc.be/v1',
            'logo' => '/images/banks/kbc.svg',
        ],
        'cbc' => [
            'name' => 'CBC',
            'bic' => 'CABORBE1',
            'auth_url' => 'https://openbanking.cbc.be/oauth/authorize',
            'token_url' => 'https://openbanking.cbc.be/oauth/token',
            'api_url' => 'https://openbanking.cbc.be/v1',
            'logo' => '/images/banks/cbc.svg',
        ],
        'bnp' => [
            'name' => 'BNP Paribas Fortis',
            'bic' => 'GEBABEBB',
            'auth_url' => 'https://api.bnpparibasfortis.be/psd2/oauth2/authorize',
            'token_url' => 'https://api.bnpparibasfortis.be/psd2/oauth2/token',
            'api_url' => 'https://api.bnpparibasfortis.be/psd2/v1',
            'logo' => '/images/banks/bnp.svg',
        ],
        'ing' => [
            'name' => 'ING Belgium',
            'bic' => 'BBRUBEBB',
            'auth_url' => 'https://api.ing.be/oauth2/authorize',
            'token_url' => 'https://api.ing.be/oauth2/token',
            'api_url' => 'https://api.ing.be/v3',
            'logo' => '/images/banks/ing.svg',
        ],
        'belfius' => [
            'name' => 'Belfius',
            'bic' => 'GKCCBEBB',
            'auth_url' => 'https://merchant.belfius.be/openbanking/oauth/authorize',
            'token_url' => 'https://merchant.belfius.be/openbanking/oauth/token',
            'api_url' => 'https://merchant.belfius.be/openbanking/v1',
            'logo' => '/images/banks/belfius.svg',
        ],
        'argenta' => [
            'name' => 'Argenta',
            'bic' => 'ARSPBE22',
            'auth_url' => 'https://openbanking.argenta.be/oauth/authorize',
            'token_url' => 'https://openbanking.argenta.be/oauth/token',
            'api_url' => 'https://openbanking.argenta.be/v1',
            'logo' => '/images/banks/argenta.svg',
        ],
        'axa' => [
            'name' => 'AXA Bank',
            'bic' => 'AXABBE22',
            'auth_url' => 'https://api.axabank.be/psd2/authorize',
            'token_url' => 'https://api.axabank.be/psd2/token',
            'api_url' => 'https://api.axabank.be/psd2/v1',
            'logo' => '/images/banks/axa.svg',
        ],
        'crelan' => [
            'name' => 'Crelan',
            'bic' => 'NICABEBB',
            'auth_url' => 'https://api.crelan.be/psd2/oauth/authorize',
            'token_url' => 'https://api.crelan.be/psd2/oauth/token',
            'api_url' => 'https://api.crelan.be/psd2/v1',
            'logo' => '/images/banks/crelan.svg',
        ],
    ];

    public function __construct()
    {
        $this->clientId = config('services.openbanking.client_id');
        $this->clientSecret = config('services.openbanking.client_secret');
        $this->redirectUri = config('services.openbanking.redirect_uri');
    }

    /**
     * Liste des banques supportées
     */
    public function getSupportedBanks(): array
    {
        return collect($this->bankConfigs)->map(fn($config, $key) => [
            'id' => $key,
            'name' => $config['name'],
            'bic' => $config['bic'],
            'logo' => $config['logo'],
        ])->values()->toArray();
    }

    /**
     * Générer l'URL d'autorisation pour une banque
     */
    public function getAuthorizationUrl(string $bankId, int $companyId): string
    {
        $config = $this->bankConfigs[$bankId] ?? null;

        if (!$config) {
            throw new Exception("Banque non supportée: {$bankId}");
        }

        // État pour la sécurité CSRF
        $state = bin2hex(random_bytes(32));
        Cache::put("psd2_state_{$state}", [
            'bank_id' => $bankId,
            'company_id' => $companyId,
        ], now()->addMinutes(15));

        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'accounts balances transactions',
            'state' => $state,
        ];

        return $config['auth_url'] . '?' . http_build_query($params);
    }

    /**
     * Traiter le callback OAuth
     */
    public function handleCallback(string $code, string $state): BankConnection
    {
        // Vérifier l'état
        $stateData = Cache::pull("psd2_state_{$state}");
        if (!$stateData) {
            throw new Exception('État de session invalide');
        }

        $bankId = $stateData['bank_id'];
        $companyId = $stateData['company_id'];
        $config = $this->bankConfigs[$bankId];

        // Échanger le code contre un token
        $response = Http::asForm()->post($config['token_url'], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            Log::error('PSD2 Token Error', [
                'bank' => $bankId,
                'response' => $response->body(),
            ]);
            throw new Exception('Erreur lors de l\'authentification bancaire');
        }

        $tokens = $response->json();

        // Créer ou mettre à jour la connexion bancaire
        $connection = BankConnection::updateOrCreate(
            [
                'company_id' => $companyId,
                'bank_id' => $bankId,
            ],
            [
                'bank_name' => $config['name'],
                'bic' => $config['bic'],
                'access_token' => Crypt::encrypt($tokens['access_token']),
                'refresh_token' => isset($tokens['refresh_token'])
                    ? Crypt::encrypt($tokens['refresh_token'])
                    : null,
                'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
                'consent_expires_at' => now()->addDays(90), // PSD2 max 90 days
                'status' => 'active',
            ]
        );

        // Synchroniser les comptes
        $this->syncAccounts($connection);

        return $connection;
    }

    /**
     * Rafraîchir le token d'accès
     */
    public function refreshToken(BankConnection $connection): void
    {
        if (!$connection->refresh_token) {
            throw new Exception('Pas de refresh token disponible');
        }

        $config = $this->bankConfigs[$connection->bank_id];

        $response = Http::asForm()->post($config['token_url'], [
            'grant_type' => 'refresh_token',
            'refresh_token' => Crypt::decrypt($connection->refresh_token),
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            $connection->update(['status' => 'expired']);
            throw new Exception('Token refresh failed - reconnexion nécessaire');
        }

        $tokens = $response->json();

        $connection->update([
            'access_token' => Crypt::encrypt($tokens['access_token']),
            'refresh_token' => isset($tokens['refresh_token'])
                ? Crypt::encrypt($tokens['refresh_token'])
                : $connection->refresh_token,
            'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
        ]);
    }

    /**
     * Vérifier et rafraîchir le token si nécessaire
     */
    protected function ensureValidToken(BankConnection $connection): string
    {
        if ($connection->token_expires_at->isPast()) {
            $this->refreshToken($connection);
            $connection->refresh();
        }

        return Crypt::decrypt($connection->access_token);
    }

    /**
     * Synchroniser les comptes bancaires
     */
    public function syncAccounts(BankConnection $connection): array
    {
        $token = $this->ensureValidToken($connection);
        $config = $this->bankConfigs[$connection->bank_id];

        $response = Http::withToken($token)
            ->get("{$config['api_url']}/accounts");

        if (!$response->successful()) {
            Log::error('PSD2 Accounts Error', [
                'connection_id' => $connection->id,
                'response' => $response->body(),
            ]);
            throw new Exception('Erreur lors de la récupération des comptes');
        }

        $accounts = $response->json()['accounts'] ?? [];
        $syncedAccounts = [];

        foreach ($accounts as $accountData) {
            $account = BankAccount::updateOrCreate(
                [
                    'company_id' => $connection->company_id,
                    'iban' => $accountData['iban'],
                ],
                [
                    'bank_connection_id' => $connection->id,
                    'resource_id' => $accountData['resourceId'] ?? $accountData['id'] ?? null,
                    'name' => $accountData['name'] ?? $accountData['product'] ?? 'Compte bancaire',
                    'currency' => $accountData['currency'] ?? 'EUR',
                    'bic' => $connection->bic,
                    'bank_name' => $connection->bank_name,
                    'account_type' => $this->mapAccountType($accountData['cashAccountType'] ?? null),
                    'status' => 'active',
                ]
            );

            // Récupérer le solde
            $this->syncAccountBalance($account, $token, $config);

            $syncedAccounts[] = $account;
        }

        $connection->update(['last_sync_at' => now()]);

        return $syncedAccounts;
    }

    /**
     * Synchroniser le solde d'un compte
     */
    protected function syncAccountBalance(BankAccount $account, string $token, array $config): void
    {
        $resourceId = $account->resource_id;
        if (!$resourceId) return;

        $response = Http::withToken($token)
            ->get("{$config['api_url']}/accounts/{$resourceId}/balances");

        if ($response->successful()) {
            $balances = $response->json()['balances'] ?? [];

            foreach ($balances as $balance) {
                if (in_array($balance['balanceType'], ['closingBooked', 'expected', 'interimAvailable'])) {
                    $account->update([
                        'current_balance' => $balance['balanceAmount']['amount'],
                        'balance_updated_at' => now(),
                    ]);
                    break;
                }
            }
        }
    }

    /**
     * Synchroniser les transactions d'un compte
     */
    public function syncTransactions(BankAccount $account, ?Carbon $dateFrom = null, ?Carbon $dateTo = null): array
    {
        $connection = $account->bankConnection;
        if (!$connection) {
            throw new Exception('Connexion bancaire non trouvée');
        }

        $token = $this->ensureValidToken($connection);
        $config = $this->bankConfigs[$connection->bank_id];

        $dateFrom = $dateFrom ?? now()->subDays(30);
        $dateTo = $dateTo ?? now();

        $params = [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'bookingStatus' => 'booked',
        ];

        $response = Http::withToken($token)
            ->get("{$config['api_url']}/accounts/{$account->resource_id}/transactions", $params);

        if (!$response->successful()) {
            Log::error('PSD2 Transactions Error', [
                'account_id' => $account->id,
                'response' => $response->body(),
            ]);
            throw new Exception('Erreur lors de la récupération des transactions');
        }

        $data = $response->json();
        $transactions = $data['transactions']['booked'] ?? [];
        $imported = [];

        foreach ($transactions as $txData) {
            $transaction = $this->importTransaction($account, $txData);
            if ($transaction) {
                $imported[] = $transaction;
            }
        }

        $account->update(['last_sync_at' => now()]);

        return $imported;
    }

    /**
     * Importer une transaction
     */
    protected function importTransaction(BankAccount $account, array $data): ?BankTransaction
    {
        // Éviter les doublons
        $externalId = $data['transactionId'] ?? $data['entryReference'] ?? null;
        if ($externalId) {
            $existing = BankTransaction::where('bank_account_id', $account->id)
                ->where('external_id', $externalId)
                ->first();

            if ($existing) {
                return null;
            }
        }

        // Parser le montant
        $amount = $data['transactionAmount']['amount'] ?? 0;
        $currency = $data['transactionAmount']['currency'] ?? 'EUR';

        // Extraire les informations de contrepartie
        $counterparty = $this->extractCounterparty($data);

        // Extraire la communication structurée belge
        $structuredComm = $this->extractStructuredCommunication($data);

        return BankTransaction::create([
            'bank_account_id' => $account->id,
            'company_id' => $account->company_id,
            'external_id' => $externalId,
            'date' => Carbon::parse($data['bookingDate'] ?? $data['valueDate']),
            'value_date' => isset($data['valueDate']) ? Carbon::parse($data['valueDate']) : null,
            'amount' => $amount,
            'currency' => $currency,
            'description' => $data['remittanceInformationUnstructured'] ?? $data['additionalInformation'] ?? null,
            'counterparty_name' => $counterparty['name'],
            'counterparty_iban' => $counterparty['iban'],
            'counterparty_bic' => $counterparty['bic'],
            'structured_communication' => $structuredComm,
            'bank_reference' => $data['bankTransactionCode'] ?? null,
            'status' => 'imported',
            'raw_data' => $data,
        ]);
    }

    /**
     * Extraire les informations de contrepartie
     */
    protected function extractCounterparty(array $data): array
    {
        $counterparty = [
            'name' => null,
            'iban' => null,
            'bic' => null,
        ];

        // Créditeur
        if (isset($data['creditorAccount'])) {
            $counterparty['iban'] = $data['creditorAccount']['iban'] ?? null;
            $counterparty['name'] = $data['creditorName'] ?? null;
        }

        // Débiteur
        if (isset($data['debtorAccount'])) {
            $counterparty['iban'] = $data['debtorAccount']['iban'] ?? null;
            $counterparty['name'] = $data['debtorName'] ?? null;
        }

        // BIC
        if (isset($data['creditorAgent'])) {
            $counterparty['bic'] = $data['creditorAgent']['bic'] ?? null;
        } elseif (isset($data['debtorAgent'])) {
            $counterparty['bic'] = $data['debtorAgent']['bic'] ?? null;
        }

        return $counterparty;
    }

    /**
     * Extraire la communication structurée belge
     */
    protected function extractStructuredCommunication(array $data): ?string
    {
        $text = $data['remittanceInformationUnstructured']
            ?? $data['remittanceInformationStructured']
            ?? '';

        // Pattern communication structurée belge: +++xxx/xxxx/xxxxx+++
        if (preg_match('/\+{3}(\d{3}\/\d{4}\/\d{5})\+{3}/', $text, $matches)) {
            return '+++' . $matches[1] . '+++';
        }

        // Pattern alternatif sans +
        if (preg_match('/(\d{3}\/\d{4}\/\d{5})/', $text, $matches)) {
            return '+++' . $matches[1] . '+++';
        }

        return null;
    }

    /**
     * Mapper le type de compte
     */
    protected function mapAccountType(?string $type): string
    {
        return match($type) {
            'CACC' => 'current',
            'SVGS' => 'savings',
            'CARD' => 'card',
            default => 'current',
        };
    }

    /**
     * Initier un paiement (PISP)
     */
    public function initiatePayment(BankConnection $connection, array $paymentData): array
    {
        $token = $this->ensureValidToken($connection);
        $config = $this->bankConfigs[$connection->bank_id];

        $payload = [
            'debtorAccount' => [
                'iban' => $paymentData['debtor_iban'],
            ],
            'instructedAmount' => [
                'amount' => number_format($paymentData['amount'], 2, '.', ''),
                'currency' => $paymentData['currency'] ?? 'EUR',
            ],
            'creditorAccount' => [
                'iban' => $paymentData['creditor_iban'],
            ],
            'creditorName' => $paymentData['creditor_name'],
            'remittanceInformationUnstructured' => $paymentData['communication'] ?? '',
        ];

        // Ajouter communication structurée si présente
        if (isset($paymentData['structured_communication'])) {
            $payload['remittanceInformationStructured'] = [
                'reference' => $paymentData['structured_communication'],
                'referenceType' => 'SCOR',
            ];
        }

        $response = Http::withToken($token)
            ->post("{$config['api_url']}/payments/sepa-credit-transfers", $payload);

        if (!$response->successful()) {
            Log::error('PSD2 Payment Error', [
                'connection_id' => $connection->id,
                'response' => $response->body(),
            ]);
            throw new Exception('Erreur lors de l\'initiation du paiement');
        }

        return $response->json();
    }

    /**
     * Obtenir le statut d'un paiement
     */
    public function getPaymentStatus(BankConnection $connection, string $paymentId): array
    {
        $token = $this->ensureValidToken($connection);
        $config = $this->bankConfigs[$connection->bank_id];

        $response = Http::withToken($token)
            ->get("{$config['api_url']}/payments/sepa-credit-transfers/{$paymentId}/status");

        if (!$response->successful()) {
            throw new Exception('Erreur lors de la récupération du statut');
        }

        return $response->json();
    }

    /**
     * Révoquer une connexion
     */
    public function revokeConnection(BankConnection $connection): void
    {
        // Marquer la connexion comme révoquée
        $connection->update([
            'status' => 'revoked',
            'access_token' => null,
            'refresh_token' => null,
        ]);

        // Les comptes restent dans le système mais ne sont plus synchronisés
        $connection->accounts()->update(['status' => 'disconnected']);
    }

    /**
     * Vérifier l'état de santé de toutes les connexions
     */
    public function healthCheck(int $companyId): array
    {
        $connections = BankConnection::where('company_id', $companyId)->get();
        $results = [];

        foreach ($connections as $connection) {
            $status = 'ok';
            $message = null;

            if ($connection->status !== 'active') {
                $status = 'inactive';
                $message = 'Connexion inactive';
            } elseif ($connection->consent_expires_at?->isPast()) {
                $status = 'expired';
                $message = 'Consentement expiré - reconnexion nécessaire';
            } elseif ($connection->consent_expires_at?->diffInDays(now()) < 7) {
                $status = 'warning';
                $message = 'Consentement expire bientôt';
            }

            $results[$connection->bank_id] = [
                'connection_id' => $connection->id,
                'bank_name' => $connection->bank_name,
                'status' => $status,
                'message' => $message,
                'last_sync' => $connection->last_sync_at?->diffForHumans(),
                'consent_expires' => $connection->consent_expires_at?->format('d/m/Y'),
                'accounts_count' => $connection->accounts()->count(),
            ];
        }

        return $results;
    }
}
