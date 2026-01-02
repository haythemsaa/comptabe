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
        Schema::table('users', function (Blueprint $table) {
            // User type - add after is_superadmin if it exists, otherwise after is_active
            $afterColumn = Schema::hasColumn('users', 'is_superadmin') ? 'is_superadmin' : 'is_active';

            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 20)->default('standard')->after($afterColumn);
            }

            // Professional title
            if (!Schema::hasColumn('users', 'professional_title')) {
                $table->string('professional_title', 100)->nullable()->after('user_type');
            }

            // Professional registration numbers
            if (!Schema::hasColumn('users', 'itaa_number')) {
                $table->string('itaa_number', 50)->nullable()->after('professional_title');
            }
            if (!Schema::hasColumn('users', 'ire_number')) {
                $table->string('ire_number', 50)->nullable()->after('itaa_number');
            }

            // Link to accounting firm
            if (!Schema::hasColumn('users', 'default_firm_id')) {
                $table->foreignUuid('default_firm_id')->nullable()->after('ire_number')
                    ->constrained('accounting_firms')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('users', 'default_firm_id')) {
                $table->dropForeign(['default_firm_id']);
                $table->dropColumn('default_firm_id');
            }

            $columnsToDrop = [];
            foreach (['user_type', 'professional_title', 'itaa_number', 'ire_number'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
