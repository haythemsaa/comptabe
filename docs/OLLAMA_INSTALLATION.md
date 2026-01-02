# Installation Ollama - Assistant AI GRATUIT pour ComptaBE

## Pourquoi Ollama?

✅ **100% GRATUIT** - Aucun coût d'API
✅ **Pas de limite** - Utilisez autant que vous voulez
✅ **Confidentialité totale** - Tout fonctionne localement
✅ **Rapide** - Modèles optimisés pour votre machine
✅ **Offline** - Fonctionne sans connexion internet

**Comparaison avec Claude:**
- **Claude:** ~$0.045 par conversation (10 messages) = **$225/mois** pour 100 utilisateurs actifs
- **Ollama:** $0 pour toujours ✨

---

## Installation Rapide (5 minutes)

### Étape 1: Télécharger Ollama

**Windows:**
1. Allez sur https://ollama.com/download
2. Cliquez sur "Download for Windows"
3. Exécutez le fichier `OllamaSetup.exe`
4. Suivez l'assistant d'installation

**Mac:**
```bash
brew install ollama
```

**Linux:**
```bash
curl -fsSL https://ollama.com/install.sh | sh
```

### Étape 2: Démarrer Ollama

Ollama démarre automatiquement après l'installation.

Pour vérifier qu'il fonctionne:
```bash
ollama --version
```

Vous devriez voir: `ollama version 0.x.x`

### Étape 3: Télécharger un modèle

Commencez avec **Llama 3.1** (recommandé - bon équilibre vitesse/qualité):

```bash
ollama pull llama3.1
```

**Autres modèles disponibles:**

| Modèle | Taille | Vitesse | Qualité | Usage RAM | Recommandation |
|--------|--------|---------|---------|-----------|----------------|
| **llama3.1** | 4.7GB | ⚡⚡⚡ | ⭐⭐⭐⭐ | 8GB | **Production (recommandé)** |
| **mistral** | 4.1GB | ⚡⚡⚡⚡ | ⭐⭐⭐ | 6GB | Serveur modeste |
| **phi3** | 2.3GB | ⚡⚡⚡⚡⚡ | ⭐⭐ | 4GB | Laptop/développement |
| **llama3.1:70b** | 40GB | ⚡ | ⭐⭐⭐⭐⭐ | 64GB | Serveur puissant |
| **qwen2.5** | 4.7GB | ⚡⚡⚡ | ⭐⭐⭐⭐ | 8GB | Excellent pour le code |

**Pour télécharger un autre modèle:**
```bash
ollama pull mistral
ollama pull phi3
```

**Pour voir les modèles installés:**
```bash
ollama list
```

### Étape 4: Tester Ollama

Testez le modèle en ligne de commande:

```bash
ollama run llama3.1
```

Tapez quelque chose comme "Bonjour, comment ça va?" et vérifiez que le modèle répond.

Pour quitter: tapez `/bye`

### Étape 5: Configuration ComptaBE

Le fichier `.env` est déjà configuré pour utiliser Ollama:

```bash
# AI Provider Configuration
AI_PROVIDER=ollama

# Ollama Configuration (FREE - Local LLM)
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama3.1
OLLAMA_MAX_TOKENS=4096
OLLAMA_TEMPERATURE=0.7
```

**Si vous utilisez un autre modèle**, changez `OLLAMA_MODEL`:
```bash
OLLAMA_MODEL=mistral
# ou
OLLAMA_MODEL=phi3
```

### Étape 6: Vérifier que tout fonctionne

Dans votre terminal Laravel:

```bash
php artisan tinker
```

Puis testez:
```php
$factory = new \App\Services\AI\AIServiceFactory();
$providers = $factory->getAvailableProviders();
dd($providers);
```

Vous devriez voir:
```php
[
  "ollama" => [
    "name" => "ollama",
    "available" => true,  // ✅ Doit être true!
    "cost" => "Free",
  ],
  "claude" => [
    "name" => "claude",
    "available" => false,
    "cost" => "Paid",
  ],
]
```

---

## Test de l'Assistant AI

1. Connectez-vous à ComptaBE
2. Cliquez sur l'icône de chat en bas à droite
3. Tapez: **"Bonjour, quelles sont mes factures impayées?"**
4. Le bot devrait répondre (avec Ollama, gratuit!)

---

## Configuration avancée

### Changer de modèle selon l'environnement

**.env.local (développement):**
```bash
OLLAMA_MODEL=phi3  # Rapide pour dev
```

**.env.production:**
```bash
OLLAMA_MODEL=llama3.1  # Meilleure qualité
```

