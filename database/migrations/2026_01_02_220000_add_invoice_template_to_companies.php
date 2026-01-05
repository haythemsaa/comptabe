<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('invoice_template', 50)->default('modern')->after('logo_path');
            $table->string('invoice_primary_color', 7)->default('#6366f1')->after('invoice_template');
            $table->string('invoice_secondary_color', 7)->default('#1e293b')->after('invoice_primary_color');
            $table->json('invoice_template_settings')->nullable()->after('invoice_secondary_color');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_template',
                'invoice_primary_color',
                'invoice_secondary_color',
                'invoice_template_settings',
            ]);
        });
    }
};
