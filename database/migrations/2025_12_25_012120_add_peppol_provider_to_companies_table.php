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
            if (!Schema::hasColumn('companies', 'peppol_provider')) {
                $table->string('peppol_provider')->default('recommand');
            }
            if (!Schema::hasColumn('companies', 'peppol_api_key')) {
                $table->string('peppol_api_key')->nullable();
            }
            if (!Schema::hasColumn('companies', 'peppol_api_secret')) {
                $table->string('peppol_api_secret')->nullable();
            }
            if (!Schema::hasColumn('companies', 'peppol_participant_id')) {
                $table->string('peppol_participant_id')->nullable();
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
                'peppol_provider',
                'peppol_api_key',
                'peppol_api_secret',
                'peppol_participant_id',
            ]);
        });
    }
};
