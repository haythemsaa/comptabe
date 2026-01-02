<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['companies'])
            ->withTrashed();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            if ($request->role === 'superadmin') {
                $query->where('is_superadmin', true);
            } elseif ($request->role === 'admin') {
                $query->whereHas('companies', fn($q) => $q->where('company_user.role', 'admin'));
            } elseif ($request->role === 'user') {
                $query->whereHas('companies', fn($q) => $q->where('company_user.role', 'user'));
            }
        }

        // Company filter
        if ($request->filled('company')) {
            $query->whereHas('companies', fn($q) => $q->where('companies.id', $request->company));
        }

        $users = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $companies = Company::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'companies'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $companyRoles = ['owner' => 'Propriétaire', 'admin' => 'Administrateur', 'accountant' => 'Comptable', 'user' => 'Utilisateur', 'readonly' => 'Lecture seule'];
        return view('admin.users.create', compact('companies', 'companyRoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => ['required', Password::defaults()],
            'is_superadmin' => 'boolean',
            'email_verified' => 'boolean',
            'company_id' => 'nullable|exists:companies,id',
            'company_role' => 'nullable|string|in:owner,admin,accountant,user,readonly',
        ]);

        $user = new User();
        $user->email = $validated['email'];
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->password = Hash::make($validated['password']);
        $user->is_superadmin = $request->boolean('is_superadmin');
        $user->is_active = true;
        if ($request->boolean('email_verified')) {
            $user->email_verified_at = now();
        }
        $user->save();

        // Attach to company if specified
        if (!empty($validated['company_id'])) {
            $user->companies()->attach($validated['company_id'], [
                'role' => $validated['company_role'] ?? 'user',
                'is_default' => true,
            ]);
        }

        AuditLog::log('create', "Utilisateur {$user->email} créé (admin)", $user);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $user->load(['companies']);

        $recentActivity = AuditLog::where('user_id', $user->id)
            ->with('company')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.users.show', compact('user', 'recentActivity'));
    }

    public function edit(User $user)
    {
        $user->load('companies');
        $companies = Company::orderBy('name')->get();
        $companyRoles = ['owner' => 'Propriétaire', 'admin' => 'Administrateur', 'accountant' => 'Comptable', 'user' => 'Utilisateur', 'readonly' => 'Lecture seule'];

        return view('admin.users.edit', compact('user', 'companies', 'companyRoles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => ['nullable', Password::defaults()],
            'is_superadmin' => 'boolean',
            'email_verified' => 'boolean',
            'company_id' => 'nullable|exists:companies,id',
            'company_role' => 'nullable|string|in:owner,admin,accountant,user,readonly',
        ]);

        $oldValues = $user->only(['email', 'first_name', 'last_name', 'is_superadmin']);

        $user->email = $validated['email'];
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];

        // Only update superadmin if not editing own account
        if ($user->id !== auth()->id()) {
            $user->is_superadmin = $request->boolean('is_superadmin');
        }

        // Handle email verification
        if ($request->boolean('email_verified') && !$user->email_verified_at) {
            $user->email_verified_at = now();
        } elseif (!$request->boolean('email_verified')) {
            $user->email_verified_at = null;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Handle company assignment
        if (!empty($validated['company_id'])) {
            $companyRole = $validated['company_role'] ?? 'user';
            if ($user->companies()->where('companies.id', $validated['company_id'])->exists()) {
                // Update existing pivot
                $user->companies()->updateExistingPivot($validated['company_id'], ['role' => $companyRole]);
            } else {
                // Attach new company
                $user->companies()->attach($validated['company_id'], [
                    'role' => $companyRole,
                    'is_default' => $user->companies()->count() === 0,
                ]);
            }
        }

        AuditLog::log('update', "Utilisateur {$user->email} modifié (admin)", $user, $oldValues);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function toggleSuperadmin(User $user)
    {
        // Prevent removing own superadmin status
        if ($user->id === auth()->id() && $user->is_superadmin) {
            return back()->with('error', 'Vous ne pouvez pas retirer vos propres droits superadmin.');
        }

        $user->is_superadmin = !$user->is_superadmin;
        $user->save();

        $action = $user->is_superadmin ? 'accordés' : 'retirés';
        AuditLog::log('update', "Droits superadmin {$action} pour {$user->email}", $user);

        return back()->with('success', "Droits superadmin {$action}.");
    }

    public function toggleActive(User $user)
    {
        // Prevent deactivating self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'activé' : 'désactivé';
        AuditLog::log($user->is_active ? 'activate' : 'suspend', "Utilisateur {$user->email} {$action}", $user);

        return back()->with('success', "Utilisateur {$action}.");
    }

    public function resetPassword(User $user)
    {
        $newPassword = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 12);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        AuditLog::log('password_reset', "Mot de passe réinitialisé pour {$user->email} (admin)", $user);

        return back()->with('success', "Mot de passe réinitialisé. Nouveau mot de passe: {$newPassword}");
    }

    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous impersonner vous-même.');
        }

        // Store original user
        session(['admin_original_user' => auth()->id()]);

        AuditLog::log('impersonate', "Impersonation de l'utilisateur {$user->email}", $user);

        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('warning', "Vous êtes maintenant connecté en tant que {$user->full_name}.");
    }

    public function stopImpersonate()
    {
        $originalUserId = session('admin_original_user');

        if ($originalUserId) {
            session()->forget('admin_original_user');
            $originalUser = User::find($originalUserId);
            if ($originalUser) {
                auth()->login($originalUser);
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Retour au mode administrateur.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $email = $user->email;
        $user->delete();

        AuditLog::log('delete', "Utilisateur {$email} supprimé");

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    public function restore(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        AuditLog::log('activate', "Utilisateur {$user->email} restauré", $user);

        return back()->with('success', 'Utilisateur restauré.');
    }
}
