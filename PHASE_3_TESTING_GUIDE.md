# Phase 3 - Guide de Test OCR & IA

**Date**: 31 Décembre 2025
**Version**: 1.0
**Objectif**: Tester le système d'auto-création de factures avec OCR et IA

---

## 🚀 Démarrage Rapide

### Prérequis

1. **Ollama installé et lancé**:
```bash
# Vérifier si Ollama tourne
curl http://localhost:11434/api/tags

# Si pas installé, installer Ollama:
# Windows: https://ollama.ai/download
# Linux/Mac: curl https://ollama.ai/install.sh | sh

# Démarrer Ollama
ollama serve

# Télécharger le modèle (dans un autre terminal)
ollama pull llama3.2
```

2. **Queue worker lancé**:
```bash
# Development
php artisan queue:work --queue=documents

# OU avec Horizon (production)
php artisan horizon
```

3. **Configuration .env**:
```env
# Ollama
OLLAMA_ENDPOINT=http://localhost:11434
OLLAMA_MODEL=llama3.2

# Queue
QUEUE_CONNECTION=redis  # ou 'database'

# OCR (optionnel)
OCR_PROVIDER=tesseract
# GOOGLE_VISION_API_KEY=...  # Si Google Vision
```

---

## 📝 Scénarios de Test

### Test 1: Facture Simple - Auto-création

**Objectif**: Vérifier l'auto-création avec haute confiance

**Fichier de test**: Facture PDF simple avec:
- Numéro de facture clair
- Date visible
- Montants bien lisibles
- Fournisseur existant dans la base

**Étapes**:
1. Aller sur `/scanner`
2. Sélectionner type "Facture"
3. Uploader le PDF
4. Cliquer "Scanner avec IA"
5. Attendre traitement (~10-15s)

**Résultat attendu**:
- ✅ Confiance ≥ 85%
- ✅ Fournisseur matché automatiquement
- ✅ Badge vert "Trouvé automatiquement"
- ✅ Tous les montants extraits
- ✅ Pas d'avertissement doublon
- ✅ Bouton "Créer facture" cliquable
- ✅ Après création → Redirection vers facture en draft

**Vérifications post-création**:
```bash
# Vérifier dans la base
php artisan tinker
>>> DocumentScan::latest()->first()
>>> Invoice::latest()->first()
```

---

### Test 2: Nouvelle Entreprise - Validation Requise

**Objectif**: Tester le matching fournisseur pour une entreprise inconnue

**Fichier de test**: Facture d'un fournisseur jamais vu

**Résultat attendu**:
- ⚠️ Confiance 70-84%
- ⚠️ Badge orange "Validation requise"
- ❌ Pas de fournisseur matché
- ✅ Données extraites modifiables
- ✅ Possibilité de créer manuellement

**Actions**:
1. Modifier/compléter les données si nécessaire
2. Créer la facture manuellement
3. Vérifier qu'un nouveau Partner est créé automatiquement

---

### Test 3: Doublon - Détection

**Objectif**: Vérifier la détection de doublons

**Étapes**:
1. Uploader une facture (créée avec succès)
2. Re-uploader LA MÊME facture
3. Attendre traitement

**Résultat attendu**:
- 🚨 Alerte orange "Doublon potentiel détecté"
- 📝 Message: "Une facture similaire existe déjà (#XXX)"
- 🔗 Lien "Voir le document existant"
- 🔗 Option "Ignorer et continuer"
- ❌ Confiance réduite (<70% probablement)

---

### Test 4: Mauvaise Qualité - Saisie Manuelle

**Objectif**: Tester avec un scan de mauvaise qualité

**Fichier de test**:
- Photo floue
- Document avec plis
- Éclairage médiocre
- Ou manuscrit

**Résultat attendu**:
- ❌ Confiance < 70%
- 🔴 Badge rouge "Saisie manuelle recommandée"
- ⚠️ Plusieurs avertissements
- 💡 Suggestions d'amélioration affichées

---

### Test 5: PDF vs Image

**Objectif**: Comparer précision PDF natif vs scan image

**Tests parallèles**:
1. Même facture en PDF natif
2. Même facture scannée en JPG

**Métriques à comparer**:
- Temps de traitement
- Confiance globale
- Précision extraction (nombre, date, montants)
- Qualité matching fournisseur

**Résultat attendu**:
- PDF: Confiance ≥ 90%, temps ~8s
- JPG: Confiance ≥ 80%, temps ~12s

---

### Test 6: Facture Multi-Lignes

**Objectif**: Tester extraction des lignes de facture

**Fichier de test**: Facture avec 5+ lignes d'articles

**Vérifications**:
- ✅ Nombre de lignes extraites
- ✅ Descriptions cohérentes
- ✅ Quantités et prix unitaires
- ✅ Comptes PCMN suggérés par l'IA
- ✅ Taux TVA par ligne

---

## 🔍 Points de Vérification

### 1. Logs à surveiller

```bash
# Logs Laravel (traitement en temps réel)
tail -f storage/logs/laravel.log

# Horizon (jobs en queue)
# Interface: http://localhost/horizon
```

**Événements importants**:
- `Scanner: Processing document`
- `Ollama enhancement failed` → ERREUR
- `Invoice auto-created from document`
- `ProcessUploadedInvoice job permanently failed` → ERREUR

---

### 2. Base de données

**Tables à vérifier**:

```sql
-- Scans uploadés
SELECT id, original_filename, status, overall_confidence, created_at
FROM document_scans
ORDER BY created_at DESC
LIMIT 10;

-- Factures auto-créées
SELECT id, invoice_number, partner_id, total_incl_vat, source, status
FROM invoices
WHERE source = 'ocr_auto'
ORDER BY created_at DESC
LIMIT 10;

-- Jobs échoués
SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 5;
```

