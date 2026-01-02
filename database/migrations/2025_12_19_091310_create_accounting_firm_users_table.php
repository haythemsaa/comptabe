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
        Schema::create('accounting_firm_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('accounting_firm_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Role in firm
            $table->string('role', 50)->default('cabinet_accountant');
            // cabinet_owner, cabinet_admin, cabinet_manager, cabinet_accountant, cabinet_assistant

            // Professional info
            $table->string('employee_number', 50)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->string('department', 100)->nullable();

            // Specific permissions (override role)
            $table->json('permissions')->nullable();

            // Client access
            $table->boolean('can_access_all_clients')->default(false);

            // Configuration
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // Unique constraint
            $table->unique(['accounting_firm_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_firm_users');
    }
};
