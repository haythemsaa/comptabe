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
            if (!Schema::hasColumn('companies', 'country_code')) {
                $table->char('country_code', 2)->default('BE')->after('vat_number');
            }

            // Tunisia-specific fields
            if (!Schema::hasColumn('companies', 'matricule_fiscal')) {
                $table->string('matricule_fiscal', 20)->nullable()->after('country_code');
            }
            if (!Schema::hasColumn('companies', 'cnss_employer_number')) {
                $table->string('cnss_employer_number', 20)->nullable()->after('matricule_fiscal');
            }

            if (!Schema::hasIndex('companies', 'companies_country_code_index')) {
                $table->index('country_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['country_code']);
            $table->dropColumn(['country_code', 'matricule_fiscal', 'cnss_employer_number']);
        });
    }
};
