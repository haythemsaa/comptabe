<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class FranceChartOfAccountSeeder extends Seeder
{
    public function run(?Company $company = null): void
    {
        // PCG - Plan Comptable Général Français
        $accounts = [
            // Classe 1 - Comptes de capitaux
            ['code' => '10', 'name' => 'Capital et réserves', 'type' => 'equity', 'is_group' => true],
            ['code' => '101', 'name' => 'Capital social', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '1013', 'name' => 'Capital souscrit appelé non versé', 'type' => 'equity', 'parent_code' => '101'],
            ['code' => '103', 'name' => 'Capital souscrit non appelé', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '104', 'name' => 'Primes liées au capital social', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '105', 'name' => 'Écarts de réévaluation', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '106', 'name' => 'Réserves', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '1061', 'name' => 'Réserve légale', 'type' => 'equity', 'parent_code' => '106'],
            ['code' => '1063', 'name' => 'Réserves statutaires', 'type' => 'equity', 'parent_code' => '106'],
            ['code' => '1068', 'name' => 'Autres réserves', 'type' => 'equity', 'parent_code' => '106'],
            ['code' => '11', 'name' => 'Report à nouveau', 'type' => 'equity', 'is_group' => true],
            ['code' => '110', 'name' => 'Report à nouveau (solde créditeur)', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '119', 'name' => 'Report à nouveau (solde débiteur)', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '12', 'name' => 'Résultat de l\'exercice', 'type' => 'equity', 'is_group' => true],
            ['code' => '120', 'name' => 'Résultat de l\'exercice (bénéfice)', 'type' => 'equity', 'parent_code' => '12'],
            ['code' => '129', 'name' => 'Résultat de l\'exercice (perte)', 'type' => 'equity', 'parent_code' => '12'],
            ['code' => '13', 'name' => 'Subventions d\'investissement', 'type' => 'equity', 'is_group' => true],
            ['code' => '14', 'name' => 'Provisions réglementées', 'type' => 'equity', 'is_group' => true],
            ['code' => '15', 'name' => 'Provisions', 'type' => 'liability', 'is_group' => true],
            ['code' => '151', 'name' => 'Provisions pour risques', 'type' => 'liability', 'parent_code' => '15'],
            ['code' => '153', 'name' => 'Provisions pour pensions et obligations similaires', 'type' => 'liability', 'parent_code' => '15'],
            ['code' => '16', 'name' => 'Emprunts et dettes assimilées', 'type' => 'liability', 'is_group' => true],
            ['code' => '161', 'name' => 'Emprunts obligataires convertibles', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '163', 'name' => 'Emprunts obligataires', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '164', 'name' => 'Emprunts auprès des établissements de crédit', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '165', 'name' => 'Dépôts et cautionnements reçus', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '17', 'name' => 'Dettes rattachées à des participations', 'type' => 'liability', 'is_group' => true],
            ['code' => '18', 'name' => 'Comptes de liaison', 'type' => 'equity', 'is_group' => true],

            // Classe 2 - Comptes d'immobilisations
            ['code' => '20', 'name' => 'Immobilisations incorporelles', 'type' => 'asset', 'is_group' => true],
            ['code' => '201', 'name' => 'Frais d\'établissement', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '203', 'name' => 'Frais de recherche et développement', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '205', 'name' => 'Concessions et droits similaires, brevets', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '206', 'name' => 'Droit au bail', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '207', 'name' => 'Fonds commercial', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '208', 'name' => 'Autres immobilisations incorporelles', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '21', 'name' => 'Immobilisations corporelles', 'type' => 'asset', 'is_group' => true],
            ['code' => '211', 'name' => 'Terrains', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '212', 'name' => 'Agencements et aménagements de terrains', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '213', 'name' => 'Constructions', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '2135', 'name' => 'Installations générales', 'type' => 'asset', 'parent_code' => '213'],
            ['code' => '215', 'name' => 'Installations techniques, matériel et outillage industriels', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '218', 'name' => 'Autres immobilisations corporelles', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '2181', 'name' => 'Installations générales, agencements, aménagements divers', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2182', 'name' => 'Matériel de transport', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2183', 'name' => 'Matériel de bureau et informatique', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2184', 'name' => 'Mobilier', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '23', 'name' => 'Immobilisations en cours', 'type' => 'asset', 'is_group' => true],
            ['code' => '26', 'name' => 'Participations et créances rattachées', 'type' => 'asset', 'is_group' => true],
            ['code' => '261', 'name' => 'Titres de participation', 'type' => 'asset', 'parent_code' => '26'],
            ['code' => '266', 'name' => 'Autres formes de participation', 'type' => 'asset', 'parent_code' => '26'],
            ['code' => '267', 'name' => 'Créances rattachées à des participations', 'type' => 'asset', 'parent_code' => '26'],
            ['code' => '27', 'name' => 'Autres immobilisations financières', 'type' => 'asset', 'is_group' => true],
            ['code' => '271', 'name' => 'Titres immobilisés', 'type' => 'asset', 'parent_code' => '27'],
            ['code' => '275', 'name' => 'Dépôts et cautionnements versés', 'type' => 'asset', 'parent_code' => '27'],
            ['code' => '28', 'name' => 'Amortissements des immobilisations', 'type' => 'asset', 'is_group' => true],
            ['code' => '280', 'name' => 'Amortissements des immobilisations incorporelles', 'type' => 'asset', 'parent_code' => '28'],
            ['code' => '281', 'name' => 'Amortissements des immobilisations corporelles', 'type' => 'asset', 'parent_code' => '28'],
            ['code' => '29', 'name' => 'Dépréciations des immobilisations', 'type' => 'asset', 'is_group' => true],

            // Classe 3 - Comptes de stocks
            ['code' => '31', 'name' => 'Matières premières', 'type' => 'asset', 'is_group' => true],
            ['code' => '32', 'name' => 'Autres approvisionnements', 'type' => 'asset', 'is_group' => true],
            ['code' => '33', 'name' => 'En-cours de production de biens', 'type' => 'asset', 'is_group' => true],
            ['code' => '34', 'name' => 'En-cours de production de services', 'type' => 'asset', 'is_group' => true],
            ['code' => '35', 'name' => 'Stocks de produits', 'type' => 'asset', 'is_group' => true],
            ['code' => '37', 'name' => 'Stocks de marchandises', 'type' => 'asset', 'is_group' => true],
            ['code' => '39', 'name' => 'Dépréciations des stocks', 'type' => 'asset', 'is_group' => true],

            // Classe 4 - Comptes de tiers
            ['code' => '40', 'name' => 'Fournisseurs et comptes rattachés', 'type' => 'liability', 'is_group' => true],
            ['code' => '401', 'name' => 'Fournisseurs', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '403', 'name' => 'Fournisseurs - Effets à payer', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '404', 'name' => 'Fournisseurs d\'immobilisations', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '408', 'name' => 'Fournisseurs - Factures non parvenues', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '409', 'name' => 'Fournisseurs débiteurs', 'type' => 'asset', 'parent_code' => '40'],
            ['code' => '41', 'name' => 'Clients et comptes rattachés', 'type' => 'asset', 'is_group' => true],
            ['code' => '411', 'name' => 'Clients', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '413', 'name' => 'Clients - Effets à recevoir', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '416', 'name' => 'Clients douteux ou litigieux', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '418', 'name' => 'Clients - Produits non encore facturés', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '419', 'name' => 'Clients créditeurs', 'type' => 'liability', 'parent_code' => '41'],
            ['code' => '42', 'name' => 'Personnel et comptes rattachés', 'type' => 'liability', 'is_group' => true],
            ['code' => '421', 'name' => 'Personnel - Rémunérations dues', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '422', 'name' => 'Comités d\'entreprise, d\'établissement', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '424', 'name' => 'Participation des salariés aux résultats', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '425', 'name' => 'Personnel - Avances et acomptes', 'type' => 'asset', 'parent_code' => '42'],
            ['code' => '428', 'name' => 'Personnel - Charges à payer', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '43', 'name' => 'Sécurité sociale et autres organismes sociaux', 'type' => 'liability', 'is_group' => true],
            ['code' => '431', 'name' => 'URSSAF', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '437', 'name' => 'Autres organismes sociaux', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '438', 'name' => 'Organismes sociaux - Charges à payer', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '44', 'name' => 'État et autres collectivités publiques', 'type' => 'liability', 'is_group' => true],
            ['code' => '441', 'name' => 'État - Subventions à recevoir', 'type' => 'asset', 'parent_code' => '44'],
            ['code' => '442', 'name' => 'État - Impôts et taxes recouvrables sur tiers', 'type' => 'asset', 'parent_code' => '44'],
            ['code' => '443', 'name' => 'Opérations particulières avec l\'État', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '444', 'name' => 'État - Impôt sur les bénéfices', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '445', 'name' => 'État - TVA', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '4452', 'name' => 'TVA due intracommunautaire', 'type' => 'liability', 'parent_code' => '445'],
            ['code' => '4455', 'name' => 'TVA à décaisser', 'type' => 'liability', 'parent_code' => '445'],
            ['code' => '4456', 'name' => 'TVA déductible', 'type' => 'asset', 'parent_code' => '445'],
            ['code' => '44562', 'name' => 'TVA sur immobilisations', 'type' => 'asset', 'parent_code' => '4456'],
            ['code' => '44566', 'name' => 'TVA sur autres biens et services', 'type' => 'asset', 'parent_code' => '4456'],
            ['code' => '4457', 'name' => 'TVA collectée', 'type' => 'liability', 'parent_code' => '445'],
            ['code' => '44571', 'name' => 'TVA collectée', 'type' => 'liability', 'parent_code' => '4457'],
            ['code' => '447', 'name' => 'État - Autres impôts, taxes et versements assimilés', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '45', 'name' => 'Groupe et associés', 'type' => 'liability', 'is_group' => true],
            ['code' => '455', 'name' => 'Associés - Comptes courants', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '456', 'name' => 'Associés - Opérations sur le capital', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '46', 'name' => 'Débiteurs divers et créditeurs divers', 'type' => 'liability', 'is_group' => true],
            ['code' => '467', 'name' => 'Autres comptes débiteurs ou créditeurs', 'type' => 'liability', 'parent_code' => '46'],
            ['code' => '47', 'name' => 'Comptes transitoires ou d\'attente', 'type' => 'liability', 'is_group' => true],
            ['code' => '471', 'name' => 'Comptes d\'attente', 'type' => 'liability', 'parent_code' => '47'],
            ['code' => '48', 'name' => 'Comptes de régularisation', 'type' => 'asset', 'is_group' => true],
            ['code' => '481', 'name' => 'Charges à répartir sur plusieurs exercices', 'type' => 'asset', 'parent_code' => '48'],
            ['code' => '486', 'name' => 'Charges constatées d\'avance', 'type' => 'asset', 'parent_code' => '48'],
            ['code' => '487', 'name' => 'Produits constatés d\'avance', 'type' => 'liability', 'parent_code' => '48'],
            ['code' => '49', 'name' => 'Dépréciations des comptes de tiers', 'type' => 'asset', 'is_group' => true],
            ['code' => '491', 'name' => 'Dépréciations des comptes de clients', 'type' => 'asset', 'parent_code' => '49'],

            // Classe 5 - Comptes financiers
            ['code' => '50', 'name' => 'Valeurs mobilières de placement', 'type' => 'asset', 'is_group' => true],
            ['code' => '51', 'name' => 'Banques, établissements financiers', 'type' => 'asset', 'is_group' => true],
            ['code' => '511', 'name' => 'Valeurs à l\'encaissement', 'type' => 'asset', 'parent_code' => '51'],
            ['code' => '512', 'name' => 'Banques', 'type' => 'asset', 'parent_code' => '51'],
            ['code' => '53', 'name' => 'Caisse', 'type' => 'asset', 'is_group' => true],
            ['code' => '531', 'name' => 'Caisse siège social', 'type' => 'asset', 'parent_code' => '53'],
            ['code' => '54', 'name' => 'Régies d\'avances et accréditifs', 'type' => 'asset', 'is_group' => true],
            ['code' => '58', 'name' => 'Virements internes', 'type' => 'asset', 'is_group' => true],
            ['code' => '59', 'name' => 'Dépréciations des comptes financiers', 'type' => 'asset', 'is_group' => true],

            // Classe 6 - Comptes de charges
            ['code' => '60', 'name' => 'Achats', 'type' => 'expense', 'is_group' => true],
            ['code' => '601', 'name' => 'Achats stockés - Matières premières', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '602', 'name' => 'Achats stockés - Autres approvisionnements', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '604', 'name' => 'Achats d\'études et prestations de services', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '606', 'name' => 'Achats non stockés de matières et fournitures', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '607', 'name' => 'Achats de marchandises', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '609', 'name' => 'Rabais, remises et ristournes obtenus', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '61', 'name' => 'Services extérieurs', 'type' => 'expense', 'is_group' => true],
            ['code' => '611', 'name' => 'Sous-traitance générale', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '613', 'name' => 'Locations', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '614', 'name' => 'Charges locatives et de copropriété', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '615', 'name' => 'Entretien et réparations', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '616', 'name' => 'Primes d\'assurances', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '617', 'name' => 'Études et recherches', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '618', 'name' => 'Divers', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '62', 'name' => 'Autres services extérieurs', 'type' => 'expense', 'is_group' => true],
            ['code' => '621', 'name' => 'Personnel extérieur à l\'entreprise', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '622', 'name' => 'Rémunérations d\'intermédiaires et honoraires', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '623', 'name' => 'Publicité, publications, relations publiques', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '624', 'name' => 'Transports de biens et transports collectifs', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '625', 'name' => 'Déplacements, missions et réceptions', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '626', 'name' => 'Frais postaux et de télécommunications', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '627', 'name' => 'Services bancaires et assimilés', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '628', 'name' => 'Divers', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '63', 'name' => 'Impôts, taxes et versements assimilés', 'type' => 'expense', 'is_group' => true],
            ['code' => '631', 'name' => 'Impôts, taxes et versements assimilés sur rémunérations', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '633', 'name' => 'Impôts, taxes et versements sur rémunérations', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '635', 'name' => 'Autres impôts, taxes et versements assimilés', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '64', 'name' => 'Charges de personnel', 'type' => 'expense', 'is_group' => true],
            ['code' => '641', 'name' => 'Rémunérations du personnel', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '6411', 'name' => 'Salaires bruts', 'type' => 'expense', 'parent_code' => '641'],
            ['code' => '6412', 'name' => 'Congés payés', 'type' => 'expense', 'parent_code' => '641'],
            ['code' => '6413', 'name' => 'Primes et gratifications', 'type' => 'expense', 'parent_code' => '641'],
            ['code' => '6414', 'name' => 'Indemnités et avantages divers', 'type' => 'expense', 'parent_code' => '641'],
            ['code' => '645', 'name' => 'Charges de sécurité sociale et de prévoyance', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '6451', 'name' => 'Cotisations URSSAF', 'type' => 'expense', 'parent_code' => '645'],
            ['code' => '6452', 'name' => 'Cotisations mutuelles', 'type' => 'expense', 'parent_code' => '645'],
            ['code' => '6453', 'name' => 'Cotisations aux caisses de retraite', 'type' => 'expense', 'parent_code' => '645'],
            ['code' => '647', 'name' => 'Autres charges sociales', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '648', 'name' => 'Autres charges de personnel', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '65', 'name' => 'Autres charges de gestion courante', 'type' => 'expense', 'is_group' => true],
            ['code' => '66', 'name' => 'Charges financières', 'type' => 'expense', 'is_group' => true],
            ['code' => '661', 'name' => 'Charges d\'intérêts', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '665', 'name' => 'Escomptes accordés', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '666', 'name' => 'Pertes de change', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '667', 'name' => 'Charges nettes sur cessions de valeurs mobilières', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '668', 'name' => 'Autres charges financières', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '67', 'name' => 'Charges exceptionnelles', 'type' => 'expense', 'is_group' => true],
            ['code' => '68', 'name' => 'Dotations aux amortissements, dépréciations et provisions', 'type' => 'expense', 'is_group' => true],
            ['code' => '681', 'name' => 'Dotations aux amortissements', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '686', 'name' => 'Dotations aux dépréciations', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '687', 'name' => 'Dotations aux provisions', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '69', 'name' => 'Participation des salariés - Impôts sur les bénéfices', 'type' => 'expense', 'is_group' => true],
            ['code' => '695', 'name' => 'Impôts sur les bénéfices', 'type' => 'expense', 'parent_code' => '69'],

            // Classe 7 - Comptes de produits
            ['code' => '70', 'name' => 'Ventes de produits fabriqués, prestations de services', 'type' => 'revenue', 'is_group' => true],
            ['code' => '701', 'name' => 'Ventes de produits finis', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '704', 'name' => 'Travaux', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '706', 'name' => 'Prestations de services', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '707', 'name' => 'Ventes de marchandises', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '708', 'name' => 'Produits des activités annexes', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '709', 'name' => 'Rabais, remises et ristournes accordés', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '71', 'name' => 'Production stockée', 'type' => 'revenue', 'is_group' => true],
            ['code' => '72', 'name' => 'Production immobilisée', 'type' => 'revenue', 'is_group' => true],
            ['code' => '74', 'name' => 'Subventions d\'exploitation', 'type' => 'revenue', 'is_group' => true],
            ['code' => '75', 'name' => 'Autres produits de gestion courante', 'type' => 'revenue', 'is_group' => true],
            ['code' => '76', 'name' => 'Produits financiers', 'type' => 'revenue', 'is_group' => true],
            ['code' => '761', 'name' => 'Produits de participations', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '762', 'name' => 'Produits des autres immobilisations financières', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '763', 'name' => 'Revenus des autres créances', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '764', 'name' => 'Revenus des valeurs mobilières de placement', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '765', 'name' => 'Escomptes obtenus', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '766', 'name' => 'Gains de change', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '768', 'name' => 'Autres produits financiers', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '77', 'name' => 'Produits exceptionnels', 'type' => 'revenue', 'is_group' => true],
            ['code' => '78', 'name' => 'Reprises sur amortissements, dépréciations et provisions', 'type' => 'revenue', 'is_group' => true],
            ['code' => '781', 'name' => 'Reprises sur amortissements', 'type' => 'revenue', 'parent_code' => '78'],
            ['code' => '786', 'name' => 'Reprises sur dépréciations', 'type' => 'revenue', 'parent_code' => '78'],
            ['code' => '787', 'name' => 'Reprises sur provisions', 'type' => 'revenue', 'parent_code' => '78'],
            ['code' => '79', 'name' => 'Transferts de charges', 'type' => 'revenue', 'is_group' => true],
        ];

        foreach ($accounts as $account) {
            $parentCode = $account['parent_code'] ?? null;
            $accountCode = $account['code'];
            unset($account['parent_code']);
            unset($account['code']);

            $parent = null;
            if ($parentCode) {
                $parent = ChartOfAccount::where('account_number', $parentCode)
                    ->when($company, fn($q) => $q->where('company_id', $company->id))
                    ->first();
            }

            $isGroup = $account['is_group'] ?? false;
            unset($account['is_group']);

            ChartOfAccount::updateOrCreate(
                [
                    'account_number' => $accountCode,
                    'company_id' => $company?->id,
                ],
                array_merge($account, [
                    'parent_id' => $parent?->id,
                    'allow_direct_posting' => !$isGroup,
                    'is_active' => true,
                ])
            );
        }
    }
}
