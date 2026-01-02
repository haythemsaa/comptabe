<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemError;
use Illuminate\Http\Request;

class AdminErrorsController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemError::with(['user', 'company', 'resolvedBy'])
            ->orderByDesc('last_occurred_at');

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'unresolved') {
                $query->unresolved();
            } elseif ($request->status === 'resolved') {
                $query->where('resolved', true);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('exception', 'like', "%{$search}%")
                  ->orWhere('file', 'like', "%{$search}%");
            });
        }

        $errors = $query->paginate(50)->withQueryString();

        // Statistics
        $stats = [
            'total' => SystemError::count(),
            'unresolved' => SystemError::unresolved()->count(),
            'critical' => SystemError::critical()->unresolved()->count(),
            'today' => SystemError::whereDate('created_at', today())->count(),
            'week' => SystemError::where('created_at', '>=', now()->subWeek())->count(),
        ];

        // Error types for filter
        $types = SystemError::distinct()->pluck('type');

        return view('admin.errors.index', compact('errors', 'stats', 'types'));
    }

    public function show(SystemError $error)
    {
        $error->load(['user', 'company', 'resolvedBy']);

        return view('admin.errors.show', compact('error'));
    }

    public function resolve(Request $request, SystemError $error)
    {
        $validated = $request->validate([
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        $error->resolve($validated['resolution_note'] ?? null);

        return back()->with('success', 'Erreur marquée comme résolue.');
    }

    public function bulkResolve(Request $request)
    {
        $validated = $request->validate([
            'error_ids' => 'required|array',
            'error_ids.*' => 'exists:system_errors,id',
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        $count = SystemError::whereIn('id', $validated['error_ids'])
            ->update([
                'resolved' => true,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'resolution_note' => $validated['resolution_note'] ?? null,
            ]);

        return back()->with('success', "{$count} erreur(s) résolue(s).");
    }

    public function destroy(SystemError $error)
    {
        $error->delete();

        return redirect()->route('admin.errors.index')
            ->with('success', 'Erreur supprimée.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'error_ids' => 'required|array',
            'error_ids.*' => 'exists:system_errors,id',
        ]);

        $count = SystemError::whereIn('id', $validated['error_ids'])->delete();

        return back()->with('success', "{$count} erreur(s) supprimée(s).");
    }

    /**
     * Clear all resolved errors older than 30 days
     */
    public function cleanup()
    {
        $count = SystemError::where('resolved', true)
            ->where('resolved_at', '<', now()->subDays(30))
            ->delete();

        return back()->with('success', "{$count} erreur(s) résolue(s) et anciennes supprimée(s).");
    }
}
