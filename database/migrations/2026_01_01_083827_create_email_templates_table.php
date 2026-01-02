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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // unique identifier (e.g., 'welcome_email', 'invoice_reminder')
            $table->string('display_name'); // Human readable name
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text')->nullable();
            $table->json('available_variables')->nullable(); // Variables that can be used in template
            $table->string('category')->nullable(); // e.g., 'system', 'invoicing', 'notifications'
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System templates cannot be deleted
            $table->uuid('last_modified_by')->nullable();
            $table->foreign('last_modified_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index('name');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
