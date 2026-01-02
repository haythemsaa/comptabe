<?php

namespace App\Listeners;

use App\Events\PaymentCreated;
use App\Services\Accounting\AccountingEntryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateAccountingEntryForPayment implements ShouldQueue
{
    use InteractsWithQueue;

    protected AccountingEntryService $accountingService;

    /**
     * Create the event listener.
     */
    public function __construct(AccountingEntryService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentCreated $event): void
    {
        try {
            // Générer l'écriture comptable de paiement automatiquement
            $entry = $this->accountingService->generateFromPayment($event->payment, true);

            if ($entry) {
                Log::info('Accounting entry auto-generated for payment', [
                    'payment_id' => $event->payment->id,
                    'entry_id' => $entry->id,
                    'status' => $entry->status,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to auto-generate accounting entry for payment', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
            ]);

            // Ne pas bloquer le processus principal
        }
    }
}
