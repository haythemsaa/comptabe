<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class TunisiaChartOfAccountSeeder extends Seeder
{
    public function run(?Company $company = null): void
    {
        // PCN Tunisien - Plan Comptable National
        $accounts = [
            // Classe 1 - Capitaux
            ['code' => '10', 'name' => 'Capital social', 'type' => 'equity', 'is_group' => true],
            ['code' => '101', 'name' => 'Capital social', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '103', 'name' => 'Capital non appelé', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '11', 'name' => 'Réserves', 'type' => 'equity', 'is_group' => true],
            ['code' => '111', 'name' => 'Réserve légale', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '112', 'name' => 'Réserves statutaires', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '113', 'name' => 'Réserves facultatives', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '12', 'name' => 'Report à nouveau', 'type' => 'equity', 'is_group' => true],
            ['code' => '120', 'name' => 'Report à nouveau (solde créditeur)', 'type' => 'equity', 'parent_code' => '12'],
            ['code' => '129', 'name' => 'Report à nouveau (solde débiteur)', 'type' => 'equity', 'parent_code' => '12'],
            ['code' => '13', 'name' => 'Résultat de l\'exercice', 'type' => 'equity', 'is_group' => true],
            ['code' => '130', 'name' => 'Résultat de l\'exercice (bénéfice)', 'type' => 'equity', 'parent_code' => '13'],
            ['code' => '139', 'name' => 'Résultat de l\'exercice (perte)', 'type' => 'equity', 'parent_code' => '13'],
            ['code' => '14', 'name' => 'Subventions d\'investissement', 'type' => 'equity', 'is_group' => true],
            ['code' => '15', 'name' => 'Provisions réglementées', 'type' => 'equity', 'is_group' => true],
            ['code' => '16', 'name' => 'Emprunts et dettes assimilées', 'type' => 'liability', 'is_group' => true],
            ['code' => '161', 'name' => 'Emprunts obligataires', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '162', 'name' => 'Emprunts auprès des établissements de crédit', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '163', 'name' => 'Dettes rattachées à des participations', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '17', 'name' => 'Dettes financières diverses', 'type' => 'liability', 'is_group' => true],
            ['code' => '18', 'name' => 'Comptes de liaison', 'type' => 'equity', 'is_group' => true],
            ['code' => '19', 'name' => 'Provisions pour risques et charges', 'type' => 'liability', 'is_group' => true],
            ['code' => '191', 'name' => 'Provisions pour litiges', 'type' => 'liability', 'parent_code' => '19'],
            ['code' => '192', 'name' => 'Provisions pour garanties', 'type' => 'liability', 'parent_code' => '19'],
            ['code' => '193', 'name' => 'Provisions pour pertes sur marchés', 'type' => 'liability', 'parent_code' => '19'],

            // Classe 2 - Immobilisations
            ['code' => '20', 'name' => 'Immobilisations incorporelles', 'type' => 'asset', 'is_group' => true],
            ['code' => '201', 'name' => 'Frais de R&D', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '203', 'name' => 'Logiciels', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '206', 'name' => 'Droit au bail', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '207', 'name' => 'Fonds commercial', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '21', 'name' => 'Immobilisations corporelles', 'type' => 'asset', 'is_group' => true],
            ['code' => '211', 'name' => 'Terrains', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '212', 'name' => 'Agencements et aménagements de terrains', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '213', 'name' => 'Constructions', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '215', 'name' => 'Installations techniques', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '218', 'name' => 'Matériel et outillage', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '2181', 'name' => 'Matériel de bureau', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2182', 'name' => 'Matériel informatique', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2183', 'name' => 'Matériel de transport', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2184', 'name' => 'Mobilier de bureau', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '22', 'name' => 'Immobilisations mises en concession', 'type' => 'asset', 'is_group' => true],
            ['code' => '23', 'name' => 'Immobilisations en cours', 'type' => 'asset', 'is_group' => true],
            ['code' => '24', 'name' => 'Immobilisations financières', 'type' => 'asset', 'is_group' => true],
            ['code' => '241', 'name' => 'Titres de participation', 'type' => 'asset', 'parent_code' => '24'],
            ['code' => '242', 'name' => 'Créances rattachées à des participations', 'type' => 'asset', 'parent_code' => '24'],
            ['code' => '25', 'name' => 'Titres immobilisés', 'type' => 'asset', 'is_group' => true],
            ['code' => '26', 'name' => 'Autres immobilisations financières', 'type' => 'asset', 'is_group' => true],
            ['code' => '27', 'name' => 'Autres actifs non courants', 'type' => 'asset', 'is_group' => true],
            ['code' => '28', 'name' => 'Amortissements', 'type' => 'asset', 'is_group' => true],
            ['code' => '280', 'name' => 'Amortissement des immobilisations incorporelles', 'type' => 'asset', 'parent_code' => '28'],
            ['code' => '281', 'name' => 'Amortissement des immobilisations corporelles', 'type' => 'asset', 'parent_code' => '28'],
            ['code' => '29', 'name' => 'Provisions pour dépréciation', 'type' => 'asset', 'is_group' => true],

            // Classe 3 - Stocks
            ['code' => '31', 'name' => 'Matières premières', 'type' => 'asset', 'is_group' => true],
            ['code' => '32', 'name' => 'Autres approvisionnements', 'type' => 'asset', 'is_group' => true],
            ['code' => '33', 'name' => 'En-cours de production de biens', 'type' => 'asset', 'is_group' => true],
            ['code' => '34', 'name' => 'En-cours de production de services', 'type' => 'asset', 'is_group' => true],
            ['code' => '35', 'name' => 'Produits finis', 'type' => 'asset', 'is_group' => true],
            ['code' => '36', 'name' => 'Produits intermédiaires et résiduels', 'type' => 'asset', 'is_group' => true],
            ['code' => '37', 'name' => 'Marchandises', 'type' => 'asset', 'is_group' => true],
            ['code' => '38', 'name' => 'Stocks en cours de route', 'type' => 'asset', 'is_group' => true],
            ['code' => '39', 'name' => 'Provisions pour dépréciation des stocks', 'type' => 'asset', 'is_group' => true],

            // Classe 4 - Comptes de tiers
            ['code' => '40', 'name' => 'Fournisseurs et comptes rattachés', 'type' => 'liability', 'is_group' => true],
            ['code' => '401', 'name' => 'Fournisseurs de biens et services', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '403', 'name' => 'Fournisseurs - Effets à payer', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '408', 'name' => 'Fournisseurs - Factures non parvenues', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '41', 'name' => 'Clients et comptes rattachés', 'type' => 'asset', 'is_group' => true],
            ['code' => '411', 'name' => 'Clients', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '413', 'name' => 'Clients - Effets à recevoir', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '416', 'name' => 'Clients douteux', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '418', 'name' => 'Clients - Factures à établir', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '42', 'name' => 'Personnel', 'type' => 'liability', 'is_group' => true],
            ['code' => '421', 'name' => 'Personnel - Rémunérations dues', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '422', 'name' => 'Personnel - Œuvres sociales', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '425', 'name' => 'Personnel - Avances et acomptes', 'type' => 'asset', 'parent_code' => '42'],
            ['code' => '43', 'name' => 'Organismes sociaux', 'type' => 'liability', 'is_group' => true],
            ['code' => '431', 'name' => 'CNSS', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '432', 'name' => 'Autres organismes sociaux', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '44', 'name' => 'État et collectivités publiques', 'type' => 'liability', 'is_group' => true],
            ['code' => '441', 'name' => 'État - Subventions à recevoir', 'type' => 'asset', 'parent_code' => '44'],
            ['code' => '442', 'name' => 'État - Impôts et taxes recouvrables', 'type' => 'asset', 'parent_code' => '44'],
            ['code' => '443', 'name' => 'Opérations particulières avec l\'État', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '444', 'name' => 'État - Impôts sur les bénéfices', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '445', 'name' => 'État - TVA à payer', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '4456', 'name' => 'TVA collectée', 'type' => 'liability', 'parent_code' => '445'],
            ['code' => '4457', 'name' => 'TVA déductible', 'type' => 'asset', 'parent_code' => '445'],
            ['code' => '446', 'name' => 'État - Retenues à la source', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '447', 'name' => 'État - Autres impôts et taxes', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '45', 'name' => 'Groupe et associés', 'type' => 'liability', 'is_group' => true],
            ['code' => '451', 'name' => 'Groupe', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '455', 'name' => 'Associés - Comptes courants', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '46', 'name' => 'Débiteurs et créditeurs divers', 'type' => 'liability', 'is_group' => true],
            ['code' => '467', 'name' => 'Autres comptes débiteurs ou créditeurs', 'type' => 'liability', 'parent_code' => '46'],
            ['code' => '47', 'name' => 'Comptes transitoires ou d\'attente', 'type' => 'liability', 'is_group' => true],
            ['code' => '48', 'name' => 'Charges et produits constatés d\'avance', 'type' => 'asset', 'is_group' => true],
            ['code' => '481', 'name' => 'Charges constatées d\'avance', 'type' => 'asset', 'parent_code' => '48'],
            ['code' => '487', 'name' => 'Produits constatés d\'avance', 'type' => 'liability', 'parent_code' => '48'],
            ['code' => '49', 'name' => 'Provisions pour dépréciation des comptes de tiers', 'type' => 'asset', 'is_group' => true],

            // Classe 5 - Comptes financiers
            ['code' => '50', 'name' => 'Valeurs mobilières de placement', 'type' => 'asset', 'is_group' => true],
            ['code' => '51', 'name' => 'Banques', 'type' => 'asset', 'is_group' => true],
            ['code' => '511', 'name' => 'Banques - Comptes courants', 'type' => 'asset', 'parent_code' => '51'],
            ['code' => '512', 'name' => 'Banques - Comptes en devises', 'type' => 'asset', 'parent_code' => '51'],
            ['code' => '52', 'name' => 'Établissements financiers', 'type' => 'asset', 'is_group' => true],
            ['code' => '53', 'name' => 'Caisse', 'type' => 'asset', 'is_group' => true],
            ['code' => '531', 'name' => 'Caisse en dinars', 'type' => 'asset', 'parent_code' => '53'],
            ['code' => '532', 'name' => 'Caisse en devises', 'type' => 'asset', 'parent_code' => '53'],
            ['code' => '54', 'name' => 'Régies d\'avances et accréditifs', 'type' => 'asset', 'is_group' => true],
            ['code' => '58', 'name' => 'Virements internes', 'type' => 'asset', 'is_group' => true],
            ['code' => '59', 'name' => 'Provisions pour dépréciation des comptes financiers', 'type' => 'asset', 'is_group' => true],

            // Classe 6 - Charges (expenses)
            ['code' => '60', 'name' => 'Achats', 'type' => 'expense', 'is_group' => true],
            ['code' => '601', 'name' => 'Achats de matières premières', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '602', 'name' => 'Achats de fournitures consommables', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '604', 'name' => 'Achats de marchandises', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '605', 'name' => 'Achats de travaux, études et prestations', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '607', 'name' => 'Achats non stockés', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '609', 'name' => 'Rabais, remises et ristournes obtenus', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '61', 'name' => 'Services extérieurs', 'type' => 'expense', 'is_group' => true],
            ['code' => '611', 'name' => 'Sous-traitance générale', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '613', 'name' => 'Locations', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '614', 'name' => 'Charges locatives et de copropriété', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '615', 'name' => 'Entretien et réparations', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '616', 'name' => 'Primes d\'assurances', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '617', 'name' => 'Études et recherches', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '618', 'name' => 'Documentation et divers', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '62', 'name' => 'Autres services extérieurs', 'type' => 'expense', 'is_group' => true],
            ['code' => '621', 'name' => 'Personnel extérieur à l\'entreprise', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '622', 'name' => 'Rémunérations d\'intermédiaires et honoraires', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '623', 'name' => 'Publicité, publications', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '624', 'name' => 'Transports de biens et transport collectif du personnel', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '625', 'name' => 'Déplacements, missions et réceptions', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '626', 'name' => 'Frais postaux et de télécommunications', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '627', 'name' => 'Services bancaires', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '628', 'name' => 'Cotisations et divers', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '63', 'name' => 'Autres charges d\'exploitation', 'type' => 'expense', 'is_group' => true],
            ['code' => '631', 'name' => 'Impôts et taxes', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '633', 'name' => 'Charges de personnel', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '6331', 'name' => 'Salaires bruts', 'type' => 'expense', 'parent_code' => '633'],
            ['code' => '6332', 'name' => 'Congés payés', 'type' => 'expense', 'parent_code' => '633'],
            ['code' => '6333', 'name' => 'Primes et gratifications', 'type' => 'expense', 'parent_code' => '633'],
            ['code' => '6334', 'name' => 'Indemnités', 'type' => 'expense', 'parent_code' => '633'],
            ['code' => '635', 'name' => 'Charges sociales', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '6351', 'name' => 'Cotisations CNSS', 'type' => 'expense', 'parent_code' => '635'],
            ['code' => '6352', 'name' => 'Cotisations aux assurances groupe', 'type' => 'expense', 'parent_code' => '635'],
            ['code' => '64', 'name' => 'Charges financières', 'type' => 'expense', 'is_group' => true],
            ['code' => '641', 'name' => 'Charges d\'intérêts', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '645', 'name' => 'Écarts de conversion - Pertes', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '646', 'name' => 'Pertes sur créances liées à des participations', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '647', 'name' => 'Autres charges financières', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '65', 'name' => 'Charges non courantes', 'type' => 'expense', 'is_group' => true],
            ['code' => '68', 'name' => 'Dotations aux amortissements', 'type' => 'expense', 'is_group' => true],
            ['code' => '681', 'name' => 'Dotations aux amortissements - Immobilisations incorporelles', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '682', 'name' => 'Dotations aux amortissements - Immobilisations corporelles', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '69', 'name' => 'Dotations aux provisions', 'type' => 'expense', 'is_group' => true],

            // Classe 7 - Produits
            ['code' => '70', 'name' => 'Ventes de produits fabriqués', 'type' => 'revenue', 'is_group' => true],
            ['code' => '701', 'name' => 'Ventes de produits finis', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '702', 'name' => 'Ventes de produits intermédiaires', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '703', 'name' => 'Ventes de produits résiduels', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '71', 'name' => 'Ventes de marchandises', 'type' => 'revenue', 'is_group' => true],
            ['code' => '72', 'name' => 'Production immobilisée', 'type' => 'revenue', 'is_group' => true],
            ['code' => '73', 'name' => 'Variation des stocks', 'type' => 'revenue', 'is_group' => true],
            ['code' => '74', 'name' => 'Prestations de services', 'type' => 'revenue', 'is_group' => true],
            ['code' => '741', 'name' => 'Services vendus', 'type' => 'revenue', 'parent_code' => '74'],
            ['code' => '75', 'name' => 'Autres produits d\'exploitation', 'type' => 'revenue', 'is_group' => true],
            ['code' => '751', 'name' => 'Redevances pour concessions', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '753', 'name' => 'Jetons de présence', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '754', 'name' => 'Ristournes perçues', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '758', 'name' => 'Produits divers', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '76', 'name' => 'Subventions d\'exploitation', 'type' => 'revenue', 'is_group' => true],
            ['code' => '77', 'name' => 'Reprises sur provisions', 'type' => 'revenue', 'is_group' => true],
            ['code' => '78', 'name' => 'Transferts de charges', 'type' => 'revenue', 'is_group' => true],
            ['code' => '79', 'name' => 'Produits financiers', 'type' => 'revenue', 'is_group' => true],
            ['code' => '791', 'name' => 'Produits des titres de participation', 'type' => 'revenue', 'parent_code' => '79'],
            ['code' => '792', 'name' => 'Gains de change', 'type' => 'revenue', 'parent_code' => '79'],
            ['code' => '793', 'name' => 'Escomptes obtenus', 'type' => 'revenue', 'parent_code' => '79'],
            ['code' => '796', 'name' => 'Intérêts et produits assimilés', 'type' => 'revenue', 'parent_code' => '79'],
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
                    'is_system' => true,
                    'company_id' => $company?->id,
                ])
            );
        }
    }
}
