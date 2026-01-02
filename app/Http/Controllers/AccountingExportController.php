<?php

namespace App\Http\Controllers;

use App\Services\Export\AccountingExportService;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountingExportController extends Controller
{
    /**
     * Afficher la page d'export comptable
     */
    public function index()
    {
        $company = Company::current();

        $formats = AccountingExportService::getAvailableFormats();
        $types = AccountingExportService::getAvailableTypes();

        // Périodes prédéfinies
        $periods = [
            'current_month' => 'Mois en cours',
            'last_month' => 'Mois dernier',
            'current_quarter' => 'Trimestre en cours',
            'last_quarter' => 'Trimestre dernier',
            'current_year' => 'Année en cours',
            'last_year' => 'Année dernière',
            'custom' => 'Période personnalisée',
        ];

        return view('accounting.export', compact('company', 'formats', 'types', 'periods'));
    }

    /**
     * Générer et télécharger l'export
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'string', 'in:' . implode(',', array_keys(AccountingExportService::getAvailableFormats()))],
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(AccountingExportService::getAvailableTypes()))],
            'period' => ['required', 'string'],
            'date_from' => ['required_if:period,custom', 'nullable', 'date'],
            'date_to' => ['required_if:period,custom', 'nullable', 'date', 'after_or_equal:date_from'],
            'invoice_type' => ['nullable', 'in:all,sales,purchases'],
        ]);

        try {
            $company = Company::current();

            // Calculer les dates selon la période
            [$dateFrom, $dateTo] = $this->calculatePeriodDates($validated['period'], $validated['date_from'] ?? null, $validated['date_to'] ?? null);

            // Créer le service d'export
            $exportService = new AccountingExportService($company);
            $exportService->setPeriod($dateFrom, $dateTo);
            $exportService->loadInvoices($validated['invoice_type'] ?? 'all');

            // Générer l'export
            $result = $exportService->export($validated['format'], $validated['type']);

            // Télécharger le fichier
            $filename = $exportService->getFilename($validated['format'], $validated['type']);
            $contentType = $exportService->getContentType($validated['format']);

            return response($result['content'])
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            \Log::error('Erreur export comptable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Erreur lors de la génération de l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Calculer les dates de début et fin selon la période
     */
    protected function calculatePeriodDates(string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $now = Carbon::now();

        return match ($period) {
            'current_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            'current_quarter' => [
                $now->copy()->firstOfQuarter(),
                $now->copy()->lastOfQuarter(),
            ],
            'last_quarter' => [
                $now->copy()->subQuarter()->firstOfQuarter(),
                $now->copy()->subQuarter()->lastOfQuarter(),
            ],
            'current_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
            ],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
            ],
            'custom' => [
                Carbon::parse($dateFrom),
                Carbon::parse($dateTo),
            ],
            default => throw new \InvalidArgumentException("Période non valide: {$period}"),
        };
    }

    /**
     * Aperçu de l'export (optionnel)
     */
    public function preview(Request $request)
    {
        // Pour afficher un aperçu avant de télécharger
        // Utile pour vérifier les données
        // TODO: Implémenter si nécessaire
    }
}
