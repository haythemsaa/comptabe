<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invitation;
use App\Models\User;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInvitation;

class InviteUserTool extends AbstractTool
{
    public function getName(): string
    {
        return 'invite_user';
    }

    public function getDescription(): string
    {
        return 'Invites a user to join the company. Sends an invitation email with a registration link. Only admins can invite users.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'description' => 'Email address of the person to invite',
                ],
                'role' => [
                    'type' => 'string',
                    'enum' => ['owner', 'admin', 'accountant', 'user', 'viewer'],
                    'description' => 'Role to assign to the invited user (default: user)',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Optional name of the person being invited (for personalization)',
                ],
                'message' => [
                    'type' => 'string',
                    'description' => 'Optional personal message to include in the invitation email',
                ],
            ],
            'required' => ['email'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        // Require confirmation before sending invitation
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Check if user is admin in current company
        if (!$context->user->isAdminInCurrentTenant()) {
            return [
                'error' => 'Seuls les administrateurs peuvent inviter des utilisateurs.',
                'required_permission' => 'admin',
            ];
        }

        $email = strtolower(trim($input['email']));

        // Check if user already exists in the company
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->companies()->where('company_id', $context->company->id)->exists()) {
            return [
                'error' => "L'utilisateur {$email} fait déjà partie de cette entreprise.",
                'user' => [
                    'name' => $existingUser->name,
                    'email' => $existingUser->email,
                ],
            ];
        }

        // Check if there's already a pending invitation
        $existingInvitation = Invitation::where('company_id', $context->company->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return [
                'error' => "Une invitation est déjà en attente pour {$email}.",
                'invitation' => [
                    'sent_at' => $existingInvitation->created_at->format('d/m/Y H:i'),
                    'expires_at' => $existingInvitation->expires_at->format('d/m/Y H:i'),
                ],
                'suggestion' => 'Vous pouvez renvoyer l\'invitation ou attendre son expiration.',
            ];
        }

        // Create invitation
        $invitation = Invitation::create([
            'company_id' => $context->company->id,
            'invited_by' => $context->user->id,
            'email' => $email,
            'role' => $input['role'] ?? 'user',
        ]);

        // Send invitation email
        try {
            Mail::to($email)->send(new UserInvitation(
                $invitation,
                $context->company,
                $context->user,
                $input['name'] ?? null,
                $input['message'] ?? null
            ));

            $emailSent = true;
        } catch (\Exception $e) {
            $emailSent = false;
            \Log::error('Failed to send invitation email', [
                'invitation_id' => $invitation->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        // Role descriptions
        $roleDescriptions = [
            'owner' => 'Propriétaire (accès complet, gestion facturation)',
            'admin' => 'Administrateur (accès complet sauf facturation)',
            'accountant' => 'Comptable (gestion comptabilité et factures)',
            'user' => 'Utilisateur (accès lecture/écriture limité)',
            'viewer' => 'Observateur (accès lecture seule)',
        ];

        return [
            'success' => true,
            'message' => $emailSent
                ? "Invitation envoyée à {$email} avec le rôle '{$invitation->role}'"
                : "Invitation créée mais l'email n'a pas pu être envoyé. Vérifiez la configuration email.",
            'invitation' => [
                'id' => $invitation->id,
                'email' => $email,
                'role' => $invitation->role,
                'role_description' => $roleDescriptions[$invitation->role] ?? $invitation->role,
                'invited_by' => $context->user->name,
                'expires_at' => $invitation->expires_at->format('d/m/Y H:i'),
                'invitation_link' => url("/invitations/{$invitation->token}"),
            ],
            'email_sent' => $emailSent,
            'next_steps' => $emailSent
                ? ["L'utilisateur recevra un email avec un lien pour créer son compte"]
                : ["Copiez le lien d'invitation et envoyez-le manuellement à l'utilisateur"],
        ];
    }
}
