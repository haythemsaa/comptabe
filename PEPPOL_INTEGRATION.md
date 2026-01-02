# Peppol Integration - Complete Documentation

## Vue d'ensemble

ComptaBE est maintenant intégré avec le réseau Peppol pour l'envoi et la réception de factures électroniques B2B, conformément à l'obligation belge de 2026.

## Fonctionnalités

### ✅ Envoi de factures
- Envoi de factures via Peppol network
- Support multi-provider (Recommand.eu, Digiteal, B2Brouter)
- Génération automatique UBL 2.1
- Suivi des transmissions

### ✅ Réception de factures
- Webhook pour recevoir les factures Peppol entrantes
- Parsing automatique UBL
- Création automatique de fournisseurs
- Création automatique de factures d'achat
- Stockage des fichiers UBL originaux

### ✅ Vérification des participants
- Recherche dans l'annuaire Peppol
- Vérification de l'enregistrement des partenaires
- Recherche par nom ou VAT

## Providers supportés

### 1. Recommand.eu (Recommandé)
**Type**: Open Source
**Avantages**:
- API developer-friendly
- Playground pour tests
- Documentation complète
- Pricing volume-based

**Configuration**:
```env
PEPPOL_PROVIDER=recommand
PEPPOL_RECOMMAND_API_URL=https://api.recommand.eu/v1
PEPPOL_RECOMMAND_API_KEY=your_api_key
```

**Documentation**: https://docs.recommand.eu
**Playground**: https://playground.recommand.eu

### 2. Digiteal
**Type**: Professionnel (Belgique)
**Avantages**:
- Basé en Belgique
- ISO 27001 certifié
- Support français/néerlandais
- Conversion de formats

**Configuration**:
```env
PEPPOL_PROVIDER=digiteal
PEPPOL_DIGITEAL_API_URL=https://api.digiteal.eu/peppol
PEPPOL_DIGITEAL_API_KEY=your_api_key
PEPPOL_DIGITEAL_CLIENT_ID=your_client_id
PEPPOL_DIGITEAL_CLIENT_SECRET=your_client_secret
```

**Documentation**: https://doc.digiteal.eu

### 3. B2Brouter
**Type**: Enterprise
**Avantages**:
- ISO 27001 certifié
- Conversion de formats
- Batch processing
- Support multi-pays

**Configuration**:
```env
PEPPOL_PROVIDER=b2brouter
PEPPOL_B2BROUTER_API_URL=https://api.b2brouter.net
PEPPOL_B2BROUTER_API_KEY=your_api_key
```

## Installation & Configuration

### 1. Variables d'environnement

Ajoutez dans votre `.env`:

```env
# Provider sélectionné
PEPPOL_PROVIDER=recommand

# Configuration Recommand.eu
PEPPOL_RECOMMAND_API_URL=https://api.recommand.eu/v1
PEPPOL_RECOMMAND_API_KEY=your_api_key_here

# ID Participant Peppol
PEPPOL_PARTICIPANT_ID=0208:BE0123456789
PEPPOL_SCHEME=0208

# Webhook
PEPPOL_WEBHOOK_ENABLED=true

# Mode test (playground)
PEPPOL_TESTING=false
```

### 2. Migration de la base de données

Les migrations ont déjà été exécutées. Vérifiez que la table `companies` contient:
- `peppol_provider`
- `peppol_api_key`
- `peppol_api_secret` (encrypted)
- `peppol_participant_id`
- `peppol_webhook_secret`

### 3. Configuration dans l'interface

1. Allez dans **Paramètres → Peppol**
2. Sélectionnez votre provider
3. Entrez vos identifiants API
4. Entrez votre Participant ID (ou cliquez sur "Générer")
5. Activez le mode test si nécessaire
6. Cliquez sur "Tester la connexion"
7. Enregistrez

## Utilisation

### Envoi de factures

#### Via l'interface
1. Créez ou ouvrez une facture
2. Assurez-vous que le client a un Peppol ID configuré
3. Cliquez sur "Envoyer via Peppol"
4. La facture sera envoyée automatiquement

#### Via le code
```php
use App\Services\PeppolService;

$peppolService = new PeppolService();
$result = $peppolService->sendInvoice($invoice);

if ($result['success']) {
    // Facture envoyée!
    $transmissionId = $result['transmission_id'];
    echo "Envoyé! ID: {$transmissionId}";
} else {
    // Erreur
    echo "Erreur: {$result['error']}";
}
```

### Vérification d'un participant

```php
$result = $peppolService->verifyParticipant('0208:BE0123456789');

if ($result['exists']) {
    echo "Participant enregistré sur Peppol";
    echo "Nom: {$result['name']}";
} else {
    echo "Participant non trouvé";
}
```

### Recherche dans l'annuaire

```php
$results = $peppolService->searchParticipants('Company Name');

foreach ($results as $participant) {
    echo "{$participant['name']} - {$participant['participant_id']}";
}
```

### Réception de factures (Webhook)

#### Configuration du webhook

L'URL du webhook est générée automatiquement dans **Paramètres → Peppol**:
```
https://your-domain.com/api/webhooks/peppol/{webhook_secret}
```

