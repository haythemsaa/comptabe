# 🔧 Corriger le Problème de Session Company

**Erreur:** "No active company found. Please ensure you are logged in and have selected a company."

**Cause:** Votre session n'a pas de `current_tenant_id` défini.

---

## ✅ Solution Rapide

### Option 1: Déconnexion/Reconnexion (Recommandé)

1. **Déconnectez-vous** de l'application
2. **Reconnectez-vous** avec vos identifiants
3. **Essayez à nouveau** d'envoyer la facture via Peppol

### Option 2: Vérifier la Session via Console

Ouvrez la console Laravel Tinker:

```bash
php artisan tinker
```

Puis exécutez:

```php
// Vérifier si vous êtes authentifié
auth()->check()  // Doit retourner true

// Vérifier l'utilisateur actuel
auth()->user()

// Vérifier la company de l'utilisateur
auth()->user()->company

// Vérifier le current_tenant_id dans la session
session('current_tenant_id')  // Doit retourner un UUID

// Si null, définir manuellement (TEMPORAIRE):
session(['current_tenant_id' => auth()->user()->company_id]);
```

### Option 3: Fix Rapide via Route

Créez une route temporaire pour fixer la session:

```php
// Dans routes/web.php (TEMPORAIRE)
Route::get('/fix-session', function() {
    $user = auth()->user();
    if ($user && $user->company_id) {
        session(['current_tenant_id' => $user->company_id]);
        return redirect('/dashboard')->with('success', 'Session corrigée!');
    }
    return redirect('/login')->with('error', 'Veuillez vous connecter d\'abord.');
})->middleware('auth');
```

Puis accédez à: `http://127.0.0.1:8002/fix-session`

---

## 🔍 Diagnostic Complet

### Vérifier l'État Actuel

```bash
php artisan tinker
```

```php
// 1. Utilisateur connecté ?
$user = auth()->user();
dd([
    'authenticated' => auth()->check(),
    'user_id' => $user?->id,
    'user_email' => $user?->email,
    'company_id' => $user?->company_id,
    'company_name' => $user?->company?->name,
    'session_tenant_id' => session('current_tenant_id'),
]);
```

### Résultat Attendu

```
✅ authenticated: true
✅ user_id: "8f2253c9-7821-41f0-b16c-2a2fbd9a9242"
✅ user_email: "admin@bruxelles-containers.be"
✅ company_id: "8f2253c9-7821-41f0-b16c-2a2fbd9a9242"
✅ company_name: "Bruxelles Containers SPRL"
✅ session_tenant_id: "8f2253c9-7821-41f0-b16c-2a2fbd9a9242"
```

### Si `session_tenant_id` est `null`

C'est ça le problème! La session n'a pas été initialisée correctement lors de la connexion.

---

## 🛠️ Correction Permanente

### Vérifier le LoginController

Le `LoginController` devrait définir `current_tenant_id` lors de la connexion.

**Fichier:** `app/Http/Controllers/Auth/LoginController.php`

**Code à ajouter/vérifier:**

```php
protected function authenticated(Request $request, $user)
{
    // Set current tenant in session
    if ($user->company_id) {
        session(['current_tenant_id' => $user->company_id]);
    }

    return redirect()->intended('/dashboard');
}
```

### Vérifier le Middleware

**Fichier:** `app/Http/Middleware/SetCurrentTenant.php` (si existe)

Devrait contenir:

```php
public function handle($request, Closure $next)
{
    if (auth()->check() && !session('current_tenant_id')) {
        session(['current_tenant_id' => auth()->user()->company_id]);
    }

    return $next($request);
}
```

---

## 🚀 Test Après Correction

1. **Déconnectez-vous**
2. **Reconnectez-vous**
3. **Vérifiez dans Tinker:**

```bash
php artisan tinker
```

```php
session('current_tenant_id')  // Doit retourner un UUID
Company::current()            // Doit retourner votre company
```

4. **Essayez d'envoyer via Peppol**
   - Allez sur la facture: http://127.0.0.1:8002/invoices/df44db03-52ef-4e35-87ae-3bc63d2749b3
   - Cliquez "Envoyer via Peppol"
   - ✅ Devrait fonctionner maintenant!

---

## 📝 Note

Si le problème persiste même après reconnexion, c'est que le code de login ne définit pas correctement `current_tenant_id`. Dans ce cas, utilisez la route `/fix-session` comme solution temporaire ou modifiez le `LoginController` de façon permanente.

---

**Date:** 2026-01-01
**Status:** Guide de correction créé
