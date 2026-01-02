<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additional performance optimization indexes based on audit recommendations.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Additional Invoices indexes
        if (Schema::hasTable('invoices')) {
            $this->createIndexIfColumnsExist('invoices', ['type', 'invoice_date'], 'idx_invoices_type_date');
            $this->createIndexIfColumnsExist('invoices', ['due_date', 'status', 'amount_due'], 'idx_invoices_due_status_amount');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'type', 'status', 'invoice_date'], 'idx_invoices_lookup');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'status', 'amount_due', 'due_date'], 'idx_invoices_payments');
        }

        // Additional Partners index
        if (Schema::hasTable('partners')) {
            $this->createIndexIfColumnsExist('partners', ['iban'], 'idx_partners_iban');
        }

        // Additional Bank Transactions indexes
        if (Schema::hasTable('bank_transactions')) {
            $this->createIndexIfColumnsExist('bank_transactions', ['value_date', 'amount'], 'idx_transactions_value_date');
            $this->createIndexIfColumnsExist('bank_transactions', ['counterparty_account'], 'idx_transactions_counterparty');
        }

        // Additional Invoice Lines indexes
        if (Schema::hasTable('invoice_lines')) {
            $this->createIndexIfColumnsExist('invoice_lines', ['vat_rate', 'vat_amount'], 'idx_invoice_lines_vat_amount');
            $this->createIndexIfColumnsExist('invoice_lines', ['account_id'], 'idx_invoice_lines_account');
        }

        // Journal Entries - index with status column
        if (Schema::hasTable('journal_entries')) {
            $this->createIndexIfColumnsExist('journal_entries', ['company_id', 'entry_date', 'status'], 'idx_journals_company_date_status');
        }

        // Journal Entry Lines - for account ledger queries
        if (Schema::hasTable('journal_entry_lines')) {
            $this->createIndexIfColumnsExist('journal_entry_lines', ['account_id', 'created_at'], 'idx_journal_entry_lines_account_date');
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('invoices', 'idx_invoices_type_date');
        $this->dropIndexIfExists('invoices', 'idx_invoices_due_status_amount');
        $this->dropIndexIfExists('invoices', 'idx_invoices_lookup');
        $this->dropIndexIfExists('invoices', 'idx_invoices_payments');

        $this->dropIndexIfExists('partners', 'idx_partners_iban');

        $this->dropIndexIfExists('bank_transactions', 'idx_transactions_value_date');
        $this->dropIndexIfExists('bank_transactions', 'idx_transactions_counterparty');

        $this->dropIndexIfExists('invoice_lines', 'idx_invoice_lines_vat_amount');
        $this->dropIndexIfExists('invoice_lines', 'idx_invoice_lines_account');

        $this->dropIndexIfExists('journal_entries', 'idx_journals_company_date_status');

        $this->dropIndexIfExists('journal_entry_lines', 'idx_journal_entry_lines_account_date');
    }

    /**
     * Create index only if all columns exist and index doesn't already exist.
     */
    protected function createIndexIfColumnsExist(string $table, array $columns, string $indexName): void
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if ($this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    /**
     * Drop index if it exists.
     */
    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!$this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    /**
     * Check if index exists on table.
     */
    protected function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = Schema::getIndexes($table);
            foreach ($indexes as $index) {
                if ($index['name'] === $indexName) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }
};
