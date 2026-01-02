<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bank Accounts
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('iban', 34);
            $table->string('bic', 11)->nullable();
            $table->string('bank_name', 255)->nullable();

            $table->uuid('account_id')->nullable(); // Compte comptable lié
            $table->uuid('journal_id')->nullable(); // Journal lié

            // CODA
            $table->boolean('coda_enabled')->default(false);
            $table->string('coda_contract_number', 50)->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'iban']);
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreign('journal_id')->references('id')->on('journals')->nullOnDelete();
        });

        // Bank Statements
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bank_account_id')->constrained()->cascadeOnDelete();

            $table->string('statement_number', 50)->nullable();
            $table->date('statement_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->decimal('opening_balance', 15, 2);
            $table->decimal('closing_balance', 15, 2);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            $table->enum('source', ['coda', 'mt940', 'camt053', 'csv', 'manual'])->default('manual');
            $table->string('original_file_path')->nullable();
            $table->string('original_filename')->nullable();

            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['bank_account_id', 'statement_date']);
        });

        // Bank Transactions
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bank_statement_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('bank_account_id')->constrained()->cascadeOnDelete();

            $table->integer('sequence_number')->nullable();
            $table->date('transaction_date');
            $table->date('value_date')->nullable();

            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('EUR');

            // Counterparty
            $table->string('counterparty_name', 255)->nullable();
            $table->string('counterparty_account', 34)->nullable();
            $table->string('counterparty_bic', 11)->nullable();

            // Communication
            $table->text('communication')->nullable();
            $table->string('structured_communication', 20)->nullable();

            // Bank reference
            $table->string('transaction_code', 20)->nullable();
            $table->string('bank_reference', 100)->nullable();

            // Reconciliation
            $table->enum('reconciliation_status', ['pending', 'matched', 'partial', 'manual', 'ignored'])->default('pending');
            $table->uuid('matched_invoice_id')->nullable();
            $table->uuid('matched_partner_id')->nullable();
            $table->uuid('journal_entry_id')->nullable();

            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
            $table->index(['bank_account_id', 'reconciliation_status']);
            $table->index('structured_communication');

            $table->foreign('matched_invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('matched_partner_id')->references('id')->on('partners')->nullOnDelete();
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });

        // Reconciliation Rules
        Schema::create('reconciliation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('priority')->default(0);

            // Conditions (JSON)
            $table->json('conditions'); // [{field, operator, value}]

            // Actions
            $table->uuid('account_id')->nullable();
            $table->uuid('partner_id')->nullable();
            $table->string('vat_code', 10)->nullable();
            $table->text('label_template')->nullable();

            $table->boolean('auto_validate')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['company_id', 'is_active', 'priority']);
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_rules');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_accounts');
    }
};
