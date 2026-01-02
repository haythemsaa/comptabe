<?php

namespace App\Http\Controllers;

use App\Models\VatDeclaration;
use App\Models\VatCode;
use App\Models\JournalEntryLine;
use App\Models\Invoice;
use App\Services\IntervatService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VatController extends Controller
{
    public function index()
    {
        $declarations = VatDeclaration::orderBy('period_start', 'desc')
            ->paginate(12);

        $pendingPeriods = $this->getPendingPeriods();

        return view('vat.index', compact('declarations', 'pendingPeriods'));
    }

    public function create(Request $request)
    {
        $periodType = $request->get('type', 'monthly');
        $year = $request->get('year', now()->year);
        $period = $request->get('period', now()->month);

        if ($periodType === 'monthly') {
            $periodStart = Carbon::create($year, $period, 1)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
        } else {
            $quarter = ceil($period / 3);
            $periodStart = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
            $periodEnd = $periodStart->copy()->addMonths(2)->endOfMonth();
        }

        $vatData = $this->calculateVatData($periodStart, $periodEnd);
        $vatCodes = VatCode::all();

        return view('vat.create', compact('vatData', 'periodStart', 'periodEnd', 'periodType', 'vatCodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'period_type' => 'required|in:monthly,quarterly',
            'grid_values' => 'required|array',
            'grid_values.*' => 'nullable|numeric',
        ]);

        // Calculate totals
        $outputVat = collect($validated['grid_values'])
            ->only(['54', '55', '56', '57', '58', '59', '63'])
            ->sum();

        $inputVat = collect($validated['grid_values'])
            ->only(['59', '60', '61', '62', '64'])
            ->sum();

        $balance = $outputVat - $inputVat;

        $declaration = VatDeclaration::create([
            'company_id' => auth()->user()->current_company_id,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'period_type' => $validated['period_type'],
            'grid_values' => $validated['grid_values'],
            'output_vat' => $outputVat,
            'input_vat' => $inputVat,
            'balance' => $balance,
            'status' => 'draft',
        ]);

        return redirect()
            ->route('vat.show', $declaration)
            ->with('success', 'Déclaration TVA créée avec succès.');
    }

    public function show(VatDeclaration $declaration)
    {
        return view('vat.show', compact('declaration'));
    }

    public function edit(VatDeclaration $declaration)
    {
        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Seules les déclarations en brouillon peuvent être modifiées.');
        }

        $vatCodes = VatCode::all();

        return view('vat.edit', compact('declaration', 'vatCodes'));
    }

    public function update(Request $request, VatDeclaration $declaration)
    {
        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Seules les déclarations en brouillon peuvent être modifiées.');
        }

        $validated = $request->validate([
            'grid_values' => 'required|array',
            'grid_values.*' => 'nullable|numeric',
        ]);

        $outputVat = collect($validated['grid_values'])
            ->only(['54', '55', '56', '57', '58', '59', '63'])
            ->sum();

        $inputVat = collect($validated['grid_values'])
            ->only(['59', '60', '61', '62', '64'])
            ->sum();

        $balance = $outputVat - $inputVat;

        $declaration->update([
            'grid_values' => $validated['grid_values'],
            'output_vat' => $outputVat,
            'input_vat' => $inputVat,
            'balance' => $balance,
        ]);

        return redirect()
            ->route('vat.show', $declaration)
            ->with('success', 'Déclaration TVA mise à jour.');
    }

    public function submit(VatDeclaration $declaration)
    {
        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Cette déclaration a déjà été soumise.');
        }

        $declaration->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Déclaration marquée comme soumise.');
    }

    public function exportIntervat(VatDeclaration $declaration)
    {
        $intervatService = new IntervatService();
        $xml = $intervatService->generateXml($declaration);

        $filename = sprintf(
            'intervat_%s_%s.xml',
            $declaration->period_start->format('Y-m'),
            now()->format('Ymd_His')
        );

        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function clientListing(Request $request)
    {
        $year = $request->get('year', now()->subYear()->year);

        // Get all sales invoices for the year grouped by client
        $clients = Invoice::where('type', 'out')
            ->whereYear('invoice_date', $year)
            ->where('status', '!=', 'cancelled')
            ->with('partner')
            ->get()
            ->groupBy('partner_id')
            ->map(function ($invoices) {
                $partner = $invoices->first()->partner;
                return [
                    'partner' => $partner,
                    'vat_number' => $partner->vat_number,
                    'total_excl' => $invoices->sum('total_excl_vat'),
                    'total_vat' => $invoices->sum('total_vat'),
                    'invoice_count' => $invoices->count(),
                ];
            })
            ->filter(fn($c) => $c['total_excl'] >= 250) // Seuil minimum
            ->sortByDesc('total_excl');

        return view('vat.client-listing', compact('clients', 'year'));
    }

    public function exportClientListing(Request $request)
    {
        $year = $request->get('year', now()->subYear()->year);

        // Similar logic, generate XML
        $clients = Invoice::where('type', 'out')
            ->whereYear('invoice_date', $year)
            ->where('status', '!=', 'cancelled')
            ->with('partner')
            ->get()
            ->groupBy('partner_id')
            ->map(function ($invoices) {
                $partner = $invoices->first()->partner;
                return [
                    'partner' => $partner,
                    'vat_number' => $partner->vat_number,
                    'total_excl' => $invoices->sum('total_excl_vat'),
                    'total_vat' => $invoices->sum('total_vat'),
                ];
            })
            ->filter(fn($c) => $c['total_excl'] >= 250 && $c['vat_number']);

        $intervatService = new IntervatService();
        $xml = $intervatService->generateClientListing($clients, $year);

        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', "attachment; filename=\"listing_clients_{$year}.xml\"");
    }

    public function intrastatDeclaration(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        // Get intracommunity operations
        $arrivals = Invoice::where('type', 'in')
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->whereHas('partner', fn($q) => $q->whereNotNull('vat_number')
                ->where('vat_number', 'not like', 'BE%'))
            ->with('partner', 'lines')
            ->get();

        $dispatches = Invoice::where('type', 'out')
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->whereHas('partner', fn($q) => $q->whereNotNull('vat_number')
                ->where('vat_number', 'not like', 'BE%'))
            ->with('partner', 'lines')
            ->get();

        return view('vat.intrastat', compact('arrivals', 'dispatches', 'year', 'month'));
    }

    protected function calculateVatData(Carbon $periodStart, Carbon $periodEnd): array
    {
        $data = [
            'grids' => [],
            'invoices' => [
                'sales' => [],
                'purchases' => [],
            ],
        ];

        // Get all invoices for the period
        $salesInvoices = Invoice::where('type', 'out')
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->where('status', '!=', 'cancelled')
            ->with('lines', 'partner')
            ->get();

        $purchaseInvoices = Invoice::where('type', 'in')
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->where('status', '!=', 'cancelled')
            ->with('lines', 'partner')
            ->get();

        $data['invoices']['sales'] = $salesInvoices;
        $data['invoices']['purchases'] = $purchaseInvoices;

        // Grid 00: Opérations soumises à un régime particulier
        $data['grids']['00'] = 0;

        // Grid 01: Opérations pour lesquelles la TVA est due au taux de 6%
        $data['grids']['01'] = $salesInvoices->sum(function ($invoice) {
            return $invoice->lines->where('vat_rate', 6)->sum('total_excl_vat');
        });

        // Grid 02: Opérations pour lesquelles la TVA est due au taux de 12%
        $data['grids']['02'] = $salesInvoices->sum(function ($invoice) {
            return $invoice->lines->where('vat_rate', 12)->sum('total_excl_vat');
        });

        // Grid 03: Opérations pour lesquelles la TVA est due au taux de 21%
        $data['grids']['03'] = $salesInvoices->sum(function ($invoice) {
            return $invoice->lines->where('vat_rate', 21)->sum('total_excl_vat');
        });

        // Grid 44: Services intracommunautaires
        $data['grids']['44'] = $salesInvoices
            ->filter(fn($i) => $i->partner && $i->partner->vat_number && !str_starts_with($i->partner->vat_number, 'BE'))
            ->sum('total_excl_vat');

        // Grid 45: Opérations non soumises au régime du cocontractant
        $data['grids']['45'] = 0;

        // Grid 46: Livraisons intracommunautaires exonérées
        $data['grids']['46'] = $salesInvoices
            ->filter(fn($i) => $i->partner && $i->partner->vat_number && !str_starts_with($i->partner->vat_number, 'BE'))
            ->sum('total_excl_vat');

        // Grid 47: Autres opérations exemptées
        $data['grids']['47'] = $salesInvoices->sum(function ($invoice) {
            return $invoice->lines->where('vat_rate', 0)->sum('total_excl_vat');
        });

        // Grid 48: Notes de crédit émises
        $data['grids']['48'] = 0;

        // Grid 49: Notes de crédit reçues
        $data['grids']['49'] = 0;

        // Grid 54: TVA due sur grilles 01, 02, 03
        $data['grids']['54'] = round(
            ($data['grids']['01'] * 0.06) +
            ($data['grids']['02'] * 0.12) +
            ($data['grids']['03'] * 0.21),
            2
        );

        // Grid 55: TVA due sur grid 86
        $data['grids']['55'] = 0;

        // Grid 56: TVA due sur grid 87
        $data['grids']['56'] = 0;

        // Grid 57: TVA due sur grid 88
        $data['grids']['57'] = 0;

        // Grid 59: TVA déductible
        $data['grids']['59'] = $purchaseInvoices->sum('vat_amount');

        // Grid 61: Diverses régularisations en faveur de l'État
        $data['grids']['61'] = 0;

        // Grid 62: Diverses régularisations en faveur du déclarant
        $data['grids']['62'] = 0;

        // Grid 63: TVA à reverser (grilles 61 - 62)
        $data['grids']['63'] = max(0, $data['grids']['61'] - $data['grids']['62']);

        // Grid 64: TVA récupérable (grilles 62 - 61)
        $data['grids']['64'] = max(0, $data['grids']['62'] - $data['grids']['61']);

        // Grid 71: Solde en faveur de l'État (54+55+56+57+63-59-64)
        $outputVat = $data['grids']['54'] + $data['grids']['55'] + $data['grids']['56'] + $data['grids']['57'] + $data['grids']['63'];
        $inputVat = $data['grids']['59'] + $data['grids']['64'];

        if ($outputVat > $inputVat) {
            $data['grids']['71'] = round($outputVat - $inputVat, 2);
            $data['grids']['72'] = 0;
        } else {
            $data['grids']['71'] = 0;
            $data['grids']['72'] = round($inputVat - $outputVat, 2);
        }

        // Grid 81: Achats de marchandises et matières premières
        $data['grids']['81'] = $purchaseInvoices->sum('total_excl_vat');

        // Grid 82: Achats de services et biens divers
        $data['grids']['82'] = 0;

        // Grid 83: Achats de biens d'investissement
        $data['grids']['83'] = 0;

        // Grid 86: Acquisitions intracommunautaires (services)
        $data['grids']['86'] = $purchaseInvoices
            ->filter(fn($i) => $i->partner && $i->partner->vat_number && !str_starts_with($i->partner->vat_number, 'BE'))
            ->sum('total_excl_vat');

        // Grid 87: Autres opérations à l'entrée
        $data['grids']['87'] = 0;

        // Grid 88: Opérations régime du cocontractant
        $data['grids']['88'] = 0;

        return $data;
    }

    protected function getPendingPeriods(): array
    {
        $pending = [];
        $now = now();

        // Check last 12 months
        for ($i = 1; $i <= 12; $i++) {
            $date = $now->copy()->subMonths($i);

            $exists = VatDeclaration::where('period_year', $date->year)
                ->where('period_number', $date->month)
                ->where('period_type', 'monthly')
                ->exists();

            if (!$exists) {
                $pending[] = [
                    'type' => 'monthly',
                    'year' => $date->year,
                    'period' => $date->month,
                    'label' => $date->translatedFormat('F Y'),
                ];
            }
        }

        return array_slice($pending, 0, 6); // Max 6 pending
    }
}
