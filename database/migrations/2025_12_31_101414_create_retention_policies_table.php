<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Table pour gérer les politiques de rétention légale des documents
     * conformément à la législation belge (TVA 10 ans, documents comptables 7 ans, etc.)
     */
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 100)->unique()->comment('Type de document (invoice, expense, vat_declaration, etc.)');
            $table->integer('retention_years')->comment('Durée de conservation en années');
            $table->string('legal_basis')->comment('Base légale (ex: AR TVA art. 60, C. soc. art. 3:17)');
            $table->boolean('permanent')->default(false)->comment('Conservation permanente (fiches de paie, PV AG)');
            $table->boolean('anonymize_after')->default(true)->comment('Anonymiser après expiration (RGPD)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retention_policies');
    }
};
