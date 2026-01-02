<?php

namespace App\Observers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;

class InvoiceObserver
{
    /**
     * Clear dashboard cache for the invoice's company.
     */
    private function clearDashboardCache(Invoice $invoice): void
    {
        $companyId = $invoice->company_id;

        // Clear all dashboard related caches
        $keysToForget = [
            "dashboard:{$companyId}:metrics",
            "dashboard:{$companyId}:revenue_chart",
            "dashboard:{$companyId}:cash_flow",
        ];

        foreach ($keysToForget as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $this->clearDashboardCache($invoice);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $this->clearDashboardCache($invoice);
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        $this->clearDashboardCache($invoice);
    }
}
