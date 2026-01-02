# Installation Complète OCR & IA - ComptaBE

**Temps total estimé**: 15-20 minutes
**Téléchargement**: ~2.5 GB (Ollama + modèle)

---

## ✅ ÉTAPE 1: Installer Ollama (5 minutes)

### 1.1 Télécharger Ollama

Ouvrez votre navigateur et allez sur:
```
https://ollama.ai/download
```

**Cliquez sur "Download for Windows"**

### 1.2 Installer Ollama

1. Double-cliquez sur `OllamaSetup.exe` téléchargé
2. Suivez l'assistant d'installation (Next, Next, Install)
3. L'installation prend ~1 minute
4. Ollama démarrera automatiquement en arrière-plan

### 1.3 Vérifier l'installation

Ouvrez un **nouveau terminal** (PowerShell ou CMD) et tapez:

```cmd
ollama --version
```

Vous devriez voir quelque chose comme:
```
ollama version is 0.1.x
```

✅ Si vous voyez la version, Ollama est installé !

---

## ✅ ÉTAPE 2: Télécharger le modèle llama3.1 (10 minutes)

### 2.1 Dans le même terminal, tapez:

```cmd
ollama pull llama3.1
```

**ATTENTION**: Ceci va télécharger ~2 GB. Attendez que ça finisse.

Vous verrez:
```
pulling manifest
pulling 8eeb52dfb3bb... 100% ▕████████████████▏ 4.7 GB
pulling 73b313b5552d... 100% ▕████████████████▏  11 KB
...
success
```

### 2.2 Vérifier le modèle

```cmd
ollama list
```

Vous devriez voir:
```
NAME            SIZE    MODIFIED
llama3.1:latest 4.7 GB  X minutes ago
```

✅ Le modèle est prêt !

---

## ✅ ÉTAPE 3: Démarrer Ollama (toujours en cours)

### 3.1 Terminal 1 - Serveur Ollama

Ouvrez un **nouveau terminal** et laissez-le ouvert:

```cmd
ollama serve
```

Vous devriez voir:
```
time=... level=INFO msg="Listening on 127.0.0.1:11434"
```

**⚠️ IMPORTANT**: Laissez ce terminal ouvert tout le temps !

### 3.2 Tester Ollama (optionnel)

Dans un **autre terminal**, testez:

```cmd
curl http://localhost:11434/api/tags
```

Ou testez une génération:
```cmd
ollama run llama3.1 "Bonjour, tu fonctionnes?"
```

Le modèle devrait répondre en français !

---

## ✅ ÉTAPE 4: Démarrer Queue Worker Laravel

### 4.1 Terminal 2 - Queue Worker

Ouvrez un **nouveau terminal** (PowerShell ou CMD):

```cmd
cd C:\laragon\www\compta
php artisan queue:work --queue=documents --timeout=300
```

Vous devriez voir:
```
INFO  Processing jobs from the [documents] queue.
```

**⚠️ IMPORTANT**: Laissez aussi ce terminal ouvert !

---

## ✅ ÉTAPE 5: Vérifier la Configuration Laravel

### 5.1 Vérifier .env

Votre fichier `.env` contient déjà:
```env
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama3.1
OLLAMA_MAX_TOKENS=4096
OLLAMA_TEMPERATURE=0.7
```

✅ Configuration OK !

### 5.2 Vérifier Queue Configuration

Dans `.env`, cherchez:
```env
QUEUE_CONNECTION=database
```
ou
```env
QUEUE_CONNECTION=redis
```

✅ C'est bon !

---

## ✅ ÉTAPE 6: Accéder au Scanner

### 6.1 Ouvrir le navigateur

**Scanner OCR**:
```
http://localhost/scanner
```
ou si vous utilisez un domaine virtuel:
```
http://compta.test/scanner
```

**Analytics Dashboard**:
```
http://localhost/ocr/analytics
```
ou
```
http://compta.test/ocr/analytics
```

---

## 🧪 ÉTAPE 7: Premier Test

### 7.1 Préparer une facture test

Vous avez besoin d'un fichier:
- **PDF** de facture (recommandé)
- ou **Image JPG/PNG** de facture
- **Taille max**: 10 MB

### 7.2 Scanner la facture

1. Allez sur `/scanner`
2. Sélectionnez "Facture" comme type
3. **Glissez-déposez** votre PDF dans la zone
4. Cliquez **"Scanner avec IA"**
5. **Attendez 10-20 secondes** (première fois peut être plus long)

### 7.3 Résultat attendu

Vous devriez voir:

✅ **Barre de progression** qui avance:
```
Détection du texte (OCR)... 30%
Extraction des données structurées... 60%
Validation et matching... 90%
Terminé ! 100%
```

✅ **Données extraites** dans le formulaire:
- Fournisseur
- N° Facture
- Date facture
- Montants (HTVA, TVA, TTC)

✅ **Score de confiance**:
- Badge **VERT** (≥85%) = Auto-création possible
- Badge **ORANGE** (70-84%) = Validation requise
- Badge **ROUGE** (<70%) = Saisie manuelle

### 7.4 Créer la facture

