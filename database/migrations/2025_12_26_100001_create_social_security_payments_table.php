<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_security_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('fiscal_year_id')->nullable();

            // Type de cotisation sociale
            $table->enum('contribution_type', [
                'onss_employer',      // Cotisations patronales ONSS
                'onss_employee',      // Cotisations ouvrières ONSS (retenues)
                'dmfa',               // Déclaration multifonctionnelle (global)
                'special_contribution', // Cotisation spéciale de sécurité sociale
                'pension_fund',       // Fonds de pension complémentaire
                'occupational_accident', // Assurance accidents du travail
                'occupational_disease',  // Assurance maladies professionnelles
                'other'
            ]);

            // Période
            $table->integer('year');
            $table->integer('quarter'); // 1, 2, 3, 4
            $table->integer('month')->nullable(); // Pour cotisations mensuelles
            $table->string('period_label'); // Ex: "T1 2025", "Janvier 2025"

            // Masse salariale et calculs
            $table->decimal('gross_salary_base', 15, 2)->default(0); // Masse salariale brute
            $table->integer('employee_count')->default(0); // Nombre d'employés
            $table->decimal('employer_rate', 5, 2)->nullable(); // Taux patronal (%)
            $table->decimal('employee_rate', 5, 2)->nullable(); // Taux ouvrier (%)

            // Montants
            $table->decimal('employer_contribution', 15, 2)->default(0); // Cotisations patronales
            $table->decimal('employee_contribution', 15, 2)->default(0); // Cotisations ouvrières
            $table->decimal('total_contribution', 15, 2); // Total à payer
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('penalties', 15, 2)->default(0);
            $table->decimal('interests', 15, 2)->default(0);

            // Dates
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->date('declaration_date')->nullable(); // Date DMFA

            // Statut
            $table->enum('status', [
                'draft',
                'calculated',
                'declared',      // DMFA déclarée
                'pending_payment',
                'partially_paid',
                'paid',
                'overdue',
                'contested'
            ])->default('draft');

            // Références
            $table->string('onss_reference')->nullable(); // Référence ONSS
            $table->string('dmfa_number')->nullable(); // Numéro déclaration DMFA
            $table->string('structured_communication')->nullable();
            $table->uuid('payment_transaction_id')->nullable();
            $table->uuid('journal_entry_id')->nullable();

            // Documents
            $table->string('dmfa_file_path')->nullable(); // Fichier DMFA XML
            $table->string('payment_proof_path')->nullable();
            $table->string('certificate_path')->nullable(); // Certificat de paiement

            // Détails par employé (pour traçabilité)
            $table->json('employee_breakdown')->nullable(); // [{employee_id, gross, employer, employee}, ...]

            // Métadonnées
            $table->text('notes')->nullable();
            $table->json('calculation_details')->nullable();
            $table->json('metadata')->nullable();

            // Audit
            $table->uuid('created_by')->nullable();
            $table->uuid('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->onDelete('set null');
            $table->foreign('payment_transaction_id')->references('id')->on('bank_transactions')->onDelete('set null');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');

            // Indexes (with custom names to avoid length issues)
            $table->index(['company_id', 'contribution_type', 'year', 'quarter'], 'ss_payments_company_type_period_idx');
            $table->index(['company_id', 'status'], 'ss_payments_company_status_idx');
            $table->index(['due_date'], 'ss_payments_due_date_idx');
            $table->index(['payment_date'], 'ss_payments_payment_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_security_payments');
    }
};
