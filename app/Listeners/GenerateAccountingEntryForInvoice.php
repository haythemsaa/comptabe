<?php

namespace App\Listeners;

use App\Events\InvoiceValidated;
use App\Services\Accounting\AccountingEntryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateAccountingEntryForInvoice implements ShouldQueue
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
    public function handle(InvoiceValidated $event): void
    {
        try {
            // Générer l'écriture comptable automatiquement
            $entry = $this->accountingService->generateFromInvoice($event->invoice, true);

            if ($entry) {
                Log::info('Accounting entry auto-generated for invoice', [
                    'invoice_id' => $event->invoice->id,
                    'entry_id' => $entry->id,
                    'status' => $entry->status,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to auto-generate accounting entry for invoice', [
                'invoice_id' => $event->invoice->id,
                'error' => $e->getMessage(),
            ]);

            // Ne pas bloquer le processus principal
            // L'écriture peut être générée manuellement plus tard
        }
    }
}
