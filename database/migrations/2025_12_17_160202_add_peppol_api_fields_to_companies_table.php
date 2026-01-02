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
            // Peppol Access Point API credentials
            $table->string('peppol_provider')->nullable()->after('peppol_id');
            $table->string('peppol_api_key')->nullable()->after('peppol_provider');
            $table->text('peppol_api_secret')->nullable()->after('peppol_api_key');
            $table->string('peppol_webhook_secret', 64)->nullable()->after('peppol_api_secret');
            $table->boolean('peppol_test_mode')->default(true)->after('peppol_webhook_secret');
            $table->timestamp('peppol_connected_at')->nullable()->after('peppol_test_mode');
            $table->json('peppol_settings')->nullable()->after('peppol_connected_at');
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
                'peppol_webhook_secret',
                'peppol_test_mode',
                'peppol_connected_at',
                'peppol_settings',
            ]);
        });
    }
};
