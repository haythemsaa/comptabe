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
        // Indexes for Invoices (AI Analytics queries)
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'due_date'], 'idx_invoices_ai_analytics');
            $table->index(['company_id', 'status', 'payment_date'], 'idx_invoices_payments');
            $table->index(['company_id', 'issue_date'], 'idx_invoices_by_date');
        });

        // Indexes for Expenses (AI Categorization queries)
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['company_id', 'category'], 'idx_expenses_category');
            $table->index(['company_id', 'expense_date'], 'idx_expenses_by_date');
            $table->index(['company_id', 'status'], 'idx_expenses_status');
        });

        // Indexes for Bank Transactions (AI Reconciliation queries)
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->index(['company_id', 'reconciled_at'], 'idx_transactions_reconciled');
            $table->index(['company_id', 'transaction_date'], 'idx_transactions_by_date');
            $table->index(['company_id', 'amount'], 'idx_transactions_amount');
        });

        // Indexes for Journal Entries (Validation queries)
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['company_id', 'entry_date'], 'idx_entries_by_date');
            $table->index(['company_id', 'status'], 'idx_entries_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_ai_analytics');
            $table->dropIndex('idx_invoices_payments');
            $table->dropIndex('idx_invoices_by_date');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_category');
            $table->dropIndex('idx_expenses_by_date');
            $table->dropIndex('idx_expenses_status');
        });

        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_reconciled');
            $table->dropIndex('idx_transactions_by_date');
            $table->dropIndex('idx_transactions_amount');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('idx_entries_by_date');
            $table->dropIndex('idx_entries_status');
        });
    }
};
