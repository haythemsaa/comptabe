<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catégories d'immobilisations
        if (!Schema::hasTable('asset_categories')) {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->enum('depreciation_method', ['linear', 'degressive', 'units_of_production'])->default('linear');
            $table->decimal('default_useful_life', 5, 2)->default(5); // En années
            $table->decimal('degressive_rate', 5, 2)->nullable(); // Coefficient dégressif belge
            $table->string('accounting_asset_account', 10)->nullable(); // Compte d'actif (ex: 2100)
            $table->string('accounting_depreciation_account', 10)->nullable(); // Compte d'amortissement (ex: 2109)
            $table->string('accounting_expense_account', 10)->nullable(); // Compte de charge (ex: 6302)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index('company_id');
        });
        }

        // Immobilisations
        if (!Schema::hasTable('assets')) {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->uuid('partner_id')->nullable(); // Fournisseur
            $table->uuid('invoice_id')->nullable(); // Facture d'achat liée
            $table->string('reference')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->date('acquisition_date');
            $table->date('service_date'); // Date de mise en service
            $table->date('disposal_date')->nullable();
            $table->decimal('acquisition_cost', 12, 2); // Prix d'achat HT
            $table->decimal('residual_value', 12, 2)->default(0); // Valeur résiduelle
            $table->decimal('current_value', 12, 2); // Valeur nette comptable actuelle
            $table->decimal('accumulated_depreciation', 12, 2)->default(0);
            $table->enum('depreciation_method', ['linear', 'degressive', 'units_of_production'])->default('linear');
            $table->decimal('useful_life', 5, 2); // Durée d'utilisation en années
            $table->decimal('degressive_rate', 5, 2)->nullable();
            $table->integer('total_units')->nullable(); // Pour méthode unités de production
            $table->integer('units_produced')->nullable();
            $table->enum('status', ['draft', 'active', 'fully_depreciated', 'disposed', 'sold'])->default('draft');
            $table->decimal('disposal_amount', 12, 2)->nullable();
            $table->text('disposal_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->index(['company_id', 'status']);
            $table->index('acquisition_date');
        });
        }

        // Lignes d'amortissement
        if (!Schema::hasTable('asset_depreciations')) {
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('year_number'); // Année d'amortissement (1, 2, 3...)
            $table->decimal('depreciation_amount', 12, 2);
            $table->decimal('accumulated_depreciation', 12, 2);
            $table->decimal('book_value', 12, 2); // Valeur nette comptable après
            $table->enum('status', ['planned', 'posted', 'cancelled'])->default('planned');
            $table->foreignId('accounting_entry_id')->nullable(); // Lien vers l'écriture comptable
            $table->date('posted_at')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'status']);
            $table->index('period_end');
        });
        }

        // Journal des événements d'immobilisations
        if (!Schema::hasTable('asset_logs')) {
        Schema::create('asset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->enum('event', [
                'created', 'activated', 'depreciation_posted', 'revalued',
                'impaired', 'disposed', 'sold', 'transferred', 'modified'
            ]);
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->uuid('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['asset_id', 'created_at']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_logs');
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
