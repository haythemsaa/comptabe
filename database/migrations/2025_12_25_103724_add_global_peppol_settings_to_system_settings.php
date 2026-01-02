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
        // Insert global Peppol settings (managed by superadmin)
        DB::table('system_settings')->insert([
            [
                'key' => 'peppol_global_provider',
                'value' => 'recommand',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'peppol_global_api_key',
                'value' => '',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'peppol_global_api_secret',
                'value' => '',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'peppol_global_test_mode',
                'value' => '1',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'peppol_enabled',
                'value' => '1',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'peppol_global_provider',
            'peppol_global_api_key',
            'peppol_global_api_secret',
            'peppol_global_test_mode',
            'peppol_enabled',
        ])->delete();
    }
};
