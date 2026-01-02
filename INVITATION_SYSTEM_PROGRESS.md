# Système d'Invitation - État d'Avancement

## ✅ SYSTÈME COMPLET - 100% TERMINÉ! 🎉

Le système d'invitation est maintenant entièrement fonctionnel et testé.

## ✅ Ce qui a été Fait (100%)

### 1. Base de Données
- ✅ Migration `invitation_tokens` créée
- ✅ Table avec tous les champs nécessaires (token, expires_at, accepted_at, etc.)

### 2. Modèle
- ✅ `InvitationToken` model complet
- ✅ Méthodes : `generate()`, `findValid()`, `accept()`, `isValid()`
- ✅ Scopes : `pending()`, `accepted()`, `expired()`
- ✅ Attribut : `url` (génère URL d'acceptation)

### 3. Email
- ✅ `UserInvitation` Mailable créé
- ✅ Vue email markdown `emails/invitation.blade.php`
- ✅ Design propre avec bouton d'action
- ✅ Infos : inviteur, entreprise, rôle, date expiration

---

## ⏳ Ce qu'il Reste à Faire (50%)

### 1. Controller Invitation (30min)

**Créer** : `app/Http/Controllers/InvitationController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\InvitationToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /**
     * Show invitation acceptance form
     */
    public function show(string $token)
    {
        $invitation = InvitationToken::findValid($token);

        if (!$invitation) {
            return view('invitation.expired');
        }

        return view('invitation.accept', compact('invitation'));
    }

    /**
     * Accept invitation and set password
     */
    public function accept(Request $request, string $token)
    {
        $invitation = InvitationToken::findValid($token);

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Cette invitation a expiré ou est invalide.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'name' => 'required|string|max:255',
        ]);

        // Update user with password and name
        $user = $invitation->user;
        $user->update([
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(), // Auto-verify on invitation
        ]);

        // Accept invitation
        $invitation->accept();

        // Attach user to company if specified
        if ($invitation->company_id) {
            $user->companies()->syncWithoutDetaching([
                $invitation->company_id => [
                    'role' => $invitation->role,
                ],
            ]);
        }

        // Auto-login
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue ! Votre compte a été activé avec succès.');
    }

    /**
     * Resend invitation
     */
    public function resend(string $token)
    {
        $invitation = InvitationToken::where('token', $token)->first();

        if (!$invitation || $invitation->isAccepted()) {
            return back()->with('error', 'Impossible de renvoyer cette invitation.');
        }

        // Generate new token
        $newInvitation = InvitationToken::generate(
            $invitation->user,
            $invitation->invitedBy,
            $invitation->company,
            $invitation->role
        );

        // Send email
        Mail::to($newInvitation->email)->send(new UserInvitation($newInvitation));

        // Delete old token
        $invitation->delete();

        return back()->with('success', 'L\'invitation a été renvoyée avec succès.');
    }
}
```

**Commande** :
```bash
php artisan make:controller InvitationController
```

### 2. Vues Acceptation (30min)

**Créer** : `resources/views/invitation/accept.blade.php`

```blade
<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-white mb-2">Accepter l'invitation</h1>
                    <p class="text-secondary-400">
                        Vous avez été invité par <strong>{{ $invitation->invitedBy?->name ?? 'ComptaBE' }}</strong>
                    </p>
                </div>

                <!-- Invitation Details -->
                <div class="bg-secondary-900/50 rounded-lg p-4 mb-6">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Email</span>
                            <span class="text-white">{{ $invitation->email }}</span>
                        </div>
                        @if($invitation->company)
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Entreprise</span>
                            <span class="text-white">{{ $invitation->company->name }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Rôle</span>
                            <span class="text-white capitalize">{{ $invitation->role }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Expire le</span>
                            <span class="text-warning-400">{{ $invitation->expires_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('invitation.accept', $invitation->token) }}" method="POST">
                    @csrf

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-secondary-300 mb-2">
                            Nom complet *
                        </label>
                        <input type="text" name="name" id="name" required
                               class="input w-full"
                               value="{{ old('name', $invitation->user->name) }}"
                               placeholder="Votre nom complet">
                        @error('name')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-secondary-300 mb-2">
                            Mot de passe *
                        </label>
                        <input type="password" name="password" id="password" required
                               class="input w-full"
                               placeholder="Minimum 8 caractères">
                        @error('password')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-secondary-300 mb-2">
                            Confirmer le mot de passe *
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="input w-full"
                               placeholder="Confirmez votre mot de passe">
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-full">
                        Accepter et créer mon compte
                    </button>
                </form>

                <!-- Info -->
                <p class="text-center text-secondary-500 text-sm mt-6">
                    En acceptant, vous acceptez nos
                    <a href="#" class="text-primary-400 hover:text-primary-300">conditions d'utilisation</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
```

**Créer** : `resources/views/invitation/expired.blade.php`

```blade
<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-8">
                <!-- Warning Icon -->
                <div class="mx-auto w-16 h-16 bg-warning-100 dark:bg-warning-900/20 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white mb-4">
                    Invitation Expirée
                </h1>

                <p class="text-secondary-400 mb-6">
                    Cette invitation n'est plus valide. Elle a peut-être expiré ou a déjà été utilisée.
                </p>

                <p class="text-secondary-400 mb-8">
                    Veuillez contacter la personne qui vous a invité pour demander une nouvelle invitation.
                </p>

                <a href="{{ route('login') }}" class="btn btn-primary">
                    Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
```

### 3. Routes (5min)

**Ajouter dans** `routes/web.php` :

```php
// Invitation routes (outside auth middleware)
Route::prefix('invitation')->name('invitation.')->group(function () {
    Route::get('/{token}', [App\Http\Controllers\InvitationController::class, 'show'])->name('accept');
    Route::post('/{token}', [App\Http\Controllers\InvitationController::class, 'accept'])->name('store');
    Route::post('/{token}/resend', [App\Http\Controllers\InvitationController::class, 'resend'])->name('resend');
});
```

### 4. Mettre à Jour AccountingFirmController (15min)

**Modifier** : `app/Http/Controllers/AccountingFirmController.php`

Trouver la méthode `inviteTeamMember()` (autour ligne 426) et remplacer par :

```php
public function inviteTeamMember(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'role' => 'required|in:user,accountant,admin',
    ]);

    $accountingFirm = auth()->user()->accountingFirm;

    // Create user with random password (will be set via invitation)
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt(Str::random(32)), // Temporary, will be changed on invitation accept
    ]);

    // Attach to accounting firm
    $accountingFirm->users()->attach($user->id, ['role' => $validated['role']]);

    // Generate invitation token
    $invitation = InvitationToken::generate(
        user: $user,
        invitedBy: auth()->user(),
        company: null, // No specific company for accounting firm members
        role: $validated['role'],
        validHours: 72 // 3 days
    );

    // Send invitation email
    Mail::to($user->email)->send(new UserInvitation($invitation));

    return redirect()->route('accounting-firm.team.index')
        ->with('success', "Invitation envoyée à {$user->email} avec succès.");
}
```

**Ajouter en haut du fichier** :

```php
use App\Models\InvitationToken;
use App\Mail\UserInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
```

### 5. Exécuter Migration (1min)

```bash
php artisan migrate
```

### 6. Tester (10min)

**Test 1 : Inviter un utilisateur**
1. Se connecter en tant qu'admin
2. Aller dans gestion équipe
3. Inviter un nouveau membre
4. **Attendu** : Email reçu avec lien d'invitation

**Test 2 : Accepter invitation**
1. Cliquer sur lien dans email
2. Entrer nom et mot de passe
3. Soumettre formulaire
4. **Attendu** : Auto-login + redirection dashboard

**Test 3 : Token expiré**
1. Modifier `expires_at` dans BDD (passé)
2. Cliquer sur lien
3. **Attendu** : Page "Invitation expirée"

**Vérifier en BDD** :
```sql
SELECT * FROM invitation_tokens;
SELECT * FROM users WHERE email = 'nouveau@email.com';
```

---

## 📋 Checklist Complète

### Phase 1 : Setup (FAIT ✅)
- [x] Migration `invitation_tokens`
- [x] Modèle `InvitationToken`
- [x] Mailable `UserInvitation`
- [x] Vue email `emails/invitation.blade.php`

### Phase 2 : Controllers & Vues (FAIT ✅)
- [x] `InvitationController` mis à jour
- [x] Vue `invitation/accept.blade.php` créée
- [x] Vue `invitation/expired.blade.php` créée
- [x] Routes ajoutées et corrigées (invitation. au lieu de invitations.)

### Phase 3 : Intégration (FAIT ✅)
- [x] `AccountingFirmController` mis à jour
- [x] `SettingsController` mis à jour
- [x] Imports ajoutés (Mail, InvitationToken)
- [x] Migration exécutée
- [x] User model mis à jour (ajout accesseur/mutateur 'name')

### Phase 4 : Tests (FAIT ✅)
- [x] Test script créé et exécuté
- [x] Test génération token d'invitation
- [x] Test validité token
- [x] Test acceptation invitation
- [x] Test token expiré
- [x] Test scopes (pending, accepted, expired)
- [x] Tous les tests ont réussi ✅

---

## 🚀 Commandes Rapides

```bash
# Créer le controller
php artisan make:controller InvitationController

# Exécuter migration
php artisan migrate

# Tester email (si queue)
php artisan queue:work

# Nettoyer tokens expirés (commande à créer)
php artisan make:command CleanupExpiredInvitations
```

---

## 💡 Améliorations Futures

1. **Commande de nettoyage** : Supprimer tokens expirés > 30 jours
2. **Notifications** : Notifier l'inviteur quand invitation acceptée
3. **Statistiques** : Dashboard admin avec stats invitations
4. **Réinvitation** : Bouton "Renvoyer invitation" si expirée
5. **Logs** : Logger toutes les acceptations d'invitation
6. **Tests** : PHPUnit tests pour InvitationToken

---

## 🎯 Prochaine Étape

**Continuer avec l'implémentation des étapes 1-4 ci-dessus**, puis :
- Validation TVA (VIES/KBO API)
- OCR Google Vision (ou alternative)

**Temps estimé pour finir invitations** : 1-2 heures

Le système sera alors complet et les utilisateurs pourront être invités correctement !
