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
        Schema::create('tax_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('fiscal_year_id')->nullable();

            // Type d'impôt
            $table->enum('tax_type', [
                'isoc',              // Impôt des sociétés
                'ipp',               // Impôt des personnes physiques
                'professional_tax',  // Précompte professionnel
                'vat',               // TVA (pour compléter les déclarations)
                'withholding_tax',   // Précompte mobilier
                'registration_tax',  // Droits d'enregistrement
                'property_tax',      // Précompte immobilier
                'vehicle_tax',       // Taxe de circulation
                'other'
            ]);

            // Période fiscale
            $table->string('period_label')->nullable(); // Ex: "Q1 2025", "Année 2024"
            $table->integer('year');
            $table->integer('quarter')->nullable(); // 1, 2, 3, 4
            $table->integer('month')->nullable(); // 1-12

            // Montants
            $table->decimal('taxable_base', 15, 2)->default(0); // Base imposable
            $table->decimal('tax_rate', 5, 2)->nullable(); // Taux d'imposition (%)
            $table->decimal('tax_amount', 15, 2); // Montant de l'impôt
            $table->decimal('advance_payments', 15, 2)->default(0); // Versements anticipés
            $table->decimal('amount_due', 15, 2); // Montant à payer (ou remboursement si négatif)
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('penalties', 15, 2)->default(0); // Pénalités de retard
            $table->decimal('interests', 15, 2)->default(0); // Intérêts de retard

            // Dates
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('declaration_date')->nullable(); // Date de déclaration

            // Statut
            $table->enum('status', [
                'draft',
                'calculated',
                'declared',
                'pending_payment',
                'partially_paid',
                'paid',
                'overdue',
                'contested'
            ])->default('draft');

            // Références
            $table->string('reference_number')->nullable(); // Numéro de référence fiscale
            $table->string('structured_communication')->nullable(); // Communication structurée pour paiement
            $table->uuid('payment_transaction_id')->nullable(); // Lien vers transaction bancaire
            $table->uuid('journal_entry_id')->nullable(); // Écriture comptable

            // Documents
            $table->string('declaration_file_path')->nullable(); // Fichier de déclaration (PDF/XML)
            $table->string('payment_proof_path')->nullable(); // Preuve de paiement

            // Métadonnées
            $table->text('notes')->nullable();
            $table->json('calculation_details')->nullable(); // Détails du calcul
            $table->json('metadata')->nullable();

            // Audit
            $table->uuid('created_by')->nullable();
            $table->uuid('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->onDelete('set null');
            $table->foreign('payment_transaction_id')->references('id')->on('bank_transactions')->onDelete('set null');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'tax_type', 'year']);
            $table->index(['company_id', 'status']);
            $table->index(['due_date']);
            $table->index(['payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_payments');
    }
};
