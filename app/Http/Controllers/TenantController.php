<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * Show company selection.
     */
    public function select()
    {
        $companies = auth()->user()->companies;

        if ($companies->count() === 0) {
            return redirect()->route('companies.create');
        }

        if ($companies->count() === 1) {
            session(['current_tenant_id' => $companies->first()->id]);
            return redirect()->route('dashboard');
        }

        return view('tenant.select', compact('companies'));
    }

    /**
     * Switch to a different company.
     */
    public function switch(Request $request)
    {
        $request->validate([
            'company_id' => ['required', 'uuid'],
        ]);

        $user = auth()->user();
        $company = $user->companies()->findOrFail($request->company_id);

        session(['current_tenant_id' => $company->id]);

        return redirect()->route('dashboard')
            ->with('success', "Vous êtes maintenant connecté à {$company->name}");
    }

    /**
     * Show company creation form.
     */
    public function create()
    {
        return view('tenant.create');
    }

    /**
     * Store a new company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'vat_number' => ['required', 'string', 'max:20', 'unique:companies'],
            'legal_form' => ['nullable', 'string', 'max:50'],
            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        // Clean VAT number
        $validated['vat_number'] = preg_replace('/[^0-9]/', '', $validated['vat_number']);
        if (strlen($validated['vat_number']) === 10) {
            $validated['vat_number'] = 'BE' . $validated['vat_number'];
        }

        // Generate Peppol ID
        $validated['peppol_id'] = '0208:' . preg_replace('/[^0-9]/', '', $validated['vat_number']);

        DB::transaction(function () use ($validated) {
            $company = Company::create($validated);

            // Attach user as owner
            auth()->user()->companies()->attach($company->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'role' => 'owner',
                'is_default' => auth()->user()->companies()->count() === 0,
            ]);

            // Set as current tenant
            session(['current_tenant_id' => $company->id]);

            // Create default chart of accounts, journals, VAT codes
            $this->createDefaultData($company);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Entreprise créée avec succès!');
    }

    /**
     * Create default accounting data for new company.
     */
    protected function createDefaultData(Company $company): void
    {
        // Create default fiscal year
        $company->fiscalYears()->create([
            'name' => now()->year,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => 'open',
        ]);

        // Create default journals
        $journals = [
            ['code' => 'VEN', 'name' => 'Ventes', 'type' => 'sales'],
            ['code' => 'ACH', 'name' => 'Achats', 'type' => 'purchases'],
            ['code' => 'BNK', 'name' => 'Banque', 'type' => 'bank'],
            ['code' => 'OD', 'name' => 'Opérations diverses', 'type' => 'misc'],
        ];

        foreach ($journals as $journal) {
            $company->journals()->create($journal);
        }

        // Create default VAT codes (Belgian)
        $vatCodes = [
            ['code' => 'V21', 'name' => 'TVA 21%', 'rate' => 21, 'category' => 'S', 'grid_base' => '03', 'grid_vat' => '54'],
            ['code' => 'V12', 'name' => 'TVA 12%', 'rate' => 12, 'category' => 'S', 'grid_base' => '02', 'grid_vat' => '54'],
            ['code' => 'V06', 'name' => 'TVA 6%', 'rate' => 6, 'category' => 'S', 'grid_base' => '01', 'grid_vat' => '54'],
            ['code' => 'V00', 'name' => 'TVA 0%', 'rate' => 0, 'category' => 'Z', 'grid_base' => '00', 'grid_vat' => null],
            ['code' => 'EXO', 'name' => 'Exonéré', 'rate' => 0, 'category' => 'E', 'grid_base' => '00', 'grid_vat' => null],
            ['code' => 'ICO', 'name' => 'Intracommunautaire', 'rate' => 0, 'category' => 'K', 'grid_base' => '46', 'grid_vat' => null],
            ['code' => 'AUT', 'name' => 'Autoliquidation', 'rate' => 0, 'category' => 'AE', 'grid_base' => '00', 'grid_vat' => null],
        ];

        foreach ($vatCodes as $vatCode) {
            $company->vatCodes()->create($vatCode);
        }

        // Create basic chart of accounts (PCMN simplified)
        $accounts = [
            // Class 1 - Equity
            ['account_number' => '1000', 'name' => 'Capital', 'type' => 'equity'],
            // Class 4 - Receivables/Payables
            ['account_number' => '4000', 'name' => 'Clients', 'type' => 'asset'],
            ['account_number' => '4400', 'name' => 'Fournisseurs', 'type' => 'liability'],
            ['account_number' => '4510', 'name' => 'TVA à récupérer', 'type' => 'asset'],
            ['account_number' => '4520', 'name' => 'TVA à payer', 'type' => 'liability'],
            // Class 5 - Bank
            ['account_number' => '5500', 'name' => 'Banque', 'type' => 'asset'],
            ['account_number' => '5700', 'name' => 'Caisse', 'type' => 'asset'],
            // Class 6 - Expenses
            ['account_number' => '6000', 'name' => 'Achats marchandises', 'type' => 'expense'],
            ['account_number' => '6100', 'name' => 'Services et biens divers', 'type' => 'expense'],
            ['account_number' => '6200', 'name' => 'Rémunérations', 'type' => 'expense'],
            // Class 7 - Revenue
            ['account_number' => '7000', 'name' => 'Ventes marchandises', 'type' => 'revenue'],
            ['account_number' => '7010', 'name' => 'Ventes services', 'type' => 'revenue'],
        ];

        foreach ($accounts as $account) {
            $company->chartOfAccounts()->create($account);
        }
    }
}
