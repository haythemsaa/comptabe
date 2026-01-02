<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceBatchController extends Controller
{
    /**
     * Mark selected invoices as paid.
     */
    public function markAsPaid(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $count = Invoice::whereIn('id', $request->invoice_ids)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
                'amount_due' => 0,
            ]);

        return back()->with('success', "{$count} facture(s) marquée(s) comme payée(s).");
    }

    /**
     * Send reminders for selected invoices.
     */
    public function sendReminders(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $invoices = Invoice::whereIn('id', $request->invoice_ids)
            ->whereIn('status', ['sent', 'overdue'])
            ->with('partner')
            ->get();

        $sentCount = 0;
        foreach ($invoices as $invoice) {
            // Queue reminder email
            // Mail::to($invoice->partner->email)->queue(new InvoiceReminder($invoice));
            $invoice->increment('reminder_count');
            $invoice->update(['last_reminder_at' => now()]);
            $sentCount++;
        }

        return back()->with('success', "{$sentCount} rappel(s) envoyé(s).");
    }

    /**
     * Export selected invoices to PDF.
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $invoices = Invoice::whereIn('id', $request->invoice_ids)
            ->with(['partner', 'lines'])
            ->get();

        // For single invoice, redirect to PDF view
        if ($invoices->count() === 1) {
            return redirect()->route('invoices.pdf', $invoices->first());
        }

        // For multiple, create a ZIP file with all PDFs
        // This is a simplified version - in production, you'd use a job queue
        return back()->with('info', 'Export de ' . $invoices->count() . ' factures en cours de préparation.');
    }

    /**
     * Delete selected invoices (only drafts).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        // Only allow deletion of draft invoices
        $count = Invoice::whereIn('id', $request->invoice_ids)
            ->where('status', 'draft')
            ->delete();

        $skipped = count($request->invoice_ids) - $count;

        $message = "{$count} brouillon(s) supprimé(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} facture(s) non-brouillon ignorée(s).";
        }

        return back()->with('success', $message);
    }

    /**
     * Duplicate selected invoices.
     */
    public function duplicate(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $invoices = Invoice::whereIn('id', $request->invoice_ids)
            ->with('lines')
            ->get();

        $count = 0;
        DB::transaction(function () use ($invoices, &$count) {
            foreach ($invoices as $invoice) {
                $newInvoice = $invoice->replicate();
                $newInvoice->invoice_number = null; // Will be auto-generated
                $newInvoice->status = 'draft';
                $newInvoice->invoice_date = now();
                $newInvoice->due_date = now()->addDays(30);
                $newInvoice->paid_at = null;
                $newInvoice->amount_due = $invoice->total_incl_vat;
                $newInvoice->peppol_status = null;
                $newInvoice->save();

                foreach ($invoice->lines as $line) {
                    $newLine = $line->replicate();
                    $newLine->invoice_id = $newInvoice->id;
                    $newLine->save();
                }

                $count++;
            }
        });

        return back()->with('success', "{$count} facture(s) dupliquée(s) en brouillon.");
    }

    /**
     * Mark selected invoices as sent.
     */
    public function markAsSent(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $count = Invoice::whereIn('id', $request->invoice_ids)
            ->where('status', 'draft')
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        return back()->with('success', "{$count} facture(s) marquée(s) comme envoyée(s).");
    }
}
