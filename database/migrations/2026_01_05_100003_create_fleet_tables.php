<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Véhicules
        if (!Schema::hasTable('vehicles')) {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete(); // Lien vers immobilisation
            $table->uuid('assigned_user_id')->nullable(); // Employé assigné
            $table->string('reference')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('vin')->nullable(); // Vehicle Identification Number
            $table->string('brand');
            $table->string('model');
            $table->integer('year')->nullable();
            $table->enum('type', ['car', 'van', 'truck', 'motorcycle', 'electric_bike', 'other'])->default('car');
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric', 'lpg', 'cng', 'hydrogen'])->default('petrol');
            $table->enum('ownership', ['owned', 'leased', 'rented', 'employee_owned'])->default('owned');
            $table->integer('co2_emission')->nullable(); // g/km pour ATN belge
            $table->enum('emission_standard', ['euro1', 'euro2', 'euro3', 'euro4', 'euro5', 'euro6', 'euro6d'])->nullable();
            $table->integer('fiscal_horsepower')->nullable(); // CV fiscaux
            $table->integer('engine_power_kw')->nullable();
            $table->integer('battery_capacity_kwh')->nullable(); // Pour électriques
            $table->decimal('catalog_value', 12, 2)->nullable(); // Valeur catalogue pour ATN
            $table->decimal('options_value', 12, 2)->default(0); // Valeur options pour ATN
            $table->date('first_registration_date')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->date('disposal_date')->nullable();
            $table->integer('odometer_start')->default(0);
            $table->integer('odometer_current')->default(0);
            $table->enum('status', ['active', 'maintenance', 'disposed', 'sold'])->default('active');
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->date('technical_inspection_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['company_id', 'status']);
        });
        }

        // ATN (Avantage de Toute Nature) - Calcul belge
        if (!Schema::hasTable('vehicle_atn')) {
        Schema::create('vehicle_atn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id'); // Employé bénéficiaire
            $table->integer('year');
            $table->integer('month');
            $table->decimal('catalog_value', 12, 2);
            $table->decimal('co2_reference', 5, 2); // Référence CO2 de l'année
            $table->decimal('co2_coefficient', 5, 4); // 5.5% + (CO2 - ref) * 0.1%
            $table->decimal('minimum_coefficient', 5, 4)->default(0.04); // Minimum 4%
            $table->decimal('age_coefficient', 5, 4); // Réduction pour ancienneté (6% par an, max 30%)
            $table->decimal('atn_amount', 12, 2); // Montant ATN mensuel
            $table->decimal('atn_annual', 12, 2); // Montant ATN annuel
            $table->decimal('employer_solidarity_contribution', 12, 2)->nullable(); // Cotisation de solidarité
            $table->boolean('is_calculated')->default(false);
            $table->json('calculation_details')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['vehicle_id', 'year', 'month']);
            $table->index(['user_id', 'year']);
        });
        }

        // Contrats de leasing/location
        if (!Schema::hasTable('vehicle_contracts')) {
        Schema::create('vehicle_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->uuid('partner_id')->nullable(); // Société de leasing
            $table->string('contract_number')->nullable();
            $table->enum('type', ['leasing', 'renting', 'loan'])->default('leasing');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months');
            $table->integer('annual_km_limit')->nullable();
            $table->decimal('monthly_payment', 12, 2);
            $table->decimal('deposit', 12, 2)->default(0);
            $table->decimal('residual_value', 12, 2)->nullable();
            $table->decimal('purchase_option', 12, 2)->nullable();
            $table->boolean('includes_maintenance')->default(false);
            $table->boolean('includes_insurance')->default(false);
            $table->boolean('includes_tyres')->default(false);
            $table->boolean('includes_fuel_card')->default(false);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
        });
        }

        // Relevés kilométriques
        if (!Schema::hasTable('vehicle_odometer_readings')) {
        Schema::create('vehicle_odometer_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id')->nullable();
            $table->date('reading_date');
            $table->integer('odometer_value');
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['vehicle_id', 'reading_date']);
        });
        }

        // Dépenses véhicule (carburant, entretien, etc.)
        if (!Schema::hasTable('vehicle_expenses')) {
        Schema::create('vehicle_expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id')->nullable();
            $table->foreignId('expense_id')->nullable(); // Lien vers note de frais
            $table->uuid('invoice_id')->nullable();
            $table->enum('type', [
                'fuel', 'maintenance', 'repair', 'insurance', 'tax',
                'parking', 'toll', 'washing', 'tyre', 'fine', 'other'
            ]);
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('quantity', 10, 3)->nullable(); // Litres pour carburant
            $table->decimal('unit_price', 10, 4)->nullable();
            $table->integer('odometer')->nullable();
            $table->string('supplier')->nullable();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_private_use')->default(false); // Usage privé vs professionnel
            $table->decimal('private_use_percent', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->index(['company_id', 'expense_date']);
            $table->index(['vehicle_id', 'type']);
        });
        }

        // Réservations de véhicules
        if (!Schema::hasTable('vehicle_reservations')) {
        Schema::create('vehicle_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id');
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->string('purpose')->nullable();
            $table->string('destination')->nullable();
            $table->integer('expected_km')->nullable();
            $table->integer('start_odometer')->nullable();
            $table->integer('end_odometer')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['vehicle_id', 'start_datetime']);
        });
        }

        // Rappels (assurance, contrôle technique, etc.)
        if (!Schema::hasTable('vehicle_reminders')) {
        Schema::create('vehicle_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'insurance', 'technical_inspection', 'maintenance',
                'oil_change', 'tyre_change', 'registration', 'other'
            ]);
            $table->date('due_date');
            $table->integer('reminder_days_before')->default(30);
            $table->boolean('is_recurring')->default(false);
            $table->integer('recurrence_months')->nullable();
            $table->enum('status', ['pending', 'notified', 'completed', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'due_date']);
            $table->index('status');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_reminders');
        Schema::dropIfExists('vehicle_reservations');
        Schema::dropIfExists('vehicle_expenses');
        Schema::dropIfExists('vehicle_odometer_readings');
        Schema::dropIfExists('vehicle_contracts');
        Schema::dropIfExists('vehicle_atn');
        Schema::dropIfExists('vehicles');
    }
};
