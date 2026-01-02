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
            // Add peppol_company_id for provider-specific company/team ID
            // (e.g., Recommand.eu Team ID, other provider's company identifier)
            if (!Schema::hasColumn('companies', 'peppol_company_id')) {
                $table->string('peppol_company_id')->nullable()->after('peppol_participant_id');
            }

            // Add peppol_test_mode flag
            if (!Schema::hasColumn('companies', 'peppol_test_mode')) {
                $table->boolean('peppol_test_mode')->default(true)->after('peppol_company_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['peppol_company_id', 'peppol_test_mode']);
        });
    }
};
