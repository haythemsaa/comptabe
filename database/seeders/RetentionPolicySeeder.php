<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder pour les politiques de rétention légale belges
 *
 * Conforme à:
 * - AR TVA art. 60 (factures: 10 ans)
 * - Code des Sociétés art. 3:17 (documents comptables: 7 ans)
 * - Code Social (fiches de paie: conservation permanente)
 * - RGPD (anonymisation après expiration)
 */
class RetentionPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $policies = [
            // ========== DOCUMENTS FISCAUX ==========
            [
                'document_type' => 'invoice',
                'retention_years' => 10,
                'legal_basis' => 'AR TVA art. 60',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'expense',
                'retention_years' => 10,
                'legal_basis' => 'AR TVA art. 60',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'vat_declaration',
                'retention_years' => 7,
                'legal_basis' => 'AR TVA',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'tax_declaration',
                'retention_years' => 7,
                'legal_basis' => 'CIR 1992',
                'permanent' => false,
                'anonymize_after' => true,
            ],

            // ========== DOCUMENTS COMPTABLES ==========
            [
                'document_type' => 'journal_entry',
                'retention_years' => 7,
                'legal_basis' => 'C. soc. art. 3:17',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'annual_accounts',
                'retention_years' => 10,
                'legal_basis' => 'C. soc. art. 3:17',
                'permanent' => false,
                'anonymize_after' => false, // Données publiques (BNB)
            ],
            [
                'document_type' => 'bank_statement',
                'retention_years' => 7,
                'legal_basis' => 'C. soc. art. 3:17',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'inventory',
                'retention_years' => 7,
                'legal_basis' => 'C. soc. art. 3:17',
                'permanent' => false,
                'anonymize_after' => true,
            ],

            // ========== RH - CONSERVATION PERMANENTE ==========
            [
                'document_type' => 'payslip',
                'retention_years' => 999,
                'legal_basis' => 'Code social',
                'permanent' => true,
                'anonymize_after' => false, // Conservation illimitée obligatoire
            ],
            [
                'document_type' => 'employee_account',
                'retention_years' => 999,
                'legal_basis' => 'Loi pensions',
                'permanent' => true,
                'anonymize_after' => false, // Droits sociaux
            ],
            [
                'document_type' => 'employment_contract',
                'retention_years' => 5,
                'legal_basis' => 'Loi sur les contrats',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'dimona_declaration',
                'retention_years' => 5,
                'legal_basis' => 'ONSS',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'dmfa_declaration',
                'retention_years' => 7,
                'legal_basis' => 'ONSS',
                'permanent' => false,
                'anonymize_after' => true,
            ],

            // ========== CONTRATS ==========
            [
                'document_type' => 'contract',
                'retention_years' => 10,
                'legal_basis' => 'Code civil',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'lease_agreement',
                'retention_years' => 10,
                'legal_basis' => 'Code civil',
                'permanent' => false,
                'anonymize_after' => true,
            ],

            // ========== DOCUMENTS SOCIAUX - CONSERVATION PERMANENTE ==========
            [
                'document_type' => 'assembly_minutes',
                'retention_years' => 999,
                'legal_basis' => 'C. soc.',
                'permanent' => true,
                'anonymize_after' => false, // Registres sociaux obligatoires
            ],
            [
                'document_type' => 'company_statutes',
                'retention_years' => 999,
                'legal_basis' => 'C. soc.',
                'permanent' => true,
                'anonymize_after' => false,
            ],

            // ========== AUTRES DOCUMENTS ==========
            [
                'document_type' => 'quote',
                'retention_years' => 7,
                'legal_basis' => 'C. soc. art. 3:17',
                'permanent' => false,
                'anonymize_after' => true,
            ],
            [
                'document_type' => 'credit_note',
                'retention_years' => 10,
                'legal_basis' => 'AR TVA art. 60',
                'permanent' => false,
                'anonymize_after' => true,
            ],
        ];

        foreach ($policies as $policy) {
            DB::table('retention_policies')->insert(array_merge($policy, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ ' . count($policies) . ' politiques de rétention légale insérées');
    }
}
