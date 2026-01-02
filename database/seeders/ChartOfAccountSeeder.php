<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        // PCMN Belge - Plan Comptable Minimum Normalisé
        $accounts = [
            // Classe 1 - Fonds propres, provisions et dettes à plus d'un an
            ['code' => '10', 'name' => 'Capital', 'type' => 'equity', 'is_group' => true],
            ['code' => '100', 'name' => 'Capital souscrit', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '101', 'name' => 'Capital non appelé', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '11', 'name' => 'Primes d\'émission', 'type' => 'equity', 'is_group' => true],
            ['code' => '12', 'name' => 'Plus-values de réévaluation', 'type' => 'equity', 'is_group' => true],
            ['code' => '13', 'name' => 'Réserves', 'type' => 'equity', 'is_group' => true],
            ['code' => '130', 'name' => 'Réserve légale', 'type' => 'equity', 'parent_code' => '13'],
            ['code' => '131', 'name' => 'Réserves indisponibles', 'type' => 'equity', 'parent_code' => '13'],
            ['code' => '133', 'name' => 'Réserves disponibles', 'type' => 'equity', 'parent_code' => '13'],
            ['code' => '14', 'name' => 'Bénéfice/Perte reporté(e)', 'type' => 'equity', 'is_group' => true],
            ['code' => '140', 'name' => 'Bénéfice reporté', 'type' => 'equity', 'parent_code' => '14'],
            ['code' => '141', 'name' => 'Perte reportée', 'type' => 'equity', 'parent_code' => '14'],
            ['code' => '15', 'name' => 'Subsides en capital', 'type' => 'equity', 'is_group' => true],
            ['code' => '16', 'name' => 'Provisions', 'type' => 'liability', 'is_group' => true],
            ['code' => '160', 'name' => 'Provisions pour pensions', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '161', 'name' => 'Provisions pour impôts', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '163', 'name' => 'Provisions pour grosses réparations', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '17', 'name' => 'Dettes à plus d\'un an', 'type' => 'liability', 'is_group' => true],
            ['code' => '170', 'name' => 'Emprunts subordonnés', 'type' => 'liability', 'parent_code' => '17'],
            ['code' => '173', 'name' => 'Établissements de crédit', 'type' => 'liability', 'parent_code' => '17'],
            ['code' => '174', 'name' => 'Autres emprunts', 'type' => 'liability', 'parent_code' => '17'],
            ['code' => '175', 'name' => 'Dettes commerciales', 'type' => 'liability', 'parent_code' => '17'],

            // Classe 2 - Frais d'établissement, actifs immobilisés et créances à plus d'un an
            ['code' => '20', 'name' => 'Frais d\'établissement', 'type' => 'asset', 'is_group' => true],
            ['code' => '21', 'name' => 'Immobilisations incorporelles', 'type' => 'asset', 'is_group' => true],
            ['code' => '210', 'name' => 'Frais de R&D', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '211', 'name' => 'Concessions, brevets, licences', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '212', 'name' => 'Goodwill', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '22', 'name' => 'Terrains et constructions', 'type' => 'asset', 'is_group' => true],
            ['code' => '220', 'name' => 'Terrains', 'type' => 'asset', 'parent_code' => '22'],
            ['code' => '221', 'name' => 'Constructions', 'type' => 'asset', 'parent_code' => '22'],
            ['code' => '23', 'name' => 'Installations, machines et outillage', 'type' => 'asset', 'is_group' => true],
            ['code' => '230', 'name' => 'Installations', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '231', 'name' => 'Machines', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '232', 'name' => 'Outillage', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '24', 'name' => 'Mobilier et matériel roulant', 'type' => 'asset', 'is_group' => true],
            ['code' => '240', 'name' => 'Mobilier', 'type' => 'asset', 'parent_code' => '24'],
            ['code' => '241', 'name' => 'Matériel de bureau', 'type' => 'asset', 'parent_code' => '24'],
            ['code' => '242', 'name' => 'Matériel informatique', 'type' => 'asset', 'parent_code' => '24'],
            ['code' => '243', 'name' => 'Matériel roulant', 'type' => 'asset', 'parent_code' => '24'],
            ['code' => '25', 'name' => 'Immobilisations détenues en location-financement', 'type' => 'asset', 'is_group' => true],
            ['code' => '26', 'name' => 'Autres immobilisations corporelles', 'type' => 'asset', 'is_group' => true],
            ['code' => '27', 'name' => 'Immobilisations en cours', 'type' => 'asset', 'is_group' => true],
            ['code' => '28', 'name' => 'Immobilisations financières', 'type' => 'asset', 'is_group' => true],
            ['code' => '280', 'name' => 'Participations', 'type' => 'asset', 'parent_code' => '28'],
            ['code' => '281', 'name' => 'Créances', 'type' => 'asset', 'parent_code' => '28'],
            ['code' => '29', 'name' => 'Créances à plus d\'un an', 'type' => 'asset', 'is_group' => true],

            // Classe 3 - Stocks et commandes en cours
            ['code' => '30', 'name' => 'Approvisionnements - Matières premières', 'type' => 'asset', 'is_group' => true],
            ['code' => '31', 'name' => 'Approvisionnements - Fournitures', 'type' => 'asset', 'is_group' => true],
            ['code' => '32', 'name' => 'En-cours de fabrication', 'type' => 'asset', 'is_group' => true],
            ['code' => '33', 'name' => 'Produits finis', 'type' => 'asset', 'is_group' => true],
            ['code' => '34', 'name' => 'Marchandises', 'type' => 'asset', 'is_group' => true],
            ['code' => '35', 'name' => 'Immeubles destinés à la vente', 'type' => 'asset', 'is_group' => true],
            ['code' => '36', 'name' => 'Acomptes versés', 'type' => 'asset', 'is_group' => true],
            ['code' => '37', 'name' => 'Commandes en cours', 'type' => 'asset', 'is_group' => true],

            // Classe 4 - Créances et dettes à un an au plus
            ['code' => '40', 'name' => 'Créances commerciales', 'type' => 'asset', 'is_group' => true],
            ['code' => '400', 'name' => 'Clients', 'type' => 'asset', 'parent_code' => '40'],
            ['code' => '401', 'name' => 'Effets à recevoir', 'type' => 'asset', 'parent_code' => '40'],
            ['code' => '404', 'name' => 'Clients douteux', 'type' => 'asset', 'parent_code' => '40'],
            ['code' => '407', 'name' => 'Créances sur sociétés liées', 'type' => 'asset', 'parent_code' => '40'],
            ['code' => '409', 'name' => 'Réductions de valeur actées', 'type' => 'asset', 'parent_code' => '40'],
            ['code' => '41', 'name' => 'Autres créances', 'type' => 'asset', 'is_group' => true],
            ['code' => '411', 'name' => 'TVA à récupérer', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '412', 'name' => 'Impôts à récupérer', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '416', 'name' => 'Créances diverses', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '42', 'name' => 'Dettes à plus d\'un an échéant dans l\'année', 'type' => 'liability', 'is_group' => true],
            ['code' => '43', 'name' => 'Dettes financières', 'type' => 'liability', 'is_group' => true],
            ['code' => '430', 'name' => 'Établissements de crédit - emprunts', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '433', 'name' => 'Établissements de crédit - découverts', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '44', 'name' => 'Dettes commerciales', 'type' => 'liability', 'is_group' => true],
            ['code' => '440', 'name' => 'Fournisseurs', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '441', 'name' => 'Effets à payer', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '444', 'name' => 'Factures à recevoir', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '45', 'name' => 'Dettes fiscales, salariales et sociales', 'type' => 'liability', 'is_group' => true],
            ['code' => '450', 'name' => 'Impôts estimés', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '451', 'name' => 'TVA à payer', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '452', 'name' => 'Impôts à payer', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '453', 'name' => 'Précomptes retenus', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '454', 'name' => 'ONSS', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '455', 'name' => 'Rémunérations', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '456', 'name' => 'Pécules de vacances', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '46', 'name' => 'Acomptes reçus sur commandes', 'type' => 'liability', 'is_group' => true],
            ['code' => '48', 'name' => 'Dettes diverses', 'type' => 'liability', 'is_group' => true],
            ['code' => '489', 'name' => 'Autres dettes diverses', 'type' => 'liability', 'parent_code' => '48'],
            ['code' => '49', 'name' => 'Comptes de régularisation', 'type' => 'liability', 'is_group' => true],
            ['code' => '490', 'name' => 'Charges à reporter', 'type' => 'asset', 'parent_code' => '49'],
            ['code' => '491', 'name' => 'Produits acquis', 'type' => 'asset', 'parent_code' => '49'],
            ['code' => '492', 'name' => 'Charges à imputer', 'type' => 'liability', 'parent_code' => '49'],
            ['code' => '493', 'name' => 'Produits à reporter', 'type' => 'liability', 'parent_code' => '49'],

            // Classe 5 - Placements de trésorerie et valeurs disponibles
            ['code' => '50', 'name' => 'Actions propres', 'type' => 'asset', 'is_group' => true],
            ['code' => '51', 'name' => 'Actions et parts', 'type' => 'asset', 'is_group' => true],
            ['code' => '52', 'name' => 'Titres à revenu fixe', 'type' => 'asset', 'is_group' => true],
            ['code' => '53', 'name' => 'Dépôts à terme', 'type' => 'asset', 'is_group' => true],
            ['code' => '55', 'name' => 'Établissements de crédit', 'type' => 'asset', 'is_group' => true],
            ['code' => '550', 'name' => 'Comptes courants', 'type' => 'asset', 'parent_code' => '55'],
            ['code' => '551', 'name' => 'Chèques émis', 'type' => 'asset', 'parent_code' => '55'],
            ['code' => '56', 'name' => 'Office des chèques postaux', 'type' => 'asset', 'is_group' => true],
            ['code' => '57', 'name' => 'Caisses', 'type' => 'asset', 'is_group' => true],
            ['code' => '570', 'name' => 'Caisse espèces', 'type' => 'asset', 'parent_code' => '57'],
            ['code' => '58', 'name' => 'Virements internes', 'type' => 'asset', 'is_group' => true],

            // Classe 6 - Charges
            ['code' => '60', 'name' => 'Approvisionnements et marchandises', 'type' => 'expense', 'is_group' => true],
            ['code' => '600', 'name' => 'Achats de matières premières', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '601', 'name' => 'Achats de fournitures', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '604', 'name' => 'Achats de marchandises', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '608', 'name' => 'Remises, ristournes et rabais obtenus', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '609', 'name' => 'Variation de stock', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '61', 'name' => 'Services et biens divers', 'type' => 'expense', 'is_group' => true],
            ['code' => '610', 'name' => 'Loyers et charges locatives', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '611', 'name' => 'Entretien et réparations', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '612', 'name' => 'Fournitures', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '613', 'name' => 'Rétributions de tiers', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '614', 'name' => 'Commissions', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '615', 'name' => 'Sous-traitants', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '616', 'name' => 'Frais de transport', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '617', 'name' => 'Personnel intérimaire', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '618', 'name' => 'Rémunérations administrateurs', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '62', 'name' => 'Rémunérations', 'type' => 'expense', 'is_group' => true],
            ['code' => '620', 'name' => 'Rémunérations employés', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '621', 'name' => 'Rémunérations ouvriers', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '622', 'name' => 'Pécules de vacances', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '623', 'name' => 'Primes patronales assurances', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '624', 'name' => 'ONSS patronale', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '625', 'name' => 'Autres frais de personnel', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '63', 'name' => 'Amortissements et réductions de valeur', 'type' => 'expense', 'is_group' => true],
            ['code' => '630', 'name' => 'Dotation amortissements incorporelles', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '631', 'name' => 'Dotation amortissements corporelles', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '634', 'name' => 'Réductions de valeur stocks', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '64', 'name' => 'Autres charges d\'exploitation', 'type' => 'expense', 'is_group' => true],
            ['code' => '640', 'name' => 'Taxes d\'exploitation', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '641', 'name' => 'Moins-values réalisations courantes', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '65', 'name' => 'Charges financières', 'type' => 'expense', 'is_group' => true],
            ['code' => '650', 'name' => 'Charges de dettes', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '651', 'name' => 'Réductions de valeur financières', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '652', 'name' => 'Moins-values cession actifs courants', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '653', 'name' => 'Frais bancaires', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '654', 'name' => 'Écarts de change', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '66', 'name' => 'Charges exceptionnelles', 'type' => 'expense', 'is_group' => true],
            ['code' => '660', 'name' => 'Amortissements exceptionnels', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '661', 'name' => 'Réductions de valeur exceptionnelles', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '662', 'name' => 'Provisions exceptionnelles', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '663', 'name' => 'Moins-values cession immobilisations', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '67', 'name' => 'Impôts sur le résultat', 'type' => 'expense', 'is_group' => true],
            ['code' => '670', 'name' => 'Impôts belges', 'type' => 'expense', 'parent_code' => '67'],
            ['code' => '671', 'name' => 'Impôts étrangers', 'type' => 'expense', 'parent_code' => '67'],
            ['code' => '672', 'name' => 'Impôts différés', 'type' => 'expense', 'parent_code' => '67'],
            ['code' => '69', 'name' => 'Affectation des résultats', 'type' => 'expense', 'is_group' => true],

            // Classe 7 - Produits
            ['code' => '70', 'name' => 'Chiffre d\'affaires', 'type' => 'income', 'is_group' => true],
            ['code' => '700', 'name' => 'Ventes de marchandises', 'type' => 'income', 'parent_code' => '70'],
            ['code' => '701', 'name' => 'Ventes de produits finis', 'type' => 'income', 'parent_code' => '70'],
            ['code' => '702', 'name' => 'Prestations de services', 'type' => 'income', 'parent_code' => '70'],
            ['code' => '708', 'name' => 'Remises, ristournes et rabais accordés', 'type' => 'income', 'parent_code' => '70'],
            ['code' => '71', 'name' => 'Variation des stocks et commandes', 'type' => 'income', 'is_group' => true],
            ['code' => '72', 'name' => 'Production immobilisée', 'type' => 'income', 'is_group' => true],
            ['code' => '74', 'name' => 'Autres produits d\'exploitation', 'type' => 'income', 'is_group' => true],
            ['code' => '740', 'name' => 'Subsides d\'exploitation', 'type' => 'income', 'parent_code' => '74'],
            ['code' => '741', 'name' => 'Plus-values réalisations courantes', 'type' => 'income', 'parent_code' => '74'],
            ['code' => '742', 'name' => 'Récupération de frais', 'type' => 'income', 'parent_code' => '74'],
            ['code' => '743', 'name' => 'Ristournes et rabais', 'type' => 'income', 'parent_code' => '74'],
            ['code' => '744', 'name' => 'Reprises de provisions', 'type' => 'income', 'parent_code' => '74'],
            ['code' => '75', 'name' => 'Produits financiers', 'type' => 'income', 'is_group' => true],
            ['code' => '750', 'name' => 'Revenus des immobilisations financières', 'type' => 'income', 'parent_code' => '75'],
            ['code' => '751', 'name' => 'Revenus des actifs circulants', 'type' => 'income', 'parent_code' => '75'],
            ['code' => '752', 'name' => 'Plus-values cession actifs courants', 'type' => 'income', 'parent_code' => '75'],
            ['code' => '753', 'name' => 'Subsides en capital et intérêts', 'type' => 'income', 'parent_code' => '75'],
            ['code' => '754', 'name' => 'Écarts de conversion', 'type' => 'income', 'parent_code' => '75'],
            ['code' => '76', 'name' => 'Produits exceptionnels', 'type' => 'income', 'is_group' => true],
            ['code' => '760', 'name' => 'Reprises d\'amortissements', 'type' => 'income', 'parent_code' => '76'],
            ['code' => '761', 'name' => 'Reprises de réductions de valeur', 'type' => 'income', 'parent_code' => '76'],
            ['code' => '762', 'name' => 'Reprises de provisions', 'type' => 'income', 'parent_code' => '76'],
            ['code' => '763', 'name' => 'Plus-values cession immobilisations', 'type' => 'income', 'parent_code' => '76'],
            ['code' => '77', 'name' => 'Régularisations d\'impôts', 'type' => 'income', 'is_group' => true],
            ['code' => '79', 'name' => 'Affectation des résultats', 'type' => 'income', 'is_group' => true],
        ];

        foreach ($accounts as $account) {
            $parentCode = $account['parent_code'] ?? null;
            unset($account['parent_code']);

            $parent = null;
            if ($parentCode) {
                $parent = ChartOfAccount::where('code', $parentCode)->first();
            }

            ChartOfAccount::updateOrCreate(
                ['code' => $account['code']],
                array_merge($account, [
                    'parent_id' => $parent?->id,
                    'is_group' => $account['is_group'] ?? false,
                ])
            );
        }
    }
}
