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
        Schema::create('accounting_firms', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_form', 50)->nullable();

            // Professional identification
            $table->string('itaa_number', 50)->nullable()->index();
            $table->string('ire_number', 50)->nullable();
            $table->string('vat_number', 20)->index();
            $table->string('enterprise_number', 20)->nullable();

            // Address
            $table->string('street')->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('box', 10)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->char('country_code', 2)->default('BE');

            // Contact
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();

            // Branding
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#3B82F6');

            // Peppol
            $table->string('peppol_id', 100)->nullable();
            $table->string('peppol_provider', 50)->nullable();
            $table->text('peppol_api_key')->nullable();
            $table->text('peppol_api_secret')->nullable();
            $table->boolean('peppol_test_mode')->default(true);

            // Subscription
            $table->foreignUuid('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subscription_status', 20)->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('max_clients')->default(10);
            $table->integer('max_users')->default(5);

            // Settings
            $table->json('settings')->nullable();
            $table->json('features')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_firms');
    }
};
