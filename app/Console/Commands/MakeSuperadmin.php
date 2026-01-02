<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Command pour donner les droits superadmin à un utilisateur
 *
 * Usage: php artisan user:make-superadmin {email} [--accountant]
 */
class MakeSuperadmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-superadmin
                            {email : Email de l\'utilisateur}
                            {--accountant : Définir comme Expert-Comptable}
                            {--remove : Retirer les droits superadmin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Donne ou retire les droits superadmin à un utilisateur';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $remove = $this->option('remove');
        $accountant = $this->option('accountant');

        // Trouver l'utilisateur
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ Utilisateur '{$email}' introuvable.");
            return Command::FAILURE;
        }

        $this->info("👤 Utilisateur trouvé:");
        $this->info("   Nom: {$user->first_name} {$user->last_name}");
        $this->info("   Email: {$user->email}");
        $this->info("   Type actuel: {$user->user_type}");
        $this->info("   Superadmin actuel: " . ($user->is_superadmin ? '✅ OUI' : '❌ NON'));

        if ($remove) {
            // Retirer droits superadmin
            if (!$user->is_superadmin) {
                $this->warn("⚠️  L'utilisateur n'est pas superadmin.");
                return Command::SUCCESS;
            }

            if (!$this->confirm("Êtes-vous sûr de vouloir retirer les droits superadmin à {$user->email} ?")) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }

            $user->update(['is_superadmin' => false]);
            $this->info("✅ Droits superadmin retirés avec succès.");

            return Command::SUCCESS;
        }

        // Donner droits superadmin
        $updates = ['is_superadmin' => true];

        if ($accountant) {
            $updates['user_type'] = 'accountant';

            // Demander le titre professionnel
            $title = $this->choice(
                'Titre professionnel',
                [
                    'expert_comptable' => 'Expert-Comptable',
                    'conseil_fiscal' => 'Conseil Fiscal',
                    'reviseur' => 'Réviseur d\'Entreprises',
                    'comptable_agree' => 'Comptable Agréé',
                ],
                'expert_comptable'
            );
            $updates['professional_title'] = $title;

            // Demander les numéros professionnels
            if ($this->confirm('Ajouter le numéro ITAA (Institut des Experts-Comptables Belgique) ?')) {
                $itaa = $this->ask('Numéro ITAA (ex: 12345)');
                if ($itaa) {
                    $updates['itaa_number'] = $itaa;
                }
            }

            if ($this->confirm('Ajouter le numéro IRE (Institut des Réviseurs d\'Entreprises) ?')) {
                $ire = $this->ask('Numéro IRE (ex: B-12345)');
                if ($ire) {
                    $updates['ire_number'] = $ire;
                }
            }
        }

        if (!$this->confirm("Confirmer la création de superadmin pour {$user->email} ?")) {
            $this->info('Opération annulée.');
            return Command::SUCCESS;
        }

        $user->update($updates);

        $this->newLine();
        $this->info("✅ Superadmin créé avec succès!");
        $this->newLine();
        $this->info("📋 Récapitulatif:");
        $this->info("   Email: {$user->email}");
        $this->info("   Superadmin: ✅ OUI");
        $this->info("   Type: {$user->user_type}");

        if ($accountant) {
            $this->info("   Titre: " . User::PROFESSIONAL_TITLE_LABELS[$user->professional_title] ?? $user->professional_title);
            if ($user->itaa_number) {
                $this->info("   ITAA: {$user->itaa_number}");
            }
            if ($user->ire_number) {
                $this->info("   IRE: {$user->ire_number}");
            }
        }

        $this->newLine();
        $this->warn("⚠️  ATTENTION: Les superadmins ont un accès TOTAL à toutes les companies!");
        $this->warn("   Ils peuvent contourner le TenantScope et voir toutes les données.");

        return Command::SUCCESS;
    }
}
