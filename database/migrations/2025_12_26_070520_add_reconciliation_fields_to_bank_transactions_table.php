<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            // Company ID pour performance (éviter JOIN via bank_account)
            $table->foreignUuid('company_id')->nullable()->after('bank_account_id')->constrained()->cascadeOnDelete();

            // Champs pour réconciliation automatique IA
            $table->boolean('is_reconciled')->default(false)->after('reconciliation_status');
            $table->timestamp('reconciled_at')->nullable()->after('is_reconciled');
            $table->foreignUuid('reconciled_by')->nullable()->after('reconciled_at')->constrained('users')->nullOnDelete();

            // Référence à la facture réconciliée (alias pour matched_invoice_id)
            $table->foreignUuid('invoice_id')->nullable()->after('reconciled_by')->constrained('invoices')->nullOnDelete();

            // Champs pour stocker le score de matching IA
            $table->decimal('match_confidence', 5, 4)->nullable()->after('invoice_id')->comment('Score de confiance du matching IA (0-1)');

            // IBAN de la contrepartie (standardisé)
            $table->string('counterparty_iban', 34)->nullable()->after('counterparty_bic');

            // Date de la transaction (alias pour transaction_date)
            $table->date('date')->nullable()->after('value_date');

            // Indexes pour performance
            $table->index('company_id');
            $table->index('is_reconciled');
            $table->index(['is_reconciled', 'date']);
            $table->index(['company_id', 'is_reconciled']);
            $table->index('counterparty_iban');
        });

        // Populate company_id from bank_account relationship
        DB::statement('
            UPDATE bank_transactions bt
            INNER JOIN bank_accounts ba ON bt.bank_account_id = ba.id
            SET bt.company_id = ba.company_id
        ');

        // Make company_id required after population
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->foreignUuid('company_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['reconciled_by']);

            $table->dropIndex(['company_id']);
            $table->dropIndex(['is_reconciled']);
            $table->dropIndex(['is_reconciled', 'date']);
            $table->dropIndex(['company_id', 'is_reconciled']);
            $table->dropIndex(['counterparty_iban']);

            $table->dropColumn([
                'company_id',
                'is_reconciled',
                'reconciled_at',
                'reconciled_by',
                'invoice_id',
                'match_confidence',
                'counterparty_iban',
                'date',
            ]);
        });
    }
};
