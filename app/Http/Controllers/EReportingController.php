<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EReportingSubmission;
use App\Models\Invoice;
use App\Services\Peppol\EReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EReportingController extends Controller
{
    protected EReportingService $service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new EReportingService();
    }

    /**
     * Display e-Reporting dashboard.
     */
    public function index()
    {
        $company = Company::current();

        $submissions = EReportingSubmission::where('company_id', $company->id)
            ->with('invoice')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statistics = $this->service->getStatistics('month');
        $pendingCount = EReportingSubmission::where('company_id', $company->id)
            ->pending()
            ->count();

        // Get invoices that need e-Reporting
        $pendingInvoices = Invoice::where('company_id', $company->id)
            ->whereNull('ereporting_submitted_at')
            ->where('status', 'validated')
            ->where(function ($q) {
                $q->where('type', 'out')
                    ->orWhere('type', 'in');
            })
            ->whereHas('partner', function ($q) {
                $q->where('country_code', 'BE')
                    ->where(function ($q2) {
                        $q2->whereNotNull('vat_number')
                            ->orWhereNotNull('enterprise_number');
                    });
            })
            ->count();

        return view('ereporting.index', compact(
            'submissions',
            'statistics',
            'pendingCount',
            'pendingInvoices'
        ));
    }

    /**
     * Submit a single invoice for e-Reporting.
     */
    public function submit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        try {
            // Check if already submitted
            if ($invoice->ereporting_submitted_at) {
                return back()->with('error', 'Cette facture a déjà été soumise à l\'e-Reporting.');
            }

            // Check if e-Reporting is required/applicable
            if (!$this->service->isEReportingRequired($invoice)) {
                return back()->with('error', 'L\'e-Reporting n\'est pas requis pour cette facture.');
            }

            $submission = $this->service->submitInvoice($invoice);

            return back()->with('success', 'Facture soumise à l\'e-Reporting avec succès. Référence: ' . $submission->government_reference);

        } catch (\Exception $e) {
            Log::error('E-Reporting submission failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur lors de la soumission: ' . $e->getMessage());
        }
    }

    /**
     * Batch submit multiple invoices.
     */
    public function batchSubmit(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $company = Company::current();

        // Verify all invoices belong to current company
        $invoiceIds = Invoice::whereIn('id', $request->invoice_ids)
            ->where('company_id', $company->id)
            ->pluck('id')
            ->toArray();

        if (empty($invoiceIds)) {
            return back()->with('error', 'Aucune facture valide sélectionnée.');
        }

        try {
            $results = $this->service->batchSubmit($invoiceIds);

            $successful = collect($results)->where('success', true)->count();
            $failed = collect($results)->where('success', false)->count();

            $message = "$successful facture(s) soumise(s) avec succès.";
            if ($failed > 0) {
                $message .= " $failed facture(s) en erreur.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la soumission: ' . $e->getMessage());
        }
    }

    /**
     * Check status of a submission.
     */
    public function checkStatus(EReportingSubmission $submission)
    {
        $company = Company::current();

        if ($submission->company_id !== $company->id) {
            abort(403);
        }

        try {
            $status = $this->service->checkStatus($submission);

            return response()->json([
                'success' => true,
                'status' => $status,
                'status_label' => $submission->fresh()->status_label,
                'status_color' => $submission->fresh()->status_color,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View submission details.
     */
    public function show(EReportingSubmission $submission)
    {
        $company = Company::current();

        if ($submission->company_id !== $company->id) {
            abort(403);
        }

        $submission->load('invoice.partner');

        return view('ereporting.show', compact('submission'));
    }

    /**
     * Generate compliance report.
     */
    public function complianceReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $report = $this->service->generateComplianceReport(
                $request->start_date,
                $request->end_date
            );

            if ($request->wantsJson()) {
                return response()->json($report);
            }

            return view('ereporting.compliance-report', compact('report'));

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération du rapport: ' . $e->getMessage());
        }
    }

    /**
     * E-Reporting settings page.
     */
    public function settings()
    {
        $company = Company::current();

        return view('ereporting.settings', compact('company'));
    }

    /**
     * Update e-Reporting settings.
     */
    public function updateSettings(Request $request)
    {
        $company = Company::current();

        $validated = $request->validate([
            'ereporting_enabled' => 'boolean',
            'ereporting_test_mode' => 'boolean',
            'ereporting_api_key' => 'nullable|string|max:255',
            'ereporting_certificate_id' => 'nullable|string|max:255',
        ]);

        $company->update($validated);

        return back()->with('success', 'Paramètres e-Reporting mis à jour.');
    }

    /**
     * Get e-Reporting statistics API.
     */
    public function statistics(Request $request)
    {
        $period = $request->get('period', 'month');

        if (!in_array($period, ['week', 'month', 'quarter', 'year'])) {
            $period = 'month';
        }

        $statistics = $this->service->getStatistics($period);

        return response()->json($statistics);
    }

    /**
     * List invoices pending e-Reporting.
     */
    public function pendingInvoices()
    {
        $company = Company::current();

        $invoices = Invoice::where('company_id', $company->id)
            ->whereNull('ereporting_submitted_at')
            ->where('status', 'validated')
            ->where(function ($q) {
                $q->where('type', 'out')
                    ->orWhere('type', 'in');
            })
            ->whereHas('partner', function ($q) {
                $q->where('country_code', 'BE')
                    ->where(function ($q2) {
                        $q2->whereNotNull('vat_number')
                            ->orWhereNotNull('enterprise_number');
                    });
            })
            ->with('partner')
            ->orderBy('invoice_date', 'desc')
            ->paginate(20);

        return view('ereporting.pending-invoices', compact('invoices'));
    }

    /**
     * Retry failed submission.
     */
    public function retry(EReportingSubmission $submission)
    {
        $company = Company::current();

        if ($submission->company_id !== $company->id) {
            abort(403);
        }

        if (!$submission->isFailed()) {
            return back()->with('error', 'Seules les soumissions échouées peuvent être réessayées.');
        }

        if (!$submission->invoice) {
            return back()->with('error', 'La facture associée n\'existe plus.');
        }

        try {
            // Reset invoice status
            $submission->invoice->update([
                'ereporting_status' => null,
                'ereporting_submitted_at' => null,
                'ereporting_reference' => null,
                'ereporting_error' => null,
            ]);

            // Delete old submission
            $submission->delete();

            // Create new submission
            $newSubmission = $this->service->submitInvoice($submission->invoice);

            return back()->with('success', 'Soumission réessayée avec succès. Nouvelle référence: ' . $newSubmission->government_reference);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la nouvelle soumission: ' . $e->getMessage());
        }
    }
}
