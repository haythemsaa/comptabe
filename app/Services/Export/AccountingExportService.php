<?php

namespace App\Services\Export;

use App\Models\Company;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service d'export comptable vers différents logiciels belges
 * Supporte: Sage BOB, Winbooks, Win Auditor, Horus
 */
class AccountingExportService
{
    protected Company $company;
    protected Collection $invoices;
    protected Carbon $dateFrom;
    protected Carbon $dateTo;

    /**
     * Formats d'export supportés
     */
    public const FORMATS = [
        'sage_bob' => 'Sage BOB 50',
        'winbooks' => 'Winbooks',
        'win_auditor' => 'Win Auditor',
        'horus' => 'Horus',
        'generic_csv' => 'CSV Générique',
        'generic_excel' => 'Excel Générique',
    ];

    /**
     * Types d'export
     */
    public const EXPORT_TYPES = [
        'invoices' => 'Factures',
        'journal' => 'Journal comptable',
        'vat' => 'Déclaration TVA',
        'balance' => 'Balance',
    ];

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Définir la période d'export
     */
    public function setPeriod(Carbon $from, Carbon $to): self
    {
        $this->dateFrom = $from;
        $this->dateTo = $to;
        return $this;
    }

    /**
     * Charger les factures pour l'export
     */
    public function loadInvoices(string $type = 'all'): self
    {
        $query = Invoice::forCompany($this->company)
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->with(['partner', 'lines', 'company'])
            ->orderBy('invoice_date')
            ->orderBy('invoice_number');

        // Filtrer par type si spécifié
        if ($type === 'sales') {
            $query->sales();
        } elseif ($type === 'purchases') {
            $query->purchases();
        }

        $this->invoices = $query->get();

        return $this;
    }

    /**
     * Exporter selon le format demandé
     */
    public function export(string $format, string $type = 'invoices'): array
    {
        return match ($format) {
            'sage_bob' => $this->exportSageBob($type),
            'winbooks' => $this->exportWinbooks($type),
            'win_auditor' => $this->exportWinAuditor($type),
            'horus' => $this->exportHorus($type),
            'generic_csv' => $this->exportGenericCsv($type),
            'generic_excel' => $this->exportGenericExcel($type),
            default => throw new \InvalidArgumentException("Format d'export non supporté: {$format}"),
        };
    }

    /**
     * Export Sage BOB 50
     */
    protected function exportSageBob(string $type): array
    {
        $exporter = new SageBobExporter($this->company);

        return match ($type) {
            'invoices' => $exporter->exportInvoices($this->invoices),
            'journal' => $exporter->exportJournal($this->invoices),
            'vat' => $exporter->exportVat($this->invoices),
            default => throw new \InvalidArgumentException("Type d'export non supporté pour Sage BOB: {$type}"),
        };
    }

    /**
     * Export Winbooks
     */
    protected function exportWinbooks(string $type): array
    {
        $exporter = new WinbooksExporter($this->company);

        return match ($type) {
            'invoices' => $exporter->exportInvoices($this->invoices),
            'journal' => $exporter->exportJournal($this->invoices),
            'vat' => $exporter->exportVat($this->invoices),
            default => throw new \InvalidArgumentException("Type d'export non supporté pour Winbooks: {$type}"),
        };
    }

    /**
     * Export Win Auditor
     */
    protected function exportWinAuditor(string $type): array
    {
        $exporter = new WinAuditorExporter($this->company);

        return match ($type) {
            'invoices' => $exporter->exportInvoices($this->invoices),
            'journal' => $exporter->exportJournal($this->invoices),
            'balance' => $exporter->exportBalance($this->invoices),
            default => throw new \InvalidArgumentException("Type d'export non supporté pour Win Auditor: {$type}"),
        };
    }

    /**
     * Export Horus
     */
    protected function exportHorus(string $type): array
    {
        $exporter = new HorusExporter($this->company);

        return match ($type) {
            'invoices' => $exporter->exportInvoices($this->invoices),
            'journal' => $exporter->exportJournal($this->invoices),
            default => throw new \InvalidArgumentException("Type d'export non supporté pour Horus: {$type}"),
        };
    }

    /**
     * Export CSV générique
     */
    protected function exportGenericCsv(string $type): array
    {
        $exporter = new GenericCsvExporter($this->company);

        return match ($type) {
            'invoices' => $exporter->exportInvoices($this->invoices),
            'journal' => $exporter->exportJournal($this->invoices),
            'vat' => $exporter->exportVat($this->invoices),
            'balance' => $exporter->exportBalance($this->invoices),
            default => throw new \InvalidArgumentException("Type d'export non supporté: {$type}"),
        };
    }

    /**
     * Export Excel générique
     */
    protected function exportGenericExcel(string $type): array
    {
        $exporter = new GenericExcelExporter($this->company);

        return match ($type) {
            'invoices' => $exporter->exportInvoices($this->invoices),
            'journal' => $exporter->exportJournal($this->invoices),
            'vat' => $exporter->exportVat($this->invoices),
            'balance' => $exporter->exportBalance($this->invoices),
            default => throw new \InvalidArgumentException("Type d'export non supporté: {$type}"),
        };
    }

    /**
     * Obtenir le nom de fichier pour l'export
     */
    public function getFilename(string $format, string $type): string
    {
        $formatName = str_replace('_', '-', $format);
        $dateRange = $this->dateFrom->format('Ymd') . '-' . $this->dateTo->format('Ymd');
        $companyName = \Str::slug($this->company->name);

        $extension = $this->getFileExtension($format);

        return "{$companyName}-{$type}-{$formatName}-{$dateRange}.{$extension}";
    }

    /**
     * Obtenir l'extension de fichier selon le format
     */
    protected function getFileExtension(string $format): string
    {
        return match ($format) {
            'winbooks' => 'xml',
            'generic_excel', 'horus' => 'xlsx',
            default => 'csv',
        };
    }

    /**
     * Obtenir le content-type selon le format
     */
    public function getContentType(string $format): string
    {
        return match ($format) {
            'winbooks' => 'application/xml',
            'generic_excel', 'horus' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'text/csv',
        };
    }

    /**
     * Obtenir les formats disponibles
     */
    public static function getAvailableFormats(): array
    {
        return self::FORMATS;
    }

    /**
     * Obtenir les types d'export disponibles
     */
    public static function getAvailableTypes(): array
    {
        return self::EXPORT_TYPES;
    }
}
