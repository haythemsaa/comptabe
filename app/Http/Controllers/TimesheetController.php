<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    /**
     * Display timesheets list
     */
    public function index(Request $request)
    {
        $company = Company::current();
        $user = auth()->user();

        $query = Timesheet::forCompany($company->id)
            ->with(['user', 'project', 'task']);

        // Show only own timesheets unless manager/admin
        $userRole = $user->getRoleInCompany($company->id);
        if (!in_array($userRole, ['owner', 'admin', 'manager'])) {
            $query->forUser($user->id);
        } elseif ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->forProject($request->project_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        // Filter billable
        if ($request->filled('billable')) {
            $query->where('billable', $request->billable === '1');
        }

        $query->orderBy('date', 'desc');

        $timesheets = $query->paginate(20);

        // Stats
        $stats = [
            'total_hours' => Timesheet::forCompany($company->id)->forUser($user->id)->sum('hours'),
            'this_week' => Timesheet::forCompany($company->id)->forUser($user->id)->forWeek(now())->sum('hours'),
            'this_month' => Timesheet::forCompany($company->id)->forUser($user->id)->forMonth(now()->year, now()->month)->sum('hours'),
            'pending_approval' => Timesheet::forCompany($company->id)->where('status', 'submitted')->count(),
        ];

        // Filter options
        $projects = Project::forCompany($company->id)->active()->get(['id', 'name', 'reference']);
        $users = $company->users()->get(['users.id', 'first_name', 'last_name']);

        return view('timesheets.index', compact('timesheets', 'stats', 'projects', 'users'));
    }

    /**
     * Show weekly timesheet view
     */
    public function week(Request $request)
    {
        $company = Company::current();
        $user = auth()->user();

        // Get week from request or use current week
        $date = $request->filled('date') ? Carbon::parse($request->date) : now();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        // Get timesheets for the week
        $timesheets = Timesheet::forCompany($company->id)
            ->forUser($user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->with(['project', 'task'])
            ->get()
            ->groupBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // Build week data
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dayKey = $day->format('Y-m-d');
            $weekDays[$dayKey] = [
                'date' => $day,
                'entries' => $timesheets->get($dayKey, collect()),
                'total' => $timesheets->get($dayKey, collect())->sum('hours'),
            ];
        }

        // Projects for dropdown
        $projects = Project::forCompany($company->id)
            ->whereIn('status', ['planning', 'in_progress'])
            ->with(['tasks' => function ($query) {
                $query->whereIn('status', ['todo', 'in_progress', 'review']);
            }])
            ->get();

        // Week totals
        $weekTotal = collect($weekDays)->sum('total');

        return view('timesheets.week', compact('weekDays', 'startOfWeek', 'endOfWeek', 'projects', 'weekTotal'));
    }

    /**
     * Store timesheet entry
     */
    public function store(Request $request)
    {
        $company = Company::current();

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string|max:500',
            'billable' => 'boolean',
        ]);

        $validated['company_id'] = $company->id;
        $validated['user_id'] = auth()->id();
        $validated['billable'] = $validated['billable'] ?? true;

        // Get hourly rate from project or user
        $project = Project::find($validated['project_id']);
        $validated['hourly_rate'] = $project->hourly_rate;

        $timesheet = Timesheet::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'timesheet' => $timesheet->load(['project', 'task']),
            ]);
        }

        return back()->with('success', 'Temps enregistré avec succès.');
    }

    /**
     * Update timesheet entry
     */
    public function update(Request $request, Timesheet $timesheet)
    {
        $this->authorize('update', $timesheet);

        // Can only update draft entries
        if ($timesheet->status !== 'draft') {
            return back()->with('error', 'Seules les entrées en brouillon peuvent être modifiées.');
        }

        $validated = $request->validate([
            'project_id' => 'sometimes|required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'date' => 'sometimes|required|date',
            'hours' => 'sometimes|required|numeric|min:0.25|max:24',
            'description' => 'nullable|string|max:500',
            'billable' => 'boolean',
        ]);

        $timesheet->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'timesheet' => $timesheet->fresh()->load(['project', 'task']),
            ]);
        }

        return back()->with('success', 'Temps mis à jour avec succès.');
    }

    /**
     * Delete timesheet entry
     */
    public function destroy(Timesheet $timesheet)
    {
        $this->authorize('delete', $timesheet);

        // Can only delete draft entries
        if ($timesheet->status !== 'draft') {
            return back()->with('error', 'Seules les entrées en brouillon peuvent être supprimées.');
        }

        $timesheet->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Temps supprimé avec succès.');
    }

    /**
     * Submit timesheets for approval
     */
    public function submit(Request $request)
    {
        $company = Company::current();
        $user = auth()->user();

        $validated = $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'exists:timesheets,id',
        ]);

        $count = Timesheet::whereIn('id', $validated['timesheet_ids'])
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->update(['status' => 'submitted']);

        return back()->with('success', "{$count} entrées soumises pour approbation.");
    }

    /**
     * Approve timesheets (manager only)
     */
    public function approve(Request $request)
    {
        $validated = $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'exists:timesheets,id',
        ]);

        $count = Timesheet::whereIn('id', $validated['timesheet_ids'])
            ->where('status', 'submitted')
            ->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

        return back()->with('success', "{$count} entrées approuvées.");
    }

    /**
     * Reject timesheets (manager only)
     */
    public function reject(Request $request)
    {
        $validated = $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'exists:timesheets,id',
        ]);

        $count = Timesheet::whereIn('id', $validated['timesheet_ids'])
            ->where('status', 'submitted')
            ->update(['status' => 'rejected']);

        return back()->with('success', "{$count} entrées rejetées.");
    }

    /**
     * Get tasks for a project (AJAX)
     */
    public function getTasks(Project $project)
    {
        $tasks = $project->tasks()
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->orderBy('title')
            ->get(['id', 'title', 'status']);

        return response()->json($tasks);
    }
}
