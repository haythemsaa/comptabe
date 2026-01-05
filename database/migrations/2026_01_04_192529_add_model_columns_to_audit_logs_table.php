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
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'model_type')) {
                $table->string('model_type')->nullable()->after('action');
            }
            if (!Schema::hasColumn('audit_logs', 'model_id')) {
                $table->string('model_id')->nullable()->after('model_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['model_type', 'model_id']);
        });
    }
};
