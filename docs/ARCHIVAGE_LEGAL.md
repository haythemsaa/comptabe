# POLITIQUE D'ARCHIVAGE LÉGAL - ComptaBE

**Date**: 2025-12-31
**Version**: 1.0
**Conforme**: Législation belge 2025

---

## DURÉES DE CONSERVATION OBLIGATOIRES

### Documents Comptables et Fiscaux

| Type de Document | Durée Minimum | Base Légale | Notes |
|------------------|---------------|-------------|-------|
| **Factures d'achat** | 10 ans | AR TVA art. 60 | À partir de la date d'émission |
| **Factures de vente** | 10 ans | AR TVA art. 60 | À partir de la date d'émission |
| **Documents comptables** | 7 ans | C. soc. art. 3:17 | Livre journal, grand livre, balances |
| **Pièces justificatives** | 7 ans | C. soc. art. 3:17 | Notes de frais, bons de commande, etc. |
| **Comptes annuels** | 10 ans | C. soc. art. 3:17 | Depuis dépôt à la BNB |
| **Déclarations TVA** | 7 ans | AR TVA | Périodiques + listings |
| **Déclarations fiscales** | 7 ans | CIR 1992 | Impôt des sociétés, précompte |
| **Inventaires** | 7 ans | C. soc. art. 3:17 | Inventaires annuels |

### Documents Sociaux et RH

| Type de Document | Durée Minimum | Base Légale | Notes |
|------------------|---------------|-------------|-------|
| **Fiches de paie** | **Illimitée** | Code social | Conservation permanente obligatoire |
| **Contrats de travail** | 5 ans | Loi sur les contrats | Après fin du contrat |
| **Déclarations DIMONA** | 5 ans | ONSS | Entrées/sorties travailleurs |
| **Déclarations DMFA** | 7 ans | ONSS | Cotisations sociales |
| **Registres du personnel** | 5 ans | Code social | Après départ du travailleur |
| **Comptes individuels** | **Permanente** | Loi pensions | Droits sociaux |

### Contrats et Documents Juridiques

| Type de Document | Durée Minimum | Base Légale | Notes |
|------------------|---------------|-------------|-------|
| **Contrats commerciaux** | 10 ans | Code civil | Après expiration |
| **Contrats de location** | 10 ans | Code civil | Après fin du bail |
| **Procès-verbaux AG** | **Permanente** | C. soc. | Registres sociaux |
| **Statuts société** | **Permanente** | C. soc. | + modifications |
| **Correspondance importante** | 10 ans | Bonne pratique | Recommandé |

### Documents Bancaires

| Type de Document | Durée Minimum | Base Légale | Notes |
|------------------|---------------|-------------|-------|
| **Relevés bancaires** | 7 ans | C. soc. art. 3:17 | Tous les comptes |
| **Preuves de paiement** | 7 ans | C. soc. art. 3:17 | Virements, paiements |
| **Lettres de crédit** | 10 ans | Code civil | Contrats de financement |

---

## IMPLÉMENTATION TECHNIQUE

### 1. Table `retention_policies`

