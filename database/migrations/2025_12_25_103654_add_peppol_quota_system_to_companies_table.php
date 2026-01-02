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
        Schema::table('companies', function (Blueprint $table) {
            // Quota system for Peppol invoices
            if (!Schema::hasColumn('companies', 'peppol_plan')) {
                $table->string('peppol_plan')->default('free')->after('peppol_participant_id');
            }
            if (!Schema::hasColumn('companies', 'peppol_quota_monthly')) {
                $table->integer('peppol_quota_monthly')->default(20)->after('peppol_plan');
            }
            if (!Schema::hasColumn('companies', 'peppol_usage_current_month')) {
                $table->integer('peppol_usage_current_month')->default(0)->after('peppol_quota_monthly');
            }
            if (!Schema::hasColumn('companies', 'peppol_usage_last_reset')) {
                $table->timestamp('peppol_usage_last_reset')->nullable()->after('peppol_usage_current_month');
            }
            if (!Schema::hasColumn('companies', 'peppol_overage_allowed')) {
                $table->boolean('peppol_overage_allowed')->default(false)->after('peppol_usage_last_reset');
            }
            if (!Schema::hasColumn('companies', 'peppol_overage_cost')) {
                $table->decimal('peppol_overage_cost', 8, 2)->default(0.50)->after('peppol_overage_allowed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'peppol_plan',
                'peppol_quota_monthly',
                'peppol_usage_current_month',
                'peppol_usage_last_reset',
                'peppol_overage_allowed',
                'peppol_overage_cost',
            ]);
        });
    }
};
