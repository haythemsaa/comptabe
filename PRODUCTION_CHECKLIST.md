# ComptaBE - Checklist de Mise en Production

## Pre-requis Serveur

### Minimum Requis
- [ ] PHP 8.2+ avec extensions: bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pcre, pdo, tokenizer, xml, zip, gd
- [ ] MySQL 8.0+ ou MariaDB 10.6+
- [ ] Composer 2.x
- [ ] Node.js 18+ et npm (pour build assets)
- [ ] Redis (recommande pour cache/queue en production)
- [ ] Tesseract OCR (pour extraction factures)

### Extensions PHP Requises
```bash
php -m | grep -E 'bcmath|curl|gd|intl|mbstring|mysql|redis|xml|zip'
```

---

## 1. Configuration Environnement

### Fichier .env
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false` (CRITIQUE!)
- [ ] `APP_KEY` genere avec `php artisan key:generate`
- [ ] `APP_URL` configure avec le domaine HTTPS
- [ ] `LOG_LEVEL=warning` ou `error`

### Base de Donnees
- [ ] Utilisateur MySQL dedie (pas root!)
- [ ] Mot de passe fort (min 16 caracteres)
- [ ] Connexion SSL activee si possible
- [ ] Backups automatiques configures

### Session & Securite
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_SAME_SITE=strict`
- [ ] `SESSION_ENCRYPT=true`
- [ ] `BCRYPT_ROUNDS=12`

---

## 2. Securite

### HTTPS
- [ ] Certificat SSL valide (Let's Encrypt ou autre)
- [ ] Redirection HTTP -> HTTPS
- [ ] HSTS active dans la config serveur

### Headers Securite (nginx/apache)
```nginx
add_header X-Frame-Options "SAMEORIGIN";
add_header X-Content-Type-Options "nosniff";
add_header X-XSS-Protection "1; mode=block";
add_header Referrer-Policy "strict-origin-when-cross-origin";
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' js.stripe.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: *.stripe.com;";
```

### Credentials Sensibles
- [ ] Peppol API keys dans un vault (pas dans .env!)
- [ ] Stripe keys en mode live
- [ ] Backup des credentials dans un endroit securise

### Firewall
- [ ] Ports 80/443 ouverts uniquement
- [ ] Port 22 restreint par IP
- [ ] MySQL accessible uniquement en local

---

## 3. Base de Donnees

### Migrations & Seeders
```bash
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=SubscriptionPlansSeeder --force
php artisan db:seed --class=ModulesSeeder --force
```

### Optimisations
- [ ] Index sur colonnes frequemment recherchees
- [ ] Query cache MySQL active
- [ ] Slow query log active pour monitoring

---

## 4. Cache & Performance

### Cache Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache  # si utilisant blade-icons
```

### Optimisation Composer
```bash
composer install --optimize-autoloader --no-dev
```

### Assets Frontend
```bash
npm run build
```

### Redis (Recommande)
- [ ] Redis installe et configure
- [ ] `CACHE_STORE=redis`
- [ ] `QUEUE_CONNECTION=redis`
- [ ] `SESSION_DRIVER=redis` (optionnel)

---

## 5. Queue & Jobs

### Worker Supervisor
Creer `/etc/supervisor/conf.d/compta-worker.conf`:
```ini
[program:compta-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/compta/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/compta/storage/logs/worker.log
stopwaitsecs=3600
```

### Demarrer Supervisor
```bash
supervisorctl reread
supervisorctl update
supervisorctl start compta-worker:*
```

---

## 6. Taches Planifiees (Cron)

Ajouter au crontab:
```cron
* * * * * cd /var/www/compta && php artisan schedule:run >> /dev/null 2>&1
```

### Taches Automatiques Incluses
- Rappels factures impayees
- Synchronisation bancaire (si configure)
- Nettoyage sessions expirees
- Backups automatiques

---

## 7. Email

### Configuration SMTP
- [ ] `MAIL_MAILER=smtp`
- [ ] Serveur SMTP configure (Mailgun, Postmark, SES, etc.)
- [ ] SPF, DKIM, DMARC configures pour le domaine
- [ ] Test d'envoi email reussi

### Test Email
```bash
php artisan tinker
>>> Mail::raw('Test email', fn($m) => $m->to('test@example.com')->subject('Test'));
```

---

## 8. Stockage Fichiers

### Permissions
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Lien Symbolique
```bash
php artisan storage:link
```

### S3/Cloud Storage (Optionnel)
- [ ] Bucket cree avec bonnes permissions
- [ ] `FILESYSTEM_DISK=s3`
- [ ] Credentials AWS configures

---

## 9. Monitoring

### Logs
- [ ] Rotation des logs configuree
- [ ] Alertes sur erreurs critiques (Slack/email)
- [ ] Canal d'audit pour transactions financieres

### Health Check
L'endpoint `/up` est disponible par defaut.

### Outils Recommandes
- [ ] Laravel Telescope (dev/staging uniquement!)
- [ ] Sentry ou Bugsnag pour error tracking
- [ ] New Relic ou Blackfire pour performance

---

## 10. Backup

### Script Backup Quotidien
```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d)
BACKUP_DIR="/backups/compta"

# Database
mysqldump -u compta_user -p compta_production | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Fichiers uploades
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /var/www/compta/storage/app

# Retention 30 jours
find $BACKUP_DIR -type f -mtime +30 -delete
```

### Backup .env
- [ ] .env sauvegarde dans un endroit securise (pas avec le code!)
- [ ] Documentation des valeurs sensibles

---

## 11. Tests Pre-Production

### Fonctionnels
- [ ] Connexion utilisateur
- [ ] Creation facture
- [ ] Envoi email facture
- [ ] Paiement Stripe (mode test puis live)
- [ ] Envoi Peppol (test puis production)
- [ ] Import bancaire
- [ ] Declaration TVA
- [ ] Rapports PDF

### Performance
- [ ] Test de charge (10+ utilisateurs simultanes)
- [ ] Temps de reponse < 500ms
- [ ] Pas de N+1 queries

### Securite
- [ ] Scan OWASP ZAP
- [ ] Headers securite verifies (securityheaders.com)
- [ ] SSL test A+ (ssllabs.com)

---

## 12. Go-Live

### Ordre des Operations
1. [ ] Mode maintenance: `php artisan down --secret="secret-token"`
2. [ ] Deployer le code
3. [ ] `composer install --no-dev --optimize-autoloader`
4. [ ] `php artisan migrate --force`
5. [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. [ ] `npm run build`
7. [ ] Restart workers: `supervisorctl restart compta-worker:*`
8. [ ] Tests rapides
9. [ ] Mode production: `php artisan up`

### Verification Post-Deploiement
- [ ] Page d'accueil accessible
- [ ] Connexion fonctionne
- [ ] Pas d'erreurs dans les logs
- [ ] Queue traite les jobs
- [ ] Emails envoyes

---

## Support

En cas de probleme:
- Logs: `tail -f storage/logs/laravel.log`
- Queue: `php artisan queue:failed`
- Cache: `php artisan cache:clear`

---

*Derniere mise a jour: Janvier 2026*
