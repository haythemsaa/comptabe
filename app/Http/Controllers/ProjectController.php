<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Partner;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display projects list
     */
    public function index(Request $request)
    {
        $company = Company::current();

        $query = Project::forCompany($company->id)
            ->notTemplate()
            ->with(['partner', 'manager', 'tasks'])
            ->withCount(['tasks', 'timesheets']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by partner
        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $projects = $query->paginate(20);

        // Stats
        $stats = [
            'total' => Project::forCompany($company->id)->notTemplate()->count(),
            'active' => Project::forCompany($company->id)->notTemplate()->active()->count(),
            'completed' => Project::forCompany($company->id)->notTemplate()->where('status', 'completed')->count(),
            'total_budget' => Project::forCompany($company->id)->notTemplate()->sum('budget'),
        ];

        // Filter options
        $partners = Partner::where('company_id', $company->id)
            ->whereIn('type', ['customer', 'both'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('projects.index', compact('projects', 'stats', 'partners'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $company = Company::current();

        $partners = Partner::where('company_id', $company->id)
            ->whereIn('type', ['customer', 'both'])
            ->orderBy('name')
            ->get();

        $users = $company->users()->get();

        $reference = Project::generateReference($company);

        return view('projects.create', compact('partners', 'users', 'reference'));
    }

    /**
     * Store new project
     */
    public function store(Request $request)
    {
        $company = Company::current();

        $validated = $request->validate([
            'reference' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'partner_id' => 'nullable|exists:partners,id',
            'status' => 'required|in:draft,planning,in_progress,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'billing_type' => 'required|in:fixed_price,time_materials,milestone,not_billable',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'color' => 'nullable|string|max:7',
            'tags' => 'nullable|array',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        // Extract members before creating project
        $members = $validated['members'] ?? [];
        unset($validated['members']);

        $validated['company_id'] = $company->id;
        $validated['reference'] = $validated['reference'] ?? Project::generateReference($company);

        DB::beginTransaction();
        try {
            $project = Project::create($validated);

            // Add members
            if (!empty($members)) {
                foreach ($members as $userId) {
                    $project->members()->attach($userId, [
                        'role' => $userId == $validated['manager_id'] ? 'manager' : 'member',
                    ]);
                }
            }

            // Add manager as member if not already
            if ($validated['manager_id'] && !in_array($validated['manager_id'], $members)) {
                $project->members()->attach($validated['manager_id'], ['role' => 'manager']);
            }

            DB::commit();

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Projet créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Show project details
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'partner',
            'manager',
            'members',
            'tasks' => function ($query) {
                $query->orderBy('sort_order');
            },
            'tasks.assignee',
            'timesheets' => function ($query) {
                $query->latest('date')->limit(10);
            },
            'timesheets.user',
        ]);

        // Task stats by status
        $taskStats = $project->tasks()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Time stats
        $timeStats = [
            'total_hours' => $project->timesheets()->sum('hours'),
            'billable_hours' => $project->timesheets()->where('billable', true)->sum('hours'),
            'invoiced_hours' => $project->timesheets()->where('invoiced', true)->sum('hours'),
            'total_amount' => $project->timesheets()->sum('amount'),
        ];

        return view('projects.show', compact('project', 'taskStats', 'timeStats'));
    }

    /**
     * Show edit form
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $company = Company::current();

        $partners = Partner::where('company_id', $company->id)
            ->whereIn('type', ['customer', 'both'])
            ->orderBy('name')
            ->get();

        $users = $company->users()->get();

        $project->load('members');

        return view('projects.edit', compact('project', 'partners', 'users'));
    }

    /**
     * Update project
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'reference' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'partner_id' => 'nullable|exists:partners,id',
            'status' => 'required|in:draft,planning,in_progress,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'billing_type' => 'required|in:fixed_price,time_materials,milestone,not_billable',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'color' => 'nullable|string|max:7',
            'tags' => 'nullable|array',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        // Extract members before updating project
        $members = $validated['members'] ?? [];
        unset($validated['members']);

        DB::beginTransaction();
        try {
            $project->update($validated);

            // Sync members
            $membersData = [];
            if (!empty($members)) {
                foreach ($members as $userId) {
                    $membersData[$userId] = [
                        'role' => $userId == $validated['manager_id'] ? 'manager' : 'member',
                    ];
                }
            }

            // Add manager
            if ($validated['manager_id']) {
                $membersData[$validated['manager_id']] = ['role' => 'manager'];
            }

            $project->members()->sync($membersData);

            DB::commit();

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Projet mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Delete project
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projet supprimé avec succès.');
    }

    /**
     * Show Kanban board
     */
    public function kanban(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['tasks.assignee', 'members']);

        $tasksByStatus = [];
        foreach (ProjectTask::STATUSES as $status => $config) {
            $tasksByStatus[$status] = $project->tasks()
                ->where('status', $status)
                ->orderBy('sort_order')
                ->get();
        }

        return view('projects.kanban', compact('project', 'tasksByStatus'));
    }

    /**
     * Store new task
     */
    public function storeTask(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,review,done,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'parent_task_id' => 'nullable|exists:project_tasks,id',
            'is_milestone' => 'boolean',
        ]);

        $validated['project_id'] = $project->id;
        $validated['created_by'] = auth()->id();
        $validated['sort_order'] = $project->tasks()->where('status', $validated['status'])->max('sort_order') + 1;

        $task = ProjectTask::create($validated);

        $project->updateProgress();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->load('assignee'),
            ]);
        }

        return back()->with('success', 'Tâche créée avec succès.');
    }

    /**
     * Update task
     */
    public function updateTask(Request $request, Project $project, ProjectTask $task)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:todo,in_progress,review,done,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        // Mark as completed if status changed to done
        if (isset($validated['status']) && $validated['status'] === 'done' && $task->status !== 'done') {
            $validated['completed_at'] = now();
            $validated['progress_percent'] = 100;
        }

        $task->update($validated);
        $project->updateProgress();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh()->load('assignee'),
            ]);
        }

        return back()->with('success', 'Tâche mise à jour avec succès.');
    }

    /**
     * Delete task
     */
    public function destroyTask(Project $project, ProjectTask $task)
    {
        $this->authorize('update', $project);

        $task->delete();
        $project->updateProgress();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Tâche supprimée avec succès.');
    }

    /**
     * Reorder tasks (for Kanban drag & drop)
     */
    public function reorderTasks(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:project_tasks,id',
            'tasks.*.status' => 'required|in:todo,in_progress,review,done,cancelled',
            'tasks.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            ProjectTask::where('id', $taskData['id'])
                ->where('project_id', $project->id)
                ->update([
                    'status' => $taskData['status'],
                    'sort_order' => $taskData['sort_order'],
                    'completed_at' => $taskData['status'] === 'done' ? now() : null,
                ]);
        }

        $project->updateProgress();

        return response()->json(['success' => true]);
    }
}
