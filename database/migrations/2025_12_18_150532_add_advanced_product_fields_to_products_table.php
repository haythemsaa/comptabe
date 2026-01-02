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
        // Create product categories table for hierarchical categories
        Schema::create('product_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');
            $table->uuid('parent_id')->nullable();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable(); // Hex color
            $table->string('icon', 50)->nullable(); // Icon class name
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('product_categories')
                ->onDelete('set null');

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'parent_id']);
        });

        // Create product types table for defining custom product schemas
        Schema::create('product_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->boolean('is_service')->default(false); // Is it a service vs physical product
            $table->boolean('track_inventory')->default(false);
            $table->boolean('has_variants')->default(false);
            $table->json('default_values')->nullable(); // Default values for new products of this type
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'slug']);
        });

        // Create custom field definitions table
        Schema::create('product_custom_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('product_type_id')->nullable()->constrained('product_types')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->string('type', 30); // text, textarea, number, decimal, date, datetime, boolean, select, multiselect, url, email, phone, file, color, json
            $table->json('options')->nullable(); // For select/multiselect: choices. For number: min/max/step. For text: pattern, min_length, max_length
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('show_in_list')->default(false);
            $table->boolean('show_in_invoice')->default(false);
            $table->string('group', 50)->nullable(); // For grouping fields in the form
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'product_type_id']);
        });

        // Add new columns to products table
        Schema::table('products', function (Blueprint $table) {
            // Foreign keys for type and category
            $table->foreignUuid('product_type_id')->nullable()->after('type')->constrained('product_types')->onDelete('set null');
            $table->foreignUuid('category_id')->nullable()->after('category')->constrained('product_categories')->onDelete('set null');

            // Extended product information
            $table->string('sku', 100)->nullable()->after('code'); // Stock Keeping Unit
            $table->string('barcode', 100)->nullable()->after('sku'); // EAN/UPC barcode
            $table->string('manufacturer', 255)->nullable()->after('barcode');
            $table->string('brand', 100)->nullable()->after('manufacturer');

            // Pricing fields
            $table->decimal('cost_price', 12, 2)->nullable()->after('unit_price'); // Purchase price
            $table->decimal('compare_price', 12, 2)->nullable()->after('cost_price'); // Original price for discounts
            $table->decimal('min_price', 12, 2)->nullable()->after('compare_price'); // Minimum allowed price
            $table->string('currency', 3)->default('EUR')->after('min_price');

            // Inventory fields
            $table->boolean('track_inventory')->default(false)->after('currency');
            $table->integer('stock_quantity')->default(0)->after('track_inventory');
            $table->integer('low_stock_threshold')->nullable()->after('stock_quantity');
            $table->string('stock_status', 20)->default('in_stock')->after('low_stock_threshold'); // in_stock, out_of_stock, backorder, preorder

            // Physical product attributes
            $table->decimal('weight', 10, 3)->nullable()->after('stock_status'); // kg
            $table->decimal('length', 10, 2)->nullable()->after('weight'); // cm
            $table->decimal('width', 10, 2)->nullable()->after('length'); // cm
            $table->decimal('height', 10, 2)->nullable()->after('width'); // cm

            // Service-specific fields
            $table->integer('duration_minutes')->nullable()->after('height'); // For services: duration
            $table->boolean('requires_scheduling')->default(false)->after('duration_minutes');

            // Media and visibility
            $table->string('image_path', 500)->nullable()->after('requires_scheduling');
            $table->json('gallery')->nullable()->after('image_path'); // Additional images
            $table->json('documents')->nullable()->after('gallery'); // Attached files (specs, manuals)

            // Custom fields storage
            $table->json('custom_fields')->nullable()->after('documents');

            // SEO and web
            $table->string('meta_title', 100)->nullable()->after('custom_fields');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->json('tags')->nullable()->after('meta_description');

            // Restrictions and rules
            $table->integer('min_quantity')->default(1)->after('tags');
            $table->integer('max_quantity')->nullable()->after('min_quantity');
            $table->integer('quantity_increment')->default(1)->after('max_quantity');

            // Timestamps for history
            $table->timestamp('last_sold_at')->nullable()->after('quantity_increment');
            $table->integer('total_sold')->default(0)->after('last_sold_at');

            // Additional indexes
            $table->index('sku');
            $table->index('barcode');
            $table->index('stock_status');
            $table->index(['company_id', 'product_type_id']);
            $table->index(['company_id', 'category_id']);
        });

        // Create product variants table for variable products
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->string('name', 255);
            $table->json('attributes'); // e.g., {"color": "Red", "size": "XL"}
            $table->decimal('unit_price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('image_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });

        // Create variant attributes table for defining attribute options
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('type', 30)->default('select'); // select, color, size, text
            $table->json('values')->nullable(); // Predefined values
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->dropForeign(['category_id']);

            $table->dropColumn([
                'product_type_id',
                'category_id',
                'sku',
                'barcode',
                'manufacturer',
                'brand',
                'cost_price',
                'compare_price',
                'min_price',
                'currency',
                'track_inventory',
                'stock_quantity',
                'low_stock_threshold',
                'stock_status',
                'weight',
                'length',
                'width',
                'height',
                'duration_minutes',
                'requires_scheduling',
                'image_path',
                'gallery',
                'documents',
                'custom_fields',
                'meta_title',
                'meta_description',
                'tags',
                'min_quantity',
                'max_quantity',
                'quantity_increment',
                'last_sold_at',
                'total_sold',
            ]);
        });

        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_custom_fields');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('product_categories');
    }
};
