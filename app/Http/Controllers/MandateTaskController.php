<?php

namespace App\Http\Controllers;

use App\Models\AccountingFirm;
use App\Models\ClientMandate;
use App\Models\MandateTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MandateTaskController extends Controller
{
    /**
     * Display all tasks for the firm.
     */
    public function index(Request $request)
    {
        $firm = AccountingFirm::current();
        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $query = MandateTask::whereHas('clientMandate', fn($q) => $q->where('accounting_firm_id', $firm->id))
            ->with(['clientMandate.company', 'assignedUser']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        } else {
            // Default: show pending tasks
            $query->pending();
        }

        // Filter by assignee
        if ($request->filled('assignee')) {
            if ($request->assignee === 'me') {
                $query->where('assigned_to', Auth::id());
            } else {
                $query->where('assigned_to', $request->assignee);
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('task_type', $request->type);
        }

        // Filter by client
        if ($request->filled('client')) {
            $query->where('client_mandate_id', $request->client);
        }

        // Sort
        $sortBy = $request->get('sort', 'due_date');
        $sortDir = $request->get('dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $tasks = $query->paginate(20);

        // Get filter options
        $users = $firm->users()->wherePivot('is_active', true)->get();
        $clients = $firm->clientMandates()->with('company')->where('status', 'active')->get();

        return view('firm.tasks.index', compact('firm', 'tasks', 'users', 'clients'));
    }

    /**
     * Show create task form.
     */
    public function create(Request $request)
    {
        $firm = AccountingFirm::current();
        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $mandate = null;
        if ($request->filled('mandate')) {
            $mandate = ClientMandate::findOrFail($request->mandate);
            $this->authorizeFirmAccess($mandate);
        }

        $clients = $firm->clientMandates()->with('company')->where('status', 'active')->get();
        $users = $firm->users()->wherePivot('is_active', true)->get();

        return view('firm.tasks.create', compact('firm', 'mandate', 'clients', 'users'));
    }

    /**
     * Store a new task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_mandate_id' => 'required|exists:client_mandates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type' => 'required|string|in:' . implode(',', array_keys(MandateTask::TYPE_LABELS)),
            'fiscal_year' => 'nullable|integer|min:2000|max:2100',
            'period' => 'nullable|string|max:20',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date|before_or_equal:due_date',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|string|in:low,normal,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'is_billable' => 'boolean',
        ]);

        $mandate = ClientMandate::findOrFail($validated['client_mandate_id']);
        $this->authorizeFirmAccess($mandate);

        $task = MandateTask::create([
            ...$validated,
            'status' => 'pending',
            'is_billable' => $validated['is_billable'] ?? false,
        ]);

        // Log activity
        $mandate->logActivity('task_created', "Tâche créée: {$task->title}");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        return redirect()->route('firm.clients.show', $mandate)
            ->with('success', 'Tâche créée avec succès.');
    }

    /**
     * Show task details.
     */
    public function show(MandateTask $task)
    {
        $this->authorizeFirmAccess($task->clientMandate);

        $task->load(['clientMandate.company', 'assignedUser', 'completedByUser']);

        return view('firm.tasks.show', compact('task'));
    }

    /**
     * Edit task.
     */
    public function edit(MandateTask $task)
    {
        $this->authorizeFirmAccess($task->clientMandate);

        $firm = AccountingFirm::current();
        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $users = $firm->users()->wherePivot('is_active', true)->get();

        return view('firm.tasks.edit', compact('task', 'users'));
    }

    /**
     * Update task.
     */
    public function update(Request $request, MandateTask $task)
    {
        $this->authorizeFirmAccess($task->clientMandate);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type' => 'required|string|in:' . implode(',', array_keys(MandateTask::TYPE_LABELS)),
            'fiscal_year' => 'nullable|integer|min:2000|max:2100',
            'period' => 'nullable|string|max:20',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date|before_or_equal:due_date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|string|in:' . implode(',', array_keys(MandateTask::STATUS_LABELS)),
            'priority' => 'required|string|in:low,normal,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'is_billable' => 'boolean',
        ]);

        $task->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task]);
        }

        return redirect()->route('firm.tasks.show', $task)
            ->with('success', 'Tâche mise à jour.');
    }

    /**
     * Update task status (quick action).
     */
    public function updateStatus(Request $request, MandateTask $task)
    {
        $this->authorizeFirmAccess($task->clientMandate);

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(MandateTask::STATUS_LABELS)),
        ]);

        $task->update(['status' => $validated['status']]);

        if ($validated['status'] === 'completed') {
            $task->update([
                'completed_at' => now(),
                'completed_by' => Auth::id(),
            ]);

            // Log activity
            $task->clientMandate->logActivity('task_completed', "Tâche terminée: {$task->title}");
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task->fresh()]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * Delete task.
     */
    public function destroy(MandateTask $task)
    {
        $this->authorizeFirmAccess($task->clientMandate);

        $mandate = $task->clientMandate;
        $task->delete();

        return redirect()->route('firm.clients.show', $mandate)
            ->with('success', 'Tâche supprimée.');
    }

    /**
     * Log time entry for task.
     */
    public function logTime(Request $request, MandateTask $task)
    {
        $this->authorizeFirmAccess($task->clientMandate);

        $validated = $request->validate([
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string|max:500',
        ]);

        // Add to actual hours
        $task->increment('actual_hours', $validated['hours']);

        // Log activity
        $task->clientMandate->logActivity('time_entry', $validated['description'] ?? "Temps logué: {$validated['hours']}h", [
            'task_id' => $task->id,
            'hours' => $validated['hours'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task->fresh()]);
        }

        return back()->with('success', 'Temps enregistré.');
    }

    /**
     * My tasks (current user's assigned tasks).
     */
    public function myTasks(Request $request)
    {
        $firm = AccountingFirm::current();
        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $query = MandateTask::whereHas('clientMandate', fn($q) => $q->where('accounting_firm_id', $firm->id))
            ->where('assigned_to', Auth::id())
            ->with(['clientMandate.company']);

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        } else {
            $query->pending();
        }

        $query->orderBy('due_date');

        $tasks = $query->paginate(20);

        return view('firm.tasks.my-tasks', compact('tasks'));
    }

    /**
     * Check if current user can access this mandate.
     */
    protected function authorizeFirmAccess(ClientMandate $mandate): void
    {
        $firm = AccountingFirm::current();
        if (!$firm) {
            abort(403, 'Aucun cabinet comptable actif.');
        }

        if ($mandate->accounting_firm_id !== $firm->id) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
