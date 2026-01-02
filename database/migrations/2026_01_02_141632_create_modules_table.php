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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // crm, stock, projects, expenses, pos, etc.
            $table->string('name'); // CRM Pipeline, Gestion de Stock, etc.
            $table->text('description')->nullable();
            $table->string('category'); // sales, inventory, hr, finance, productivity
            $table->text('icon')->nullable(); // Icon class or SVG (TEXT pour SVG paths longs)
            $table->string('version')->default('1.0.0');
            $table->boolean('is_core')->default(false); // Core modules (invoices, accounting) always enabled
            $table->boolean('is_premium')->default(false); // Require premium subscription
            $table->decimal('monthly_price', 10, 2)->default(0); // Prix mensuel add-on
            $table->integer('sort_order')->default(0);
            $table->json('dependencies')->nullable(); // ['accounting', 'invoices'] modules requis
            $table->json('routes')->nullable(); // Routes principales du module
            $table->json('permissions')->nullable(); // Permissions associées
            $table->boolean('is_active')->default(true); // Module disponible au catalogue
            $table->timestamps();
        });

        // Table pivot: modules activés par tenant
        Schema::create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true); // Tenant peut désactiver temporairement
            $table->boolean('is_visible')->default(true); // Tenant peut masquer de l'UI
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->foreignUuid('enabled_by')->nullable()->constrained('users'); // Admin qui a activé
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'module_id']);
        });

        // Table demandes de modules (marketplace)
        Schema::create('module_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('message')->nullable(); // Message du tenant
            $table->text('admin_response')->nullable(); // Réponse admin
            $table->foreignUuid('requested_by')->constrained('users');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_requests');
        Schema::dropIfExists('company_modules');
        Schema::dropIfExists('modules');
    }
};
