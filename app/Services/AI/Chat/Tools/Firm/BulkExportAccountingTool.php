<?php

namespace App\Services\AI\Chat\Tools\Firm;

use App\Models\ClientMandate;
use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BulkExportAccountingTool extends AbstractTool
{
    public function getName(): string
    {
        return 'bulk_export_accounting';
    }

    public function getDescription(): string
    {
        return 'For accounting firms: exports accounting data (invoices, VAT) for multiple client companies at once. Creates individual export files per client and packages them in a ZIP. Use this for end-of-period processing or when you need exports for all clients.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'client_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Array of company IDs to export. Leave empty to export all active clients.',
                ],
                'format' => [
                    'type' => 'string',
                    'enum' => ['winbooks', 'winauditor', 'octopus', 'csv'],
                    'description' => 'Export format (default: csv)',
                ],
                'period_from' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Start date for export (YYYY-MM-DD)',
                ],
                'period_to' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'End date for export (YYYY-MM-DD)',
                ],
                'include_sales' => [
                    'type' => 'boolean',
                    'description' => 'Include sales invoices (default: true)',
                ],
                'include_purchases' => [
                    'type' => 'boolean',
                    'description' => 'Include purchase invoices (default: true)',
                ],
            ],
            'required' => ['period_from', 'period_to'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true; // Bulk operations should be confirmed
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate user is a firm member
        if (!$context->user->isCabinetMember()) {
            return [
                'error' => 'Cet outil est réservé aux membres de cabinets comptables.',
            ];
        }

        $firm = $context->user->currentFirm();

        if (!$firm) {
            return [
                'error' => 'Aucun cabinet comptable trouvé.',
            ];
        }

        // Check if user can manage clients (requires admin/manager role)
        $firmUser = $firm->users()->where('user_id', $context->user->id)->first();
        if (!$firmUser || !$firmUser->pivot->canManageClients()) {
            return [
                'error' => 'Vous devez être admin ou manager du cabinet pour effectuer des exports groupés.',
                'your_role' => $firmUser?->pivot->role ?? 'unknown',
            ];
        }

        $format = $input['format'] ?? 'csv';
        $includeSales = $input['include_sales'] ?? true;
        $includePurchases = $input['include_purchases'] ?? true;

        // Get client mandates
        $query = ClientMandate::where('accounting_firm_id', $firm->id)
            ->where('status', 'active')
            ->with('company');

        // Filter by specific clients if provided
        if (!empty($input['client_ids'])) {
            $query->whereIn('company_id', $input['client_ids']);
        }

        $mandates = $query->get();

        if ($mandates->isEmpty()) {
            return [
                'warning' => 'Aucun client actif trouvé pour cet export.',
                'firm' => $firm->name,
            ];
        }

        // Create temporary directory for exports
        $exportDir = 'exports/bulk_' . now()->format('Ymd_His') . '_' . Str::random(8);
        Storage::disk('local')->makeDirectory($exportDir);

        $exportResults = [];
        $totalInvoices = 0;

        // Export each client
        foreach ($mandates as $mandate) {
            $company = $mandate->company;

            try {
                // Build query for invoices
                $query = Invoice::where('company_id', $company->id)
                    ->whereBetween('invoice_date', [$input['period_from'], $input['period_to']])
                    ->whereIn('status', ['validated', 'sent', 'paid']);

                // Filter by type
                if ($includeSales && !$includePurchases) {
                    $query->where('type', 'sale');
                } elseif (!$includeSales && $includePurchases) {
                    $query->where('type', 'purchase');
                }

                $invoices = $query->orderBy('invoice_date')->get();

                if ($invoices->isEmpty()) {
                    $exportResults[] = [
                        'company_name' => $company->name,
                        'status' => 'skipped',
                        'reason' => 'Aucune facture pour la période',
                    ];
                    continue;
                }

                // Generate export using the same logic as ExportAccountingDataTool
                $exportData = $this->generateExport($invoices, $format, $company);

                // Save file
                $filename = sprintf(
                    '%s_%s_%s.%s',
                    Str::slug($company->name),
                    now()->format('Ymd'),
                    $company->vat_number ?? 'NO_VAT',
                    $exportData['extension']
                );

                Storage::disk('local')->put("{$exportDir}/{$filename}", $exportData['content']);

                $exportResults[] = [
                    'company_name' => $company->name,
                    'company_id' => $company->id,
                    'status' => 'success',
                    'filename' => $filename,
                    'invoices_count' => $invoices->count(),
                    'total_amount' => (float) $invoices->sum('total_incl_vat'),
                ];

                $totalInvoices += $invoices->count();

            } catch (\Exception $e) {
                $exportResults[] = [
                    'company_name' => $company->name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Create ZIP archive
        $zipFilename = "export_bulk_{$firm->id}_" . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path("app/{$exportDir}/{$zipFilename}");

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            // Add all export files to ZIP
            foreach ($exportResults as $result) {
                if ($result['status'] === 'success') {
                    $filePath = storage_path("app/{$exportDir}/{$result['filename']}");
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, $result['filename']);
                    }
                }
            }

            // Add summary file
            $summary = $this->generateSummaryFile($exportResults, $input, $firm);
            $zip->addFromString('_SUMMARY.txt', $summary);

            $zip->close();
        }

        // Generate download URL
        $zipUrl = Storage::disk('local')->url("{$exportDir}/{$zipFilename}");

        // Count successes/failures
        $successCount = collect($exportResults)->where('status', 'success')->count();
        $errorCount = collect($exportResults)->where('status', 'error')->count();
        $skippedCount = collect($exportResults)->where('status', 'skipped')->count();

        return [
            'success' => true,
            'message' => "Export groupé terminé : {$successCount} clients exportés",
            'firm' => [
                'name' => $firm->name,
                'id' => $firm->id,
            ],
            'period' => [
                'from' => $input['period_from'],
                'to' => $input['period_to'],
            ],
            'format' => $format,
            'stats' => [
                'total_clients' => $mandates->count(),
                'exported' => $successCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
                'total_invoices' => $totalInvoices,
            ],
            'zip_file' => [
                'filename' => $zipFilename,
                'url' => $zipUrl,
                'size' => file_exists($zipPath) ? filesize($zipPath) : 0,
            ],
            'results' => $exportResults,
            'next_steps' => [
                'Téléchargez le fichier ZIP via le lien fourni',
                'Extrayez les fichiers et importez-les dans votre logiciel comptable',
                'Vérifiez le fichier _SUMMARY.txt pour les détails',
                'Le ZIP sera supprimé automatiquement après 24h',
            ],
        ];
    }

    /**
     * Generate export in specified format (reusing ExportAccountingDataTool logic).
     */
    protected function generateExport($invoices, string $format, $company): array
    {
        return match ($format) {
            'winbooks' => $this->generateWinbooksExport($invoices),
            'winauditor' => $this->generateWinbooksExport($invoices), // Same format
            'octopus' => $this->generateOctopusExport($invoices),
            default => $this->generateCSVExport($invoices),
        };
    }

    /**
     * Generate CSV export.
     */
    protected function generateCSVExport($invoices): array
    {
        $csv = [];

        // Header
        $csv[] = [
            'Type',
            'Numéro',
            'Date',
            'Partenaire',
            'N° TVA',
            'Référence',
            'HTVA',
            'TVA',
            'TVAC',
            'Statut',
        ];

        // Data rows
        foreach ($invoices as $invoice) {
            $csv[] = [
                $invoice->type === 'sale' ? 'Vente' : 'Achat',
                $invoice->invoice_number,
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->partner?->name ?? '-',
                $invoice->partner?->vat_number ?? '-',
                $invoice->reference ?? '-',
                number_format($invoice->total_excl_vat, 2, ',', ''),
                number_format($invoice->total_vat, 2, ',', ''),
                number_format($invoice->total_incl_vat, 2, ',', ''),
                $invoice->status,
            ];
        }

        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csv as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return [
            'content' => $content,
            'extension' => 'csv',
        ];
    }

    /**
     * Generate Winbooks format export.
     */
    protected function generateWinbooksExport($invoices): array
    {
        // Simplified Winbooks format
        $csv = [];

        foreach ($invoices as $invoice) {
            $csv[] = [
                $invoice->type === 'sale' ? 'VEN' : 'ACH',
                $invoice->invoice_number,
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->partner?->accounting_code ?? '400000',
                number_format($invoice->total_excl_vat, 2, '.', ''),
                number_format($invoice->total_vat, 2, '.', ''),
                number_format($invoice->total_incl_vat, 2, '.', ''),
            ];
        }

        $output = fopen('php://temp', 'r+');
        foreach ($csv as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return [
            'content' => $content,
            'extension' => 'csv',
        ];
    }

    /**
     * Generate Octopus format export.
     */
    protected function generateOctopusExport($invoices): array
    {
        // Simplified Octopus format
        return $this->generateCSVExport($invoices);
    }

    /**
     * Generate summary text file.
     */
    protected function generateSummaryFile(array $results, array $input, $firm): string
    {
        $summary = "=== EXPORT GROUPÉ COMPTABLE ===\n\n";
        $summary .= "Cabinet: {$firm->name}\n";
        $summary .= "Date d'export: " . now()->format('d/m/Y H:i') . "\n";
        $summary .= "Période: {$input['period_from']} → {$input['period_to']}\n";
        $summary .= "Format: " . ($input['format'] ?? 'csv') . "\n\n";

        $summary .= "=== RÉSULTATS PAR CLIENT ===\n\n";

        foreach ($results as $result) {
            $summary .= "• {$result['company_name']}\n";
            $summary .= "  Statut: {$result['status']}\n";

            if ($result['status'] === 'success') {
                $summary .= "  Factures: {$result['invoices_count']}\n";
                $summary .= "  Total: " . number_format($result['total_amount'], 2, ',', ' ') . " €\n";
                $summary .= "  Fichier: {$result['filename']}\n";
            } elseif ($result['status'] === 'error') {
                $summary .= "  Erreur: {$result['error']}\n";
            } elseif ($result['status'] === 'skipped') {
                $summary .= "  Raison: {$result['reason']}\n";
            }

            $summary .= "\n";
        }

        $successCount = collect($results)->where('status', 'success')->count();
        $summary .= "\n=== STATISTIQUES ===\n";
        $summary .= "Clients exportés avec succès: {$successCount}\n";
        $summary .= "Total factures: " . collect($results)->sum('invoices_count') . "\n";
        $summary .= "Montant total: " . number_format(collect($results)->sum('total_amount'), 2, ',', ' ') . " €\n";

        return $summary;
    }
}
