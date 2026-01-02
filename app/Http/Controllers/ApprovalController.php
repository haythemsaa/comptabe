<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalRule;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\Workflow\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalWorkflowService $workflowService
    ) {}

    /**
     * Dashboard des approbations
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        // Demandes en attente de mon approbation
        $pendingApprovals = collect($this->workflowService->getPendingRequestsForUser($user));

        // Mes demandes soumises
        $myRequests = ApprovalRequest::where('requester_id', $user->id)
            ->with(['workflow', 'approvable', 'steps.decider'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Historique de mes décisions
        $myDecisions = $this->workflowService->getApprovalHistory($user, 20);

        // Stats
        $stats = [
            'pending' => $pendingApprovals->count(),
            'approved_this_month' => ApprovalRequest::where('company_id', $companyId)
                ->where('status', 'approved')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'rejected_this_month' => ApprovalRequest::where('company_id', $companyId)
                ->where('status', 'rejected')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'avg_approval_time' => $this->getAverageApprovalTime($companyId),
        ];

        return view('approvals.index', compact(
            'pendingApprovals',
            'myRequests',
            'myDecisions',
            'stats'
        ));
    }

    /**
     * Liste des demandes en attente
     */
    public function pending()
    {
        $user = Auth::user();
        $pendingApprovals = collect($this->workflowService->getPendingRequestsForUser($user));

        return view('approvals.pending', compact('pendingApprovals'));
    }

    /**
     * Détail d'une demande
     */
    public function show(ApprovalRequest $request)
    {
        $this->authorize('view', $request);

        $request->load(['workflow', 'approvable', 'requester', 'steps.decider', 'steps.delegate']);
        $canApprove = $this->workflowService->canUserApprove($request, Auth::user());

        return view('approvals.show', compact('request', 'canApprove'));
    }

    /**
     * Approuver une demande
     */
    public function approve(Request $httpRequest, ApprovalRequest $request)
    {
        $httpRequest->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        try {
            $this->workflowService->approve($request, $httpRequest->comment);

            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Demande approuvée avec succès.',
                ]);
            }

            return redirect()->route('approvals.index')
                ->with('success', 'Demande approuvée avec succès.');
        } catch (\Exception $e) {
            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rejeter une demande
     */
    public function reject(Request $httpRequest, ApprovalRequest $request)
    {
        $httpRequest->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->workflowService->reject($request, $httpRequest->reason);

            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Demande rejetée.',
                ]);
            }

            return redirect()->route('approvals.index')
                ->with('success', 'Demande rejetée.');
        } catch (\Exception $e) {
            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Demander des modifications
     */
    public function requestChanges(Request $httpRequest, ApprovalRequest $request)
    {
        $httpRequest->validate([
            'changes' => 'required|string|max:2000',
        ]);

        try {
            $this->workflowService->requestChanges($request, $httpRequest->changes);

            return redirect()->route('approvals.index')
                ->with('success', 'Modifications demandées.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Déléguer une approbation
     */
    public function delegate(Request $httpRequest, ApprovalRequest $request)
    {
        $httpRequest->validate([
            'user_id' => 'required|exists:users,id',
            'until' => 'nullable|date|after:today',
        ]);

        try {
            $delegate = User::find($httpRequest->user_id);
            $until = $httpRequest->until ? \Carbon\Carbon::parse($httpRequest->until) : null;

            $this->workflowService->delegate($request, $delegate, $until);

            return back()->with('success', "Délégation effectuée à {$delegate->name}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Resoumettre une demande
     */
    public function resubmit(ApprovalRequest $request)
    {
        try {
            $this->workflowService->resubmit($request);

            return redirect()->route('approvals.show', $request)
                ->with('success', 'Demande soumise à nouveau.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Annuler une demande
     */
    public function cancel(ApprovalRequest $request)
    {
        try {
            $this->workflowService->cancel($request);

            return redirect()->route('approvals.index')
                ->with('success', 'Demande annulée.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ===== GESTION DES WORKFLOWS =====

    /**
     * Liste des workflows
     */
    public function workflows()
    {
        $companyId = Auth::user()->current_company_id;

        $workflows = ApprovalWorkflow::where('company_id', $companyId)
            ->with('rules')
            ->orderBy('document_type')
            ->orderBy('min_amount')
            ->get();

        return view('approvals.workflows.index', compact('workflows'));
    }

    /**
     * Créer un workflow
     */
    public function createWorkflow()
    {
        $users = User::where('company_id', Auth::user()->current_company_id)->get();

        return view('approvals.workflows.create', compact('users'));
    }

    /**
     * Enregistrer un workflow
     */
    public function storeWorkflow(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|in:invoice_purchase,invoice_sale,expense,payment,journal_entry',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gt:min_amount',
            'timeout_hours' => 'nullable|integer|min:1|max:720',
            'escalate_on_timeout' => 'boolean',
            'escalate_on_rejection' => 'boolean',
            'max_escalations' => 'nullable|integer|min:1|max:5',
            'rules' => 'required|array|min:1',
            'rules.*.approver_type' => 'required|in:user,role,manager',
            'rules.*.approver_id' => 'nullable|exists:users,id',
            'rules.*.approver_role' => 'nullable|string',
            'rules.*.required_approvals' => 'required|integer|min:1',
        ]);

        $workflow = ApprovalWorkflow::create([
            'company_id' => Auth::user()->current_company_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'document_type' => $validated['document_type'],
            'min_amount' => $validated['min_amount'] ?? null,
            'max_amount' => $validated['max_amount'] ?? null,
            'timeout_hours' => $validated['timeout_hours'] ?? null,
            'escalate_on_timeout' => $validated['escalate_on_timeout'] ?? false,
            'escalate_on_rejection' => $validated['escalate_on_rejection'] ?? false,
            'max_escalations' => $validated['max_escalations'] ?? 2,
            'is_active' => true,
        ]);

        foreach ($validated['rules'] as $index => $ruleData) {
            ApprovalRule::create([
                'approval_workflow_id' => $workflow->id,
                'name' => "Étape " . ($index + 1),
                'step_order' => $index + 1,
                'approver_type' => $ruleData['approver_type'],
                'approver_id' => $ruleData['approver_id'] ?? null,
                'approver_role' => $ruleData['approver_role'] ?? null,
                'required_approvals' => $ruleData['required_approvals'],
            ]);
        }

        return redirect()->route('approvals.workflows')
            ->with('success', 'Workflow créé avec succès.');
    }

    /**
     * Modifier un workflow
     */
    public function editWorkflow(ApprovalWorkflow $workflow)
    {
        $this->authorize('update', $workflow);

        $workflow->load('rules');
        $users = User::where('company_id', Auth::user()->current_company_id)->get();

        return view('approvals.workflows.edit', compact('workflow', 'users'));
    }

    /**
     * Mettre à jour un workflow
     */
    public function updateWorkflow(Request $request, ApprovalWorkflow $workflow)
    {
        $this->authorize('update', $workflow);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gt:min_amount',
            'timeout_hours' => 'nullable|integer|min:1|max:720',
            'escalate_on_timeout' => 'boolean',
            'escalate_on_rejection' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $workflow->update($validated);

        return redirect()->route('approvals.workflows')
            ->with('success', 'Workflow mis à jour.');
    }

    /**
     * Supprimer un workflow
     */
    public function destroyWorkflow(ApprovalWorkflow $workflow)
    {
        $this->authorize('delete', $workflow);

        // Vérifier qu'il n'y a pas de demandes en cours
        if ($workflow->requests()->whereIn('status', ['pending', 'changes_requested'])->exists()) {
            return back()->with('error', 'Impossible de supprimer: des demandes sont en cours.');
        }

        $workflow->rules()->delete();
        $workflow->delete();

        return redirect()->route('approvals.workflows')
            ->with('success', 'Workflow supprimé.');
    }

    // ===== HELPERS =====

    protected function getAverageApprovalTime(int $companyId): ?float
    {
        $result = ApprovalRequest::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
            ->first();

        return $result->avg_hours ? round($result->avg_hours, 1) : null;
    }
}
