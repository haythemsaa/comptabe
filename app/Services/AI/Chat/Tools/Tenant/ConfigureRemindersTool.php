<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\InvoiceReminderConfig;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class ConfigureRemindersTool extends AbstractTool
{
    public function getName(): string
    {
        return 'configure_invoice_reminders';
    }

    public function getDescription(): string
    {
        return 'Configures automatic payment reminders for overdue invoices. Set up when to send reminders, apply late fees, and customize email templates.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'is_enabled' => [
                    'type' => 'boolean',
                    'description' => 'Enable or disable automatic reminders',
                ],
                'send_before_due' => [
                    'type' => 'boolean',
                    'description' => 'Send reminder before due date',
                ],
                'days_before_due' => [
                    'type' => 'integer',
                    'description' => 'Days before due date to send reminder (default: 7)',
                ],
                'send_on_due_date' => [
                    'type' => 'boolean',
                    'description' => 'Send reminder on due date',
                ],
                'send_first_overdue' => [
                    'type' => 'boolean',
                    'description' => 'Send first overdue reminder',
                ],
                'days_after_due_first' => [
                    'type' => 'integer',
                    'description' => 'Days after due for first reminder (default: 7)',
                ],
                'send_second_overdue' => [
                    'type' => 'boolean',
                    'description' => 'Send second overdue reminder',
                ],
                'days_after_due_second' => [
                    'type' => 'integer',
                    'description' => 'Days after due for second reminder (default: 15)',
                ],
                'send_final_reminder' => [
                    'type' => 'boolean',
                    'description' => 'Send final overdue reminder',
                ],
                'days_after_due_final' => [
                    'type' => 'integer',
                    'description' => 'Days after due for final reminder (default: 30)',
                ],
                'apply_late_fees' => [
                    'type' => 'boolean',
                    'description' => 'Apply late payment fees',
                ],
                'late_fee_percentage' => [
                    'type' => 'number',
                    'description' => 'Late fee as percentage (e.g., 2.0 for 2%)',
                ],
                'late_fee_fixed' => [
                    'type' => 'number',
                    'description' => 'Fixed late fee amount in euros',
                ],
                'apply_interest' => [
                    'type' => 'boolean',
                    'description' => 'Apply interest on overdue amounts',
                ],
                'interest_rate_annual' => [
                    'type' => 'number',
                    'description' => 'Annual interest rate (default: 10% Belgian legal rate)',
                ],
            ],
            'required' => ['is_enabled'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Get or create config
        $config = InvoiceReminderConfig::getOrCreateForCompany($context->company);

        // Update configuration
        $config->update([
            'is_enabled' => $input['is_enabled'],
            'send_before_due' => $input['send_before_due'] ?? $config->send_before_due,
            'days_before_due' => $input['days_before_due'] ?? $config->days_before_due,
            'send_on_due_date' => $input['send_on_due_date'] ?? $config->send_on_due_date,
            'send_first_overdue' => $input['send_first_overdue'] ?? $config->send_first_overdue,
            'days_after_due_first' => $input['days_after_due_first'] ?? $config->days_after_due_first,
            'send_second_overdue' => $input['send_second_overdue'] ?? $config->send_second_overdue,
            'days_after_due_second' => $input['days_after_due_second'] ?? $config->days_after_due_second,
            'send_final_reminder' => $input['send_final_reminder'] ?? $config->send_final_reminder,
            'days_after_due_final' => $input['days_after_due_final'] ?? $config->days_after_due_final,
            'apply_late_fees' => $input['apply_late_fees'] ?? $config->apply_late_fees,
            'late_fee_percentage' => $input['late_fee_percentage'] ?? $config->late_fee_percentage,
            'late_fee_fixed' => $input['late_fee_fixed'] ?? $config->late_fee_fixed,
            'apply_interest' => $input['apply_interest'] ?? $config->apply_interest,
            'interest_rate_annual' => $input['interest_rate_annual'] ?? $config->interest_rate_annual,
        ]);

        // Build schedule summary
        $schedule = [];

        if ($config->send_before_due) {
            $schedule[] = "{$config->days_before_due} jours avant échéance : Rappel préventif";
        }

        if ($config->send_on_due_date) {
            $schedule[] = "À l'échéance : Rappel échéance";
        }

        if ($config->send_first_overdue) {
            $schedule[] = "{$config->days_after_due_first} jours de retard : 1ère relance";
        }

        if ($config->send_second_overdue) {
            $schedule[] = "{$config->days_after_due_second} jours de retard : 2ème relance";
        }

        if ($config->send_final_reminder) {
            $schedule[] = "{$config->days_after_due_final} jours de retard : Relance finale";
        }

        // Build fees summary
        $fees = [];

        if ($config->apply_late_fees) {
            if ($config->late_fee_percentage) {
                $fees[] = "Frais de retard : {$config->late_fee_percentage}% du montant";
            }
            if ($config->late_fee_fixed) {
                $fees[] = "Frais fixes : " . number_format($config->late_fee_fixed, 2) . " €";
            }
        }

        if ($config->apply_interest) {
            $fees[] = "Intérêts de retard : {$config->interest_rate_annual}% par an (taux légal belge)";
        }

        return [
            'success' => true,
            'message' => $input['is_enabled']
                ? 'Relances automatiques activées'
                : 'Relances automatiques désactivées',
            'configuration' => [
                'status' => $input['is_enabled'] ? 'Activé' : 'Désactivé',
                'reminder_schedule' => $schedule,
                'fees_and_penalties' => $fees ?: ['Aucun frais appliqué'],
            ],
            'reminders_sent' => empty($schedule) ? 0 : count($schedule),
            'next_steps' => $input['is_enabled'] ? [
                'Les relances seront envoyées automatiquement selon le calendrier défini',
                'Les factures impayées seront suivies automatiquement',
                'Vous recevrez des notifications pour chaque relance envoyée',
                'Personnalisez les templates d\'email si nécessaire',
            ] : [
                'Les relances automatiques sont désactivées',
                'Activez-les pour automatiser le suivi des impayés',
            ],
        ];
    }
}
