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
        // Warehouses (Entrepôts)
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country_code', 2)->default('BE');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->foreignUuid('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->json('settings')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'is_default']);
        });

        // Product stock per warehouse (Stock par produit et entrepôt)
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('reserved_quantity', 15, 4)->default(0); // Reserved for orders
            $table->decimal('incoming_quantity', 15, 4)->default(0); // Expected from suppliers
            $table->string('location')->nullable(); // Bin/shelf location
            $table->decimal('min_quantity', 15, 4)->default(0); // Reorder point
            $table->decimal('max_quantity', 15, 4)->nullable(); // Max capacity
            $table->decimal('reorder_quantity', 15, 4)->nullable(); // Qty to reorder
            $table->date('last_counted_at')->nullable();
            $table->decimal('last_counted_quantity', 15, 4)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['product_id', 'warehouse_id', 'quantity']);
        });

        // Stock movements (Mouvements de stock)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 50); // MVT-2026-0001
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('destination_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete(); // For transfers
            $table->enum('type', [
                'in',           // Entrée (achat, retour, ajustement+)
                'out',          // Sortie (vente, casse, ajustement-)
                'transfer',     // Transfert inter-entrepôts
                'adjustment',   // Ajustement d'inventaire
                'production',   // Production/assemblage
                'consumption'   // Consommation matières
            ]);
            $table->enum('reason', [
                'purchase',     // Achat fournisseur
                'sale',         // Vente client
                'return_in',    // Retour client
                'return_out',   // Retour fournisseur
                'transfer',     // Transfert
                'inventory',    // Inventaire/correction
                'production',   // Production
                'consumption',  // Consommation
                'damage',       // Casse/dommage
                'theft',        // Vol
                'expired',      // Périmé
                'sample',       // Échantillon
                'gift',         // Don/cadeau
                'other'         // Autre
            ]);
            $table->decimal('quantity', 15, 4); // Positive for in, negative for out
            $table->decimal('quantity_before', 15, 4);
            $table->decimal('quantity_after', 15, 4);
            $table->decimal('unit_cost', 15, 4)->nullable(); // Cost per unit at time of movement
            $table->decimal('total_cost', 15, 4)->nullable();
            $table->string('batch_number')->nullable(); // Lot number
            $table->date('expiry_date')->nullable();
            $table->string('serial_number')->nullable();

            // Relations to source documents
            $table->string('source_type')->nullable(); // Invoice, Quote, Order, etc.
            $table->uuid('source_id')->nullable();

            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->enum('status', ['draft', 'validated', 'cancelled'])->default('validated');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'product_id']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'type']);
            $table->index(['source_type', 'source_id']);
        });

        // Inventory sessions (Sessions d'inventaire)
        Schema::create('inventory_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 50); // INV-2026-0001
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUuid('warehouse_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'full',         // Inventaire complet
                'partial',      // Inventaire partiel (par catégorie, zone, etc.)
                'cycle',        // Comptage cyclique
                'spot'          // Vérification ponctuelle
            ])->default('full');
            $table->enum('status', [
                'draft',        // En préparation
                'in_progress',  // En cours de comptage
                'review',       // En révision
                'validated',    // Validé, mouvements générés
                'cancelled'     // Annulé
            ])->default('draft');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('filters')->nullable(); // Product category, location filters
            $table->integer('total_products')->default(0);
            $table->integer('counted_products')->default(0);
            $table->integer('discrepancies')->default(0);
            $table->decimal('total_value_difference', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'warehouse_id']);
        });

        // Inventory lines (Lignes d'inventaire)
        Schema::create('inventory_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->string('location')->nullable(); // Bin/shelf
            $table->decimal('expected_quantity', 15, 4)->default(0); // System quantity
            $table->decimal('counted_quantity', 15, 4)->nullable(); // Physical count
            $table->decimal('difference', 15, 4)->nullable(); // counted - expected
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('value_difference', 15, 2)->nullable();
            $table->enum('status', [
                'pending',      // Waiting to be counted
                'counted',      // Counted
                'verified',     // Verified by second person
                'adjusted'      // Adjustment created
            ])->default('pending');
            $table->foreignUuid('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['inventory_session_id', 'product_id', 'location']);
            $table->index(['inventory_session_id', 'status']);
        });

        // Stock alerts (Alertes de stock)
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'low_stock',        // Stock faible
                'out_of_stock',     // Rupture
                'overstock',        // Sur-stockage
                'expiring_soon',    // Péremption proche
                'expired',          // Périmé
                'reorder_point'     // Point de réapprovisionnement
            ]);
            $table->decimal('current_quantity', 15, 4)->nullable();
            $table->decimal('threshold_quantity', 15, 4)->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'is_resolved']);
            $table->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('inventory_lines');
        Schema::dropIfExists('inventory_sessions');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('warehouses');
    }
};
