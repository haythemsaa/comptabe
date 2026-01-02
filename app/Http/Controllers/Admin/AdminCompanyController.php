<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AdminCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withCount(['users', 'invoices'])
            ->withTrashed();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->status === 'suspended') {
                $query->whereNotNull('deleted_at');
            }
        }

        $companies = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.companies.index', compact('companies'));
    }

    public function show(Company $company)
    {
        $company->load(['users.roles', 'subscription.plan', 'products']);

        $recentActivity = AuditLog::where('company_id', $company->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.companies.show', compact('company', 'recentActivity'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'street' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:2',
            // Tunisia fields
            'matricule_fiscal' => 'nullable|string|max:50',
            'cnss_employer_number' => 'nullable|string|max:50',
            // France fields
            'siret' => 'nullable|string|max:14',
            'siren' => 'nullable|string|max:9',
            'ape_code' => 'nullable|string|max:6',
            'urssaf_number' => 'nullable|string|max:20',
            'convention_collective' => 'nullable|string|max:100',
            'peppol_id' => 'nullable|string|max:50',
            'peppol_provider' => 'nullable|string|in:storecove,billit,unifiedpost,basware,avalara',
            'peppol_test_mode' => 'boolean',
        ]);

        $oldValues = $company->only([
            'name', 'vat_number', 'email', 'phone', 'country_code',
            'matricule_fiscal', 'cnss_employer_number',
            'siret', 'siren', 'ape_code', 'urssaf_number', 'convention_collective',
            'peppol_id', 'peppol_provider'
        ]);

        $updateData = [
            'name' => $validated['name'],
            'vat_number' => $validated['vat_number'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'website' => $validated['website'] ?? null,
            'street' => $validated['street'],
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'country_code' => $validated['country_code'] ?? 'BE',
            // Tunisia fields
            'matricule_fiscal' => $validated['matricule_fiscal'] ?? null,
            'cnss_employer_number' => $validated['cnss_employer_number'] ?? null,
            // France fields
            'siret' => $validated['siret'] ?? null,
            'siren' => $validated['siren'] ?? null,
            'ape_code' => $validated['ape_code'] ?? null,
            'urssaf_number' => $validated['urssaf_number'] ?? null,
            'convention_collective' => $validated['convention_collective'] ?? null,
            'peppol_id' => $validated['peppol_id'],
            'peppol_provider' => $validated['peppol_provider'],
            'peppol_test_mode' => $request->boolean('peppol_test_mode'),
        ];

        $company->update($updateData);

        AuditLog::log('update', "Entreprise {$company->name} modifiée (admin)", $company, $oldValues, $updateData);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('success', 'Entreprise mise à jour.');
    }

    public function suspend(Company $company)
    {
        if ($company->trashed()) {
            return back()->with('error', 'Cette entreprise est déjà suspendue.');
        }

        $company->delete();

        AuditLog::log('suspend', "Entreprise {$company->name} suspendue", $company);

        return back()->with('success', 'Entreprise suspendue.');
    }

    public function restore(string $id)
    {
        $company = Company::withTrashed()->findOrFail($id);
        $company->restore();

        AuditLog::log('activate', "Entreprise {$company->name} réactivée", $company);

        return back()->with('success', 'Entreprise réactivée.');
    }

    public function impersonate(Company $company)
    {
        // Store original company
        session(['admin_original_tenant' => session('current_tenant_id')]);
        session(['current_tenant_id' => $company->id]);

        AuditLog::log('impersonate', "Impersonation de l'entreprise {$company->name}", $company);

        return redirect()->route('dashboard')
            ->with('warning', "Vous êtes maintenant connecté en tant que {$company->name}. Cliquez sur le bandeau pour revenir.");
    }

    public function stopImpersonate()
    {
        $originalTenant = session('admin_original_tenant');

        if ($originalTenant) {
            session()->forget('admin_original_tenant');
            session(['current_tenant_id' => $originalTenant]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Retour au mode administrateur.');
    }

    public function destroy(Company $company)
    {
        $name = $company->name;

        // Force delete (permanent)
        $company->forceDelete();

        AuditLog::log('delete', "Entreprise {$name} supprimée définitivement");

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Entreprise supprimée définitivement.');
    }
}
