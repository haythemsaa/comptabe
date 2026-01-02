<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->notifications();

        // Filter by read/unread
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        // Filter by type
        if ($request->has('type')) {
            $query->whereJsonContains('data->type', $request->type);
        }

        // Filter by severity
        if ($request->has('severity')) {
            $query->whereJsonContains('data->severity', $request->severity);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $notifications = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'unknown',
                    'severity' => $notification->data['severity'] ?? 'info',
                    'title' => $notification->data['title'] ?? '',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $notification->data['icon'] ?? 'bell',
                    'color' => $notification->data['color'] ?? 'info',
                    'action_url' => $notification->data['action_url'] ?? null,
                    'action_text' => $notification->data['action_text'] ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'data' => $notification->data,
                ];
            }),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $user->unreadNotifications()->count(),
                'by_severity' => [
                    'critical' => $user->unreadNotifications()
                        ->whereJsonContains('data->severity', 'critical')
                        ->count(),
                    'warning' => $user->unreadNotifications()
                        ->whereJsonContains('data->severity', 'warning')
                        ->count(),
                    'info' => $user->unreadNotifications()
                        ->whereJsonContains('data->severity', 'info')
                        ->count(),
                ],
            ],
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        // Optionally filter by type
        if ($request->has('type')) {
            $user->unreadNotifications()
                ->whereJsonContains('data->type', $request->type)
                ->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $user = Auth::user();

        $user->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All read notifications deleted',
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics()
    {
        $user = Auth::user();
        $company = $user->currentCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company context',
            ], 400);
        }

        $stats = $this->notificationService->getStatistics($company);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Test notification system (admin only)
     */
    public function test(Request $request)
    {
        $user = Auth::user();
        $company = $user->currentCompany();

        if (!$company || !$user->isAdminInCurrentTenant()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'check_type' => 'required|in:all,invoices,cash_flow,bank_reconciliation,vat_declarations',
        ]);

        $checkType = $request->check_type;
        $results = [];

        try {
            if ($checkType === 'all') {
                $results = $this->notificationService->runAllChecks($company);
            } elseif ($checkType === 'invoices') {
                $results['invoices'] = $this->notificationService->checkInvoiceOverdue($company);
            } elseif ($checkType === 'cash_flow') {
                $results['cash_flow'] = $this->notificationService->checkLowCashFlow($company);
            } elseif ($checkType === 'bank_reconciliation') {
                $results['bank_reconciliation'] = $this->notificationService->checkBankReconciliation($company);
            } elseif ($checkType === 'vat_declarations') {
                $results['vat_declarations'] = $this->notificationService->checkVatDeclarations($company);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification checks completed',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error running notification checks: ' . $e->getMessage(),
            ], 500);
        }
    }
}
