# Guide Rapide - Test OCR & IA

**Durée**: 10-15 minutes
**Prérequis**: Windows avec Laragon

---

## 🚀 Démarrage Ultra-Rapide

### Étape 1: Exécuter le script automatique

```cmd
cd C:\laragon\www\compta
setup-ocr-testing.bat
```

Ce script va:
- ✅ Vérifier si Ollama est installé
- ✅ Télécharger le modèle llama3.2 si nécessaire
- ✅ Configurer le fichier .env automatiquement
- ✅ Vérifier les services (Redis, etc.)

---

### Étape 2: Démarrer les services (2 terminaux)

**Terminal 1 - Ollama**:
```cmd
ollama serve
```

**Terminal 2 - Queue Worker**:
```cmd
cd C:\laragon\www\compta
php artisan queue:work --queue=documents
```

**Gardez ces 2 terminaux ouverts pendant vos tests!**

---

### Étape 3: Accéder à l'interface

Ouvrez votre navigateur:

**Scanner**:
```
http://localhost/scanner
```
ou si domaine virtuel:
```
http://compta.test/scanner
```

**Analytics** (nouveau!):
```
http://localhost/ocr/analytics
```

---

## 📝 Premier Test Simple

### 1. Préparer une facture

Vous avez besoin d'une facture PDF belge avec:
- Un numéro de facture visible
- Une date
- Un montant TTC
- Idéalement un numéro de TVA BE

Vous n'avez pas de facture? Utilisez une capture d'écran d'une facture exemple.

### 2. Upload et Scan

1. Allez sur `/scanner`
2. Sélectionnez "Facture" comme type
3. Glissez-déposez votre PDF/image
4. Cliquez "Scanner avec IA"
5. **Attendez 10-15 secondes** (la première fois peut être plus long)

### 3. Vérifier les résultats

Vous devriez voir:
- ✅ **Barre de progression** avec étapes
- ✅ **Données extraites** dans le formulaire
- ✅ **Score de confiance** (pourcentage)
- ✅ **Fournisseur matché** (si existe déjà)
- ⚠️ **Avertissements** si données manquantes

**Si confiance ≥ 85%**:
- Badge vert "Haute confiance"
- Bouton "Créer facture" activé
- Possibilité d'auto-création

**Si confiance 70-84%**:
- Badge orange "Validation requise"
- Vérification manuelle nécessaire

**Si confiance < 70%**:
- Badge rouge "Saisie manuelle recommandée"
- Qualité document probablement faible

### 4. Créer la facture

Si satisfait des données:
1. Vérifiez/modifiez les champs si nécessaire
2. Cliquez "Créer facture"
3. Vous serez redirigé vers la facture créée (status: draft)

---

## 📊 Voir les Statistiques

Allez sur `/ocr/analytics` pour voir:

- **Métriques temps réel**: Documents en traitement, en queue
- **Statistiques globales**: Total scans, taux auto-création, confiance moyenne
- **Performance**: Temps de traitement moyen, distribution confiance
- **Historique**: Liste de tous les scans avec statuts
- **Export CSV**: Téléchargez toutes les données

---

## 🔍 Vérifications

### Terminal Ollama doit afficher:

```
Listening on 127.0.0.1:11434
```

### Terminal Queue Worker doit afficher:

```
[2025-12-31 12:00:00][xxx] Processing: App\Jobs\ProcessUploadedInvoice
[2025-12-31 12:00:15][xxx] Processed:  App\Jobs\ProcessUploadedInvoice
```

### Logs Laravel (si problème):

```cmd
tail -f storage/logs/laravel.log
```

Recherchez:
- `Scanner: Processing document`
- `Invoice auto-created from document`
- Ou erreurs rouges

---

## ❌ Problèmes Courants

### 1. "Ollama API failed"

**Cause**: Ollama pas démarré

**Solution**:
```cmd
ollama serve
```

### 2. Job reste "pending" indéfiniment

**Cause**: Queue worker pas lancé

**Solution**:
```cmd
php artisan queue:work --queue=documents
```

### 3. Confiance toujours très basse (<50%)

**Causes possibles**:
- Document de mauvaise qualité (flou, sombre)
- Format non supporté
- Texte manuscrit

**Solutions**:
- Utilisez un PDF natif plutôt qu'un scan
- Améliorez l'éclairage et la netteté
- Prenez une photo droite (sans angle)

### 4. Erreur "File not found" ou "Permission denied"

**Cause**: Permissions fichiers

**Solution**:
```cmd
icacls storage /grant Everyone:F /t
icacls bootstrap/cache /grant Everyone:F /t
```

### 5. "Model not found: llama3.2"

**Solution**:
```cmd
ollama pull llama3.2
ollama list  # Vérifier
```

---

## 🎯 Scénarios de Test Recommandés

### Test 1: Facture PDF Simple ✅
- Facture propre, claire, PDF natif
- **Attendu**: Confiance ≥ 85%, auto-création possible

### Test 2: Image JPG de Facture 📸
- Photo de facture papier
- **Attendu**: Confiance 70-85%, validation requise

### Test 3: Doublon 🔁
- Uploader 2× la même facture
- **Attendu**: Alerte doublon à la 2ème tentative

### Test 4: Fournisseur Inconnu 🆕
- Facture d'une entreprise jamais vue
- **Attendu**: Pas de match fournisseur, nouveau Partner créé

### Test 5: Mauvaise Qualité ❌
- Scan flou, sombre, ou manuscrit
- **Attendu**: Confiance < 70%, saisie manuelle

---

## 📈 Métriques à Suivre

Après 10-20 tests, vérifiez sur `/ocr/analytics`:

| Métrique | Cible | Votre Résultat |
|----------|-------|----------------|
| Confiance moyenne | ≥ 85% | ___% |
| Taux auto-création | ≥ 70% | ___% |
| Temps moyen | 8-14s | ___s |
| Taux succès | ≥ 95% | ___% |

---

## ✅ Checklist Validation

Avant de passer en production:

- [ ] 10+ factures testées avec succès
- [ ] Confiance moyenne ≥ 80%
- [ ] Taux auto-création ≥ 60%
- [ ] Aucun faux positif doublon
- [ ] Temps traitement < 20s
- [ ] Logs propres (pas d'erreurs rouges)
- [ ] Notifications email reçues
- [ ] UI responsive et rapide

---

## 🎓 Prochaines Étapes

Une fois les tests de base réussis:

1. **Tuner les seuils** si nécessaire (voir `PHASE_3_TESTING_GUIDE.md`)
2. **Ajuster les prompts** Ollama pour votre cas d'usage
3. **Tester avec vrais fournisseurs** de votre entreprise
4. **Former les utilisateurs** sur le workflow
5. **Monitorer les analytics** pendant 1 semaine

---

## 📞 Besoin d'Aide?

**Documentation complète**:
- `PHASE_3_OCR_IA_PROGRESS.md` - Architecture détaillée
- `PHASE_3_TESTING_GUIDE.md` - Guide de test approfondi

**Logs et Debug**:
- Laravel: `storage/logs/laravel.log`
- Queue: `php artisan queue:failed`
- Horizon: `http://localhost/horizon` (si installé)

**Ollama**:
- Documentation: https://ollama.ai/docs
- Liste modèles: `ollama list`
- Test rapide: `ollama run llama3.2 "Test"`

---

**Bon test! 🚀**

*Si tout fonctionne, vous devriez avoir votre première facture auto-créée en moins de 15 minutes!*
