<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'company'])
            ->orderByDesc('created_at');

        // User filter
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        // Company filter
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get filter options
        $users = User::orderBy('first_name')->get();

        return view('admin.audit-logs.index', compact('logs', 'users'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['user', 'company']);
        $log = $auditLog;

        return view('admin.audit-logs.show', compact('log'));
    }

    public function export(Request $request)
    {
        $query = AuditLog::with(['user', 'company'])
            ->orderByDesc('created_at');

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->limit(10000)->get();

        $csv = "Date,Utilisateur,Entreprise,Action,Description,IP,User Agent\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user?->full_name ?? 'Système',
                $log->company?->name ?? '-',
                $log->action_label,
                str_replace('"', '""', $log->description),
                $log->ip_address ?? '-',
                str_replace('"', '""', $log->user_agent ?? '-')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit_logs_' . now()->format('Y-m-d') . '.csv"');
    }

    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
        ]);

        $date = now()->subDays($request->days);
        $count = AuditLog::where('created_at', '<', $date)->count();

        AuditLog::where('created_at', '<', $date)->delete();

        AuditLog::log('delete', "Nettoyage des logs: {$count} entrées supprimées (> {$request->days} jours)");

        return back()->with('success', "{$count} entrées de logs supprimées.");
    }
}
