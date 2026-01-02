# 🚀 Peppol Belgique - Démarrage Rapide (5 minutes)

## ✅ MODE TEST GRATUIT - Configurer un Client MAINTENANT

### **Prérequis**
- ✅ Application ComptaBE installée
- ✅ `.env` configuré (déjà fait avec `PEPPOL_TESTING=true`)

---

## 📝 **Méthode 1: Commande Automatique (RECOMMANDÉ)**

### Étape 1: Lancer la commande setup

```bash
php artisan peppol:setup-client --test
```

### Étape 2: Répondre aux questions

```
Company name: Mon Client SA
VAT number: BE0123456789
Contact email: client@example.com
Create admin user? Yes
User email: admin@client.be
User name: Jean Dupont
Create test partner? Yes
```

### Étape 3: C'est fait ! ✓

Vous recevrez:
- ✅ Société créée avec Peppol activé (mode test)
- ✅ Utilisateur admin créé (mot de passe affiché)
- ✅ Partenaire test créé
- ✅ Peppol ID: `0208:BE0123456789`

**Temps:** 2 minutes

---

## 📝 **Méthode 2: Interface Web (Via Admin)**

### Étape 1: Créer la Société

1. Login en tant que **superadmin**
2. Aller dans **Admin → Companies → Create**
3. Remplir:
   - Name: `Mon Client SA`
   - VAT: `0123456789`
   - Country: `Belgium`
   - Email: `client@example.com`
4. Cliquer **Save**

### Étape 2: Activer Peppol

1. Sur la page de la société, cliquer **Edit**
2. Scroller vers **Peppol Settings**
3. Cocher:
   - ✅ `Peppol Enabled`
   - ✅ `Test Mode`
4. Le champ `Peppol Participant ID` se remplit automatiquement: `0208:BE0123456789`
5. Cliquer **Save**

### Étape 3: Créer un Utilisateur

1. **Admin → Users → Create**
2. Remplir:
   - Email: `admin@client.be`
   - Name: `Jean Dupont`
   - Company: `Mon Client SA`
   - Role: `Owner`
3. Cliquer **Save**
4. Envoyer les identifiants au client

### Étape 4: Créer un Partenaire Test (optionnel)

1. Login avec le compte client
2. **Partners → Create**
3. Remplir:
   - Name: `Test Customer SA`
   - VAT: `0987654321`
   - Type: `Customer`
   - Peppol ID: `0208:BE0987654321`
   - ✅ Peppol Enabled
4. Cliquer **Save**

**Temps:** 5 minutes

---

## 💡 **Méthode 3: Via Tinker (Développeurs)**

```bash
php artisan tinker
```

```php
use App\Models\Company;
use App\Models\User;
use App\Models\Partner;
use Illuminate\Support\Str;

// 1. Créer la société
$company = Company::create([
    'id' => Str::uuid(),
    'name' => 'Mon Client SA',
    'vat_number' => '0123456789',
    'country_code' => 'BE',
    'email' => 'client@example.com',
    'currency' => 'EUR',
    'language' => 'fr',
    'peppol_enabled' => true,
    'peppol_test_mode' => true,
    'peppol_participant_id' => '0208:BE0123456789',
]);

// 2. Créer l'utilisateur
$user = User::create([
    'id' => Str::uuid(),
    'company_id' => $company->id,
    'email' => 'admin@client.be',
    'name' => 'Jean Dupont',
    'first_name' => 'Jean',
    'last_name' => 'Dupont',
    'password' => bcrypt('password123'),
    'role' => 'owner',
    'is_active' => true,
]);

// 3. Créer un partenaire test
$partner = Partner::create([
    'id' => Str::uuid(),
    'company_id' => $company->id,
    'name' => 'Test Customer SA',
    'vat_number' => '0987654321',
    'email' => 'customer@test.be',
    'type' => 'customer',
    'peppol_id' => '0208:BE0987654321',
    'peppol_enabled' => true,
]);

echo "✓ Client configuré!\n";
echo "Email: {$user->email}\n";
echo "Peppol ID: {$company->peppol_participant_id}\n";
```

**Temps:** 1 minute

---

## 🧪 **Tester l'Envoi Peppol**

### Via l'interface:

