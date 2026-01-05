<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Activity;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmController extends Controller
{
    /**
     * Pipeline Kanban view
     */
    public function pipeline()
    {
        $company = Auth::user()->currentCompany;

        // Get all opportunities grouped by stage (including won/lost for counts)
        $allOpportunities = Opportunity::where('company_id', $company->id)
            ->with(['partner', 'assignedTo'])
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('stage');

        // For the Kanban board, only show open opportunities
        $opportunities = collect();
        foreach ($allOpportunities as $stage => $opps) {
            $opportunities[$stage] = $opps;
        }

        // Ensure won and lost keys exist for the summary section
        if (!isset($opportunities['won'])) {
            $opportunities['won'] = collect();
        }
        if (!isset($opportunities['lost'])) {
            $opportunities['lost'] = collect();
        }

        $stats = Opportunity::getPipelineStats($company->id);

        $users = User::whereHas('companies', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->get();

        return view('crm.pipeline', compact('opportunities', 'stats', 'users'));
    }

    /**
     * List all opportunities
     */
    public function index(Request $request)
    {
        $company = Auth::user()->currentCompany;

        $query = Opportunity::where('company_id', $company->id)
            ->with(['partner', 'assignedTo', 'createdBy']);

        // Filters
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('partner', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortField, $sortDir);

        $opportunities = $query->paginate(20)->withQueryString();

        $stats = Opportunity::getPipelineStats($company->id);

        $users = User::whereHas('companies', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->get();

        return view('crm.opportunities.index', compact('opportunities', 'stats', 'users'));
    }

    /**
     * Show create opportunity form
     */
    public function create(Request $request)
    {
        $company = Auth::user()->currentCompany;

        $partners = Partner::where('company_id', $company->id)
            ->whereIn('type', ['customer', 'both'])
            ->orderBy('name')
            ->get();

        $users = User::whereHas('companies', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->get();

        $selectedPartnerId = $request->get('partner_id');

        return view('crm.opportunities.create', compact('partners', 'users', 'selectedPartnerId'));
    }

    /**
     * Store new opportunity
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'partner_id' => 'nullable|exists:partners,id',
            'amount' => 'required|numeric|min:0',
            'probability' => 'required|integer|min:0|max:100',
            'stage' => 'required|in:lead,qualified,proposal,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'source' => 'nullable|string|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $company = Auth::user()->currentCompany;

        $opportunity = Opportunity::create([
            'company_id' => $company->id,
            'partner_id' => $request->partner_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'currency' => 'EUR',
            'probability' => $request->probability,
            'stage' => $request->stage,
            'expected_close_date' => $request->expected_close_date,
            'source' => $request->source,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
            'notes' => $request->notes,
        ]);

        // Record initial stage
        $opportunity->stageHistory()->create([
            'from_stage' => null,
            'to_stage' => $request->stage,
            'changed_by' => Auth::id(),
            'notes' => 'Création de l\'opportunité',
        ]);

        return redirect()->route('crm.opportunities.show', $opportunity)
            ->with('success', 'Opportunité créée avec succès');
    }

    /**
     * Show opportunity details
     */
    public function show(Opportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $opportunity->load([
            'partner',
            'assignedTo',
            'createdBy',
            'stageHistory.changedBy',
            'activities' => function ($q) {
                $q->with(['assignedTo', 'createdBy'])->orderBy('created_at', 'desc');
            }
        ]);

        return view('crm.opportunities.show', compact('opportunity'));
    }

    /**
     * Show edit opportunity form
     */
    public function edit(Opportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $company = Auth::user()->currentCompany;

        $partners = Partner::where('company_id', $company->id)
            ->whereIn('type', ['customer', 'both'])
            ->orderBy('name')
            ->get();

        $users = User::whereHas('companies', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->get();

        return view('crm.opportunities.edit', compact('opportunity', 'partners', 'users'));
    }

    /**
     * Update opportunity
     */
    public function update(Request $request, Opportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $request->validate([
            'title' => 'required|string|max:255',
            'partner_id' => 'nullable|exists:partners,id',
            'amount' => 'required|numeric|min:0',
            'probability' => 'required|integer|min:0|max:100',
            'stage' => 'required|in:lead,qualified,proposal,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'source' => 'nullable|string|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'lost_reason' => 'nullable|string|max:255',
        ]);

        $oldStage = $opportunity->stage;
        $newStage = $request->stage;

        $opportunity->update([
            'partner_id' => $request->partner_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'probability' => $request->probability,
            'expected_close_date' => $request->expected_close_date,
            'source' => $request->source,
            'assigned_to' => $request->assigned_to,
            'notes' => $request->notes,
            'lost_reason' => $request->lost_reason,
        ]);

        // Handle stage change
        if ($oldStage !== $newStage) {
            $opportunity->changeStage($newStage, Auth::user());
        }

        return redirect()->route('crm.opportunities.show', $opportunity)
            ->with('success', 'Opportunité mise à jour');
    }

    /**
     * Delete opportunity
     */
    public function destroy(Opportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $opportunity->delete();

        return redirect()->route('crm.pipeline')
            ->with('success', 'Opportunité supprimée');
    }

    /**
     * Update opportunity stage (AJAX - Kanban drag & drop)
     */
    public function updateStage(Request $request, Opportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $request->validate([
            'stage' => 'required|in:lead,qualified,proposal,negotiation,won,lost',
            'sort_order' => 'nullable|integer',
        ]);

        $opportunity->changeStage($request->stage, Auth::user());

        if ($request->has('sort_order')) {
            $opportunity->update(['sort_order' => $request->sort_order]);
        }

        return response()->json([
            'success' => true,
            'stage' => $opportunity->stage,
            'stage_label' => $opportunity->getStageLabel(),
            'probability' => $opportunity->probability,
        ]);
    }

    /**
     * Mark opportunity as won
     */
    public function markWon(Request $request, Opportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $opportunity->markAsWon(Auth::user(), $request->notes);

        return redirect()->back()->with('success', 'Opportunité marquée comme gagnée !');
    }

    /**
     * Mark opportunity as lost
     */
    public function markLost(Request $request, Opportunity $opportunity)
    {
        $this->authorizeOpportunity($opportunity);

        $request->validate([
            'lost_reason' => 'required|string|max:255',
        ]);

        $opportunity->markAsLost($request->lost_reason, Auth::user());

        return redirect()->back()->with('success', 'Opportunité marquée comme perdue');
    }

    // ==================== ACTIVITIES ====================

    /**
     * List all activities
     */
    public function activities(Request $request)
    {
        $company = Auth::user()->currentCompany;

        $query = Activity::where('company_id', $company->id)
            ->with(['related', 'assignedTo', 'createdBy']);

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->pending();
            } elseif ($request->status === 'completed') {
                $query->completed();
            } elseif ($request->status === 'overdue') {
                $query->overdue();
            }
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $activities = $query->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = Activity::getStats($company->id, Auth::id());

        $users = User::whereHas('companies', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->get();

        return view('crm.activities.index', compact('activities', 'stats', 'users'));
    }

    /**
     * Store new activity
     */
    public function storeActivity(Request $request)
    {
        $request->validate([
            'related_type' => 'required|in:opportunity,partner',
            'related_id' => 'required|integer',
            'type' => 'required|in:call,email,meeting,note,task,demo,follow_up',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'duration' => 'nullable|integer|min:1',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $company = Auth::user()->currentCompany;

        // Map related_type to full class name
        $relatedTypeMap = [
            'opportunity' => Opportunity::class,
            'partner' => Partner::class,
        ];

        $activity = Activity::create([
            'company_id' => $company->id,
            'related_type' => $relatedTypeMap[$request->related_type],
            'related_id' => $request->related_id,
            'type' => $request->type,
            'subject' => $request->subject,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'duration' => $request->duration,
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'created_by' => Auth::id(),
            'priority' => $request->priority ?? 'medium',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity->load(['assignedTo', 'createdBy']),
            ]);
        }

        return redirect()->back()->with('success', 'Activité créée');
    }

    /**
     * Toggle activity completion
     */
    public function toggleActivity(Activity $activity)
    {
        $this->authorizeActivity($activity);

        if ($activity->isCompleted()) {
            $activity->markAsPending();
            $message = 'Activité marquée comme en attente';
        } else {
            $activity->markAsCompleted();
            $message = 'Activité terminée';
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'completed' => $activity->isCompleted(),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete activity
     */
    public function destroyActivity(Activity $activity)
    {
        $this->authorizeActivity($activity);

        $activity->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Activité supprimée');
    }

    // ==================== DASHBOARD ====================

    /**
     * CRM Dashboard
     */
    public function dashboard()
    {
        $company = Auth::user()->currentCompany;
        $userId = Auth::id();

        $pipelineStats = Opportunity::getPipelineStats($company->id);
        $activityStats = Activity::getStats($company->id, $userId);

        // Recent opportunities
        $recentOpportunities = Opportunity::where('company_id', $company->id)
            ->with(['partner', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Overdue opportunities
        $overdueOpportunities = Opportunity::where('company_id', $company->id)
            ->with(['partner', 'assignedTo'])
            ->overdue()
            ->orderBy('expected_close_date')
            ->take(5)
            ->get();

        // Today's activities
        $todaysActivities = Activity::where('company_id', $company->id)
            ->with(['related', 'assignedTo'])
            ->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->orWhereNull('assigned_to');
            })
            ->dueToday()
            ->orderBy('due_date')
            ->get();

        // Overdue activities
        $overdueActivities = Activity::where('company_id', $company->id)
            ->with(['related', 'assignedTo'])
            ->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->orWhereNull('assigned_to');
            })
            ->overdue()
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Won this month
        $wonThisMonth = Opportunity::where('company_id', $company->id)
            ->won()
            ->whereMonth('actual_close_date', now()->month)
            ->whereYear('actual_close_date', now()->year)
            ->sum('amount');

        // Lost this month
        $lostThisMonth = Opportunity::where('company_id', $company->id)
            ->lost()
            ->whereMonth('actual_close_date', now()->month)
            ->whereYear('actual_close_date', now()->year)
            ->sum('amount');

        return view('crm.dashboard', compact(
            'pipelineStats',
            'activityStats',
            'recentOpportunities',
            'overdueOpportunities',
            'todaysActivities',
            'overdueActivities',
            'wonThisMonth',
            'lostThisMonth'
        ));
    }

    // ==================== HELPERS ====================

    private function authorizeOpportunity(Opportunity $opportunity): void
    {
        $company = Auth::user()->currentCompany;
        if ($opportunity->company_id !== $company->id) {
            abort(403, 'Non autorisé');
        }
    }

    private function authorizeActivity(Activity $activity): void
    {
        $company = Auth::user()->currentCompany;
        if ($activity->company_id !== $company->id) {
            abort(403, 'Non autorisé');
        }
    }
}
