<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Connexions e-commerce (WooCommerce, Shopify, etc.)
        if (!Schema::hasTable('ecommerce_connections')) {
        Schema::create('ecommerce_connections', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('name');
            $table->enum('platform', ['woocommerce', 'shopify', 'prestashop', 'magento', 'custom']);
            $table->string('store_url');
            $table->string('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_sync_orders')->default(true);
            $table->boolean('auto_sync_products')->default(false);
            $table->boolean('auto_sync_customers')->default(true);
            $table->boolean('auto_create_invoices')->default(false);
            $table->integer('sync_interval_minutes')->default(15);
            $table->timestamp('last_sync_at')->nullable();
            $table->json('settings')->nullable();
            $table->json('field_mappings')->nullable(); // Mapping des champs personnalisés
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index('company_id');
        });
        }

        // Commandes e-commerce importées
        if (!Schema::hasTable('ecommerce_orders')) {
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignId('connection_id')->constrained('ecommerce_connections')->cascadeOnDelete();
            $table->uuid('partner_id')->nullable(); // Client lié
            $table->uuid('invoice_id')->nullable();
            $table->string('external_id'); // ID sur la plateforme
            $table->string('order_number');
            $table->enum('status', [
                'pending', 'processing', 'on_hold', 'completed',
                'cancelled', 'refunded', 'failed', 'shipped'
            ])->default('pending');
            $table->enum('sync_status', ['pending', 'synced', 'invoiced', 'error'])->default('pending');
            $table->string('currency', 3)->default('EUR');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('shipping_method')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->json('customer_data')->nullable();
            $table->text('customer_note')->nullable();
            $table->timestamp('order_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->json('raw_data')->nullable(); // Données brutes de la plateforme
            $table->text('sync_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->index(['company_id', 'status']);
            $table->index(['connection_id', 'external_id']);
            $table->index('order_date');
        });
        }

        // Lignes de commande
        if (!Schema::hasTable('ecommerce_order_items')) {
        Schema::create('ecommerce_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('ecommerce_orders')->cascadeOnDelete();
            $table->uuid('product_id')->nullable();
            $table->string('external_product_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
        }

        // Mapping produits (liaison plateforme <-> local)
        if (!Schema::hasTable('ecommerce_product_mappings')) {
        Schema::create('ecommerce_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('ecommerce_connections')->cascadeOnDelete();
            $table->uuid('product_id');
            $table->string('external_id');
            $table->string('external_sku')->nullable();
            $table->string('external_name')->nullable();
            $table->boolean('sync_stock')->default(false);
            $table->boolean('sync_price')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['connection_id', 'external_id']);
            $table->unique(['connection_id', 'product_id']);
        });
        }

        // Logs de synchronisation
        if (!Schema::hasTable('ecommerce_sync_logs')) {
        Schema::create('ecommerce_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('ecommerce_connections')->cascadeOnDelete();
            $table->enum('type', ['orders', 'products', 'customers', 'stock', 'manual']);
            $table->enum('direction', ['import', 'export']);
            $table->enum('status', ['started', 'completed', 'failed']);
            $table->integer('items_processed')->default(0);
            $table->integer('items_created')->default(0);
            $table->integer('items_updated')->default(0);
            $table->integer('items_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['connection_id', 'created_at']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_sync_logs');
        Schema::dropIfExists('ecommerce_product_mappings');
        Schema::dropIfExists('ecommerce_order_items');
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_connections');
    }
};
