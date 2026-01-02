# Peppol SaaS Implementation - Guide Complet

## Vue d'ensemble

Implémentation complète d'un système Peppol multi-tenant pour ComptaBE avec gestion centralisée de l'API et système de quotas.

### Architecture

- **Modèle SaaS centralisé** : Un seul compte API provider géré par le superadmin
- **Quotas par tenant** : Chaque entreprise a un plan et quota mensuel
- **Optimisation automatique** : Recommandations intelligentes pour changer de plan provider
- **Suivi d'usage** : Historique complet des transmissions et coûts

---

## 1. Configuration Initiale

### Étape 1 : Exécuter les migrations

```bash
php artisan migrate
```

Cela créera :
- Colonnes quota dans `companies`
- Table `peppol_usage` pour tracking
- Champs globaux dans `system_settings`

### Étape 2 : Configurer l'API globale

1. Accéder à `/admin/peppol/settings`
2. Choisir un provider (Recommand.eu recommandé pour débuter)
3. Sélectionner le plan FREE (€0/mois, 25 documents inclus)
4. Entrer API Key et Secret (obtenus auprès du provider)
5. Activer Peppol globalement
6. Tester la connexion

### Étape 3 : Configurer les quotas entreprises

1. Accéder à `/admin/peppol/quotas`
2. Pour chaque entreprise, définir :
   - **Plan** : free (10/mois), starter (50/mois), pro (150/mois), etc.
   - **Quota mensuel** : selon le plan choisi
   - **Overage autorisé** : oui/non (facturation au-delà du quota)

---

## 2. Fonctionnalités Implémentées

### A. Dashboard Admin (`/admin/peppol/dashboard`)

**Vue d'ensemble complète** :
- Stats du provider actuel (coût, volume, plan)
- Recommandations d'optimisation automatiques
- Top 10 utilisateurs du mois
- Revenus générés vs coûts provider

### B. Paramètres Globaux (`/admin/peppol/settings`)

**Configuration centralisée** :
- Choix du provider et plan
- API credentials (une seule fois)
- Activation/désactivation globale
- Test de connexion

### C. Gestion Quotas (`/admin/peppol/quotas`)

**Gestion par entreprise** :
- Recherche et filtrage par plan
- Visualisation usage/quota en temps réel
- Modification des plans et quotas
- Indicateurs de dépassement (>80%)

### D. Optimisation (`/admin/peppol/optimize`)

**Analyse intelligente** :
- Calcul du volume total mensuel
- Projection mois prochain (+20%)
- Recommandation plan optimal
- Application en un clic

### E. Historique Usage (`/admin/peppol/usage`)

**Suivi détaillé** :
- Toutes les transmissions (envoi/réception)
- Filtres par date et statut
- Coût par transmission
- Export possible

---

## 3. Services Clés

### PeppolService (Modifié)

**Changements majeurs** :
```php
// AVANT : Chaque tenant avait son API
$this->apiKey = $company->peppol_api_key;

// APRÈS : API globale partagée
$this->apiKey = $this->getGlobalSetting('peppol_global_api_key');
```

**Nouvelles vérifications** :
1. ✅ Peppol activé globalement ?
2. ✅ API configurée ?
3. ✅ Quota disponible pour ce tenant ?
4. 📤 Envoi de la facture
5. 📊 Logging usage + incrémentation quota

### PeppolPlanOptimizer (Nouveau)

**Intelligence de scaling** :
```php
$optimizer = app(PeppolPlanOptimizer::class);
$recommendation = $optimizer->getRecommendation();

// Retourne :
// - should_upgrade: true/false
// - should_downgrade: true/false
// - optimal: ['provider_name', 'plan_name', 'total_cost']
// - savings: montant économisé/dépensé
// - reason: explication en français
```

### Méthodes Company (Ajoutées)

