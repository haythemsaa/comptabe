<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminExportController extends Controller
{
    public function index()
    {
        $exports = [
            'companies' => [
                'label' => 'Entreprises',
                'description' => 'Liste complete des entreprises avec leurs informations',
                'count' => Company::count(),
                'icon' => 'building',
            ],
            'users' => [
                'label' => 'Utilisateurs',
                'description' => 'Liste complete des utilisateurs et leurs roles',
                'count' => User::count(),
                'icon' => 'users',
            ],
            'subscriptions' => [
                'label' => 'Abonnements',
                'description' => 'Tous les abonnements actifs et historique',
                'count' => Subscription::count(),
                'icon' => 'credit-card',
            ],
            'subscription_invoices' => [
                'label' => 'Factures d\'abonnement',
                'description' => 'Toutes les factures de la plateforme',
                'count' => SubscriptionInvoice::count(),
                'icon' => 'document',
            ],
            'invoices' => [
                'label' => 'Factures clients',
                'description' => 'Toutes les factures creees par les entreprises',
                'count' => Invoice::count(),
                'icon' => 'receipt',
            ],
            'audit_logs' => [
                'label' => 'Logs d\'audit',
                'description' => 'Historique complet des actions',
                'count' => AuditLog::count(),
                'icon' => 'clipboard-list',
            ],
        ];

        return view('admin.exports.index', compact('exports'));
    }

    public function export(Request $request, string $type)
    {
        $format = $request->get('format', 'csv');

        $data = match ($type) {
            'companies' => $this->exportCompanies(),
            'users' => $this->exportUsers(),
            'subscriptions' => $this->exportSubscriptions(),
            'subscription_invoices' => $this->exportSubscriptionInvoices(),
            'invoices' => $this->exportInvoices(),
            'audit_logs' => $this->exportAuditLogs(),
            default => abort(404),
        };

        $filename = "{$type}_" . now()->format('Y-m-d_His');

        if ($format === 'json') {
            return Response::json($data['rows'])
                ->header('Content-Disposition', "attachment; filename={$filename}.json");
        }

        // CSV Export
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            fputcsv($file, $data['headers'], ';');

            // Data
            foreach ($data['rows'] as $row) {
                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}.csv",
        ]);
    }

    private function exportCompanies()
    {
        $headers = ['ID', 'Nom', 'Email', 'TVA', 'Adresse', 'Code Postal', 'Ville', 'Pays', 'Telephone', 'Utilisateurs', 'Factures', 'Abonnement', 'Statut Abonnement', 'Cree le'];

        $rows = Company::with(['users', 'subscription.plan'])
            ->withCount(['users', 'invoices'])
            ->get()
            ->map(function ($company) {
                return [
                    $company->id,
                    $company->name,
                    $company->email,
                    $company->vat_number,
                    $company->address,
                    $company->postal_code,
                    $company->city,
                    $company->country,
                    $company->phone,
                    $company->users_count,
                    $company->invoices_count,
                    $company->subscription?->plan?->name ?? 'Aucun',
                    $company->subscription?->status ?? '-',
                    $company->created_at->format('d/m/Y H:i'),
                ];
            })
            ->toArray();

        return compact('headers', 'rows');
    }

    private function exportUsers()
    {
        $headers = ['ID', 'Prenom', 'Nom', 'Email', 'Entreprises', 'Roles', 'Superadmin', 'Email Verifie', 'Derniere Connexion', 'Cree le'];

        $rows = User::with('companies')
            ->get()
            ->map(function ($user) {
                $companies = $user->companies->pluck('name')->join(', ');
                $roles = $user->companies->pluck('pivot.role')->unique()->join(', ');

                return [
                    $user->id,
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $companies ?: '-',
                    $roles ?: '-',
                    $user->is_superadmin ? 'Oui' : 'Non',
                    $user->email_verified_at ? 'Oui' : 'Non',
                    $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais',
                    $user->created_at->format('d/m/Y H:i'),
                ];
            })
            ->toArray();

        return compact('headers', 'rows');
    }

    private function exportSubscriptions()
    {
        $headers = ['ID', 'Entreprise', 'Plan', 'Statut', 'Montant', 'Cycle', 'Date Debut', 'Fin Essai', 'Prochaine Facturation', 'Cree le'];

        $rows = Subscription::with(['company', 'plan'])
            ->get()
            ->map(function ($sub) {
                return [
                    $sub->id,
                    $sub->company?->name ?? 'N/A',
                    $sub->plan?->name ?? 'N/A',
                    $sub->status,
                    number_format($sub->amount, 2) . ' EUR',
                    $sub->billing_cycle,
                    $sub->starts_at?->format('d/m/Y') ?? '-',
                    $sub->trial_ends_at?->format('d/m/Y') ?? '-',
                    $sub->next_billing_date?->format('d/m/Y') ?? '-',
                    $sub->created_at->format('d/m/Y H:i'),
                ];
            })
            ->toArray();

        return compact('headers', 'rows');
    }

    private function exportSubscriptionInvoices()
    {
        $headers = ['ID', 'Numero', 'Entreprise', 'Montant HT', 'TVA', 'Total TTC', 'Statut', 'Date Emission', 'Date Echeance', 'Date Paiement'];

        $rows = SubscriptionInvoice::with('company')
            ->get()
            ->map(function ($invoice) {
                return [
                    $invoice->id,
                    $invoice->number,
                    $invoice->company?->name ?? 'N/A',
                    number_format($invoice->subtotal, 2) . ' EUR',
                    number_format($invoice->tax, 2) . ' EUR',
                    number_format($invoice->total, 2) . ' EUR',
                    $invoice->status,
                    $invoice->issued_at?->format('d/m/Y') ?? '-',
                    $invoice->due_date?->format('d/m/Y') ?? '-',
                    $invoice->paid_at?->format('d/m/Y') ?? '-',
                ];
            })
            ->toArray();

        return compact('headers', 'rows');
    }

    private function exportInvoices()
    {
        $headers = ['ID', 'Numero', 'Entreprise', 'Client', 'Montant HT', 'TVA', 'Total TTC', 'Statut', 'Date', 'Date Echeance'];

        $rows = Invoice::with(['company', 'client'])
            ->take(10000) // Limit for large datasets
            ->get()
            ->map(function ($invoice) {
                return [
                    $invoice->id,
                    $invoice->number,
                    $invoice->company?->name ?? 'N/A',
                    $invoice->client?->name ?? 'N/A',
                    number_format($invoice->subtotal ?? 0, 2) . ' EUR',
                    number_format($invoice->tax_amount ?? 0, 2) . ' EUR',
                    number_format($invoice->total ?? 0, 2) . ' EUR',
                    $invoice->status,
                    $invoice->date?->format('d/m/Y') ?? '-',
                    $invoice->due_date?->format('d/m/Y') ?? '-',
                ];
            })
            ->toArray();

        return compact('headers', 'rows');
    }

    private function exportAuditLogs()
    {
        $headers = ['ID', 'Action', 'Description', 'Utilisateur', 'Entreprise', 'IP', 'Date'];

        $rows = AuditLog::with(['user', 'company'])
            ->orderByDesc('created_at')
            ->take(10000) // Limit for large datasets
            ->get()
            ->map(function ($log) {
                return [
                    $log->id,
                    $log->action,
                    $log->description,
                    $log->user?->full_name ?? 'Systeme',
                    $log->company?->name ?? '-',
                    $log->ip_address ?? '-',
                    $log->created_at->format('d/m/Y H:i:s'),
                ];
            })
            ->toArray();

        return compact('headers', 'rows');
    }
}
