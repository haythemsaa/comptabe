<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PartnerApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\BankApiController;
use App\Http\Controllers\Api\PeppolApiController;
use App\Http\Controllers\Api\V1\InvoiceApiController as InvoiceApiV1;
use App\Http\Controllers\Api\V1\PartnerApiController as PartnerApiV1;
use App\Http\Controllers\Api\V1\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API REST pour ComptaBE
| v1: API complète avec webhooks
|
*/

// Health check (rate limited)
Route::middleware('throttle:api-public')->get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Webhook events (public, rate limited)
Route::middleware('throttle:api-public')->get('/v1/webhooks/events', [WebhookController::class, 'events']);

/*
|--------------------------------------------------------------------------
| API V1 - Full REST API with Webhooks
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v1')->group(function () {

    // Current user
    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()->only(['id', 'name', 'email']),
                'company_id' => $request->user()->current_company_id,
            ],
        ]);
    });

    // ===== INVOICES =====
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceApiV1::class, 'index']);
        Route::post('/', [InvoiceApiV1::class, 'store']);
        Route::get('/{invoice}', [InvoiceApiV1::class, 'show']);
        Route::put('/{invoice}', [InvoiceApiV1::class, 'update']);
        Route::delete('/{invoice}', [InvoiceApiV1::class, 'destroy']);
        Route::post('/{invoice}/validate', [InvoiceApiV1::class, 'markAsValidated']);
        Route::post('/{invoice}/payments', [InvoiceApiV1::class, 'recordPayment']);
        Route::get('/{invoice}/pdf', [InvoiceApiV1::class, 'downloadPdf']);
        Route::get('/{invoice}/ubl', [InvoiceApiV1::class, 'downloadUbl']);
    });

    // ===== PARTNERS =====
    Route::prefix('partners')->group(function () {
        Route::get('/', [PartnerApiV1::class, 'index']);
        Route::post('/', [PartnerApiV1::class, 'store']);
        Route::get('/{partner}', [PartnerApiV1::class, 'show']);
        Route::put('/{partner}', [PartnerApiV1::class, 'update']);
        Route::delete('/{partner}', [PartnerApiV1::class, 'destroy']);
        Route::post('/verify-vat', [PartnerApiV1::class, 'verifyVat']);
    });

    // ===== WEBHOOKS =====
    Route::prefix('webhooks')->group(function () {
        Route::get('/', [WebhookController::class, 'index']);
        Route::post('/', [WebhookController::class, 'store']);
        Route::get('/{webhook}', [WebhookController::class, 'show']);
        Route::put('/{webhook}', [WebhookController::class, 'update']);
        Route::delete('/{webhook}', [WebhookController::class, 'destroy']);
        Route::post('/{webhook}/regenerate-secret', [WebhookController::class, 'regenerateSecret']);
        Route::post('/{webhook}/ping', [WebhookController::class, 'ping']);
        Route::get('/{webhook}/deliveries', [WebhookController::class, 'deliveries']);
        Route::get('/{webhook}/deliveries/{delivery}', [WebhookController::class, 'deliveryDetail']);
        Route::post('/{webhook}/deliveries/{delivery}/retry', [WebhookController::class, 'retry']);
    });

    // ===== BANK =====
    Route::prefix('bank')->group(function () {
        Route::get('/accounts', function (\Illuminate\Http\Request $request) {
            $accounts = \App\Models\BankAccount::where('company_id', $request->user()->current_company_id)
                ->with('bankConnection:id,bank_name,status')
                ->get();

            return response()->json(['success' => true, 'data' => $accounts]);
        });

        Route::get('/accounts/{account}/transactions', function (\Illuminate\Http\Request $request, \App\Models\BankAccount $account) {
            if ($account->company_id !== $request->user()->current_company_id) {
                abort(403);
            }

            $query = $account->transactions()->orderByDesc('date');

            if ($request->filled('from')) {
                $query->whereDate('date', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $query->whereDate('date', '<=', $request->to);
            }

            $transactions = $query->paginate($request->integer('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'total' => $transactions->total(),
                ],
            ]);
        });

        Route::get('/balance', function (\Illuminate\Http\Request $request) {
            $totalBalance = \App\Models\BankAccount::where('company_id', $request->user()->current_company_id)
                ->where('status', 'active')
                ->sum('current_balance');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_balance' => $totalBalance,
                    'currency' => 'EUR',
                ],
            ]);
        });

        // Réconciliation bancaire automatique IA
        Route::prefix('reconciliation')->controller(\App\Http\Controllers\BankReconciliationController::class)->group(function () {
            Route::get('/pending', 'getPending')->name('api.reconciliation.pending');
            Route::get('/suggestions/{transaction}', 'getSuggestions')->name('api.reconciliation.suggestions');
            Route::get('/transaction/{transaction}', 'getTransactionDetails')->name('api.reconciliation.transaction');
            Route::post('/reconcile', 'reconcile')->name('api.reconciliation.reconcile');
            Route::delete('/unreconcile/{transaction}', 'unreconcile')->name('api.reconciliation.unreconcile');
            Route::post('/batch', 'batchReconcile')->name('api.reconciliation.batch');
            Route::get('/stats', 'getStats')->name('api.reconciliation.stats');
        });
    });

    // ===== ANALYTICS =====
    Route::prefix('analytics')->group(function () {
        Route::get('/summary', function (\Illuminate\Http\Request $request) {
            $companyId = $request->user()->current_company_id;
            $year = $request->integer('year', now()->year);

            $revenue = \App\Models\Invoice::where('company_id', $companyId)
                ->where('type', 'sale')
                ->whereYear('issue_date', $year)
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->sum('total_amount');

            $expenses = \App\Models\Invoice::where('company_id', $companyId)
                ->where('type', 'purchase')
                ->whereYear('issue_date', $year)
                ->sum('total_amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'year' => $year,
                    'revenue' => $revenue,
                    'expenses' => $expenses,
                    'profit' => $revenue - $expenses,
                    'margin' => $revenue > 0 ? round((($revenue - $expenses) / $revenue) * 100, 1) : 0,
                ],
            ]);
        });
    });

    // ===== TREASURY =====
    Route::prefix('treasury')->group(function () {
        Route::get('/forecast', function (\Illuminate\Http\Request $request) {
            $service = app(\App\Services\AI\TreasuryForecastService::class);
            $days = min(365, max(7, $request->integer('days', 90)));
            $forecast = $service->generateForecast($days);

            return response()->json(['success' => true, 'data' => $forecast]);
        });
    });

    // ===== VAT DECLARATIONS =====
    Route::prefix('vat')->group(function () {
        Route::prefix('declarations')->controller(\App\Http\Controllers\VatDeclarationController::class)->group(function () {
            Route::get('/', 'apiIndex');
            Route::get('/{declaration}', 'apiShow');
            Route::post('/generate', 'apiGenerate');
            Route::post('/{declaration}/submit', 'apiSubmit');
        });

        Route::get('/stats', [\App\Http\Controllers\VatDeclarationController::class, 'apiStats']);
    });

    // ===== API TOKENS ===== (sensitive operations have stricter rate limit)
    Route::prefix('tokens')->group(function () {
        Route::get('/', function (\Illuminate\Http\Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()->tokens,
            ]);
        });

        Route::middleware('throttle:api-sensitive')->post('/', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'abilities' => 'nullable|array',
            ]);

            $token = $request->user()->createToken(
                $request->name,
                $request->abilities ?? ['*']
            );

            return response()->json([
                'success' => true,
                'data' => ['token' => $token->plainTextToken],
                'message' => 'Token créé. Conservez-le précieusement.',
            ], 201);
        });

        Route::middleware('throttle:api-sensitive')->delete('/{tokenId}', function (\Illuminate\Http\Request $request, $tokenId) {
            $request->user()->tokens()->where('id', $tokenId)->delete();

            return response()->json(['success' => true, 'message' => 'Token révoqué.']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Legacy API (backward compatibility)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'tenant', 'throttle:api'])->group(function () {
    // Partners API
    Route::prefix('partners')->group(function () {
        Route::get('/search', [PartnerApiController::class, 'search']);
        Route::get('/lookup', [PartnerApiController::class, 'lookupByVat']);
        Route::get('/{partner}/peppol-status', [PartnerApiController::class, 'peppolStatus']);
    });

    // Invoices API
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceApiController::class, 'index']);
        Route::get('/{invoice}/lines', [InvoiceApiController::class, 'lines']);
        Route::post('/{invoice}/lines', [InvoiceApiController::class, 'addLine']);
        Route::put('/{invoice}/lines/{line}', [InvoiceApiController::class, 'updateLine']);
        Route::delete('/{invoice}/lines/{line}', [InvoiceApiController::class, 'deleteLine']);
    });

    // Bank API
    Route::prefix('bank')->group(function () {
        Route::get('/transactions', [BankApiController::class, 'transactions']);
        Route::get('/transactions/{transaction}/suggestions', [BankApiController::class, 'matchSuggestions']);
        Route::post('/transactions/{transaction}/match', [BankApiController::class, 'match']);
        Route::post('/transactions/{transaction}/ignore', [BankApiController::class, 'ignore']);
    });

    // Peppol API
    Route::prefix('peppol')->group(function () {
        Route::get('/lookup/{participantId}', [PeppolApiController::class, 'lookup']);
        Route::get('/inbox', [PeppolApiController::class, 'inbox']);
        Route::post('/send/{invoice}', [PeppolApiController::class, 'send']);
    });

    // Dashboard stats
    Route::get('/dashboard/stats', function () {
        return response()->json([
            'receivables' => \App\Models\Invoice::sales()->unpaid()->sum('amount_due'),
            'payables' => \App\Models\Invoice::purchases()->unpaid()->sum('amount_due'),
            'overdue_count' => \App\Models\Invoice::overdue()->count(),
            'pending_reconciliation' => \App\Models\BankTransaction::pending()->count(),
        ]);
    });

    // Chat AI Assistant
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [\App\Http\Controllers\ChatController::class, 'index']);
        Route::get('/conversations/{conversation}', [\App\Http\Controllers\ChatController::class, 'show']);
        Route::post('/send', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
        Route::delete('/conversations/{conversation}', [\App\Http\Controllers\ChatController::class, 'destroy']);
        Route::post('/tools/{execution}/confirm', [\App\Http\Controllers\ChatController::class, 'confirmTool']);
    });

    // Notifications & Alerts
    Route::prefix('notifications')->controller(\App\Http\Controllers\NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/unread-count', 'unreadCount');
        Route::get('/statistics', 'statistics');
        Route::post('/{id}/mark-as-read', 'markAsRead');
        Route::post('/mark-all-as-read', 'markAllAsRead');
        Route::delete('/{id}', 'destroy');
        Route::delete('/read/all', 'deleteAllRead');
        Route::post('/test', 'test'); // Admin only - test notification system
    });

    // Firm/Cabinet Dashboard (Multi-Client Management)
    Route::prefix('firm')->controller(\App\Http\Controllers\Firm\FirmDashboardController::class)->group(function () {
        Route::get('/clients', 'apiGetClients');
        Route::get('/statistics', 'apiGetStatistics');
    });
});

// Public API (webhook endpoints) - higher rate limit for integration needs
Route::middleware('throttle:webhooks')->prefix('webhooks')->group(function () {
    Route::post('/peppol', [PeppolApiController::class, 'webhook'])->name('peppol.webhook');
    // Company-specific Peppol webhook with secret validation
    Route::post('/peppol/{secret}', [PeppolApiController::class, 'companyWebhook'])->name('peppol.webhook.company');
});
