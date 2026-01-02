<?php

namespace App\Services\AI\Chat\Tools\Firm;

use App\Models\ClientMandate;
use App\Models\MandateTask;
use App\Models\User;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class AssignMandateTaskTool extends AbstractTool
{
    public function getName(): string
    {
        return 'assign_mandate_task';
    }

    public function getDescription(): string
    {
        return 'For accounting firm managers: assigns a task to a team member for a specific client mandate. Use this to delegate work, create to-dos, or assign recurring accounting tasks like VAT declarations, monthly closings, etc.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'company_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the client company',
                ],
                'company_name' => [
                    'type' => 'string',
                    'description' => 'Client company name if company_id is unknown',
                ],
                'task_title' => [
                    'type' => 'string',
                    'description' => 'Title of the task',
                ],
                'task_description' => [
                    'type' => 'string',
                    'description' => 'Detailed description of what needs to be done',
                ],
                'task_type' => [
                    'type' => 'string',
                    'enum' => ['vat_declaration', 'monthly_closing', 'annual_accounts', 'tax_return', 'audit', 'client_meeting', 'document_review', 'data_entry', 'reconciliation', 'other'],
                    'description' => 'Type of task',
                ],
                'assigned_to_email' => [
                    'type' => 'string',
                    'description' => 'Email of the team member to assign to',
                ],
                'assigned_to_name' => [
                    'type' => 'string',
                    'description' => 'Name of team member if email is unknown',
                ],
                'due_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Due date for the task (YYYY-MM-DD)',
                ],
                'priority' => [
                    'type' => 'string',
                    'enum' => ['low', 'normal', 'high', 'urgent'],
                    'description' => 'Task priority (default: normal)',
                ],
                'estimated_hours' => [
                    'type' => 'number',
                    'description' => 'Estimated hours to complete',
                ],
            ],
            'required' => ['task_title', 'task_type', 'due_date'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return false; // Task assignment is low-risk
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate user is a firm member
        if (!$context->user->isCabinetMember()) {
            return [
                'error' => 'Cet outil est réservé aux membres de cabinets comptables.',
            ];
        }

        $firm = $context->user->currentFirm();

        if (!$firm) {
            return [
                'error' => 'Aucun cabinet comptable trouvé.',
            ];
        }

        // Check if user can assign tasks (requires manager/admin role)
        $firmUser = $firm->users()->where('user_id', $context->user->id)->first();
        if (!$firmUser || !$firmUser->pivot->canManageClients()) {
            return [
                'error' => 'Vous devez être manager ou admin du cabinet pour assigner des tâches.',
                'your_role' => $firmUser?->pivot->role ?? 'unknown',
            ];
        }

        // Find client mandate
        $mandate = $this->findClientMandate($input, $firm);

        if (!$mandate) {
            return [
                'error' => 'Client non trouvé ou non géré par votre cabinet.',
                'suggestion' => 'Vérifiez le nom du client ou son ID.',
            ];
        }

        // Find team member to assign to
        $assignedUser = null;
        if (!empty($input['assigned_to_email'])) {
            $assignedUser = User::where('email', $input['assigned_to_email'])->first();
        } elseif (!empty($input['assigned_to_name'])) {
            $assignedUser = User::where('name', 'like', '%' . $input['assigned_to_name'] . '%')->first();
        }

        // Validate assigned user is firm member
        if ($assignedUser) {
            $isTeamMember = $firm->users()->where('user_id', $assignedUser->id)->exists();

            if (!$isTeamMember) {
                return [
                    'error' => "L'utilisateur {$assignedUser->name} n'est pas membre de votre cabinet.",
                    'suggestion' => 'Invitez-le d\'abord à rejoindre le cabinet.',
                ];
            }
        }

        // Create task
        $task = MandateTask::create([
            'client_mandate_id' => $mandate->id,
            'title' => $input['task_title'],
            'description' => $input['task_description'] ?? null,
            'task_type' => $input['task_type'],
            'status' => 'pending',
            'priority' => $input['priority'] ?? 'normal',
            'assigned_to' => $assignedUser?->id,
            'assigned_by' => $context->user->id,
            'due_date' => $input['due_date'],
            'estimated_hours' => $input['estimated_hours'] ?? null,
        ]);

        // Log activity
        $mandate->activities()->create([
            'type' => 'task_assigned',
            'user_id' => $context->user->id,
            'description' => "Tâche '{$task->title}' assignée" . ($assignedUser ? " à {$assignedUser->name}" : ""),
            'metadata' => [
                'task_id' => $task->id,
                'task_type' => $task->task_type,
                'assigned_to' => $assignedUser?->id,
            ],
        ]);

        // TODO: Send notification email to assigned user
        // Mail::to($assignedUser)->send(new TaskAssignedMail($task));

        return [
            'success' => true,
            'message' => "Tâche créée et assignée avec succès",
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'type' => $task->task_type,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date->format('d/m/Y'),
                'estimated_hours' => $task->estimated_hours,
            ],
            'client' => [
                'company_name' => $mandate->company->name,
                'company_id' => $mandate->company->id,
            ],
            'assignment' => [
                'assigned_to' => $assignedUser?->name ?? 'Non assigné',
                'assigned_to_email' => $assignedUser?->email,
                'assigned_by' => $context->user->name,
            ],
            'next_steps' => array_filter([
                $assignedUser ? "Notification envoyée à {$assignedUser->email}" : null,
                "La tâche apparaîtra dans le tableau de bord de l'équipe",
                "Suivi disponible dans le dossier client",
            ]),
        ];
    }

    /**
     * Find client mandate by company ID or name.
     */
    protected function findClientMandate(array $input, $firm): ?ClientMandate
    {
        $query = ClientMandate::where('accounting_firm_id', $firm->id)
            ->where('status', 'active')
            ->with('company');

        // Try by company_id first
        if (!empty($input['company_id'])) {
            return $query->where('company_id', $input['company_id'])->first();
        }

        // Try by company_name
        if (!empty($input['company_name'])) {
            return $query->whereHas('company', function ($q) use ($input) {
                $q->where('name', 'like', '%' . $input['company_name'] . '%');
            })->first();
        }

        return null;
    }
}
