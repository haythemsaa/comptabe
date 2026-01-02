<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column already exists
        if (!Schema::hasColumn('employment_contracts', 'company_id')) {
            Schema::table('employment_contracts', function (Blueprint $table) {
                // Add company_id as nullable first
                $table->uuid('company_id')->nullable()->after('employee_id');
            });
        }

        // Populate company_id from employee's company (only for null values)
        DB::statement('
            UPDATE employment_contracts ec
            INNER JOIN employees e ON ec.employee_id = e.id
            SET ec.company_id = e.company_id
            WHERE ec.company_id IS NULL
        ');

        Schema::table('employment_contracts', function (Blueprint $table) {
            // Make it non-nullable
            $table->uuid('company_id')->nullable(false)->change();
        });

        // Add foreign key and index - use try-catch to avoid duplicate errors
        try {
            Schema::table('employment_contracts', function (Blueprint $table) {
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, ignore
        }

        try {
            Schema::table('employment_contracts', function (Blueprint $table) {
                $table->index(['company_id', 'status']);
            });
        } catch (\Exception $e) {
            // Index already exists, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_contracts', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'status']);
            $table->dropColumn('company_id');
        });
    }
};
