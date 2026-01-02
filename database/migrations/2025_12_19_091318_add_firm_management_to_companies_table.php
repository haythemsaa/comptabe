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
            // Company type
            $table->string('company_type', 20)->default('standalone')->after('id');
            // standalone: Autonomous company (enterprise version)
            // client: Client of an accounting firm
            // accounting_firm: The firm itself (internal use)

            // Managed by accounting firm
            $table->foreignUuid('managed_by_firm_id')->nullable()->after('company_type')
                ->constrained('accounting_firms')->nullOnDelete();

            // Client accepts firm management
            $table->boolean('accepts_firm_management')->default(false)->after('managed_by_firm_id');

            // Firm access level
            $table->string('firm_access_level', 20)->default('full')->after('accepts_firm_management');
            // full: Complete access
            // limited: Limited access (read + certain operations)
            // readonly: Read-only
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['managed_by_firm_id']);
            $table->dropColumn([
                'company_type',
                'managed_by_firm_id',
                'accepts_firm_management',
                'firm_access_level',
            ]);
        });
    }
};
