<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportAccountingDataTool extends AbstractTool
{
    public function getName(): string
    {
        return 'export_accounting_data';
    }

    public function getDescription(): string
    {
        return 'Exports accounting data (invoices, expenses) to various formats for Belgian accounting software like Winbooks, WinAuditor, Octopus, etc. Use this when the user wants to export invoices for their accountant or import into accounting software.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'format' => [
                    'type' => 'string',
                    'enum' => ['winbooks', 'winauditor', 'octopus', 'csv', 'excel'],
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
                    'description' => 'Include purchase invoices/expenses (default: true)',
                ],
                'status_filter' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => ['draft', 'validated', 'sent', 'paid', 'cancelled'],
                    ],
                    'description' => 'Filter by invoice status (default: all except draft)',
                ],
            ],
            'required' => ['period_from', 'period_to'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        $format = $input['format'] ?? 'csv';
        $includeSales = $input['include_sales'] ?? true;
        $includePurchases = $input['include_purchases'] ?? true;
        $statusFilter = $input['status_filter'] ?? ['validated', 'sent', 'paid'];

        // Build query
        $query = Invoice::where('company_id', $context->company->id)
            ->whereBetween('invoice_date', [$input['period_from'], $input['period_to']])
            ->whereIn('status', $statusFilter)
            ->with(['partner', 'lines']);

        // Filter by type
        if ($includeSales && !$includePurchases) {
            $query->where('type', 'sale');
        } elseif (!$includeSales && $includePurchases) {
            $query->where('type', 'purchase');
        }
        // If both true, no filter needed

        $invoices = $query->orderBy('invoice_date')->get();

        if ($invoices->isEmpty()) {
            return [
                'warning' => 'Aucune facture trouvée pour la période spécifiée.',
                'period' => [
                    'from' => $input['period_from'],
                    'to' => $input['period_to'],
                ],
            ];
        }

        // Generate export
        $exportData = $this->generateExport($invoices, $format, $context);

        // Save file temporarily
        $filename = sprintf(
            'export_%s_%s_%s.%s',
            $context->company->id,
            now()->format('Ymd_His'),
            Str::random(8),
            $exportData['extension']
        );

        Storage::disk('local')->put("exports/{$filename}", $exportData['content']);
        $fileUrl = Storage::disk('local')->url("exports/{$filename}");

        return [
            'success' => true,
            'message' => "Export comptable généré avec succès",
            'export' => [
                'format' => $format,
                'filename' => $filename,
                'url' => $fileUrl,
                'size' => strlen($exportData['content']),
                'period' => [
                    'from' => $input['period_from'],
                    'to' => $input['period_to'],
                ],
                'stats' => [
                    'total_invoices' => $invoices->count(),
                    'sales_invoices' => $invoices->where('type', 'sale')->count(),
                    'purchase_invoices' => $invoices->where('type', 'purchase')->count(),
                    'total_amount_excl_vat' => (float) $invoices->sum('total_excl_vat'),
                    'total_vat' => (float) $invoices->sum('total_vat'),
                    'total_incl_vat' => (float) $invoices->sum('total_incl_vat'),
                ],
            ],
            'next_steps' => [
                'Téléchargez le fichier via le lien fourni',
                'Importez-le dans votre logiciel comptable',
                'Le fichier sera supprimé automatiquement après 24h',
            ],
        ];
    }

    /**
     * Generate export in the specified format.
     */
    protected function generateExport($invoices, string $format, ToolContext $context): array
    {
        return match ($format) {
            'winbooks' => $this->generateWinbooksExport($invoices, $context),
            'winauditor' => $this->generateWinAuditorExport($invoices, $context),
            'octopus' => $this->generateOctopusExport($invoices, $context),
            'excel' => $this->generateExcelExport($invoices, $context),
            default => $this->generateCSVExport($invoices, $context),
        };
    }

    /**
     * Generate CSV export (universal format).
     */
    protected function generateCSVExport($invoices, ToolContext $context): array
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
    protected function generateWinbooksExport($invoices, ToolContext $context): array
    {
        // Winbooks uses a specific CSV format
        $csv = [];

        // Winbooks header format
        $csv[] = [
            'DBKTYPE',
            'DBKCODE',
            'DOCTYPE',
            'DOCNUMBER',
            'DOCORDER',
            'OPCODE',
            'ACCOUNTGL',
            'ACCOUNTRP',
            'BOOKYEAR',
            'PERIOD',
            'DATE',
            'DATEDOC',
            'DUEDATE',
            'COMMENT',
            'AMOUNTEUR',
            'VATBASE',
            'VATCODE',
            'VATPERC',
        ];

        foreach ($invoices as $invoice) {
            $bookYear = $invoice->invoice_date->format('Y');
            $period = (int) $invoice->invoice_date->format('m');

            // Main accounting entry
            $csv[] = [
                'VEN', // DBKTYPE (VEN = Ventes, ACH = Achats)
                $invoice->type === 'sale' ? 'VEN' : 'ACH',
                'FAC', // DOCTYPE (FAC = Facture)
                $invoice->invoice_number,
                '1',
                $invoice->partner?->accounting_code ?? '400000',
                '700000', // General account (revenue/expense)
                $invoice->partner?->accounting_code ?? '400000',
                $bookYear,
                $period,
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->due_date?->format('d/m/Y') ?? '',
                $invoice->reference ?? '',
                number_format($invoice->total_excl_vat, 2, '.', ''),
                number_format($invoice->total_excl_vat, 2, '.', ''),
                '1', // VAT code
                '21', // VAT percentage
            ];

            // VAT entry
            if ($invoice->total_vat > 0) {
                $csv[] = [
                    'VEN',
                    $invoice->type === 'sale' ? 'VEN' : 'ACH',
                    'FAC',
                    $invoice->invoice_number,
                    '2',
                    '411000', // VAT account
                    '411000',
                    '',
                    $bookYear,
                    $period,
                    $invoice->invoice_date->format('d/m/Y'),
                    $invoice->invoice_date->format('d/m/Y'),
                    '',
                    'TVA',
                    number_format($invoice->total_vat, 2, '.', ''),
                    '',
                    '',
                    '',
                ];
            }
        }

        // Convert to CSV
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
     * Generate WinAuditor format export.
     */
    protected function generateWinAuditorExport($invoices, ToolContext $context): array
    {
        // WinAuditor uses similar format to Winbooks
        return $this->generateWinbooksExport($invoices, $context);
    }

    /**
     * Generate Octopus format export.
     */
    protected function generateOctopusExport($invoices, ToolContext $context): array
    {
        // Octopus uses a specific format
        $csv = [];

        $csv[] = [
            'Journal',
            'Date',
            'Compte',
            'Libellé',
            'Débit',
            'Crédit',
            'Tiers',
            'Échéance',
        ];

        foreach ($invoices as $invoice) {
            $journal = $invoice->type === 'sale' ? 'VEN' : 'ACH';

            // Customer/Supplier line
            $csv[] = [
                $journal,
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->partner?->accounting_code ?? '400000',
                $invoice->reference ?? $invoice->invoice_number,
                $invoice->type === 'sale' ? number_format($invoice->total_incl_vat, 2, ',', '') : '',
                $invoice->type === 'purchase' ? number_format($invoice->total_incl_vat, 2, ',', '') : '',
                $invoice->partner?->name ?? '',
                $invoice->due_date?->format('d/m/Y') ?? '',
            ];

            // Revenue/Expense line
            $csv[] = [
                $journal,
                $invoice->invoice_date->format('d/m/Y'),
                '700000',
                $invoice->reference ?? $invoice->invoice_number,
                $invoice->type === 'purchase' ? number_format($invoice->total_excl_vat, 2, ',', '') : '',
                $invoice->type === 'sale' ? number_format($invoice->total_excl_vat, 2, ',', '') : '',
                '',
                '',
            ];

            // VAT line
            if ($invoice->total_vat > 0) {
                $csv[] = [
                    $journal,
                    $invoice->invoice_date->format('d/m/Y'),
                    '411000',
                    'TVA',
                    $invoice->type === 'purchase' ? number_format($invoice->total_vat, 2, ',', '') : '',
                    $invoice->type === 'sale' ? number_format($invoice->total_vat, 2, ',', '') : '',
                    '',
                    '',
                ];
            }
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
     * Generate Excel export.
     */
    protected function generateExcelExport($invoices, ToolContext $context): array
    {
        // For now, use CSV format (can be opened in Excel)
        // TODO: Use PhpSpreadsheet for true .xlsx format
        return $this->generateCSVExport($invoices, $context);
    }
}
