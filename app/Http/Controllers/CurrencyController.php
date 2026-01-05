<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\CompanyCurrency;
use App\Models\ExchangeRateDifference;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $companyCurrencies = CompanyCurrency::where('company_id', auth()->user()->current_company_id)
            ->with('currency')
            ->orderByDesc('is_default')
            ->orderBy('currency_code')
            ->get();

        $availableCurrencies = Currency::where('is_active', true)
            ->whereNotIn('code', $companyCurrencies->pluck('currency_code'))
            ->orderBy('name')
            ->get();

        $latestRates = ExchangeRate::where('base_currency', 'EUR')
            ->whereIn('target_currency', $companyCurrencies->pluck('currency_code'))
            ->orderByDesc('rate_date')
            ->get()
            ->unique('target_currency');

        return view('currencies.index', compact('companyCurrencies', 'availableCurrencies', 'latestRates'));
    }

    public function addCurrency(Request $request)
    {
        $validated = $request->validate([
            'currency_code' => 'required|exists:currencies,code',
            'rate_type' => 'required|in:live,daily,fixed',
            'fixed_rate' => 'required_if:rate_type,fixed|nullable|numeric|min:0.0001',
        ]);

        $validated['company_id'] = auth()->user()->current_company_id;
        $validated['is_active'] = true;

        CompanyCurrency::create($validated);

        return back()->with('success', 'Devise ajoutée.');
    }

    public function updateCurrency(Request $request, CompanyCurrency $companyCurrency)
    {
        $this->authorize('update', $companyCurrency);

        $validated = $request->validate([
            'is_active' => 'boolean',
            'rate_type' => 'required|in:live,daily,fixed',
            'fixed_rate' => 'required_if:rate_type,fixed|nullable|numeric|min:0.0001',
        ]);

        $companyCurrency->update($validated);

        return back()->with('success', 'Devise mise à jour.');
    }

    public function setDefault(CompanyCurrency $companyCurrency)
    {
        $this->authorize('update', $companyCurrency);

        CompanyCurrency::setDefault(
            auth()->user()->currentCompany,
            $companyCurrency->currency_code
        );

        return back()->with('success', 'Devise par défaut modifiée.');
    }

    public function removeCurrency(CompanyCurrency $companyCurrency)
    {
        $this->authorize('delete', $companyCurrency);

        if ($companyCurrency->is_default) {
            return back()->with('error', 'Impossible de supprimer la devise par défaut.');
        }

        $companyCurrency->delete();

        return back()->with('success', 'Devise retirée.');
    }

    public function rates(Request $request)
    {
        $currency = $request->get('currency', 'USD');
        $startDate = $request->get('start_date', now()->subMonths(3)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $rates = ExchangeRate::where('base_currency', 'EUR')
            ->where('target_currency', $currency)
            ->whereBetween('rate_date', [$startDate, $endDate])
            ->orderBy('rate_date')
            ->get();

        $currencies = Currency::where('is_active', true)
            ->orderBy('code')
            ->pluck('code');

        return view('currencies.rates', compact('rates', 'currencies', 'currency', 'startDate', 'endDate'));
    }

    public function fetchRates()
    {
        $imported = ExchangeRate::fetchFromEcb();

        if (empty($imported)) {
            return back()->with('error', 'Erreur lors de la récupération des taux de change.');
        }

        return back()->with('success', count($imported) . ' taux de change mis à jour depuis la BCE.');
    }

    public function setManualRate(Request $request)
    {
        $validated = $request->validate([
            'target_currency' => 'required|exists:currencies,code',
            'rate' => 'required|numeric|min:0.0001',
            'rate_date' => 'required|date',
        ]);

        ExchangeRate::setManualRate(
            'EUR',
            $validated['target_currency'],
            $validated['rate'],
            $validated['rate_date']
        );

        return back()->with('success', 'Taux de change enregistré.');
    }

    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'date' => 'nullable|date',
        ]);

        $result = ExchangeRate::convert(
            $validated['amount'],
            $validated['from'],
            $validated['to'],
            $validated['date'] ?? null
        );

        if ($result === null) {
            return response()->json([
                'error' => 'Taux de change non disponible',
            ], 404);
        }

        $rate = ExchangeRate::getRate($validated['from'], $validated['to'], $validated['date'] ?? null);

        return response()->json([
            'from_amount' => $validated['amount'],
            'from_currency' => $validated['from'],
            'to_amount' => round($result, 2),
            'to_currency' => $validated['to'],
            'rate' => $rate,
            'date' => $validated['date'] ?? now()->toDateString(),
        ]);
    }

    public function exchangeDifferences(Request $request)
    {
        $query = ExchangeRateDifference::where('company_id', auth()->user()->current_company_id)
            ->with('documentable');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('settlement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('settlement_date', '<=', $request->date_to);
        }

        $differences = $query->orderByDesc('settlement_date')->paginate(20);

        $stats = [
            'total_gains' => ExchangeRateDifference::where('company_id', auth()->user()->current_company_id)
                ->where('is_gain', true)
                ->sum('difference_amount'),
            'total_losses' => ExchangeRateDifference::where('company_id', auth()->user()->current_company_id)
                ->where('is_gain', false)
                ->sum('difference_amount'),
        ];

        $stats['net_result'] = $stats['total_gains'] - $stats['total_losses'];

        return view('currencies.differences', compact('differences', 'stats'));
    }

    // API endpoints for invoice form
    public function getCompanyCurrencies()
    {
        $currencies = CompanyCurrency::where('company_id', auth()->user()->current_company_id)
            ->where('is_active', true)
            ->with('currency')
            ->get()
            ->map(function ($cc) {
                return [
                    'code' => $cc->currency_code,
                    'name' => $cc->currency->name,
                    'symbol' => $cc->currency->symbol,
                    'rate' => $cc->getRate(),
                    'is_default' => $cc->is_default,
                ];
            });

        return response()->json($currencies);
    }

    public function getRate(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'date' => 'nullable|date',
        ]);

        $rate = ExchangeRate::getRate(
            $validated['from'],
            $validated['to'],
            $validated['date'] ?? null
        );

        return response()->json([
            'rate' => $rate,
            'date' => $validated['date'] ?? now()->toDateString(),
        ]);
    }
}
