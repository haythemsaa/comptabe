<?php

namespace Database\Seeders;

use App\Models\VatCode;
use Illuminate\Database\Seeder;

class VatCodeSeeder extends Seeder
{
    public function run(): void
    {
        $vatCodes = [
            // Standard rates
            [
                'code' => 'S21',
                'rate' => 21.00,
                'description' => 'TVA standard 21%',
                'type' => 'standard',
                'is_active' => true,
            ],
            [
                'code' => 'S12',
                'rate' => 12.00,
                'description' => 'TVA réduit 12%',
                'type' => 'reduced',
                'is_active' => true,
            ],
            [
                'code' => 'S6',
                'rate' => 6.00,
                'description' => 'TVA réduit 6%',
                'type' => 'reduced',
                'is_active' => true,
            ],
            [
                'code' => 'S0',
                'rate' => 0.00,
                'description' => 'TVA 0% (exonéré)',
                'type' => 'exempt',
                'is_active' => true,
            ],
            // Intracommunautaire
            [
                'code' => 'IC0',
                'rate' => 0.00,
                'description' => 'Livraison intracommunautaire',
                'type' => 'intra_community',
                'is_active' => true,
            ],
            [
                'code' => 'ICA21',
                'rate' => 21.00,
                'description' => 'Acquisition intracommunautaire 21%',
                'type' => 'intra_community_acquisition',
                'is_active' => true,
            ],
            [
                'code' => 'ICA12',
                'rate' => 12.00,
                'description' => 'Acquisition intracommunautaire 12%',
                'type' => 'intra_community_acquisition',
                'is_active' => true,
            ],
            [
                'code' => 'ICA6',
                'rate' => 6.00,
                'description' => 'Acquisition intracommunautaire 6%',
                'type' => 'intra_community_acquisition',
                'is_active' => true,
            ],
            // Cocontractant
            [
                'code' => 'CC',
                'rate' => 0.00,
                'description' => 'Cocontractant (autoliquidation)',
                'type' => 'reverse_charge',
                'is_active' => true,
            ],
            // Export
            [
                'code' => 'EXP',
                'rate' => 0.00,
                'description' => 'Exportation hors UE',
                'type' => 'export',
                'is_active' => true,
            ],
        ];

        foreach ($vatCodes as $code) {
            VatCode::updateOrCreate(
                ['code' => $code['code']],
                $code
            );
        }
    }
}