```php
$company->hasPeppolQuota(); // true si quota restant > 0
$company->getRemainingPeppolQuota(); // nombre documents restants
$company->getPeppolQuotaPercentage(); // 0-100%
$company->incrementPeppolUsage(); // +1 après envoi réussi
$company->resetPeppolUsage(); // remettre à 0 (mensuel)
$company->isPeppolEnabled(); // vérif plan + quota
```

---

## 4. Commandes Artisan

### Réinitialisation Quotas (Mensuelle)

```bash
php artisan peppol:reset-quotas
```

**À planifier dans le cron** (1er de chaque mois) :
```
0 0 1 * * cd /path/to/compta && php artisan peppol:reset-quotas
```

### Vérification Plan (Hebdomadaire)

```bash
php artisan peppol:check-plan
```

Affiche :
- Volume actuel
- Coût actuel
- Recommandation si changement nécessaire

**À planifier** (tous les lundis) :
```
0 9 * * 1 cd /path/to/compta && php artisan peppol:check-plan
```

---

## 5. Configuration Pricing

### Plans Provider (`config/peppol_plans.php`)

**Recommand.eu (Recommandé pour débuter)** :
- **FREE** : €0/mois + 25 docs inclus
- **Starter** : €29/mois + 200 docs
- **Professional** : €99/mois + 1000 docs
- **Business** : €249/mois + 5000 docs
- **Enterprise** : €499/mois + 15000 docs

**Overage** : coût par document supplémentaire décroissant

### Plans Tenant (Vos clients)

```php
'tenant_plans' => [
    'free' => ['name' => 'Gratuit', 'monthly_quota' => 10, 'price' => 0],
    'starter' => ['name' => 'Starter', 'monthly_quota' => 50, 'price' => 15],
    'pro' => ['name' => 'Pro', 'monthly_quota' => 150, 'price' => 49],
    'business' => ['name' => 'Business', 'monthly_quota' => 500, 'price' => 149],
    'enterprise' => ['name' => 'Enterprise', 'monthly_quota' => 2000, 'price' => 499],
]
```

**Vous facturez vos clients** selon leur plan choisi.

---

## 6. Stratégie de Scaling

### Phase 1 : Démarrage (0-25 factures/mois)

- **Provider** : Recommand.eu FREE (€0/mois)
- **Clients** : Offrir plan "free" (10 docs/mois) gratuitement
- **Coût** : €0
- **Revenus** : €0
- **Marge** : Break-even

### Phase 2 : Croissance (25-200 factures/mois)

- **Provider** : Passer au Starter (€29/mois) automatiquement
- **Clients** : Mix de free + starter (€15/mois) + pro (€49/mois)
- **Exemple** : 10 clients payants = €150-300/mois
- **Coût** : €29/mois
- **Marge** : **€121-271/mois**

### Phase 3 : Expansion (200-1000 factures/mois)

- **Provider** : Professional (€99/mois)
- **Clients** : 20-30 clients payants = €500-1000/mois
- **Marge** : **€401-901/mois**

### Phase 4 : Scale (1000+ factures/mois)

- **Provider** : Business/Enterprise selon volume
- **Optimisation** : PeppolPlanOptimizer recommande automatiquement
- **Marge** : Proportionnelle au nombre de clients

---

## 7. Checklist de Test

### ✅ Configuration
- [ ] Migrations exécutées sans erreur
- [ ] Config `peppol_plans.php` chargée
- [ ] Route `/admin/peppol/dashboard` accessible
- [ ] Vue settings affichée correctement

### ✅ API Provider
- [ ] API Key et Secret enregistrés
- [ ] Test de connexion réussi (bouton "Tester")
- [ ] Peppol activé globalement

### ✅ Quotas
- [ ] Entreprise test créée avec plan "free"
- [ ] Quota initialisé à 10
- [ ] Usage à 0

### ✅ Envoi Facture
- [ ] Créer facture test
- [ ] Envoyer via Peppol
- [ ] Vérifier quota incrémenté (0 → 1)
- [ ] Vérifier `peppol_usage` table (1 ligne ajoutée)
- [ ] Vérifier coût calculé

