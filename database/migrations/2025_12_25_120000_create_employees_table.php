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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');

            // Personal Information
            $table->string('employee_number', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('maiden_name', 100)->nullable();
            $table->date('birth_date');
            $table->string('birth_place', 100)->nullable();
            $table->string('birth_country', 2)->default('BE');
            $table->enum('gender', ['M', 'F', 'X'])->default('M');
            $table->string('nationality', 2)->default('BE');

            // National Registry Number (Numéro national / Rijksregisternummer)
            $table->string('national_number', 20)->unique();

            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();

            // Address
            $table->string('street', 200);
            $table->string('house_number', 10);
            $table->string('box', 10)->nullable();
            $table->string('postal_code', 10);
            $table->string('city', 100);
            $table->string('country_code', 2)->default('BE');

            // Banking
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();

            // Employment Status
            $table->enum('status', ['active', 'suspended', 'terminated', 'retired'])->default('active');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->string('termination_reason')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index('employee_number');
            $table->index('national_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
