<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Devises supportées
        if (!Schema::hasTable('currencies')) {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // EUR, USD, GBP
            $table->string('name');
            $table->string('symbol', 10);
            $table->integer('decimal_places')->default(2);
            $table->string('decimal_separator', 1)->default(',');
            $table->string('thousands_separator', 1)->default(' ');
            $table->boolean('symbol_before')->default(false); // € après ou $ avant
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        }

        // Taux de change
        if (!Schema::hasTable('exchange_rates')) {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('EUR');
            $table->string('target_currency', 3);
            $table->date('rate_date');
            $table->decimal('rate', 18, 8); // Taux avec précision
            $table->string('source')->default('ecb'); // ecb, manual, api
            $table->timestamps();

            $table->unique(['base_currency', 'target_currency', 'rate_date']);
            $table->index(['target_currency', 'rate_date']);
        });
        }

        // Configuration devise par entreprise
        if (!Schema::hasTable('company_currencies')) {
        Schema::create('company_currencies', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('currency_code', 3);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('fixed_rate', 18, 8)->nullable(); // Taux fixe optionnel
            $table->enum('rate_type', ['live', 'daily', 'fixed'])->default('daily');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->unique(['company_id', 'currency_code']);
        });
        }

        // Ajout colonnes multi-devises aux factures existantes (sans after pour éviter les erreurs)
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'currency')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('currency', 3)->default('EUR');
                $table->decimal('exchange_rate', 18, 8)->default(1);
                $table->decimal('total_amount_base', 12, 2)->nullable();
            });
        }

        // Ajout colonnes multi-devises aux devis
        if (Schema::hasTable('quotes') && !Schema::hasColumn('quotes', 'currency')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->string('currency', 3)->default('EUR');
                $table->decimal('exchange_rate', 18, 8)->default(1);
            });
        }

        // Ajout colonnes multi-devises aux avoirs
        if (Schema::hasTable('credit_notes') && !Schema::hasColumn('credit_notes', 'currency')) {
            Schema::table('credit_notes', function (Blueprint $table) {
                $table->string('currency', 3)->default('EUR');
                $table->decimal('exchange_rate', 18, 8)->default(1);
            });
        }

        // Ajout devise préférée au partenaire
        if (Schema::hasTable('partners') && !Schema::hasColumn('partners', 'preferred_currency')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->string('preferred_currency', 3)->nullable();
            });
        }

        // Écarts de change
        if (!Schema::hasTable('exchange_rate_differences')) {
        Schema::create('exchange_rate_differences', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->index(['documentable_type', 'documentable_id'], 'ex_rate_diff_doc_index');
            $table->date('transaction_date');
            $table->date('settlement_date');
            $table->string('currency', 3);
            $table->decimal('original_rate', 18, 8);
            $table->decimal('settlement_rate', 18, 8);
            $table->decimal('original_amount', 12, 2);
            $table->decimal('difference_amount', 12, 2); // Gain ou perte
            $table->enum('type', ['realized', 'unrealized']);
            $table->boolean('is_gain')->default(false);
            $table->foreignId('accounting_entry_id')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['company_id', 'transaction_date']);
        });
        }

        // Historique des conversions
        if (!Schema::hasTable('currency_conversions')) {
        Schema::create('currency_conversions', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('convertible_type');
            $table->unsignedBigInteger('convertible_id');
            $table->index(['convertible_type', 'convertible_id'], 'currency_conv_doc_index');
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->decimal('from_amount', 12, 2);
            $table->decimal('to_amount', 12, 2);
            $table->decimal('exchange_rate', 18, 8);
            $table->date('conversion_date');
            $table->string('rate_source')->default('ecb');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['company_id', 'conversion_date']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_conversions');
        Schema::dropIfExists('exchange_rate_differences');

        if (Schema::hasColumn('partners', 'preferred_currency')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->dropColumn('preferred_currency');
            });
        }

        if (Schema::hasColumn('credit_notes', 'currency')) {
            Schema::table('credit_notes', function (Blueprint $table) {
                $table->dropColumn(['currency', 'exchange_rate']);
            });
        }

        if (Schema::hasColumn('quotes', 'currency')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->dropColumn(['currency', 'exchange_rate']);
            });
        }

        if (Schema::hasColumn('invoices', 'currency')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn(['currency', 'exchange_rate', 'total_amount_base']);
            });
        }

        Schema::dropIfExists('company_currencies');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
};