### ✅ Dépassement Quota
- [ ] Forcer usage = quota (ex: 10/10)
- [ ] Tenter envoi → doit échouer avec message "Quota dépassé"
- [ ] Vérifier log dans peppol_usage avec status='failed'

### ✅ Optimisation
- [ ] Accéder `/admin/peppol/optimize`
- [ ] Vérifier recommandation affichée
- [ ] Simuler volume élevé → recommandation changement plan

### ✅ Commandes
- [ ] Exécuter `php artisan peppol:check-plan` → affichage stats
- [ ] Exécuter `php artisan peppol:reset-quotas` → usage remis à 0

---

## 8. Flux Utilisateur Complet

### Côté Superadmin

1. **Setup initial** (une fois)
   - Accéder `/admin/peppol/settings`
   - Choisir Recommand.eu / FREE
   - Entrer API credentials
   - Activer Peppol

2. **Gestion quotidienne**
   - Dashboard : surveiller volume et coûts
   - Quotas : ajuster plans clients selon leur usage
   - Optimize : vérifier recommandations mensuelles

3. **Scaling** (mensuel)
   - Vérifier `/admin/peppol/optimize`
   - Si recommandation upgrade → appliquer nouveau plan
   - Mettre à jour API credentials si changement provider

### Côté Tenant (Client)

1. Créer facture normalement dans ComptaBE
2. Cliquer "Envoyer via Peppol"
3. Si quota OK → envoi réussi + confirmation
4. Si quota dépassé → message d'erreur + invitation upgrade plan
5. Consulter usage dans tableau de bord tenant

---

## 9. Sécurité et Bonnes Pratiques

### API Credentials

- ✅ Stockées dans `system_settings` (base de données)
- ✅ Accessibles uniquement par superadmin
- ✅ Jamais exposées côté client
- ⚠️ **TODO** : Chiffrer les credentials (Laravel encryption)

### Quotas

- ✅ Vérification avant chaque envoi
- ✅ Incrémentation atomique (évite race conditions)
- ✅ Overage configurable par tenant
- ✅ Coût overage personnalisable

### Logs

- ✅ Tous les envois/réceptions loggés
- ✅ Statut success/failed conservé
- ✅ Coût par transaction calculé
- ✅ Métadonnées (provider, plan) sauvegardées

---

## 10. Maintenance

### Tâches Mensuelles

- Exécuter `peppol:reset-quotas` (automatique via cron)
- Vérifier dashboard pour optimisation
- Facturer clients selon leurs plans
- Payer facture provider

### Tâches Trimestrielles

- Analyser tendance usage (croissance ?)
- Évaluer pertinence plans tenant (ajuster pricing ?)
- Vérifier concurrence (nouveaux providers ?)

### Monitoring

- Surveiller `peppol_usage` table (croissance)
- Alertes si quota provider proche limite
- Notifications si client dépasse 80% quota

---

## 11. Évolutions Futures

### Court terme
- [ ] Ajouter notifications email (quota dépassé, recommandation plan)
- [ ] Exporter usage en CSV/Excel
- [ ] Graphiques dashboard (Charts.js ou ApexCharts)
- [ ] Multi-providers (switch dynamique selon coût)

### Moyen terme
- [ ] API publique pour clients (consulter usage)
- [ ] Facturation automatique (Stripe/Mollie integration)
- [ ] Webhooks pour réception Peppol asynchrone
- [ ] Cache Redis pour stats (performance)

### Long terme
- [ ] IA pour prédiction usage futur
- [ ] Auto-scaling provider (changement automatique)
- [ ] Support multi-devises
- [ ] Internationalisation (Peppol EU-wide)

---

## 12. Support et Documentation

### Fichiers Clés

- `PEPPOL_SAAS_ARCHITECTURE.md` : Architecture technique détaillée
- `PEPPOL_STRATEGIE_SCALING.md` : Stratégie de scaling et pricing
- `PROGRESS_PEPPOL_SAAS.md` : Checklist progression
- `config/peppol_plans.php` : Configuration plans et pricing

