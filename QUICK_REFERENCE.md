# ComptaBE - Référence Rapide 🚀

## Commandes Artisan Essentielles

### Setup & Démo
```bash
# Générer données de démo complètes
php artisan demo:setup --full

# Pour une entreprise spécifique
php artisan demo:setup --company=uuid

# Migrations
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:status
```

### TVA
```bash
# Générer déclarations manquantes
php artisan vat:generate-missing --year=2025 --period-type=monthly

# Trimestre spécifique
php artisan vat:generate-missing --year=2025 --period-type=quarterly
```

### Banque
```bash
# Importer fichier CODA
php artisan bank:import-coda /path/to/file.cod --bank-account=uuid

# Rapprochement automatique
php artisan bank:reconcile-auto --company=uuid
```

### Peppol
```bash
# Envoyer facture
php artisan peppol:send-invoice {invoice-id}

# Vérifier statut
php artisan peppol:check-status {invoice-id}
```

### Machine Learning
```bash
# Entraîner modèle prédictions
php artisan ml:train-cash-flow --company=uuid

# Générer prédictions 6 mois
php artisan ml:predict-cash-flow --company=uuid --months=6
```

---

## URLs Principales

### Application
```
Dashboard:        http://localhost:8000/dashboard
Factures:         http://localhost:8000/invoices
Devis:            http://localhost:8000/quotes
Partenaires:      http://localhost:8000/partners
TVA:              http://localhost:8000/vat-declarations
Banque:           http://localhost:8000/bank-accounts
```

### Portail Client
```
Login:            http://localhost:8000/portal/{company-id}
Dashboard:        http://localhost:8000/portal/{company-id}/dashboard
Factures:         http://localhost:8000/portal/{company-id}/invoices
Documents:        http://localhost:8000/portal/{company-id}/documents
```

### Présentation
```
Slides HTML:      http://localhost:8000/presentation.html
```

---

## Credentials Démo

### Après `php artisan demo:setup --full`

| Rôle | Email | Password |
|------|-------|----------|
| Owner | owner@demo.comptabe.be | demo123 |
| Accountant | accountant@demo.comptabe.be | demo123 |
| Client Portal | client@demo.comptabe.be | demo123 |

---

## API REST Endpoints

### Base URL
```
http://localhost:8000/api/v1
```

### Authentification
```bash
POST /api/v1/login
{
  "email": "user@example.com",
  "password": "password"
}

# Response includes token
Authorization: Bearer {token}
```

### Ressources
```bash
# Factures
GET    /api/v1/invoices
POST   /api/v1/invoices
GET    /api/v1/invoices/{id}
PUT    /api/v1/invoices/{id}
DELETE /api/v1/invoices/{id}
POST   /api/v1/invoices/{id}/send-email

# Devis
GET    /api/v1/quotes
POST   /api/v1/quotes
POST   /api/v1/quotes/{id}/convert-to-invoice

# Partenaires
GET    /api/v1/partners
POST   /api/v1/partners

# Produits
GET    /api/v1/products
POST   /api/v1/products

# Chat AI
GET    /api/chat/conversations
POST   /api/chat/send
POST   /api/chat/tools/{execution}/confirm
```

---

## Variables d'Environnement Importantes

### Minimum requis
```env
APP_URL=http://localhost:8000
DB_DATABASE=compta
DB_USERNAME=root
DB_PASSWORD=
```

### Claude AI Assistant
```env
CLAUDE_API_KEY=sk-ant-api03-...
CLAUDE_MODEL=claude-3-5-sonnet-20241022
CLAUDE_MAX_TOKENS=4096
```

### Peppol (optionnel)
```env
PEPPOL_PROVIDER=storecove
STORECOVE_API_KEY=your_key
STORECOVE_LEGAL_ENTITY_ID=your_id
```

### Email (optionnel)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
```

---

## Raccourcis Clavier (Frontend)

### Navigation présentation HTML
```
← (Left arrow)    : Slide précédent
→ (Right arrow)   : Slide suivant
Home              : Premier slide
End               : Dernier slide
```

### Chat Widget
```
Cmd/Ctrl + /      : Ouvrir/fermer chat (si implémenté)
Esc               : Fermer chat
```

---

## Débogage Rapide

### Vider caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Logs
```bash
# Surveiller logs en temps réel
tail -f storage/logs/laravel.log