Si satisfait:
1. Vérifiez/corrigez les données
2. Cliquez **"Créer facture"**
3. Vous serez redirigé vers la facture (status: draft)

---

## 🔍 Vérifications

### Terminal 1 (Ollama) doit montrer:

```
time=... level=INFO msg="127.0.0.1:xxxxx POST /api/generate"
```

Chaque fois que vous scannez un document.

### Terminal 2 (Queue Worker) doit montrer:

```
[2025-12-31 ...] Processing: App\Jobs\ProcessUploadedInvoice
[2025-12-31 ...] Processed:  App\Jobs\ProcessUploadedInvoice
```

Si vous utilisez le mode asynchrone.

### Logs Laravel

Si problème, vérifiez:
```cmd
type storage\logs\laravel.log | findstr "Scanner:"
```

Ou ouvrez le fichier:
```
storage\logs\laravel.log
```

Recherchez:
- `Scanner: Processing document` ✅
- `Invoice auto-created from document` ✅
- Erreurs en rouge ❌

---

## ❌ Problèmes Courants

### 1. "OLLAMA API failed" dans les logs

**Cause**: Ollama pas démarré

**Solution**:
```cmd
ollama serve
```

### 2. "Model not found: llama3.1"

**Solution**:
```cmd
ollama pull llama3.1
ollama list  # Vérifier
```

### 3. Job reste "queued" indéfiniment

**Cause**: Queue worker pas lancé

**Solution**:
```cmd
cd C:\laragon\www\compta
php artisan queue:work --queue=documents
```

### 4. "Connection refused" à localhost:11434

**Causes possibles**:
- Ollama pas démarré → `ollama serve`
- Firewall bloque → Autoriser Ollama
- Port déjà utilisé → Vérifier avec `netstat -an | findstr 11434`

### 5. Scan très lent (>30s)

**Causes**:
- Première fois (cache froid) → Normal
- CPU faible → Considérer GPU
- Fichier trop gros → Réduire taille/qualité

### 6. Confiance toujours < 50%

**Causes**:
- Document flou/sombre
- Texte manuscrit
- Format inhabituel

**Solutions**:
- Utiliser PDF natif
- Améliorer qualité image
- Tester Google Vision API (payant)

---

## 📊 Voir les Statistiques

Allez sur `/ocr/analytics` pour voir:

- **Temps réel**: Documents en traitement, en queue
- **Stats globales**: Total scans, confiance moyenne, taux auto-création
- **Performance**: Temps moyen, distribution confiance
- **Historique**: Liste tous les scans
- **Export**: Télécharger CSV

---

## ✅ Checklist Finale

Avant de considérer que tout fonctionne:

- [ ] Ollama installé et tourne (`ollama serve`)
- [ ] Modèle llama3.1 téléchargé (`ollama list`)
- [ ] Queue worker lancé (`php artisan queue:work`)
- [ ] Scanner accessible (`/scanner`)
- [ ] Analytics accessible (`/ocr/analytics`)
- [ ] 1 facture test scannée avec succès
- [ ] Confiance ≥ 70%
- [ ] Facture créée en draft
- [ ] Pas d'erreurs dans les logs

---

## 🎯 Résumé Commandes

### Terminaux à garder ouverts

**Terminal 1**:
```cmd
ollama serve
```

**Terminal 2**:
```cmd
cd C:\laragon\www\compta
php artisan queue:work --queue=documents
```

### URLs

```
http://localhost/scanner           # Scanner OCR
http://localhost/ocr/analytics     # Dashboard Analytics
```

### Debug

```cmd
# Logs Laravel
type storage\logs\laravel.log

# Jobs échoués
php artisan queue:failed

# Liste modèles Ollama
ollama list

# Test Ollama
curl http://localhost:11434/api/tags
```

---

## 📈 Métriques à Suivre

Après 10-20 tests, notez:

| Métrique | Votre Résultat |
|----------|----------------|
| Confiance moyenne | ___% |
| Taux auto-création | ___% |
| Temps moyen | ___s |
| Taux succès | ___% |

**Cibles**:
- Confiance: ≥ 80%
- Auto-création: ≥ 60%
- Temps: < 20s
- Succès: ≥ 95%

---

## 🚀 Prochaines Étapes

Une fois que tout fonctionne:

1. Tester avec 10-20 vraies factures belges
2. Ajuster seuils si nécessaire
3. Former les utilisateurs
4. Monitorer avec `/ocr/analytics`
5. Optimiser prompts Ollama selon résultats

---

## 📞 Besoin d'Aide?

**Documentation**:
- `QUICK_START_OCR.md` - Guide rapide
- `PHASE_3_TESTING_GUIDE.md` - Tests approfondis
- `PHASE_3_COMPLETION_REPORT.md` - Documentation complète

**Ressources Ollama**:
- Site officiel: https://ollama.ai
- Documentation: https://ollama.ai/docs
- Modèles disponibles: https://ollama.ai/library

---

**Bonne installation ! 🚀**

*Si vous suivez ce guide, vous devriez avoir un système OCR fonctionnel en ~20 minutes maximum.*