```sql
CREATE TABLE retention_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type VARCHAR(100) NOT NULL,
    retention_years INT NOT NULL,
    legal_basis VARCHAR(255) NOT NULL,
    permanent BOOLEAN DEFAULT FALSE,
    anonymize_after BOOLEAN DEFAULT TRUE COMMENT 'RGPD compliance',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 2. Données de référence (Seeder)

```php
// database/seeders/RetentionPolicySeeder.php
DB::table('retention_policies')->insert([
    // Fiscales
    ['document_type' => 'invoice', 'retention_years' => 10, 'legal_basis' => 'AR TVA art. 60', 'permanent' => false],
    ['document_type' => 'expense', 'retention_years' => 10, 'legal_basis' => 'AR TVA art. 60', 'permanent' => false],
    ['document_type' => 'vat_declaration', 'retention_years' => 7, 'legal_basis' => 'AR TVA', 'permanent' => false],

    // Comptables
    ['document_type' => 'journal_entry', 'retention_years' => 7, 'legal_basis' => 'C. soc. art. 3:17', 'permanent' => false],
    ['document_type' => 'annual_accounts', 'retention_years' => 10, 'legal_basis' => 'C. soc. art. 3:17', 'permanent' => false],

    // RH - PERMANENTES
    ['document_type' => 'payslip', 'retention_years' => 999, 'legal_basis' => 'Code social', 'permanent' => true],
    ['document_type' => 'employee_account', 'retention_years' => 999, 'legal_basis' => 'Loi pensions', 'permanent' => true],

    // Contrats
    ['document_type' => 'contract', 'retention_years' => 10, 'legal_basis' => 'Code civil', 'permanent' => false],

    // Sociaux - PERMANENTS
    ['document_type' => 'assembly_minutes', 'retention_years' => 999, 'legal_basis' => 'C. soc.', 'permanent' => true],
    ['document_type' => 'company_statutes', 'retention_years' => 999, 'legal_basis' => 'C. soc.', 'permanent' => true],
]);
```

### 3. Command de purge automatique

```bash
# Exécution mensuelle via CRON
php artisan documents:purge-expired
```

**Logique**:
1. Récupérer tous les documents dont `created_at < now() - retention_years`
2. **Exporter en PDF/A avant suppression** (archive légale)
3. Soft delete (`deleted_at`)
4. Logger l'action dans `audit_logs`

### 4. Anonymisation RGPD

Pour les documents contenant des données personnelles:
- Après expiration de la durée légale
- Anonymiser: nom, email, numéro registre national, IBAN
- Conserver: montants, dates, références (besoins statistiques)

---

## OBLIGATIONS LÉGALES SPÉCIFIQUES

### Conservation Numérique

**Article 6 AR 21/02/2014** - Conditions conservation électronique:
1. ✅ **Intégrité**: Hash SHA-256 de chaque document
2. ✅ **Authenticité**: Signature électronique (optionnel mais recommandé)
3. ✅ **Lisibilité**: Format PDF/A pour longue durée
4. ✅ **Accessibilité**: Téléchargement instantané pour contrôle fiscal

### Format des Documents

- **Factures électroniques**: PDF, UBL XML (Peppol)
- **Documents scannés**: PDF/A-1b ou PDF/A-2b
- **Résolution minimum**: 200 DPI pour scans
- **Couleur**: Recommandée pour factures avec logo

### Backup et Redondance

**Obligations** (bonne pratique, pas légal strict):
- Backup quotidien automatique
- Conservation sur 2 sites géographiques différents
- Test de restauration trimestriel
- Encryption AES-256 au repos

---

## CONTRÔLE FISCAL

### Droits de l'Administration

L'administration fiscale peut:
- Demander accès aux documents **dans les 7 ans** (délai normal)
- **10 ans** si soupçon de fraude
- **Accès immédiat** en cas de contrôle sur place

### Pénalités en cas de Non-Conservation

| Infraction | Amende | Base Légale |
|------------|--------|-------------|
| Absence de pièces justificatives | €50 - €125,000 | C. TVA art. 70 |
| Non-présentation lors contrôle | €250 - €3,000 | CIR 1992 |
| Conservation insuffisante | Rejet de frais | Doctrine fiscale |
| Factures non conformes | €12.50 - €1,250 / facture | AR TVA art. 60 §4 |

---

## RGPD ET PROTECTION DES DONNÉES

### Durée de Conservation vs RGPD

**Principe**: Minimisation de la durée de conservation

**Exception comptabilité**: Les obligations légales (7-10 ans) **priment** sur le principe RGPD de minimisation.

**Solution ComptaBE**:
1. Conservation complète durant durée légale
2. **Après expiration**: Anonymisation des données perso
3. Conservation anonymisée pour statistiques (si utile)
4. Suppression définitive si pas de valeur historique

### Données à Anonymiser

Après expiration durée légale:
- Noms et prénoms
- Emails personnels
- Numéros de téléphone
- Numéros de registre national
- IBAN (sauf 4 derniers chiffres)
- Adresses personnelles

**Conservation**:
- Montants €
- Dates
- Types de transactions
- Catégories comptables
- Références document

---

## DESTRUCTION SÉCURISÉE

### Après Expiration Durée Légale

**Méthode**:
1. Soft delete (`deleted_at`) durant 30 jours (période de grâce)
2. Hard delete automatique après 30 jours
3. **Fichiers physiques**: Suppression définitive (overwrite 3 passes)
4. Logging destruction dans `audit_logs`

### Certificat de Destruction

Pour les clients nécessitant traçabilité:
- Générer certificat PDF
- Liste documents détruits
- Date de destruction
- Signature électronique

---

## MIGRATION ANCIENS DOCUMENTS

### Documents Papier vers Numérique

**Recommandations**:
1. Scanner en PDF/A (200 DPI minimum)
2. OCR pour recherche (optionnel)
3. Vérification visuelle qualité
4. Destruction papier **APRÈS** période de conservation numérique validée

**Attention**: Un document scanné doit être **conservé au format numérique** pendant toute la durée légale restante.

---

## CHECKLIST CONFORMITÉ

### Mensuel
- [ ] Vérifier backups fonctionnent
- [ ] Contrôler espace disque disponible

### Trimestriel
- [ ] Test restauration backup
- [ ] Revue documents approchant expiration
- [ ] Génération rapport documents à anonymiser

### Annuel
- [ ] Audit complet politique archivage
- [ ] Mise à jour durées légales si changement législation
- [ ] Formation utilisateurs sur obligations
- [ ] Revue accès admin/superadmin

---

## RÉFÉRENCES LÉGALES

### Législation Belge

1. **Code des Sociétés et Associations**
   - Art. 3:17 - Obligation conservation documents comptables (7 ans)

2. **Arrêté Royal TVA**
   - Art. 60 - Conservation factures (10 ans)
   - Art. 70 - Sanctions non-conservation

3. **Code des Impôts sur les Revenus (CIR 1992)**
   - Conservation déclarations fiscales (7 ans)

4. **Code Social**
   - Conservation fiches de paie (illimitée)
   - Conservation registres personnel (5 ans après départ)

5. **RGPD - Règlement Général Protection Données**
   - Art. 5 - Principe minimisation
   - Art. 17 - Droit à l'effacement

6. **AR 21/02/2014**
   - Conservation électronique documents comptables

### Ressources

- SPF Finances: https://finances.belgium.be
- SPF Justice (Code sociétés): https://justice.belgium.be
- ONSS: https://www.onss.be
- RGPD (APD): https://www.autoriteprotectiondonnees.be

---

## CONTACT

Pour toute question sur cette politique:
- **Support ComptaBE**: support@comptabe.be
- **Expert-comptable**: Consulter votre fiduciaire
- **Juridique**: Avocat spécialisé droit fiscal belge

---

**Dernière mise à jour**: 2025-12-31
**Révision annuelle**: Obligatoire
**Prochaine révision**: 2026-01-01
