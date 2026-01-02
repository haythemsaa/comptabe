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
        Schema::table('employees', function (Blueprint $table) {
            // Tunisia-specific identification
            $table->string('cin', 8)->nullable()->after('national_number')
                ->comment('CIN - Carte d\'Identité Nationale (Tunisia)');
            $table->string('cnss_number', 20)->nullable()->after('cin')
                ->comment('CNSS number (Tunisia social security)');

            // RIB for Tunisia (instead of IBAN)
            $table->string('rib', 20)->nullable()->after('iban')
                ->comment('RIB - Relevé d\'Identité Bancaire (Tunisia)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['cin', 'cnss_number', 'rib']);
        });
    }
};
