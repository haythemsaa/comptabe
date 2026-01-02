# ComptaBE - Guide Utilisateur

## Table des matières

1. [Introduction](#introduction)
2. [Premiers pas](#premiers-pas)
3. [Gestion des factures](#gestion-des-factures)
4. [Partenaires (Clients/Fournisseurs)](#partenaires)
5. [Facturation électronique Peppol](#peppol)
6. [E-Reporting 2028](#e-reporting)
7. [Déclarations TVA](#tva)
8. [Comptabilité](#comptabilite)
9. [Open Banking (PSD2)](#open-banking)
10. [Rapports et analyses](#rapports)
11. [Paramètres](#parametres)

---

## Introduction

ComptaBE est une application de comptabilité belge complète, conforme aux normes légales en vigueur et préparée pour le mandat de facturation électronique B2B 2026 (Peppol) et l'e-Reporting 2028.

### Fonctionnalités principales

- Facturation de vente et d'achat
- Facturation électronique via Peppol (conforme UBL 2.1 / BIS Billing 3.0)
- Préparation e-Reporting 2028 (modèle 5 coins)
- Déclarations TVA belges (mensuelle/trimestrielle)
- Comptabilité en partie double (PCMN)
- Open Banking (synchronisation bancaire PSD2)
- Gestion multi-sociétés
- Collaboration (invitations d'utilisateurs)

---

## Premiers pas

### Connexion

1. Accédez à l'application via votre navigateur
2. Entrez votre email et mot de passe
3. Si l'authentification à deux facteurs (2FA) est activée, entrez le code de votre application d'authentification

### Sélection d'entreprise

Si vous avez accès à plusieurs entreprises :
1. Cliquez sur le sélecteur d'entreprise dans la barre de navigation
2. Choisissez l'entreprise sur laquelle vous souhaitez travailler

### Tableau de bord

Le tableau de bord affiche :
- Créances clients en cours
- Dettes fournisseurs
- Solde bancaire total
- Factures en retard
- Graphique des revenus mensuels
- Actions à effectuer (factures à envoyer, transactions à catégoriser, etc.)

---

## Gestion des factures

### Créer une facture de vente

1. Naviguez vers **Ventes > Factures**
2. Cliquez sur **Nouvelle facture**
3. Sélectionnez ou créez un client
4. Ajoutez les lignes de facturation :
   - Description
   - Quantité
   - Prix unitaire
   - Taux de TVA (21%, 12%, 6%, 0%)
5. Vérifiez les totaux
6. Cliquez sur **Enregistrer** pour sauvegarder en brouillon
7. Cliquez sur **Valider** pour finaliser la facture

### Communication structurée

Pour les factures belges, le système génère automatiquement une communication structurée au format +++XXX/XXXX/XXXXX+++ pour le suivi des paiements.

### Factures d'achat

1. Naviguez vers **Achats > Factures**
2. Cliquez sur **Nouvelle facture** ou **Importer UBL**
3. Pour l'import UBL :
   - Téléversez le fichier XML reçu via Peppol
   - Le système crée automatiquement la facture et le fournisseur si nécessaire

### Notes de crédit

Pour créer une note de crédit :
1. Ouvrez la facture concernée
2. Cliquez sur **Créer une note de crédit**
3. Ajustez les montants si nécessaire
4. Validez la note de crédit

---

## Partenaires

### Créer un client

1. Naviguez vers **Partenaires > Clients**
2. Cliquez sur **Nouveau client**
3. Remplissez les informations :
   - Nom de l'entreprise
   - Numéro de TVA (validation automatique VIES)
   - Adresse
   - Coordonnées de contact
   - Conditions de paiement par défaut

### Vérification Peppol

Pour vérifier si un partenaire est inscrit sur le réseau Peppol :
1. Ouvrez la fiche du partenaire
2. Cliquez sur **Vérifier Peppol**
3. Le système interroge le répertoire Peppol et affiche le résultat

### Import de partenaires

Vous pouvez importer vos clients/fournisseurs via un fichier CSV avec les colonnes :
- Nom, Numéro TVA, Email, Téléphone, Adresse, Code postal, Ville, Pays

---

## Peppol (Facturation électronique)

### Qu'est-ce que Peppol ?

Peppol (Pan-European Public Procurement OnLine) est le réseau européen pour l'échange de documents commerciaux électroniques. À partir de 2026, les entreprises belges assujetties à la TVA devront envoyer leurs factures B2B via ce réseau.

### Configuration Peppol

1. Naviguez vers **Paramètres > Peppol**
2. Configurez :
   - **Mode test** : Activez pour tester sans envoyer de vraies factures
   - **Clé API** : Fournie par votre Access Point Peppol
   - **Identifiant Peppol** : Généralement basé sur votre numéro d'entreprise (0208:XXXXXXXXXX)

### Envoi d'une facture via Peppol

1. Créez et validez votre facture
2. Vérifiez que le client a un identifiant Peppol configuré
3. Cliquez sur **Envoyer via Peppol**
4. Le système :
   - Génère le document UBL
   - L'envoie via le réseau Peppol
   - Suit le statut de livraison

### Réception de factures Peppol

Les factures reçues via Peppol sont automatiquement :
- Importées dans le système
- Converties en factures d'achat
- Liées au fournisseur (créé automatiquement si nouveau)

---

## E-Reporting 2028

### Modèle 5 coins

À partir de 2028, la Belgique introduit le modèle "5 coins" où chaque facture B2B est :
1. Envoyée via Peppol au client (coins 1-4)
2. Déclarée au SPF Finances (coin 5) pour un contrôle fiscal en temps réel

### Configuration E-Reporting

1. Naviguez vers **E-Reporting > Paramètres**
2. Configurez :
   - **Activer l'e-Reporting** : Active la soumission automatique
   - **Mode test** : Utilisez le sandbox pour les tests
   - **Clé API SPF** : Sera fournie lors de l'inscription au programme

### Fonctionnement

Lorsqu'une facture est envoyée via Peppol, le système :
1. Envoie la facture au client via Peppol
2. Soumet automatiquement les données fiscales au SPF Finances
3. Suit le statut d'acceptation

### Tableau de bord E-Reporting

Le tableau de bord affiche :
- Nombre de soumissions
- Taux d'acceptation
- Factures en attente de soumission
- Erreurs à corriger

---

## TVA

### Déclaration TVA

1. Naviguez vers **TVA > Déclarations**
2. Cliquez sur **Nouvelle déclaration**
3. Sélectionnez la période (mensuelle ou trimestrielle)
4. Le système calcule automatiquement :
   - Grille 01-09 : Opérations à la sortie
   - Grille 81-83 : Opérations à l'entrée
   - Grille 54-55 : TVA due/déductible
   - Grille 71-72 : Solde

### Listing clients TVA

Pour générer le listing annuel des clients assujettis :
1. Naviguez vers **TVA > Listing clients**
2. Sélectionnez l'année
3. Générez le fichier XML conforme au format SPF Finances

### Relevé intracommunautaire

Pour les opérations intracommunautaires :
1. Naviguez vers **TVA > Relevé intracom**
2. Sélectionnez la période
3. Vérifiez les transactions
4. Exportez au format requis

---

## Comptabilité

### Plan comptable (PCMN)

Le système utilise le Plan Comptable Minimum Normalisé belge. Vous pouvez personnaliser les comptes :
1. Naviguez vers **Comptabilité > Plan comptable**
2. Ajoutez ou modifiez des comptes selon vos besoins

### Écritures comptables

Les écritures sont générées automatiquement lors de la validation des factures. Pour les écritures manuelles :
1. Naviguez vers **Comptabilité > Écritures**
2. Cliquez sur **Nouvelle écriture**
3. Saisissez le débit et le crédit (doivent être équilibrés)

### Balance des comptes

Consultez la balance en temps réel :
1. Naviguez vers **Comptabilité > Balance**
2. Filtrez par période si nécessaire
3. Exportez au format Excel ou PDF

---

## Open Banking

### Connexion bancaire (PSD2)

Pour connecter vos comptes bancaires :
1. Naviguez vers **Banque > Connexions**
2. Cliquez sur **Connecter une banque**
3. Sélectionnez votre banque
4. Suivez le processus d'authentification sécurisé
5. Autorisez l'accès aux comptes

### Synchronisation des transactions

Les transactions sont synchronisées automatiquement. Pour une synchronisation manuelle :
1. Cliquez sur **Synchroniser** sur le compte concerné

### Catégorisation automatique

Le système apprend de vos catégorisations précédentes pour suggérer automatiquement :
- Le compte comptable
- Le partenaire associé
- La nature de la dépense

### Rapprochement bancaire

1. Naviguez vers **Banque > Rapprochement**
2. Le système propose automatiquement des correspondances entre les transactions et les factures
3. Confirmez ou ajustez les correspondances
4. Validez le rapprochement

---

## Rapports

### Types de rapports disponibles

- **Bilan** : Situation patrimoniale à une date donnée
- **Compte de résultats** : Revenus et charges sur une période
- **Flux de trésorerie** : Mouvements de liquidités
- **Âge des créances** : Analyse des factures impayées
- **Analyse TVA** : Détail des opérations TVA

### Générer un rapport

1. Naviguez vers **Rapports**
2. Sélectionnez le type de rapport
3. Définissez les paramètres (période, filtres)
4. Cliquez sur **Générer**
5. Exportez en PDF, Excel, ou CSV

### Tableau de bord analytique

Le module analytique offre :
- Graphiques de revenus
- Répartition des dépenses
- Comparaisons périodiques
- Tendances et prévisions

---

## Paramètres

### Paramètres de l'entreprise

- Informations légales (nom, TVA, adresse)
- Logo pour les factures
- Coordonnées bancaires par défaut
- Mentions légales sur les factures

### Paramètres de facturation

- Numérotation des factures
- Conditions de paiement par défaut
- Mentions légales
- Communication structurée automatique

### Gestion des utilisateurs

Pour inviter un nouvel utilisateur :
1. Naviguez vers **Paramètres > Utilisateurs**
2. Cliquez sur **Inviter**
3. Entrez l'email et le rôle (membre, comptable, admin)
4. L'utilisateur reçoit un email avec un lien d'invitation

### Rôles disponibles

- **Propriétaire** : Accès complet, gestion des abonnements
- **Admin** : Accès complet sauf gestion des abonnements
- **Comptable** : Accès aux fonctions comptables
- **Membre** : Accès limité (lecture seule ou opérations courantes)

---

## Support

Pour toute question ou assistance :
- Consultez cette documentation
- Contactez le support technique via l'interface de l'application
- Consultez la FAQ dans les paramètres

---

*Documentation mise à jour : Décembre 2025*
*Version de l'application : 1.0.0*
