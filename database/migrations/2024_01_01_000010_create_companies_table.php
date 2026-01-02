<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('legal_form', 50)->nullable(); // SPRL, SA, SRL, etc.
            $table->string('vat_number', 20)->unique(); // BE0123456789
            $table->string('enterprise_number', 20)->nullable(); // 0123.456.789

            // Address
            $table->string('street', 255)->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('box', 20)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->char('country_code', 2)->default('BE');

            // Contact
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website', 255)->nullable();

            // Peppol
            $table->string('peppol_id', 50)->nullable(); // 0208:BE0123456789
            $table->boolean('peppol_registered')->default(false);
            $table->timestamp('peppol_registered_at')->nullable();

            // Bank
            $table->string('default_iban', 34)->nullable();
            $table->string('default_bic', 11)->nullable();

            // Accounting parameters
            $table->tinyInteger('fiscal_year_start_month')->default(1);
            $table->string('vat_regime', 50)->default('normal'); // normal, franchise, forfait
            $table->string('vat_periodicity', 20)->default('quarterly'); // monthly, quarterly

            // Logo
            $table->string('logo_path')->nullable();

            // Settings
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('vat_number');
            $table->index('peppol_id');
        });

        // Pivot table for user-company relationship (multi-tenant)
        Schema::create('company_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50)->default('user'); // owner, admin, accountant, user, readonly
            $table->json('permissions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
            $table->index(['company_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
    }
};
