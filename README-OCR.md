# 🚀 Guide d'Installation et Test OCR - ComptaBE

**Temps total**: 15-20 minutes
**Téléchargement**: ~2.5 GB

---

## ✅ Installation Automatique (Recommandé)

### Étape 1: Installer Ollama

**Double-cliquez sur:**
```
INSTALLER-OLLAMA.bat
```

Ce script va:
- ✅ Télécharger Ollama automatiquement
- ✅ Installer Ollama
- ✅ Télécharger le modèle llama3.1 (~2.5 GB)
- ✅ Vérifier que tout fonctionne

**⏳ Temps**: ~15 minutes (selon votre connexion)

---

### Étape 2: Démarrer le Système OCR

**Double-cliquez sur:**
```
DEMARRER-OCR.bat
```

Ce script va:
- ✅ Vérifier que Ollama est installé
- ✅ Démarrer le serveur Ollama
- ✅ Démarrer le Queue Worker Laravel
- ✅ Ouvrir le Scanner dans votre navigateur
- ✅ Ouvrir le Dashboard Analytics

**⏳ Temps**: ~30 secondes

---

### Étape 3: Tester avec une Facture

1. **Préparez** une facture PDF belge
2. Dans le **Scanner** qui s'est ouvert:
   - Glissez-déposez votre PDF
   - Cliquez "Scanner avec IA"
   - Attendez 10-20 secondes
3. **Vérifiez** les résultats:
   - Score de confiance
   - Données extraites
   - Badge couleur (vert/orange/rouge)
4. **Créez** la facture si satisfait

---

## 📁 Fichiers Disponibles

### Scripts d'Installation
- `INSTALLER-OLLAMA.bat` - Installation automatique complète
- `installer-ollama.ps1` - Script PowerShell (utilisé par le .bat)

### Scripts de Démarrage
- `DEMARRER-OCR.bat` - Démarre tout automatiquement ⭐ RECOMMANDÉ
- `ouvrir-scanner.bat` - Ouvre juste les pages web
- `setup-ocr-testing.bat` - Configuration initiale (ancien)

### Documentation
- `INSTALLATION_COMPLETE.md` - Guide détaillé pas-à-pas
- `QUICK_START_OCR.md` - Guide rapide (10 minutes)
- `PHASE_3_TESTING_GUIDE.md` - Tests approfondis (6 scénarios)
- `PHASE_3_COMPLETION_REPORT.md` - Documentation technique complète
- `README-OCR.md` - Ce fichier

---

## 🎯 Utilisation Quotidienne

Une fois Ollama installé, pour utiliser le système OCR:

**1. Démarrer** (double-clic):
```
DEMARRER-OCR.bat
```

**2. Utiliser:**
- Scanner: `http://localhost/scanner`
- Analytics: `http://localhost/ocr/analytics`

**3. Arrêter:**
- Fermez les fenêtres "Ollama Server" et "Queue Worker"

---

## 📊 URLs Importantes

| Page | URL | Description |
|------|-----|-------------|
| **Scanner OCR** | `/scanner` | Upload et scan de factures |
| **Analytics** | `/ocr/analytics` | Stats et métriques OCR |
| **Invoices** | `/invoices` | Liste des factures |
| **Dashboard** | `/dashboard` | Dashboard principal |

---

## ❌ Résolution de Problèmes

### Problème: "Ollama n'est pas installé"

**Solution:**
```
Lancez: INSTALLER-OLLAMA.bat
```

---

### Problème: "Ollama API failed"

**Cause**: Serveur Ollama pas démarré

**Solution:**
```
Lancez: DEMARRER-OCR.bat
```

Ou manuellement dans un terminal:
```cmd
ollama serve
```

---

### Problème: Job reste "queued"

**Cause**: Queue Worker pas lancé

**Solution:**
Déjà inclus dans `DEMARRER-OCR.bat`

Ou manuellement:
```cmd
cd C:\laragon\www\compta
php artisan queue:work --queue=documents
```

---

### Problème: Confiance très basse (<50%)

**Causes:**
- Document flou ou sombre
- Texte manuscrit
- Format inhabituel

**Solutions:**
- Utilisez un PDF natif (pas un scan)
- Améliorez la qualité de l'image
- Prenez une photo bien éclairée

---

### Problème: Scan très lent (>30s)

**Causes:**
- Première utilisation (cache froid) - Normal
- Fichier trop volumineux
- CPU faible

**Solutions:**
- Attendez la fin (première fois prend plus de temps)
- Réduisez la taille du PDF
- Les scans suivants seront plus rapides

---

## 🔍 Vérifications

### Services en cours

**Vérifier Ollama:**
```cmd
curl http://localhost:11434/api/tags
```

**Vérifier modèles:**
```cmd
ollama list
```

### Logs

**Laravel:**
```cmd
type storage\logs\laravel.log
```

**Jobs échoués:**
```cmd
php artisan queue:failed
```

---

## 📈 Métriques de Succès

Après 10-20 tests:

| Métrique | Cible | Vérifier sur |
|----------|-------|-------------|
| Confiance moyenne | ≥ 80% | `/ocr/analytics` |
| Taux auto-création | ≥ 60% | `/ocr/analytics` |
| Temps moyen | < 20s | `/ocr/analytics` |
| Taux succès | ≥ 95% | `/ocr/analytics` |

---

## 🎓 Pour Aller Plus Loin

### Tests Approfondis

Consultez `PHASE_3_TESTING_GUIDE.md` pour:
- 6 scénarios de test détaillés
- Feuille de résultats
- Tuning et optimisation
- Troubleshooting avancé

### Documentation Technique

Consultez `PHASE_3_COMPLETION_REPORT.md` pour:
- Architecture complète
- API documentation
- Métriques de performance
- Code source expliqué

---

## 📞 Support

### Commandes Utiles

```cmd
# Vérifier version Ollama
ollama --version

# Vérifier version Laravel
php artisan --version

# Lister les routes
php artisan route:list | findstr scanner

# Nettoyer le cache
php artisan cache:clear

# Voir les jobs en queue
php artisan queue:failed

# Retry un job échoué
php artisan queue:retry <job-id>
```

### Ressources

- **Ollama**: https://ollama.ai
- **Documentation Ollama**: https://ollama.ai/docs
- **Modèles disponibles**: https://ollama.ai/library
- **Laravel Queues**: https://laravel.com/docs/11.x/queues

---

## ✅ Checklist Installation

- [ ] Ollama installé (`INSTALLER-OLLAMA.bat`)
- [ ] Modèle llama3.1 téléchargé
- [ ] Serveur Ollama démarre (`ollama serve`)
- [ ] Queue Worker démarre
- [ ] Scanner accessible (`/scanner`)
- [ ] Analytics accessible (`/ocr/analytics`)
- [ ] 1 facture test scannée avec succès

---

## 🎉 C'est Prêt!

Si vous avez suivi ce guide:
- ✅ Ollama est installé
- ✅ Le système OCR fonctionne
- ✅ Vous pouvez scanner des factures
- ✅ Les analytics sont disponibles

**Profitez du système! 🚀**

---

*Dernière mise à jour: 31 Décembre 2025*
