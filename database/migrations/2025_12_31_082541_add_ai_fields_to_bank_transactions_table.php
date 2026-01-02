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
        Schema::table('bank_transactions', function (Blueprint $table) {
            // AI Reconciliation Suggestions
            $table->json('ai_reconciliation_suggestions')->nullable()->after('reconciled_at');
            $table->timestamp('suggested_at')->nullable()->after('ai_reconciliation_suggestions');

            // AI Reconciliation
            $table->boolean('ai_reconciled')->default(false)->after('suggested_at');
            $table->decimal('ai_confidence', 5, 4)->nullable()->after('ai_reconciled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'ai_reconciliation_suggestions',
                'suggested_at',
                'ai_reconciled',
                'ai_confidence',
            ]);
        });
    }
};
