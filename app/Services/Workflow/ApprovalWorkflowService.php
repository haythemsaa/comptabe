<?php

namespace App\Services\Workflow;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalRule;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use App\Notifications\ApprovalRequestNotification;
use App\Notifications\ApprovalDecisionNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class ApprovalWorkflowService
{
    /**
     * Types d'approbation supportés
     */
    const TYPE_INVOICE_PURCHASE = 'invoice_purchase';
    const TYPE_INVOICE_SALE = 'invoice_sale';
    const TYPE_EXPENSE = 'expense';
    const TYPE_PAYMENT = 'payment';
    const TYPE_JOURNAL_ENTRY = 'journal_entry';

    /**
     * Conditions d'escalade
     */
    const ESCALATE_TIMEOUT = 'timeout';
    const ESCALATE_REJECTION = 'rejection';
    const ESCALATE_AMOUNT = 'amount';

    /**
     * Créer une demande d'approbation
     */
    public function createRequest(Model $approvable, ?string $notes = null): ApprovalRequest
    {
        $type = $this->getApprovableType($approvable);
        $companyId = $approvable->company_id;
        $amount = $this->getApprovableAmount($approvable);

        // Trouver le workflow applicable
        $workflow = $this->findApplicableWorkflow($companyId, $type, $amount);

        if (!$workflow) {
            throw new \Exception("Aucun workflow d'approbation trouvé pour ce type de document");
        }

        return DB::transaction(function () use ($approvable, $workflow, $amount, $notes) {
            // Créer la demande
            $request = ApprovalRequest::create([
                'company_id' => $approvable->company_id,
                'workflow_id' => $workflow->id,
                'approvable_type' => get_class($approvable),
                'approvable_id' => $approvable->id,
                'requester_id' => Auth::id(),
                'amount' => $amount,
                'notes' => $notes,
                'status' => 'pending',
                'current_step' => 1,
                'expires_at' => $workflow->timeout_hours
                    ? now()->addHours($workflow->timeout_hours)
                    : null,
            ]);

            // Créer les étapes d'approbation
            $this->createApprovalSteps($request, $workflow);

            // Notifier les approbateurs de la première étape
            $this->notifyCurrentApprovers($request);

            // Mettre à jour le statut de l'objet
            $approvable->update(['approval_status' => 'pending_approval']);

            return $request;
        });
    }

    /**
     * Approuver une demande
     */
    public function approve(ApprovalRequest $request, ?string $comment = null): void
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur peut approuver
        if (!$this->canUserApprove($request, $user)) {
            throw new \Exception("Vous n'êtes pas autorisé à approuver cette demande");
        }

        DB::transaction(function () use ($request, $user, $comment) {
            // Enregistrer la décision
            $step = $this->getCurrentStep($request);
            $step->update([
                'decision' => 'approved',
                'decided_by' => $user->id,
                'decided_at' => now(),
                'comment' => $comment,
            ]);

            // Vérifier si toutes les approbations de cette étape sont complètes
            if ($this->isStepComplete($request, $step->step_number)) {
                $this->advanceToNextStep($request);
            }
        });
    }

    /**
     * Rejeter une demande
     */
    public function reject(ApprovalRequest $request, string $reason): void
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur peut rejeter
        if (!$this->canUserApprove($request, $user)) {
            throw new \Exception("Vous n'êtes pas autorisé à rejeter cette demande");
        }

        DB::transaction(function () use ($request, $user, $reason) {
            // Enregistrer le rejet
            $step = $this->getCurrentStep($request);
            $step->update([
                'decision' => 'rejected',
                'decided_by' => $user->id,
                'decided_at' => now(),
                'comment' => $reason,
            ]);

            // Vérifier s'il y a une escalade possible
            $workflow = $request->workflow;
            if ($workflow->escalate_on_rejection && $this->canEscalate($request)) {
                $this->escalateRequest($request, 'rejection');
            } else {
                // Marquer comme rejetée
                $request->update([
                    'status' => 'rejected',
                    'completed_at' => now(),
                ]);

                // Mettre à jour l'objet
                $request->approvable->update(['approval_status' => 'rejected']);

                // Notifier le demandeur
                $this->notifyRequester($request, 'rejected', $reason);
            }
        });
    }

    /**
     * Demander des modifications
     */
    public function requestChanges(ApprovalRequest $request, string $changes): void
    {
        $user = Auth::user();

        if (!$this->canUserApprove($request, $user)) {
            throw new \Exception("Vous n'êtes pas autorisé à demander des modifications");
        }

        DB::transaction(function () use ($request, $user, $changes) {
            $step = $this->getCurrentStep($request);
            $step->update([
                'decision' => 'changes_requested',
                'decided_by' => $user->id,
                'decided_at' => now(),
                'comment' => $changes,
            ]);

            $request->update([
                'status' => 'changes_requested',
            ]);

            // Mettre à jour l'objet
            $request->approvable->update(['approval_status' => 'changes_requested']);

            // Notifier le demandeur
            $this->notifyRequester($request, 'changes_requested', $changes);
        });
    }

    /**
     * Soumettre à nouveau après modifications
     */
    public function resubmit(ApprovalRequest $request): void
    {
        if ($request->status !== 'changes_requested') {
            throw new \Exception("Cette demande ne peut pas être soumise à nouveau");
        }

        if ($request->requester_id !== Auth::id()) {
            throw new \Exception("Seul le demandeur peut soumettre à nouveau");
        }

        DB::transaction(function () use ($request) {
            // Réinitialiser l'étape courante
            $request->update([
                'status' => 'pending',
            ]);

            // Réinitialiser la décision de l'étape
            $step = $this->getCurrentStep($request);
            $step->update([
                'decision' => null,
                'decided_by' => null,
                'decided_at' => null,
                'comment' => null,
            ]);

            // Mettre à jour l'objet
            $request->approvable->update(['approval_status' => 'pending_approval']);

            // Re-notifier les approbateurs
            $this->notifyCurrentApprovers($request);
        });
    }

    /**
     * Annuler une demande
     */
    public function cancel(ApprovalRequest $request): void
    {
        if (!in_array($request->status, ['pending', 'changes_requested'])) {
            throw new \Exception("Cette demande ne peut pas être annulée");
        }

        if ($request->requester_id !== Auth::id()) {
            throw new \Exception("Seul le demandeur peut annuler la demande");
        }

        $request->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        $request->approvable->update(['approval_status' => 'draft']);
    }

    /**
     * Déléguer l'approbation à un autre utilisateur
     */
    public function delegate(ApprovalRequest $request, User $delegate, ?Carbon $until = null): void
    {
        $user = Auth::user();

        if (!$this->canUserApprove($request, $user)) {
            throw new \Exception("Vous n'êtes pas autorisé à déléguer cette demande");
        }

        $step = $this->getCurrentStep($request);

        // Vérifier que le délégué peut approuver
        if (!$this->canUserBeDelegate($delegate, $step)) {
            throw new \Exception("Cet utilisateur ne peut pas recevoir de délégation");
        }

        $step->update([
            'delegated_to' => $delegate->id,
            'delegated_by' => $user->id,
            'delegation_expires_at' => $until,
        ]);

        // Notifier le délégué
        Notification::send($delegate, new ApprovalRequestNotification($request, 'delegated'));
    }

    /**
     * Vérifier les demandes expirées et les escalader
     */
    public function processExpiredRequests(): int
    {
        $expired = ApprovalRequest::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        $processed = 0;

        foreach ($expired as $request) {
            if ($request->workflow->escalate_on_timeout && $this->canEscalate($request)) {
                $this->escalateRequest($request, 'timeout');
            } else {
                // Auto-rejeter si pas d'escalade
                $request->update([
                    'status' => 'expired',
                    'completed_at' => now(),
                ]);
                $request->approvable->update(['approval_status' => 'expired']);
                $this->notifyRequester($request, 'expired');
            }
            $processed++;
        }

        return $processed;
    }

    // ===== MÉTHODES PRIVÉES =====

    protected function getApprovableType(Model $approvable): string
    {
        return match(get_class($approvable)) {
            Invoice::class => $approvable->type === 'purchase'
                ? self::TYPE_INVOICE_PURCHASE
                : self::TYPE_INVOICE_SALE,
            Expense::class => self::TYPE_EXPENSE,
            default => throw new \Exception("Type d'objet non supporté pour l'approbation"),
        };
    }

    protected function getApprovableAmount(Model $approvable): float
    {
        return match(get_class($approvable)) {
            Invoice::class => $approvable->total_amount,
            Expense::class => $approvable->amount,
            default => 0,
        };
    }

    protected function findApplicableWorkflow(int $companyId, string $type, float $amount): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::where('company_id', $companyId)
            ->where('document_type', $type)
            ->where('is_active', true)
            ->where(function ($query) use ($amount) {
                $query->where(function ($q) use ($amount) {
                    $q->whereNotNull('min_amount')
                      ->whereNotNull('max_amount')
                      ->where('min_amount', '<=', $amount)
                      ->where('max_amount', '>=', $amount);
                })
                ->orWhere(function ($q) use ($amount) {
                    $q->whereNotNull('min_amount')
                      ->whereNull('max_amount')
                      ->where('min_amount', '<=', $amount);
                })
                ->orWhere(function ($q) {
                    $q->whereNull('min_amount')
                      ->whereNull('max_amount');
                });
            })
            ->orderByDesc('min_amount')
            ->first();
    }

    protected function createApprovalSteps(ApprovalRequest $request, ApprovalWorkflow $workflow): void
    {
        foreach ($workflow->rules as $rule) {
            ApprovalStep::create([
                'approval_request_id' => $request->id,
                'approval_rule_id' => $rule->id,
                'step_number' => $rule->step_order,
                'approver_type' => $rule->approver_type,
                'approver_id' => $rule->approver_id,
                'approver_role' => $rule->approver_role,
                'required_approvals' => $rule->required_approvals,
            ]);
        }
    }

    protected function getCurrentStep(ApprovalRequest $request): ApprovalStep
    {
        return ApprovalStep::where('approval_request_id', $request->id)
            ->where('step_number', $request->current_step)
            ->first();
    }

    protected function canUserApprove(ApprovalRequest $request, User $user): bool
    {
        if ($request->status !== 'pending') {
            return false;
        }

        $step = $this->getCurrentStep($request);

        // Vérifier la délégation
        if ($step->delegated_to === $user->id) {
            if (!$step->delegation_expires_at || $step->delegation_expires_at->isFuture()) {
                return true;
            }
        }

        // Vérifier le type d'approbateur
        return match($step->approver_type) {
            'user' => $step->approver_id === $user->id,
            'role' => $user->hasRole($step->approver_role),
            'department' => $user->department_id === $step->approver_id,
            'manager' => $this->isUserManager($user, $request->requester),
            default => false,
        };
    }

    protected function canUserBeDelegate(User $user, ApprovalStep $step): bool
    {
        // Le délégué doit avoir au moins le même niveau d'autorisation
        return match($step->approver_type) {
            'role' => $user->hasRole($step->approver_role) || $user->hasHigherRole($step->approver_role),
            default => true,
        };
    }

    protected function isUserManager(User $user, User $employee): bool
    {
        // Parcourir la hiérarchie
        $current = $employee;
        while ($current->manager_id) {
            if ($current->manager_id === $user->id) {
                return true;
            }
            $current = $current->manager;
        }
        return false;
    }

    protected function isStepComplete(ApprovalRequest $request, int $stepNumber): bool
    {
        $steps = ApprovalStep::where('approval_request_id', $request->id)
            ->where('step_number', $stepNumber)
            ->get();

        $requiredApprovals = $steps->first()->required_approvals ?? 1;
        $approvedCount = $steps->where('decision', 'approved')->count();

        return $approvedCount >= $requiredApprovals;
    }

    protected function advanceToNextStep(ApprovalRequest $request): void
    {
        $nextStep = ApprovalStep::where('approval_request_id', $request->id)
            ->where('step_number', '>', $request->current_step)
            ->orderBy('step_number')
            ->first();

        if ($nextStep) {
            $request->update([
                'current_step' => $nextStep->step_number,
            ]);
            $this->notifyCurrentApprovers($request);
        } else {
            // Toutes les étapes sont complètes
            $request->update([
                'status' => 'approved',
                'completed_at' => now(),
            ]);
            $request->approvable->update(['approval_status' => 'approved']);
            $this->notifyRequester($request, 'approved');
        }
    }

    protected function canEscalate(ApprovalRequest $request): bool
    {
        $maxEscalations = $request->workflow->max_escalations ?? 2;
        return $request->escalation_count < $maxEscalations;
    }

    protected function escalateRequest(ApprovalRequest $request, string $reason): void
    {
        $nextApprover = $this->findEscalationApprover($request);

        if (!$nextApprover) {
            // Pas d'approbateur supérieur, rejeter
            $request->update([
                'status' => 'rejected',
                'completed_at' => now(),
            ]);
            $request->approvable->update(['approval_status' => 'rejected']);
            $this->notifyRequester($request, 'rejected', "Escalade impossible - aucun approbateur disponible");
            return;
        }

        // Créer une nouvelle étape d'escalade
        $currentStep = $this->getCurrentStep($request);

        ApprovalStep::create([
            'approval_request_id' => $request->id,
            'step_number' => $currentStep->step_number,
            'approver_type' => 'user',
            'approver_id' => $nextApprover->id,
            'required_approvals' => 1,
            'is_escalation' => true,
            'escalation_reason' => $reason,
        ]);

        $request->increment('escalation_count');

        // Prolonger le délai si nécessaire
        if ($request->workflow->timeout_hours) {
            $request->update([
                'expires_at' => now()->addHours($request->workflow->timeout_hours),
            ]);
        }

        // Notifier l'approbateur escaladé
        Notification::send($nextApprover, new ApprovalRequestNotification($request, 'escalated'));
    }

    protected function findEscalationApprover(ApprovalRequest $request): ?User
    {
        $currentStep = $this->getCurrentStep($request);

        // Stratégie: trouver le manager du niveau supérieur
        if ($currentStep->approver_type === 'user') {
            $currentApprover = User::find($currentStep->approver_id);
            return $currentApprover?->manager;
        }

        // Pour les rôles, trouver le rôle supérieur
        // (À personnaliser selon la hiérarchie de l'entreprise)
        return null;
    }

    protected function notifyCurrentApprovers(ApprovalRequest $request): void
    {
        $step = $this->getCurrentStep($request);
        $approvers = $this->getApproversForStep($step);

        foreach ($approvers as $approver) {
            Notification::send($approver, new ApprovalRequestNotification($request, 'new'));
        }
    }

    protected function notifyRequester(ApprovalRequest $request, string $decision, ?string $comment = null): void
    {
        $requester = $request->requester;
        Notification::send($requester, new ApprovalDecisionNotification($request, $decision, $comment));
    }

    protected function getApproversForStep(ApprovalStep $step): array
    {
        return match($step->approver_type) {
            'user' => [User::find($step->approver_id)],
            'role' => User::role($step->approver_role)->get()->all(),
            'department' => User::where('department_id', $step->approver_id)->get()->all(),
            default => [],
        };
    }

    /**
     * Obtenir les demandes en attente pour un utilisateur
     */
    public function getPendingRequestsForUser(User $user): array
    {
        $requests = ApprovalRequest::where('status', 'pending')
            ->with(['approvable', 'workflow', 'requester'])
            ->get();

        return $requests->filter(function ($request) use ($user) {
            return $this->canUserApprove($request, $user);
        })->values()->toArray();
    }

    /**
     * Obtenir l'historique des approbations pour un utilisateur
     */
    public function getApprovalHistory(User $user, int $limit = 50): array
    {
        return ApprovalStep::where('decided_by', $user->id)
            ->with(['request.approvable', 'request.workflow'])
            ->orderByDesc('decided_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