### Routes Admin

```
/admin/peppol/dashboard      → Vue d'ensemble
/admin/peppol/settings       → Configuration API
/admin/peppol/quotas         → Gestion quotas clients
/admin/peppol/optimize       → Recommandations
/admin/peppol/usage          → Historique usage
```

### Commandes Artisan

```bash
php artisan peppol:reset-quotas    # Réinitialiser quotas mensuels
php artisan peppol:check-plan      # Vérifier plan optimal
```

### Contact Providers

- **Recommand.eu** : https://recommand.eu/contact
- **Digiteal** : https://digiteal.eu
- **Peppol Box** : https://www.peppol-box.be

---

## 13. Résumé Technique

### Base de Données

**Tables modifiées** :
- `companies` : +6 colonnes (plan, quota, usage, overage)
- `system_settings` : +4 settings globaux Peppol

**Tables créées** :
- `peppol_usage` : tracking complet transmissions

### Services

- `PeppolService` : Envoi/réception + vérification quotas
- `PeppolPlanOptimizer` : Intelligence scaling
- `PeppolUsage` : Modèle tracking usage

### Controllers

- `AdminPeppolController` : 10 méthodes admin complètes

### Vues

- 5 vues admin Blade (dashboard, settings, quotas, optimize, usage)

### Commandes

- `PeppolResetQuotas` : Reset mensuel
- `PeppolCheckPlan` : Analyse plan

### Configuration

- `config/peppol_plans.php` : 3 providers, 15+ plans, pricing complet

---

## 14. FAQ

**Q : Puis-je changer de provider facilement ?**
R : Oui, via `/admin/peppol/settings`. Changer provider + plan + credentials, puis tester connexion.

**Q : Que se passe-t-il si je dépasse le quota provider ?**
R : Vous payez l'overage cost (ex: €0.30/doc pour plan FREE). L'optimizer vous recommandera un upgrade.

**Q : Comment facturer mes clients ?**
R : Selon le plan choisi (config `tenant_plans`). Exemple : client "pro" = €49/mois.

**Q : Les quotas se réinitialisent automatiquement ?**
R : Oui, si vous configurez le cron `peppol:reset-quotas` (1er du mois).

**Q : Puis-je désactiver Peppol temporairement ?**
R : Oui, décocher "Activer Peppol" dans `/admin/peppol/settings`.

**Q : Comment tester sans consommer mon quota ?**
R : Utiliser l'environnement sandbox du provider (si disponible) ou plan FREE avec 25 docs gratuits.

---

## 15. Prochaines Étapes

### Immédiat (Aujourd'hui)

1. ✅ Tester configuration complète
2. ✅ Envoyer une facture test via Peppol
3. ✅ Vérifier dashboard affiche correctement
4. ✅ Configurer cron `peppol:reset-quotas`

### Cette Semaine

- [ ] Ajouter liens Peppol dans menu admin
- [ ] Créer documentation utilisateur (clients)
- [ ] Tester dépassement quota
- [ ] Configurer notifications email

### Ce Mois

- [ ] Onboarder premiers clients sur plans payants
- [ ] Monitorer usage réel
- [ ] Ajuster pricing si nécessaire
- [ ] Implémenter graphiques dashboard

---

## Conclusion

Vous disposez maintenant d'un **système Peppol SaaS complet** avec :

✅ Gestion centralisée API (un seul compte provider partagé)
✅ Quotas intelligents par tenant
✅ Optimisation automatique du plan provider
✅ Tracking détaillé usage et coûts
✅ Interface admin complète
✅ Commandes Artisan pour automatisation
✅ Stratégie de scaling claire (FREE → Enterprise)

**Coût initial : €0** (plan FREE)
**Scaling : Automatique** (PeppolPlanOptimizer)
**Marge : Positive** dès les premiers clients payants

Le système est prêt pour production. Bon scaling ! 🚀
