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
            // Tunisia-specific fields
            $table->string('matricule_fiscal', 50)->nullable()->after('vat_number')
                ->comment('Matricule Fiscal Tunisien (ex: 1234567/A/M/000)');

            $table->string('cnss_employer_number', 50)->nullable()->after('matricule_fiscal')
                ->comment('Numéro Employeur CNSS (Caisse Nationale de Sécurité Sociale)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['matricule_fiscal', 'cnss_employer_number']);
        });
    }
};
