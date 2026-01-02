<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fiscal Years
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50); // "2026", "2025-2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closing', 'closed'])->default('open');
            $table->timestamps();

            $table->unique(['company_id', 'start_date']);
            $table->index(['company_id', 'status']);
        });

        // Chart of Accounts (PCMN Belge)
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('account_number', 10);
            $table->string('name', 255);
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->uuid('parent_id')->nullable();
            $table->string('vat_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->boolean('allow_direct_posting')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'account_number']);
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->index(['company_id', 'type']);
        });

        // VAT Codes
        Schema::create('vat_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->decimal('rate', 5, 2);
            $table->char('category', 2); // S, Z, E, AE, K, G, O
            $table->string('grid_base', 10)->nullable(); // Grille déclaration TVA (00, 01, 02, 03...)
            $table->string('grid_vat', 10)->nullable(); // Grille TVA (54, 59, 64...)
            $table->uuid('account_vat_due_id')->nullable();
            $table->uuid('account_vat_deductible_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->foreign('account_vat_due_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreign('account_vat_deductible_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });

        // Journals
        Schema::create('journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->enum('type', ['purchases', 'sales', 'bank', 'cash', 'misc', 'opening', 'closing']);
            $table->uuid('default_account_id')->nullable();
            $table->uuid('bank_account_id')->nullable();
            $table->integer('next_number')->default(1);
            $table->string('number_prefix', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->foreign('default_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });

        // Journal Entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->string('entry_number', 50);
            $table->date('entry_date');
            $table->date('accounting_date')->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('source_type', 50)->nullable(); // invoice, bank_statement, manual
            $table->uuid('source_id')->nullable();
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'journal_id', 'entry_number']);
            $table->index(['company_id', 'entry_date']);
            $table->index(['company_id', 'status']);
        });

        // Journal Entry Lines
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->integer('line_number');
            $table->foreignUuid('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->uuid('partner_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('vat_code', 10)->nullable();
            $table->decimal('vat_amount', 15, 2)->nullable();
            $table->decimal('vat_base', 15, 2)->nullable();
            $table->uuid('analytic_account_id')->nullable();
            $table->uuid('reconciliation_id')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id', 'line_number']);
            $table->index('account_id');
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('vat_codes');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('fiscal_years');
    }
};
