<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['customer', 'supplier', 'both'])->default('both');
            $table->string('reference', 50)->nullable(); // Code client/fournisseur

            // Identity
            $table->string('name', 255);
            $table->string('vat_number', 20)->nullable();
            $table->string('enterprise_number', 20)->nullable();
            $table->boolean('is_company')->default(true);

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
            $table->string('mobile', 50)->nullable();
            $table->string('contact_person', 255)->nullable();

            // Peppol
            $table->string('peppol_id', 50)->nullable();
            $table->boolean('peppol_capable')->default(false);
            $table->timestamp('peppol_verified_at')->nullable();

            // Accounting defaults
            $table->uuid('default_account_receivable_id')->nullable();
            $table->uuid('default_account_payable_id')->nullable();
            $table->integer('payment_terms_days')->default(30);
            $table->string('default_vat_code', 10)->nullable();

            // Bank
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'reference']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'name']);
            $table->index('vat_number');
            $table->index('peppol_id');

            $table->foreign('default_account_receivable_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreign('default_account_payable_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