### Utiliser un serveur Ollama distant

Si Ollama tourne sur un autre serveur:

```bash
OLLAMA_BASE_URL=http://192.168.1.100:11434
```

### Augmenter la vitesse

Pour des réponses plus rapides (moins précises):
```bash
OLLAMA_TEMPERATURE=0.3
OLLAMA_MAX_TOKENS=2048
```

### Basculer vers Claude temporairement

Si vous voulez tester Claude:
```bash
AI_PROVIDER=claude
CLAUDE_API_KEY=sk-ant-api03-votre-clé
```

Puis revenez à Ollama:
```bash
AI_PROVIDER=ollama
```

---

## Dépannage

### Erreur: "Ollama not available"

**Vérifiez qu'Ollama tourne:**
```bash
curl http://localhost:11434/api/tags
```

Si erreur, démarrez Ollama:
- **Windows:** Ouvrir "Ollama" depuis le menu Démarrer
- **Mac/Linux:** `ollama serve`

### Modèle non trouvé

```bash
# Vérifier modèles installés
ollama list

# Télécharger le modèle manquant
ollama pull llama3.1
```

### Réponses lentes

**Options:**
1. Utilisez un modèle plus léger: `phi3` ou `mistral`
2. Ajoutez plus de RAM à votre serveur
3. Utilisez un GPU (NVIDIA/AMD) - Ollama l'utilisera automatiquement

### Utilisation CPU/RAM élevée

C'est normal! Les modèles LLM utilisent beaucoup de ressources.

**Solutions:**
- Modèle plus petit: `phi3` (2.3GB RAM)
- Limitez conversations simultanées
- Ajoutez un cache devant Ollama

### Réponses de mauvaise qualité

**Essayez:**
1. Modèle plus gros: `llama3.1:70b` (si vous avez 64GB RAM)
2. Augmenter température: `OLLAMA_TEMPERATURE=0.9`
3. Basculer vers Claude pour tâches critiques

---

## Comparaison des coûts (100 utilisateurs, 50 conv/mois)

| Provider | Coût mensuel | Coût annuel |
|----------|--------------|-------------|
| **Ollama (llama3.1)** | **$0** ✨ | **$0** ✨ |
| Claude Sonnet | $225 | $2,700 |
| GPT-4 | $300 | $3,600 |

**Ollama = Économie de $2,700/an minimum!**

---

## Maintenance

### Mettre à jour Ollama

**Windows:** Télécharger la nouvelle version depuis le site

**Mac:**
```bash
brew upgrade ollama
```

**Linux:**
```bash
curl -fsSL https://ollama.com/install.sh | sh
```

### Mettre à jour un modèle

```bash
ollama pull llama3.1
```

### Supprimer un modèle

```bash
ollama rm phi3
```

### Libérer de l'espace

```bash
# Voir les modèles
ollama list

# Supprimer les modèles inutilisés
ollama rm nom-du-modele
```

---

## FAQ

**Q: Ollama est-il vraiment gratuit?**
A: Oui, 100% gratuit et open source. Pas de limites, pas de coûts cachés.

**Q: Puis-je utiliser Ollama en production?**
A: Absolument! Des milliers d'entreprises l'utilisent. Assurez-vous d'avoir assez de RAM.

**Q: Ollama est-il moins bon que Claude?**
A: Pour des tâches simples (factures, questions basiques): qualité similaire. Pour des tâches complexes: Claude est meilleur mais coûte cher.

**Q: Puis-je utiliser les deux (Ollama + Claude)?**
A: Oui! Configurez `AI_PROVIDER=ollama` par défaut, et basculez vers Claude pour tâches critiques.

**Q: Combien de RAM nécessaire?**
A: Minimum 8GB pour llama3.1. Recommandé 16GB pour usage fluide.

**Q: Fonctionne avec Docker?**
A: Oui! Image disponible: `docker pull ollama/ollama`

**Q: Supporte GPU?**
A: Oui, automatiquement si NVIDIA/AMD GPU détecté. 10x plus rapide!

---

## Ressources

- **Site officiel:** https://ollama.com
- **GitHub:** https://github.com/ollama/ollama
- **Modèles disponibles:** https://ollama.com/library
- **Documentation:** https://github.com/ollama/ollama/tree/main/docs

---

## Support

Pour toute question:
1. Vérifiez d'abord ce guide
2. Testez avec `ollama run llama3.1` en CLI
3. Consultez logs Laravel: `storage/logs/laravel.log`

**Status final:** ✅ Ollama est configuré, ComptaBE utilise désormais un AI **100% gratuit**! 🎉