Configurez cette URL dans le dashboard de votre Access Point.

#### Traitement automatique

Quand une facture est reçue via Peppol:

1. Le webhook vérifie le secret
2. Parse le payload selon le provider
3. Extrait les données UBL
4. Trouve ou crée le fournisseur
5. Crée une facture d'achat en brouillon
6. Stocke le fichier UBL original
7. Notifie l'utilisateur (optionnel)

Les factures reçues apparaissent dans **Achats** avec le tag "Peppol".

## Architecture

### Services

#### `App\Services\PeppolService`
Service principal pour toutes les opérations Peppol:
- `sendInvoice(Invoice $invoice)` - Envoie une facture
- `verifyParticipant(string $participantId)` - Vérifie un participant
- `searchParticipants(string $query)` - Recherche dans l'annuaire
- `getTransmissionStatus(string $id)` - Statut d'envoi
- `testConnection()` - Test de connexion

#### `App\Services\UblService`
Gestion des fichiers UBL:
- `generateInvoiceUbl(Invoice $invoice)` - Génère UBL XML
- `parseInvoiceUbl(string $xml)` - Parse UBL XML
- Conforme Peppol BIS 3.0

### Controllers

#### `App\Http\Controllers\PeppolWebhookController`
Gestion des webhooks entrants:
- Support Recommand.eu, Digiteal, B2Brouter
- Parsing automatique selon provider
- Création automatique de factures
- Logs détaillés

### Routes

```php
// Webhook public (pas d'authentification)
POST /api/webhooks/peppol/{webhookSecret}

// Dans l'interface (authentifié)
POST /invoices/{invoice}/send-peppol
POST /settings/peppol/test
```

## Sécurité

### Webhook
- Secret unique par entreprise (64 caractères)
- Vérification du secret avant traitement
- Logs de tous les appels
- Validation des données entrantes

### API Keys
- Stockage encrypted dans la DB (`peppol_api_secret`)
- Transmission via HTTPS uniquement
- Rotation possible via l'interface

### Isolation multi-tenant
- Chaque entreprise a sa propre config
- Webhook secret unique
- Isolation des fichiers UBL

## Monitoring & Logs

### Logs disponibles
```bash
# Voir les logs Peppol
tail -f storage/logs/laravel.log | grep Peppol

# Logs d'envoi
grep "Peppol send" storage/logs/laravel.log

# Logs de réception
grep "Peppol webhook" storage/logs/laravel.log
```

### Données loggées
- Tous les envois de factures
- Toutes les réceptions webhook
- Erreurs de parsing UBL
- Tests de connexion

## Dépannage

### Erreur: "Invalid webhook secret"
- Vérifiez que le secret dans l'URL correspond à celui stocké
- Régénérez le secret dans Paramètres → Peppol

### Erreur: "Provider not configured"
- Vérifiez que `PEPPOL_PROVIDER` est défini dans `.env`
- Vérifiez que les credentials du provider sont configurés

### Factures non reçues
1. Vérifiez que le webhook est configuré chez le provider
2. Testez le webhook manuellement avec curl
3. Vérifiez les logs: `storage/logs/laravel.log`

### Test du webhook
```bash
curl -X POST \
  https://your-domain.com/api/webhooks/peppol/your_secret \
  -H 'Content-Type: application/json' \
  -d '{
    "document": "base64_encoded_ubl_xml",
    "document_type": "invoice",
    "sender_id": "0208:0123456789"
  }'
```

## Conformité 2026

L'intégration est conforme aux exigences belges:

✅ Format UBL 2.1 (Peppol BIS 3.0)
✅ Schéma 0208 (numéro d'entreprise belge)
✅ Envoi via réseau Peppol
✅ Réception automatique
✅ Archivage des fichiers UBL
✅ Multi-Access Point

## Prochaines étapes

### Recommandé
- [ ] Obtenir credentials Recommand.eu
- [ ] Configurer le webhook chez le provider
- [ ] Tester l'envoi en mode sandbox
- [ ] Tester la réception
- [ ] Former les utilisateurs

### Optionnel
- [ ] Ajouter notifications email pour factures reçues
- [ ] Dashboard Peppol avec statistiques
- [ ] Export des transmissions
- [ ] Retry automatique en cas d'échec
- [ ] Validation avancée des factures entrantes

## Support

### Documentation providers
- Recommand.eu: https://docs.recommand.eu
- Digiteal: https://doc.digiteal.eu
- Peppol: https://peppol.org

### Liens utiles
- SPF Finances Belgique: https://finances.belgium.be/fr/entreprises/tva/declaration-tva/factures-electroniques-b2b
- OpenPeppol: https://peppol.org
- UBL Specs: http://docs.oasis-open.org/ubl/UBL-2.1.html

## Changelog

### v1.0.0 (2025-12-25)
- ✅ Intégration Recommand.eu, Digiteal, B2Brouter
- ✅ Envoi de factures via Peppol
- ✅ Réception via webhook
- ✅ Création automatique de factures d'achat
- ✅ Vérification et recherche de participants
- ✅ Interface de configuration
- ✅ Support multi-tenant
- ✅ Logs et monitoring
