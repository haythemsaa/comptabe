# Mode Sans IA - OCR Classique Uniquement

Si Ollama ne fonctionne pas, vous pouvez utiliser le système OCR en mode dégradé.

## ✅ Ce qui fonctionnera:

- ✅ Upload de documents PDF/images
- ✅ OCR classique (Tesseract)
- ✅ Extraction basique des données:
  - Numéros de facture
  - Dates
  - Montants
  - Numéros TVA (avec regex)
- ✅ Création manuelle de factures
- ✅ Interface de scan
- ✅ Historique des scans

## ❌ Ce qui ne fonctionnera PAS:

- ❌ Amélioration IA des données extraites
- ❌ Matching intelligent des fournisseurs
- ❌ Détection de doublons avancée
- ❌ Suggestions de comptes PCMN
- ❌ Auto-création avec haute confiance

## 📊 Performance Attendue:

| Métrique | Avec IA | Sans IA |
|----------|---------|---------|
| Précision | 89% | 70-75% |
| Auto-création | 70-80% | 0% (validation requise) |
| Temps | 10-15s | 3-5s |

## 🔧 Activation du Mode Sans IA:

### Option 1: Configuration Automatique

Exécutez:
```cmd
MODE-SANS-IA.bat
```

### Option 2: Configuration Manuelle

Modifiez `.env`:
```env
# Désactiver Ollama
OLLAMA_ENABLED=false
# ou commentez les lignes Ollama:
# OLLAMA_BASE_URL=http://localhost:11434
# OLLAMA_MODEL=llama3.1
```

## 🚀 Démarrage Mode Sans IA:

```cmd
# Seulement le Queue Worker, pas Ollama
cd C:\laragon\www\compta
php artisan queue:work --queue=documents
```

Puis accédez à:
```
http://localhost/scanner
```

## 📝 Utilisation:

1. Uploadez votre facture PDF/image
2. Cliquez "Scanner avec IA" (utilisera seulement OCR)
3. **Vérifiez TOUTES les données** (moins fiable)
4. Corrigez les erreurs
5. Créez la facture manuellement

## 💡 Conseils pour Améliorer la Précision:

Sans IA, la qualité du document est CRITIQUE:

- ✅ Utilisez des **PDF natifs** (pas des scans)
- ✅ Photos **bien éclairées** et **nettes**
- ✅ Document **à plat**, sans plis
- ✅ Texte **imprimé** (pas manuscrit)
- ✅ **Haute résolution** (min 300 DPI)

## 🔄 Passer au Mode Avec IA Plus Tard:

Quand Ollama sera installé:

1. Installez Ollama manuellement
2. Téléchargez le modèle: `ollama pull llama3.1`
3. Modifiez `.env`:
   ```env
   OLLAMA_ENABLED=true
   OLLAMA_BASE_URL=http://localhost:11434
   OLLAMA_MODEL=llama3.1
   ```
4. Redémarrez: `DEMARRAGE-SIMPLE.bat`

## ❓ Pourquoi utiliser ce mode?

- 🚀 **Démarrage rapide** (pas besoin d'installer Ollama)
- 💻 **Moins de ressources** (pas de RAM/CPU pour l'IA)
- 🌐 **Pas de téléchargement** (pas de 2.5 GB)
- ⚡ **Plus rapide** (3-5s vs 10-15s)

**Mais moins précis et nécessite plus de validation manuelle.**

## 📞 Support:

Si vous choisissez ce mode et avez des questions, consultez:
- `QUICK_START_OCR.md` - Utilisation du scanner
- `PHASE_3_TESTING_GUIDE.md` - Tests et validation
