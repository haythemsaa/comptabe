<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\Company;
use App\Models\InvitationToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show company settings
     */
    public function company()
    {
        $company = Company::current();

        return view('settings.company', compact('company'));
    }

    /**
     * Update company settings
     */
    public function updateCompany(Request $request)
    {
        $company = Company::current();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'required|string|max:50',
            'legal_form' => 'nullable|string|max:50',
            'enterprise_number' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|size:2',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
        ]);

        // Map iban/bic to the actual database fields
        if (isset($validated['iban'])) {
            $validated['default_iban'] = $validated['iban'];
            unset($validated['iban']);
        }
        if (isset($validated['bic'])) {
            $validated['default_bic'] = $validated['bic'];
            unset($validated['bic']);
        }

        $company->update($validated);

        return redirect()
            ->route('settings.company')
            ->with('success', 'Informations de l\'entreprise mises à jour.');
    }

    /**
     * Show Peppol settings
     */
    public function peppol()
    {
        $company = Company::current();

        return view('settings.peppol', compact('company'));
    }

    /**
     * Update Peppol settings
     */
    public function updatePeppol(Request $request)
    {
        $company = Company::current();

        $validated = $request->validate([
            'peppol_id' => 'nullable|string|max:255',
            'peppol_provider' => 'nullable|string|in:recommand,digiteal,b2brouter,custom,storecove,billit,unifiedpost,basware,avalara,other',
            'peppol_api_key' => 'nullable|string|max:255',
            'peppol_api_secret' => 'nullable|string|max:500',
            'peppol_test_mode' => 'boolean',
            'peppol_participant_id' => 'nullable|string|max:255',
        ]);

        // Generate Peppol ID if not provided
        if (empty($validated['peppol_id']) && $company->vat_number) {
            $validated['peppol_id'] = $company->generatePeppolId();
        }

        // Format participant ID if provided
        if (!empty($validated['peppol_participant_id'])) {
            $validated['peppol_participant_id'] = \App\Services\PeppolService::formatToPeppolId($validated['peppol_participant_id']);
        } elseif (empty($validated['peppol_participant_id']) && $company->vat_number) {
            $validated['peppol_participant_id'] = \App\Services\PeppolService::formatToPeppolId($company->vat_number);
        }

        // Generate webhook secret if provider is set and secret doesn't exist
        if (!empty($validated['peppol_provider']) && empty($company->peppol_webhook_secret)) {
            $validated['peppol_webhook_secret'] = bin2hex(random_bytes(32));
        }

        // Set registration status
        if (!empty($validated['peppol_id']) && !empty($validated['peppol_provider'])) {
            $validated['peppol_registered'] = true;
            $validated['peppol_registered_at'] = $company->peppol_registered_at ?? now();
        }

        // Handle checkbox default
        $validated['peppol_test_mode'] = $request->boolean('peppol_test_mode');

        $company->update($validated);

        return redirect()
            ->route('settings.peppol')
            ->with('success', 'Configuration Peppol mise à jour.');
    }

    /**
     * Test Peppol connection
     */
    public function testPeppolConnection(Request $request)
    {
        $company = Company::current();

        if (!$company->peppol_provider || !$company->peppol_api_key) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez d\'abord configurer un fournisseur et une clé API.',
            ]);
        }

        try {
            $peppolService = new \App\Services\PeppolService();
            $result = $peppolService->testConnection();

            if ($result['success']) {
                $company->update(['peppol_connected_at' => now()]);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show invoice settings
     */
    public function invoices()
    {
        $company = Company::current();

        return view('settings.invoices', compact('company'));
    }

    /**
     * Update invoice settings
     */
    public function updateInvoices(Request $request)
    {
        $company = Company::current();

        $validated = $request->validate([
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_next_number' => 'nullable|integer|min:1',
            'default_payment_terms_days' => 'nullable|integer|min:0|max:365',
            'default_vat_rate' => 'nullable|numeric|min:0|max:100',
            'invoice_footer_text' => 'nullable|string|max:1000',
            'invoice_notes_template' => 'nullable|string|max:2000',
        ]);

        // Build settings array
        $settings = $company->settings ?? [];
        $settings['invoice'] = array_merge($settings['invoice'] ?? [], $validated);

        $company->update(['settings' => $settings]);

        return redirect()
            ->route('settings.invoices')
            ->with('success', 'Paramètres de facturation mis à jour.');
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $company = Company::current();

        // Delete old logo
        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos', 'public');

        $company->update(['logo_path' => $path]);

        return redirect()
            ->route('settings.company')
            ->with('success', 'Logo mis à jour.');
    }

    /**
     * Delete company logo
     */
    public function deleteLogo()
    {
        $company = Company::current();

        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->update(['logo_path' => null]);

        return redirect()
            ->route('settings.company')
            ->with('success', 'Logo supprimé.');
    }

    /**
     * Show users management
     */
    public function users()
    {
        $company = Company::current();
        $users = $company->users()->withPivot('role')->get();

        return view('settings.users', compact('company', 'users'));
    }

    /**
     * Invite a user to the company
     */
    public function inviteUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:member,accountant,admin',
        ]);

        $company = Company::current();

        // Check if there's already a pending invitation
        $existingInvitation = InvitationToken::where('email', $validated['email'])
            ->where('company_id', $company->id)
            ->pending()
            ->first();

        if ($existingInvitation) {
            return back()->with('error', 'Une invitation est déjà en attente pour cette adresse email.');
        }

        // Create user with random password (will be set via invitation)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt(\Illuminate\Support\Str::random(32)), // Temporary, will be changed on invitation accept
        ]);

        // Generate invitation token
        $invitation = InvitationToken::generate(
            user: $user,
            invitedBy: auth()->user(),
            company: $company,
            role: $validated['role'],
            validHours: 72 // 3 days
        );

        // Send invitation email
        Mail::to($user->email)->send(new UserInvitation($invitation));

        return back()->with('success', "Invitation envoyée à {$user->email} avec succès.");
    }

    /**
     * Update user role in company
     */
    public function updateUserRole(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'role' => 'required|in:member,accountant,admin,owner',
        ]);

        $company = Company::current();

        // Check if user is in the company
        if (!$company->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Cet utilisateur ne fait pas partie de l\'entreprise.');
        }

        // Cannot change your own role
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        // If changing to owner, current user must be owner
        if ($request->role === 'owner') {
            $currentUserRole = $company->users()->where('user_id', auth()->id())->first()?->pivot->role;
            if ($currentUserRole !== 'owner') {
                return back()->with('error', 'Seul un propriétaire peut transférer la propriété.');
            }

            // Transfer ownership: current owner becomes admin
            $company->users()->updateExistingPivot(auth()->id(), ['role' => 'admin']);
        }

        $company->users()->updateExistingPivot($user->id, ['role' => $request->role]);

        return back()->with('success', "Le rôle de {$user->full_name} a été mis à jour.");
    }

    /**
     * Remove user from company
     */
    public function removeUser(\App\Models\User $user)
    {
        $company = Company::current();

        // Cannot remove yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous retirer vous-même de l\'entreprise.');
        }

        // Check if user is in the company
        if (!$company->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Cet utilisateur ne fait pas partie de l\'entreprise.');
        }

        // Cannot remove the last owner
        $userRole = $company->users()->where('user_id', $user->id)->first()?->pivot->role;
        if ($userRole === 'owner') {
            $ownerCount = $company->users()->wherePivot('role', 'owner')->count();
            if ($ownerCount <= 1) {
                return back()->with('error', 'Impossible de retirer le dernier propriétaire de l\'entreprise.');
            }
        }

        $company->users()->detach($user->id);

        return back()->with('success', "{$user->full_name} a été retiré de l'entreprise.");
    }

    /**
     * Export company data
     */
    public function export()
    {
        $company = Company::current();

        // Generate export data
        $data = [
            'company' => $company->toArray(),
            'partners' => $company->partners()->get()->toArray(),
            'invoices' => $company->invoices()->with('lines')->get()->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="export-' . $company->slug . '-' . date('Y-m-d') . '.json"');
    }
}