---

### 3. Métriques de Performance

**À mesurer**:

| Métrique | Cible | Réel | Écart |
|----------|-------|------|-------|
| Temps traitement moyen | 8-14s | ___ | ___ |
| Confiance moyenne | ≥85% | ___ | ___ |
| Taux auto-création | 70-80% | ___ | ___ |
| Taux matching fournisseur | ≥90% | ___ | ___ |
| Précision extraction montant | ≥95% | ___ | ___ |
| Détection doublons | 100% | ___ | ___ |

---

### 4. Tests d'Erreurs

**Cas limites à tester**:

- [ ] Fichier trop gros (>10MB) → Erreur validation
- [ ] Format non supporté (.doc) → Erreur validation
- [ ] PDF corrompu → Erreur OCR
- [ ] Ollama éteint → Fallback sur OCR seul
- [ ] Queue worker arrêté → Job reste en pending
- [ ] Timeout Ollama (>30s) → Retry automatique

---

## 📊 Feuille de Résultats

### Batch de Test: [Date]

| # | Fichier | Type | Taille | Temps | Conf. | Auto? | Fournisseur | Doublon | Notes |
|---|---------|------|--------|-------|-------|-------|-------------|---------|-------|
| 1 | facture_acme.pdf | PDF | 250KB | 9s | 92% | ✅ | ✅ Match | ❌ | Parfait |
| 2 | scan_xyz.jpg | JPG | 1.2MB | 13s | 78% | ❌ | ⚠️ Fuzzy | ❌ | Validation OK |
| 3 | duplicate.pdf | PDF | 250KB | 8s | 45% | ❌ | ✅ | ✅ | Doublon détecté |
| 4 | low_quality.jpg | JPG | 800KB | 15s | 62% | ❌ | ❌ | ❌ | Saisie manuelle |
| 5 | multi_lines.pdf | PDF | 400KB | 11s | 88% | ✅ | ✅ | ❌ | 6 lignes OK |

**Statistiques**:
- Total tests: ___
- Auto-créées: ___ (___%)
- Validation requise: ___ (___%)
- Saisie manuelle: ___ (___%)
- Temps moyen: ___s
- Confiance moyenne: ___%

---

## 🐛 Troubleshooting

### Problème: "Ollama API failed"

**Cause**: Ollama non démarré

**Solution**:
```bash
# Démarrer Ollama
ollama serve

# Vérifier
curl http://localhost:11434/api/tags
```

---

### Problème: Job reste en "pending"

**Cause**: Queue worker pas lancé

**Solution**:
```bash
# Démarrer worker
php artisan queue:work --queue=documents

# Vérifier jobs en attente
php artisan queue:failed
```

---

### Problème: Confiance toujours < 70%

**Causes possibles**:
1. Mauvaise qualité scans
2. Prompt Ollama non optimisé
3. OCR baseline faible

**Solutions**:
1. Tester avec PDFs natifs d'abord
2. Ajuster prompt dans `IntelligentInvoiceExtractor.php:79-128`
3. Vérifier Google Vision API (plus précis que Tesseract)

---

### Problème: Matching fournisseur échoue

**Debug**:
```php
// Dans tinker
$scan = DocumentScan::latest()->first();
$data = $scan->extracted_data;

// Vérifier VAT extrait
$data['vat_number']; // Format BE0123456789?

// Vérifier fournisseurs existants
Partner::where('vat_number', $data['vat_number'])->first();
```

**Solutions**:
- Normaliser format VAT (enlever espaces/points)
- Créer manuellement partenaire test
- Vérifier fuzzy matching IA

---

## 🎯 Tuning & Optimisation

### Ajuster Seuils de Confiance

**Fichier**: `app/Jobs/ProcessUploadedInvoice.php:108,126`

```php
// Plus agressif (plus d'auto-création)
if ($overallConfidence >= 0.80) { // au lieu de 0.85

// Moins agressif (plus de validation)
if ($overallConfidence >= 0.90) { // au lieu de 0.85
```

---

### Améliorer Prompts Ollama

**Fichier**: `app/Services/AI/IntelligentInvoiceExtractor.php:75-128`

**Modifications suggérées**:
- Ajouter exemples dans le prompt (few-shot learning)
- Spécifier formats belges (DD/MM/YYYY, virgule décimale)
- Lister comptes PCMN les plus courants

---

### Changer Modèle Ollama

**Plus rapide** (moins précis):
```bash
ollama pull llama3.2:1b  # Modèle 1 milliard params
```

**Plus précis** (plus lent):
```bash
ollama pull llama3.1:8b  # Modèle 8 milliards params
```

**Mise à jour config**:
```env
OLLAMA_MODEL=llama3.1:8b
```

---

## ✅ Checklist Finale

Avant de considérer la Phase 3 comme 100% complète:

- [ ] Ollama installé et configuré
- [ ] 10+ factures testées avec succès
- [ ] Taux auto-création ≥ 70%
- [ ] Aucun doublon non détecté
- [ ] Temps traitement < 20s
- [ ] Notifications email fonctionnelles
- [ ] Logs propres (pas d'erreurs critiques)
- [ ] UI responsive et intuitive
- [ ] Documentation lue et comprise

---

## 📞 Support

**Problèmes techniques**:
- Logs: `storage/logs/laravel.log`
- Horizon: `/horizon/failed`
- Documentation Ollama: https://ollama.ai/docs

**Améliorations**:
- Soumettre issue avec exemples de factures problématiques
- Partager logs d'erreur
- Proposer ajustements prompts

---

**Bonne chance avec les tests !** 🚀

---

*Document généré - ComptaBE Phase 3 Testing Guide*
*Version: 1.0 - 31/12/2025*
