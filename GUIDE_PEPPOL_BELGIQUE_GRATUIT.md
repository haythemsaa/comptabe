# 🇧🇪 Guide Peppol GRATUIT pour la Belgique

## Options Gratuites pour Commencer

### **Option 1: Mode Test Intégré (GRATUIT - Recommandé pour débuter)**

✅ **Idéal pour**: Tests, démo clients, apprentissage
❌ **Limitation**: Factures non envoyées réellement (simulation)

**Avantages:**
- Gratuit, illimité
- Aucune inscription requise
- Génération UBL XML conforme
- Test complet du workflow
- Parfait pour montrer à un client

**Configuration:**
```env
PEPPOL_TESTING=true
PEPPOL_PROVIDER=recommand
```

---

### **Option 2: Recommand.eu - Open Source (GRATUIT)**

✅ **Idéal pour**: Développement, tests réels, petites PME
❌ **Limitation**: Support communautaire uniquement

**Inscription gratuite:**
1. Aller sur: https://playground.recommand.eu
2. Créer un compte développeur (gratuit)
3. Obtenir une API key de test
4. Utiliser leur sandbox pour tests réels

**Configuration:**
```env
PEPPOL_PROVIDER=recommand
PEPPOL_RECOMMAND_API_URL=https://api.recommand.eu/v1
PEPPOL_RECOMMAND_API_KEY=votre_api_key_ici
PEPPOL_TESTING=false
```

**Coût:** GRATUIT (open-source)

---

### **Option 3: Peppol-Box.be - Service Belge**

✅ **Idéal pour**: PME belges, support local
❌ **Limitation**: À partir de 5€/mois

**Plans:**
- **Starter**: 5€/mois - 25 factures
- **Pro**: 15€/mois - 100 factures
- **Business**: 35€/mois - 500 factures

**Inscription:**
1. https://www.peppol-box.be
2. Choisir plan Starter (1er mois souvent gratuit)
3. Obtenir API credentials

**Configuration:**
```env
PEPPOL_PROVIDER=custom
PEPPOL_CUSTOM_API_URL=https://api.peppol-box.be/v1
PEPPOL_CUSTOM_API_KEY=votre_api_key
```

---

### **Option 4: eFacure.belgium.be - Plateforme Gouvernementale**

✅ **Idéal pour**: Factures B2G (entreprise → gouvernement)
❌ **Limitation**: Uniquement pour factures au gouvernement

**Gratuit pour:**
- Factures aux administrations publiques
- Conformité obligatoire B2G

**Site:** https://efacture.belgium.be

---

## 🚀 GUIDE RAPIDE: Démarrer avec Mode Test (GRATUIT)

### Étape 1: Configuration .env

Ajoutez ces lignes à votre fichier `.env`:

```env
# Peppol Configuration - Mode Test (GRATUIT)
PEPPOL_PROVIDER=recommand
PEPPOL_TESTING=true
PEPPOL_SCHEME=0208
```

### Étape 2: Configurer une Entreprise pour Peppol

Via l'interface admin ou la console:

```bash
php artisan tinker
```

Puis:

```php
$company = App\Models\Company::first(); // Ou ::find('company-id')

// Activer Peppol en mode test
$company->update([
    'peppol_enabled' => true,
    'peppol_test_mode' => true,
    'peppol_participant_id' => '0208:BE' . $company->vat_number, // Ex: 0208:BE0123456789
]);
```

### Étape 3: Configurer un Partenaire (Client) Peppol

```php
$partner = App\Models\Partner::first(); // Votre client

$partner->update([
    'peppol_id' => '0208:BE9876543210', // Son numéro Peppol
    'peppol_enabled' => true,
]);
```

### Étape 4: Envoyer une Facture Test

Via l'interface web ou:

```php
$invoice = App\Models\Invoice::first();
$peppolService = new App\Services\Peppol\PeppolService();

try {
    $transmission = $peppolService->sendInvoice($invoice);
    echo "✓ Facture envoyée via Peppol (mode test)\n";
    echo "Message ID: " . $transmission->message_id . "\n";
    echo "Statut: " . $transmission->status . "\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
```

---

## 📋 Checklist pour un Client Réel

### Documents nécessaires:

- [ ] **Numéro BCE** (Banque-Carrefour des Entreprises)
- [ ] **Numéro TVA** belge actif
- [ ] **Email de contact** de l'entreprise
- [ ] **Coordonnées bancaires** IBAN/BIC

### Configuration ComptaBE:

1. **Créer la société du client:**
   - Admin → Companies → New Company
   - Renseigner BCE/TVA
   - Activer Peppol en mode test

2. **Créer un utilisateur client:**
   - Admin → Users → New User
   - Assigner à la société
   - Envoyer identifiants

3. **Tester l'envoi:**
   - Créer une facture test
   - Envoyer via Peppol (mode test)
   - Vérifier la génération UBL XML

4. **Migration vers production:**
   - Obtenir API key d'un provider
   - Désactiver mode test
   - Envoyer facture réelle

---

## 🔧 Commandes Utiles

### Vérifier la configuration Peppol:

```bash
php artisan peppol:check
```

### Tester l'envoi d'une facture:

```bash
php artisan peppol:send-test {invoice_id}
```

### Vérifier un Participant ID:

```bash
php artisan peppol:lookup {participant_id}
```

---

## 📊 Format Peppol ID Belgique

**Format:** `scheme:identifier`

**Exemples:**
- `0208:BE0123456789` (numéro BCE/KBO)
- `9925:BE0123456789` (numéro TVA)

**Schémas Belgique:**
- `0208` = Numéro d'entreprise belge (BCE/KBO) - **RECOMMANDÉ**
- `9925` = Numéro TVA belge

---

## ⚠️ Points d'Attention

### Obligatoire en Belgique:

- **B2G (Entreprise → Gouvernement)**: Déjà obligatoire depuis 2019
- **B2B (Entreprise → Entreprise)**: Obligatoire à partir du **1er janvier 2026**

### Avant d'envoyer en production:

1. ✅ Vérifier que le client a un Peppol ID valide
2. ✅ Tester en mode sandbox
3. ✅ Valider le fichier UBL XML généré
4. ✅ Avoir un provider configuré (API key)
5. ✅ Informer le client qu'il recevra via Peppol

---

## 🎯 Recommandation pour Votre Cas

**Pour démarrer AUJOURD'HUI avec un client:**

1. **Utilisez le MODE TEST** (gratuit, illimité)
   - Montrez la génération UBL XML
   - Démontrez le workflow complet
   - Aucun coût

2. **Pour production ensuite:**
   - **Peppol-Box.be** (5€/mois) - Support belge, simple
   - OU **Recommand.eu** (gratuit) - Open-source, API complète

3. **Workflow recommandé:**
   ```
   Mode Test (démo) → Recommand.eu (tests réels) → Peppol-Box.be (production)
   ```

---

## 📞 Support

- **Documentation Peppol BE**: https://peppol.eu/get-started/
- **Recommand.eu Docs**: https://docs.recommand.eu
- **eFacure Belgium**: https://efacture.belgium.be

---

**Dernière mise à jour:** 2026-01-01
**Version:** 1.0
**Auteur:** ComptaBE Team
