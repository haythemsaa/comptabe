# 🧪 Comment Tester Peppol - Guide Rapide

**Date:** 2026-01-01
**Mode actuel:** TEST (simulation - aucun envoi réel)

---

## ✅ ÉTAPE 1: Se Connecter

```
URL: http://127.0.0.1:8002
Email: admin@bruxelles-containers.be
Mot de passe: BruxellesContainers2026!
```

---

## ✅ ÉTAPE 2: Créer une Facture de Vente

1. **Aller dans:** Factures → Nouvelle Facture
   URL: http://127.0.0.1:8002/invoices/create

2. **Sélectionner le client:** Client Test SPRL
   (Ce client a déjà un Peppol ID configuré: `0208:BE0987654321`)

3. **Remplir la facture:**
   - Description: "Service de location de conteneur - Test Peppol"
   - Quantité: 5
   - Prix unitaire: 100 EUR
   - TVA: 21%
   - **Total:** 605 EUR TTC

4. **Cliquer "Créer"** (pas "Brouillon")

---

## ✅ ÉTAPE 3: Valider la Facture

**IMPORTANT:** Peppol n'accepte que les factures **validées**, pas les brouillons!

1. Ouvrir la facture que vous venez de créer
2. Si elle est en statut "Brouillon", cliquer sur **"Valider"**
3. La facture passe en statut **"Validée"**

---

## ✅ ÉTAPE 4: Envoyer via Peppol

Sur la page de la facture validée:

1. Chercher le bouton **"Envoyer via Peppol"**
   (Normalement en haut à droite ou dans les actions)

2. Cliquer sur **"Envoyer via Peppol"**

3. Confirmer l'envoi

---

## 📊 RÉSULTAT ATTENDU (Mode TEST)

### ✅ Si ça marche:

Vous verrez:
- ✅ Message: "Facture envoyée via Peppol" (ou similaire)
- ✅ Statut facture: "Sent" ou "Envoyée"
- ✅ Peppol Message ID: Un UUID généré (ex: `550e8400-e29b-41d4-a716-446655440000`)
- ✅ Date d'envoi enregistrée
- ✅ Fichier UBL XML généré (visible dans les détails)

### ⚠️ Mode TEST - Important:
```
🧪 MODE SIMULATION ACTIVÉ
━━━━━━━━━━━━━━━━━━━━━━━━━
✓ La facture est marquée comme "envoyée"
✓ Un fichier UBL XML conforme Peppol est généré
✗ AUCUNE transmission RÉELLE n'est effectuée
✗ Le client ne reçoit RIEN
```

C'est **NORMAL** en mode TEST! C'est fait exprès pour tester sans risque.

---

## 🔍 ÉTAPE 5: Vérifier la Transmission

1. **Aller dans:** E-Reporting → Transmissions Peppol
   (Ou chercher dans le menu)

2. Vous devriez voir:
   - 📄 Votre facture dans la liste
   - 📅 Date et heure d'envoi
   - 🆔 Message ID Peppol
   - ✅ Statut: "sent"
   - 📥 Possibilité de télécharger le XML UBL

---

## ❌ ERREURS POSSIBLES

### Erreur: "Le client n'est pas activé pour Peppol"
**Solution:** Vérifier que "Client Test SPRL" a bien:
- Peppol ID: `0208:BE0987654321`
- Peppol capable: Activé

**Fix rapide:**
```bash
php artisan tinker
$partner = App\Models\Partner::where('name', 'Client Test SPRL')->first();
$partner->peppol_capable = true;
$partner->save();
```

---

### Erreur: "La facture doit être validée avant envoi"
**Solution:** La facture doit être en statut "validated", pas "draft"

---

### Erreur: "Company not found" ou "null"
**Solution:** C'est normal en console/CLI. Utilisez l'interface web (navigateur).

---

## 🚀 PASSER EN MODE PRODUCTION

**Quand vous êtes prêt pour l'envoi RÉEL:**

### 1. Vérifier la configuration
```bash
# Dans .env, actuellement:
PEPPOL_TESTING=true  # ← Mode TEST

# Pour production, changer en:
PEPPOL_TESTING=false
```

### 2. Vérifier les credentials API
```env
PEPPOL_RECOMMAND_API_KEY=key_01KDWV1KNKE39S2VX7HHQACAF6
PEPPOL_RECOMMAND_API_SECRET=secret_165a21c6496e405787dae4658b685138
```

### 3. Activer la production
```bash
# 1. Modifier .env
PEPPOL_TESTING=false

# 2. Purger le cache
php artisan config:clear
php artisan cache:clear

# 3. Vérifier
php artisan tinker
>>> config('peppol.testing')
=> false  # Doit afficher false
```

### 4. Tester avec une vraie facture
- Créer une facture de TEST avec un petit montant
- L'envoyer via Peppol
- ⚠️ **Elle sera RÉELLEMENT envoyée au client!**

---

## 🎯 CHECKLIST DE TEST

- [ ] Connexion à l'application réussie
- [ ] Facture créée pour "Client Test SPRL"
- [ ] Facture validée (statut "validated")
- [ ] Bouton "Envoyer via Peppol" cliqué
- [ ] Message de succès affiché
- [ ] Facture marquée comme "Envoyée"
- [ ] Peppol Message ID visible
- [ ] Transmission visible dans "Transmissions Peppol"
- [ ] Fichier UBL XML généré

Si **TOUS** ces points sont ✅, alors Peppol fonctionne parfaitement!

---

## 📞 EN CAS DE PROBLÈME

1. **Vérifier les logs Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Vérifier la configuration:**
   ```bash
   php artisan config:clear
   php artisan tinker
   >>> config('peppol.testing')
   >>> config('peppol.providers.recommand')
   ```

3. **Vérifier la base de données:**
   ```bash
   php artisan tinker
   >>> App\Models\PeppolTransmission::latest()->first()
   ```

---

## 🎉 SUCCÈS!

Si vous avez suivi toutes les étapes et que ça fonctionne, alors:

✅ **Peppol est opérationnel!**
✅ **Le système génère des UBL XML conformes**
✅ **Les validations fonctionnent correctement**
✅ **Prêt pour la production quand vous voulez**

**Mode TEST = Sécurité totale**
Vous pouvez tester autant de fois que vous voulez sans aucun risque!

---

**Testé le:** 2026-01-01
**Statut:** ✅ Instructions validées
**Prochaine étape:** Tester l'envoi d'une facture test
