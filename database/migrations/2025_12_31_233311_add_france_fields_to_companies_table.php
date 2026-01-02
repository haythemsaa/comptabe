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
        Schema::table('companies', function (Blueprint $table) {
            // SIRET (Système d'Identification du Répertoire des Établissements)
            $table->string('siret', 14)->nullable()->after('vat_number')
                ->comment('SIRET - 14 chiffres (SIREN 9 + NIC 5)');

            // SIREN (Système d'Identification du Répertoire des Entreprises)
            $table->string('siren', 9)->nullable()->after('siret')
                ->comment('SIREN - 9 chiffres');

            // Code APE/NAF (Nomenclature des Activités Françaises)
            $table->string('ape_code', 6)->nullable()->after('siren')
                ->comment('Code APE/NAF (ex: 6201Z)');

            // Numéro URSSAF
            $table->string('urssaf_number', 20)->nullable()->after('ape_code')
                ->comment('Numéro d\'affiliation URSSAF');

            // Convention collective
            $table->string('convention_collective', 100)->nullable()->after('urssaf_number')
                ->comment('Convention collective applicable (IDCC)');

            // Index pour recherches
            $table->index('siret');
            $table->index('siren');
        });

        Schema::table('employees', function (Blueprint $table) {
            // Numéro de Sécurité Sociale (NIR)
            $table->string('social_security_number', 15)->nullable()->after('email')
                ->comment('Numéro de Sécurité Sociale (15 chiffres)');

            // Mutuelle obligatoire
            $table->string('mutuelle', 50)->nullable()->after('social_security_number')
                ->comment('Nom de la mutuelle');

            // Taux de prélèvement à la source
            $table->decimal('taux_prelevement', 5, 2)->nullable()->after('mutuelle')
                ->comment('Taux de prélèvement à la source (%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['siret']);
            $table->dropIndex(['siren']);
            $table->dropColumn([
                'siret',
                'siren',
                'ape_code',
                'urssaf_number',
                'convention_collective',
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'social_security_number',
                'mutuelle',
                'taux_prelevement',
            ]);
        });
    }
};
