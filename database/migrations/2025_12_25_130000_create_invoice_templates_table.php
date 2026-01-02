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
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');

            // Template Info
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Design Settings
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#3B82F6'); // Hex color
            $table->string('secondary_color', 7)->default('#1E40AF');
            $table->string('font_family')->default('Inter');

            // Layout Options
            $table->enum('layout', ['classic', 'modern', 'minimal', 'professional'])->default('classic');
            $table->enum('header_position', ['left', 'center', 'right'])->default('left');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_company_info')->default(true);
            $table->boolean('show_footer')->default(true);

            // Content Sections
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->text('payment_terms')->nullable(); // Default payment terms
            $table->text('notes')->nullable(); // Default notes
            $table->text('legal_text')->nullable(); // Legal mentions

            // Column Visibility
            $table->boolean('show_item_code')->default(true);
            $table->boolean('show_item_description')->default(true);
            $table->boolean('show_quantity')->default(true);
            $table->boolean('show_unit_price')->default(true);
            $table->boolean('show_discount')->default(true);
            $table->boolean('show_vat_rate')->default(true);
            $table->boolean('show_subtotal')->default(true);

            // Formatting
            $table->string('date_format')->default('d/m/Y'); // Belgian format
            $table->string('number_format')->default('0,00'); // European format
            $table->enum('currency_position', ['before', 'after'])->default('after'); // € after

            // Custom Fields (JSON)
            $table->json('custom_fields')->nullable(); // Additional configurable fields
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'is_default']);
            $table->unique(['company_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