1. **Login** avec le compte client
2. **Invoices → Create**
3. Créer une facture pour le partenaire test
4. Cliquer **Send via Peppol**
5. ✅ **Mode test** → La facture sera simulée, pas envoyée réellement

### Via Console:

```bash
php artisan tinker
```

```php
use App\Models\Invoice;
use App\Services\Peppol\PeppolService;

// Récupérer une facture
$invoice = Invoice::first(); // ou ::find('invoice-id')

// Envoyer via Peppol
$peppolService = new PeppolService();
$transmission = $peppolService->sendInvoice($invoice);

echo "Statut: " . $transmission->status . "\n";
echo "Message ID: " . $transmission->message_id . "\n";
```

### Résultat attendu:

```
✓ Facture envoyée (MODE TEST - simulé)
Statut: sent
Message ID: 550e8400-e29b-41d4-a716-446655440000
```

---

## 📊 **Vérifier les Transmissions**

### Via l'interface:

**E-Reporting → Peppol Transmissions**

Vous verrez:
- ✅ Liste des factures envoyées
- 📅 Date d'envoi
- 🆔 Message ID Peppol
- ✅ Statut (sent/failed)
- 📄 XML UBL généré

### Via Database:

```bash
php artisan tinker
```

```php
use App\Models\PeppolTransmission;

// Dernières transmissions
PeppolTransmission::latest()->take(5)->get();

// Transmissions d'une société
PeppolTransmission::where('company_id', 'company-id')->get();
```

---

## 🎯 **Informations Client à Communiquer**

Envoyez ceci à votre client:

```
Bonjour,

Votre compte ComptaBE avec facturation Peppol est prêt!

🔐 Identifiants:
- URL: http://compta.test (ou votre domaine)
- Email: admin@client.be
- Mot de passe: [généré par la commande]

📋 Votre Peppol ID: 0208:BE0123456789

✅ Mode TEST activé:
- Vous pouvez créer des factures
- L'envoi Peppol est simulé (pas de transmission réelle)
- Parfait pour tester l'interface et le workflow

📖 Documentation:
- Guide utilisateur: /docs
- Support: support@comptabe.com

Cordialement,
L'équipe ComptaBE
```

---

## ⚠️ **Limitations Mode Test**

✅ **Ce qui fonctionne:**
- Création de factures
- Génération UBL XML conforme
- Workflow complet (envoi, statut, historique)
- Tous les écrans et rapports

❌ **Ce qui est simulé:**
- L'envoi réel via le réseau Peppol
- La réception par le client final
- Les accusés de réception

**→ Pour l'envoi RÉEL, il faut:**
1. Obtenir une API key (Recommand.eu gratuit ou Peppol-Box.be à partir de 5€/mois)
2. Mettre `PEPPOL_TESTING=false` dans `.env`
3. Configurer `PEPPOL_RECOMMAND_API_KEY` (ou autre provider)

---

## 🚀 **Passer en Production**

Quand le client est prêt pour l'envoi réel:

### Option A: Recommand.eu (Gratuit)

1. Aller sur https://playground.recommand.eu
2. Créer un compte (gratuit)
3. Obtenir une API key de test
4. Mettre dans `.env`:
   ```env
   PEPPOL_TESTING=false
   PEPPOL_RECOMMAND_API_KEY=votre_clé_api
   ```
5. Tester avec 1 facture
6. Passer en production

### Option B: Peppol-Box.be (Payant - Support BE)

1. Aller sur https://www.peppol-box.be
2. S'inscrire (plan Starter à 5€/mois)
3. Obtenir API credentials
4. Mettre dans `.env`:
   ```env
   PEPPOL_PROVIDER=custom
   PEPPOL_CUSTOM_API_URL=https://api.peppol-box.be/v1
   PEPPOL_CUSTOM_API_KEY=votre_clé_api
   PEPPOL_TESTING=false
   ```

---

## 📞 **Besoin d'Aide?**

- 📖 **Documentation complète**: `GUIDE_PEPPOL_BELGIQUE_GRATUIT.md`
- 🛠️ **Commandes disponibles**:
  ```bash
  php artisan peppol:setup-client --help
  php artisan peppol:check
  php artisan peppol:send-test {invoice_id}
  ```

---

**Dernière mise à jour:** 2026-01-01
**Temps total setup:** 2-5 minutes
**Coût:** 0€ (mode test illimité)
