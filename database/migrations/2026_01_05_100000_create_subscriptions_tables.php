<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Templates de facturation récurrente (modèles réutilisables)
        if (!Schema::hasTable('recurring_invoice_templates')) {
            Schema::create('recurring_invoice_templates', function (Blueprint $table) {
                $table->id();
                $table->uuid('company_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->index('company_id');
            });
        }

        // Factures récurrentes - ajouter colonnes manquantes si la table existe déjà
        if (Schema::hasTable('recurring_invoices')) {
            // Ajouter les colonnes manquantes à la table existante (sans after pour éviter les erreurs)
            Schema::table('recurring_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('recurring_invoices', 'template_id')) {
                    $table->foreignId('template_id')->nullable();
                }
                if (!Schema::hasColumn('recurring_invoices', 'billing_interval')) {
                    $table->string('billing_interval')->default('monthly');
                }
                if (!Schema::hasColumn('recurring_invoices', 'billing_interval_count')) {
                    $table->integer('billing_interval_count')->default(1);
                }
                if (!Schema::hasColumn('recurring_invoices', 'billing_day')) {
                    $table->integer('billing_day')->nullable();
                }
                if (!Schema::hasColumn('recurring_invoices', 'auto_renew')) {
                    $table->boolean('auto_renew')->default(true);
                }
                if (!Schema::hasColumn('recurring_invoices', 'max_invoices')) {
                    $table->integer('max_invoices')->nullable();
                }
                if (!Schema::hasColumn('recurring_invoices', 'internal_notes')) {
                    $table->text('internal_notes')->nullable();
                }
                if (!Schema::hasColumn('recurring_invoices', 'cancellation_reason')) {
                    $table->text('cancellation_reason')->nullable();
                }
                if (!Schema::hasColumn('recurring_invoices', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable();
                }
                if (!Schema::hasColumn('recurring_invoices', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
            });
        }

        // Lignes de la facture récurrente
        if (!Schema::hasTable('recurring_invoice_items')) {
            Schema::create('recurring_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->uuid('recurring_invoice_id');
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('description');
                $table->decimal('quantity', 10, 2)->default(1);
                $table->decimal('unit_price', 12, 2);
                $table->decimal('vat_rate', 5, 2)->default(21.00);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('recurring_invoice_id')->references('id')->on('recurring_invoices')->cascadeOnDelete();
            });
        }

        // Historique des factures générées
        if (!Schema::hasTable('recurring_invoice_history')) {
            Schema::create('recurring_invoice_history', function (Blueprint $table) {
                $table->id();
                $table->uuid('recurring_invoice_id');
                $table->uuid('invoice_id');
                $table->date('billing_period_start');
                $table->date('billing_period_end');
                $table->decimal('amount', 12, 2);
                $table->enum('status', ['generated', 'sent', 'paid', 'overdue', 'cancelled'])->default('generated');
                $table->timestamps();

                $table->foreign('recurring_invoice_id')->references('id')->on('recurring_invoices')->cascadeOnDelete();
                $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
                $table->index('recurring_invoice_id');
            });
        }

        // Journal des événements
        if (!Schema::hasTable('recurring_invoice_logs')) {
            Schema::create('recurring_invoice_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('recurring_invoice_id');
                $table->enum('event', [
                    'created', 'activated', 'paused', 'resumed', 'cancelled',
                    'renewed', 'modified', 'invoice_generated', 'invoice_sent',
                    'email_failed', 'completed'
                ]);
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->uuid('user_id')->nullable();
                $table->timestamps();

                $table->foreign('recurring_invoice_id')->references('id')->on('recurring_invoices')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->index(['recurring_invoice_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_logs');
        Schema::dropIfExists('recurring_invoice_history');
        Schema::dropIfExists('recurring_invoice_items');
        Schema::dropIfExists('recurring_invoices');
        Schema::dropIfExists('recurring_invoice_templates');
    }
};
