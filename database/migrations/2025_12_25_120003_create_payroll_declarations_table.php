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
        Schema::create('payroll_declarations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');

            // Declaration Type
            $table->enum('type', [
                'dimona',           // Déclaration immédiate/Onmiddellijke Aangifte
                'dmfa',             // Déclaration Multi-Fonctionnelle
                'tax_281_10',       // Fiche 281.10 (salaires)
                'tax_281_20',       // Fiche 281.20 (commissions)
                'annual_account',   // Compte individuel
            ]);

            // Period
            $table->string('period', 7)->nullable(); // YYYY-MM or YYYY-QQ
            $table->integer('year');
            $table->integer('quarter')->nullable();

            // Reference
            $table->string('declaration_number', 50)->unique();

            // Status
            $table->enum('status', ['draft', 'ready', 'submitted', 'accepted', 'rejected'])->default('draft');

            // Submission
            $table->timestamp('submitted_at')->nullable();
            $table->string('submission_reference')->nullable();
            $table->string('submission_channel')->nullable(); // web, api, file

            // Response
            $table->text('response_message')->nullable();
            $table->json('response_data')->nullable();

            // File Paths
            $table->string('xml_file_path')->nullable();
            $table->string('pdf_file_path')->nullable();

            // Data
            $table->json('declaration_data'); // Full declaration content
            $table->integer('employees_count')->default(0);
            $table->decimal('total_gross_salary', 12, 2)->default(0);
            $table->decimal('total_employee_contributions', 12, 2)->default(0);
            $table->decimal('total_employer_contributions', 12, 2)->default(0);

            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'type', 'year', 'quarter']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_declarations');
    }
};
