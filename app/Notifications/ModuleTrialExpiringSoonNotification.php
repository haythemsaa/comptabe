<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class ModuleTrialExpiringSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Company $company,
        public Module $module,
        public Carbon $expiresAt,
        public int $daysLeft
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urgencyEmoji = $this->daysLeft <= 3 ? '!!' : '';

        $message = (new MailMessage)
            ->subject("{$urgencyEmoji}Essai du module {$this->module->name} expire bientôt")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("La période d'essai du module **{$this->module->name}** expire dans **{$this->daysLeft} jour(s)**.")
            ->line("")
            ->line("**Détails :**")
            ->line("- Module : {$this->module->name}")
            ->line("- Entreprise : {$this->company->name}")
            ->line("- Fin de l'essai : " . $this->expiresAt->format('d/m/Y'));

        if ($this->module->monthly_price > 0) {
            $message->line("- Prix après essai : " . number_format($this->module->monthly_price, 2, ',', ' ') . " EUR/mois");
        }

        if ($this->daysLeft <= 3) {
            $message->line("")
                ->line("**Action requise :**")
                ->line("Pour continuer à utiliser ce module, veuillez contacter l'administrateur pour passer à un abonnement payant.");
        }

        $message->action('Voir mes modules', route('modules.my-modules'))
            ->line('Contactez votre administrateur pour prolonger ou activer définitivement ce module.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'module_trial_expiring',
            'severity' => $this->daysLeft <= 3 ? 'warning' : 'info',
            'title' => 'Essai de module expire bientôt',
            'message' => "L'essai du module {$this->module->name} expire dans {$this->daysLeft} jour(s)",
            'module_id' => $this->module->id,
            'module_name' => $this->module->name,
            'module_code' => $this->module->code,
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'expires_at' => $this->expiresAt->toISOString(),
            'days_left' => $this->daysLeft,
            'action_url' => route('modules.my-modules'),
            'action_text' => 'Voir mes modules',
            'icon' => 'clock',
            'color' => $this->daysLeft <= 3 ? 'warning' : 'info',
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'module_trial_expiring';
    }
}
