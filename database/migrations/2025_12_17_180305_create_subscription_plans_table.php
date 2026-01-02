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
        // Subscription Plans (defined by superadmin)
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Gratuit, Starter, Pro, Enterprise
            $table->string('slug')->unique(); // free, starter, pro, enterprise
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0); // Prix mensuel
            $table->decimal('price_yearly', 10, 2)->default(0); // Prix annuel (réduction)
            $table->integer('trial_days')->default(14); // Jours d'essai gratuit
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Plan mis en avant
            $table->integer('sort_order')->default(0);

            // Limites du plan
            $table->integer('max_users')->default(1); // -1 = illimité
            $table->integer('max_invoices_per_month')->default(10); // -1 = illimité
            $table->integer('max_clients')->default(20); // -1 = illimité
            $table->integer('max_products')->default(50); // -1 = illimité
            $table->bigInteger('max_storage_mb')->default(100); // Stockage en MB

            // Fonctionnalités
            $table->boolean('feature_peppol')->default(false);
            $table->boolean('feature_recurring_invoices')->default(false);
            $table->boolean('feature_credit_notes')->default(true);
            $table->boolean('feature_quotes')->default(false);
            $table->boolean('feature_multi_currency')->default(false);
            $table->boolean('feature_api_access')->default(false);
            $table->boolean('feature_custom_branding')->default(false);
            $table->boolean('feature_advanced_reports')->default(false);
            $table->boolean('feature_priority_support')->default(false);

            $table->timestamps();
        });

        // Company Subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('subscription_plans')->cascadeOnDelete();

            $table->enum('status', [
                'trialing',      // En période d'essai
                'active',        // Abonnement actif et payé
                'past_due',      // Paiement en retard
                'cancelled',     // Annulé par le client
                'suspended',     // Suspendu par admin
                'expired',       // Expiré
            ])->default('trialing');

            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('amount', 10, 2)->default(0); // Montant actuel

            $table->date('trial_ends_at')->nullable(); // Fin de l'essai
            $table->date('current_period_start')->nullable(); // Début période actuelle
            $table->date('current_period_end')->nullable(); // Fin période actuelle
            $table->date('cancelled_at')->nullable(); // Date d'annulation
            $table->date('suspended_at')->nullable(); // Date de suspension

            $table->string('cancellation_reason')->nullable();
            $table->text('admin_notes')->nullable(); // Notes du superadmin

            $table->timestamps();

            $table->index(['company_id', 'status']);
        });

        // Payment/Invoice History (factures d'abonnement)
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('EUR');

            $table->enum('status', ['draft', 'pending', 'paid', 'failed', 'refunded'])->default('pending');

            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('paid_at')->nullable();

            $table->string('payment_method')->nullable(); // bank_transfer, card, etc.
            $table->string('payment_reference')->nullable(); // Référence de paiement
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
        });

        // Usage tracking per month (suivi utilisation)
        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7); // Format: 2025-01

            $table->integer('invoices_created')->default(0);
            $table->integer('clients_count')->default(0);
            $table->integer('products_count')->default(0);
            $table->integer('users_count')->default(0);
            $table->bigInteger('storage_used_mb')->default(0);

            $table->timestamps();

            $table->unique(['company_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_usage');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