# Dernières 50 lignes
tail -50 storage/logs/laravel.log
```

### Tinker (REPL PHP)
```bash
php artisan tinker

# Exemples dans tinker:
>> User::count()
>> Company::first()
>> Invoice::where('status', 'paid')->count()
```

---

## Tests

### Lancer tests
```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter InvoiceTest
php artisan test tests/Feature/InvoiceTest.php

# Avec couverture
php artisan test --coverage
```

---

## Base de Données

### Accès MySQL (Laragon)
```bash
# Via terminal
mysql -u root

# Sélectionner DB
USE compta;

# Requêtes utiles
SHOW TABLES;
DESCRIBE invoices;
SELECT COUNT(*) FROM invoices;
```

### Seeders
```bash
php artisan db:seed
php artisan db:seed --class=CompanySeeder
```

---

## Documentation Locale

### Fichiers à consulter
```
README.md                    - Installation
GUIDE_UTILISATEUR.md         - Guide complet
FEATURES_STATUS.md           - État fonctionnalités
PRESENTATION_COMMERCIALE.md  - Pitch commercial
SESSION_RECAP.md             - Dernière session
QUICK_REFERENCE.md           - Ce fichier
```

---

## Outils AI Chat

### Exemples de prompts utilisateur
```
"Crée une facture pour Acme Corporation avec 10h de consulting à 85€/h"

"Combien de factures impayées ai-je ?"

"Génère ma déclaration TVA pour décembre 2024"

"Invite jean.dupont@example.com comme comptable"

"Envoie la facture DEMO-00015 par email"

"Convertis le devis DEVIS-00003 en facture"

"Rapproche la transaction bancaire de 1028,50€ du 15 décembre"
```

### Outils disponibles (30+)
- **read_invoices** - Lire factures
- **create_invoice** - Créer facture
- **create_quote** - Créer devis
- **search_partners** - Chercher partenaires
- **create_partner** - Créer partenaire
- **record_payment** - Enregistrer paiement
- **invite_user** - Inviter utilisateur
- **send_invoice_email** - Envoyer facture
- **convert_quote_to_invoice** - Convertir devis
- **generate_vat_declaration** - Déclaration TVA
- **send_via_peppol** - Envoyer Peppol
- **reconcile_bank_transaction** - Rapprocher banque
- ... et 18+ autres

---

## Dépannage Express

### Problème : "Class not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

### Problème : "Permission denied" (storage)
```bash
# Linux/Mac
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Windows (run as admin)
icacls storage /grant Users:F /T
```

### Problème : Migration échoue
```bash
# Recommencer à zéro
php artisan migrate:fresh --seed

# Ou marquer manuellement
php artisan tinker
>> DB::table('migrations')->insert(['migration' => 'nom_migration', 'batch' => 1]);
```

### Problème : AI Chat ne répond pas
```bash
# Vérifier config
php artisan config:show ai

# Re-publier config
php artisan vendor:publish --tag=config
```

---

## Performances

### Optimisation production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
composer install --optimize-autoloader --no-dev
```

### Désactiver debug
```env
APP_DEBUG=false
APP_ENV=production
```

---

## Support Rapide

### Contact
- **Email** : support@comptabe.be
- **Tél** : +32 2 123 45 67
- **GitHub Issues** : https://github.com/comptabe/app/issues

### Resources
- **Docs** : https://docs.comptabe.be
- **API** : https://api.comptabe.be/docs
- **Status** : https://status.comptabe.be

---

## Checklist Avant Démo Client

- [ ] `php artisan demo:setup --full` exécuté
- [ ] Login owner testé
- [ ] Créer 1-2 factures manuellement
- [ ] Tester AI Chat (créer facture)
- [ ] Tester portail client (login client)
- [ ] Vérifier présentation HTML
- [ ] Préparer pitch 2 minutes
- [ ] Avoir calcul ROI prêt
- [ ] Grille tarifaire imprimée
- [ ] Questions fréquentes préparées

---

**Dernière mise à jour** : 28 décembre 2024
**Version** : 2.0.0

🎯 **Gardez ce fichier à portée de main pour référence rapide !**
