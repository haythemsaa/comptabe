<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminSupportController extends Controller
{
    /**
     * Display support tickets dashboard
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'company', 'assignedTo']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->category($request->category);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->unassigned();
            } else {
                $query->assignedTo($request->assigned_to);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Statistics
        $stats = Cache::remember('admin.support.stats', now()->addMinutes(5), function () {
            return [
                'total' => SupportTicket::count(),
                'open' => SupportTicket::open()->count(),
                'in_progress' => SupportTicket::inProgress()->count(),
                'resolved' => SupportTicket::resolved()->count(),
                'urgent' => SupportTicket::urgent()->count(),
                'unassigned' => SupportTicket::unassigned()->count(),
                'avg_response_time' => $this->getAverageResponseTime(),
                'avg_resolution_time' => $this->getAverageResolutionTime(),
            ];
        });

        // Get admin users for assignment
        $admins = User::where('role', 'superadmin')->get();

        return view('admin.support.index', compact('tickets', 'stats', 'admins'));
    }

    /**
     * Show ticket details
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'company', 'assignedTo', 'messages.user']);

        // Get admin users for reassignment
        $admins = User::where('role', 'superadmin')->get();

        return view('admin.support.show', compact('ticket', 'admins'));
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,waiting_customer,resolved,closed',
            'resolution_note' => 'nullable|string',
        ]);

        $oldStatus = $ticket->status;

        $ticket->update([
            'status' => $validated['status'],
            'resolution_note' => $validated['resolution_note'] ?? $ticket->resolution_note,
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed']) && !$ticket->resolved_at ? now() : $ticket->resolved_at,
            'closed_at' => $validated['status'] === 'closed' && !$ticket->closed_at ? now() : $ticket->closed_at,
        ]);

        AuditLog::log('support', "Ticket {$ticket->ticket_number} status changed: {$oldStatus} → {$validated['status']}");

        return back()->with('success', 'Statut du ticket mis à jour.');
    }

    /**
     * Assign ticket to admin
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|uuid|exists:users,id',
        ]);

        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);

        $admin = User::find($validated['assigned_to']);

        AuditLog::log('support', "Ticket {$ticket->ticket_number} assigned to {$admin->full_name}");

        return back()->with('success', "Ticket assigné à {$admin->full_name}.");
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $oldPriority = $ticket->priority;
        $ticket->update(['priority' => $validated['priority']]);

        AuditLog::log('support', "Ticket {$ticket->ticket_number} priority changed: {$oldPriority} → {$validated['priority']}");

        return back()->with('success', 'Priorité du ticket mise à jour.');
    }

    /**
     * Add message to ticket
     */
    public function addMessage(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal_note' => 'boolean',
        ]);

        $message = $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_internal_note' => $validated['is_internal_note'] ?? false,
        ]);

        // Update first response time if this is the first admin response
        if (!$ticket->first_response_at && !$validated['is_internal_note']) {
            $ticket->update(['first_response_at' => now()]);
        }

        return back()->with('success', 'Message ajouté au ticket.');
    }

    /**
     * Get statistics for dashboard
     */
    protected function getAverageResponseTime(): ?float
    {
        $avg = SupportTicket::whereNotNull('first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_time')
            ->value('avg_time');

        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get average resolution time
     */
    protected function getAverageResolutionTime(): ?float
    {
        $avg = SupportTicket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_time')
            ->value('avg_time');

        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get real-time stats for AJAX
     */
    public function stats()
    {
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::open()->count(),
            'in_progress' => SupportTicket::inProgress()->count(),
            'resolved_today' => SupportTicket::resolved()->whereDate('resolved_at', today())->count(),
            'urgent' => SupportTicket::urgent()->count(),
            'unassigned' => SupportTicket::unassigned()->count(),
            'response_time' => $this->getAverageResponseTime(),
            'resolution_time' => $this->getAverageResolutionTime(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk actions on tickets
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:assign,close,delete',
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:support_tickets,id',
            'assigned_to' => 'nullable|uuid|exists:users,id',
        ]);

        $tickets = SupportTicket::whereIn('id', $validated['ticket_ids'])->get();

        switch ($validated['action']) {
            case 'assign':
                if (!$validated['assigned_to']) {
                    return back()->with('error', 'Veuillez sélectionner un administrateur.');
                }
                $tickets->each(fn($ticket) => $ticket->update(['assigned_to' => $validated['assigned_to']]));
                AuditLog::log('support', count($tickets) . " tickets assigned");
                return back()->with('success', count($tickets) . ' tickets assignés.');

            case 'close':
                $tickets->each(fn($ticket) => $ticket->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                    'resolved_at' => $ticket->resolved_at ?? now(),
                ]));
                AuditLog::log('support', count($tickets) . " tickets closed");
                return back()->with('success', count($tickets) . ' tickets fermés.');

            case 'delete':
                $tickets->each(fn($ticket) => $ticket->delete());
                AuditLog::log('support', count($tickets) . " tickets deleted");
                return back()->with('success', count($tickets) . ' tickets supprimés.');
        }
    }

    /**
     * Export tickets to CSV
     */
    public function export(Request $request)
    {
        $tickets = SupportTicket::with(['user', 'company', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'support-tickets-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ticket', 'Company', 'User', 'Subject', 'Status', 'Priority', 'Category', 'Assigned To', 'Created', 'Resolved']);

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->ticket_number,
                    $ticket->company?->name,
                    $ticket->user?->full_name,
                    $ticket->subject,
                    $ticket->status,
                    $ticket->priority,
                    $ticket->category,
                    $ticket->assignedTo?->full_name,
                    $ticket->created_at->format('Y-m-d H:i'),
                    $ticket->resolved_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
