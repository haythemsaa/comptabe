<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance optimization indexes for commonly queried columns.
 * All index creations are conditional based on column existence.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Invoices - most frequently queried table
        if (Schema::hasTable('invoices')) {
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'type', 'status'], 'idx_invoices_company_type_status');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'invoice_date'], 'idx_invoices_company_date');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'due_date', 'status'], 'idx_invoices_company_due_status');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'peppol_status'], 'idx_invoices_peppol_status');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'ereporting_status'], 'idx_invoices_ereporting_status');
            // Additional performance indexes from audit
            $this->createIndexIfColumnsExist('invoices', ['type', 'invoice_date'], 'idx_invoices_type_date');
            $this->createIndexIfColumnsExist('invoices', ['due_date', 'status', 'amount_due'], 'idx_invoices_due_status_amount');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'type', 'status', 'invoice_date'], 'idx_invoices_lookup');
            $this->createIndexIfColumnsExist('invoices', ['company_id', 'status', 'amount_due', 'due_date'], 'idx_invoices_payments');
        }

        // Partners - frequently joined with invoices
        if (Schema::hasTable('partners')) {
            $this->createIndexIfColumnsExist('partners', ['company_id', 'vat_number'], 'idx_partners_company_vat');
            $this->createIndexIfColumnsExist('partners', ['company_id', 'type', 'is_active'], 'idx_partners_company_type_active');
            $this->createIndexIfColumnsExist('partners', ['company_id', 'peppol_capable'], 'idx_partners_peppol');
            // For IBAN lookup in bank reconciliation
            $this->createIndexIfColumnsExist('partners', ['iban'], 'idx_partners_iban');
        }

        // Bank Transactions - high volume table
        if (Schema::hasTable('bank_transactions')) {
            $this->createIndexIfColumnsExist('bank_transactions', ['bank_account_id', 'is_reconciled'], 'idx_transactions_account_reconciled');
            $this->createIndexIfColumnsExist('bank_transactions', ['bank_account_id', 'transaction_date'], 'idx_transactions_account_date');
            $this->createIndexIfColumnsExist('bank_transactions', ['bank_account_id', 'category_id'], 'idx_transactions_category');
            // For cash flow reports and partner matching
            $this->createIndexIfColumnsExist('bank_transactions', ['value_date', 'amount'], 'idx_transactions_value_date');
            $this->createIndexIfColumnsExist('bank_transactions', ['counterparty_account'], 'idx_transactions_counterparty');
        }

        // Invoice Lines - joined frequently with invoices
        if (Schema::hasTable('invoice_lines')) {
            $this->createIndexIfColumnsExist('invoice_lines', ['invoice_id', 'vat_rate'], 'idx_invoice_lines_vat');
            $this->createIndexIfColumnsExist('invoice_lines', ['vat_rate', 'vat_amount'], 'idx_invoice_lines_vat_amount');
            $this->createIndexIfColumnsExist('invoice_lines', ['account_id'], 'idx_invoice_lines_account');
        }

        // Journal Entries - accounting queries
        if (Schema::hasTable('journal_entries')) {
            $this->createIndexIfColumnsExist('journal_entries', ['company_id', 'entry_date', 'is_posted'], 'idx_journals_company_date_posted');
            $this->createIndexIfColumnsExist('journal_entries', ['company_id', 'fiscal_year_id'], 'idx_journals_company_fiscal');
            // Index with status column for period queries
            $this->createIndexIfColumnsExist('journal_entries', ['company_id', 'entry_date', 'status'], 'idx_journals_company_date_status');
            // Alternative index without is_posted if column doesn't exist
            if (!Schema::hasColumn('journal_entries', 'is_posted')) {
                $this->createIndexIfColumnsExist('journal_entries', ['company_id', 'entry_date'], 'idx_journals_company_date');
            }
        }

        // Journal Lines - heavily queried for reports
        if (Schema::hasTable('journal_lines')) {
            $this->createIndexIfColumnsExist('journal_lines', ['account_id', 'journal_entry_id'], 'idx_journal_lines_account');
        }

        // Journal Entry Lines - for account ledger queries
        if (Schema::hasTable('journal_entry_lines')) {
            $this->createIndexIfColumnsExist('journal_entry_lines', ['account_id', 'created_at'], 'idx_journal_entry_lines_account_date');
        }

        // Peppol Transmissions
        if (Schema::hasTable('peppol_transmissions')) {
            $this->createIndexIfColumnsExist('peppol_transmissions', ['company_id', 'status', 'direction'], 'idx_peppol_company_status');
        }

        // Audit Logs - if using audit logging
        if (Schema::hasTable('audit_logs')) {
            $this->createIndexIfColumnsExist('audit_logs', ['auditable_type', 'auditable_id'], 'idx_audit_entity');
            $this->createIndexIfColumnsExist('audit_logs', ['user_id', 'created_at'], 'idx_audit_user_date');
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('invoices', 'idx_invoices_company_type_status');
        $this->dropIndexIfExists('invoices', 'idx_invoices_company_date');
        $this->dropIndexIfExists('invoices', 'idx_invoices_company_due_status');
        $this->dropIndexIfExists('invoices', 'idx_invoices_peppol_status');
        $this->dropIndexIfExists('invoices', 'idx_invoices_ereporting_status');
        $this->dropIndexIfExists('invoices', 'idx_invoices_type_date');
        $this->dropIndexIfExists('invoices', 'idx_invoices_due_status_amount');
        $this->dropIndexIfExists('invoices', 'idx_invoices_lookup');
        $this->dropIndexIfExists('invoices', 'idx_invoices_payments');

        $this->dropIndexIfExists('partners', 'idx_partners_company_vat');
        $this->dropIndexIfExists('partners', 'idx_partners_company_type_active');
        $this->dropIndexIfExists('partners', 'idx_partners_peppol');
        $this->dropIndexIfExists('partners', 'idx_partners_iban');

        $this->dropIndexIfExists('bank_transactions', 'idx_transactions_account_reconciled');
        $this->dropIndexIfExists('bank_transactions', 'idx_transactions_account_date');
        $this->dropIndexIfExists('bank_transactions', 'idx_transactions_category');
        $this->dropIndexIfExists('bank_transactions', 'idx_transactions_value_date');
        $this->dropIndexIfExists('bank_transactions', 'idx_transactions_counterparty');

        $this->dropIndexIfExists('invoice_lines', 'idx_invoice_lines_vat');
        $this->dropIndexIfExists('invoice_lines', 'idx_invoice_lines_vat_amount');
        $this->dropIndexIfExists('invoice_lines', 'idx_invoice_lines_account');

        $this->dropIndexIfExists('journal_entries', 'idx_journals_company_date_posted');
        $this->dropIndexIfExists('journal_entries', 'idx_journals_company_fiscal');
        $this->dropIndexIfExists('journal_entries', 'idx_journals_company_date');
        $this->dropIndexIfExists('journal_entries', 'idx_journals_company_date_status');

        $this->dropIndexIfExists('journal_lines', 'idx_journal_lines_account');

        $this->dropIndexIfExists('journal_entry_lines', 'idx_journal_entry_lines_account_date');

        $this->dropIndexIfExists('peppol_transmissions', 'idx_peppol_company_status');

        $this->dropIndexIfExists('audit_logs', 'idx_audit_entity');
        $this->dropIndexIfExists('audit_logs', 'idx_audit_user_date');
    }

    /**
     * Create index only if all columns exist and index doesn't already exist.
     */
    protected function createIndexIfColumnsExist(string $table, array $columns, string $indexName): void
    {
        // Check if all columns exist
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return; // Skip if any column is missing
            }
        }

        // Check if index already exists
        if ($this->hasIndex($table, $indexName)) {
            return;
        }

        // Create the index
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
            // Table might not exist or other issue
            return false;
        }
        return false;
    }
};
