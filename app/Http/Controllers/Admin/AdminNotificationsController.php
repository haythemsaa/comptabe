<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AdminNotificationsController extends Controller
{
    /**
     * Display notifications dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get all notifications
        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->paginate(20);

        // Statistics
        $stats = [
            'unread' => $user->unreadNotifications()->count(),
            'total' => $user->notifications()->count(),
            'today' => $user->notifications()
                ->whereDate('created_at', today())
                ->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Get unread notifications count (for real-time updates)
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count()
        ]);
    }

    /**
     * Get latest notifications (for real-time polling)
     */
    public function latest()
    {
        $notifications = auth()->user()
            ->notifications()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return back()->with('success', 'Notification supprimée.');
    }

    /**
     * Delete all read notifications
     */
    public function deleteRead()
    {
        auth()->user()
            ->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return back()->with('success', 'Notifications lues supprimées.');
    }

    /**
     * Notification preferences
     */
    public function preferences()
    {
        $user = auth()->user();

        $preferences = $user->notification_preferences ?? [
            'critical_errors' => true,
            'new_company' => true,
            'new_user' => true,
            'system_alerts' => true,
            'failed_jobs' => true,
            'revenue_milestones' => true,
            'email_notifications' => false,
            'browser_notifications' => true,
        ];

        return view('admin.notifications.preferences', compact('preferences'));
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $preferences = [
            'critical_errors' => $request->boolean('critical_errors'),
            'new_company' => $request->boolean('new_company'),
            'new_user' => $request->boolean('new_user'),
            'system_alerts' => $request->boolean('system_alerts'),
            'failed_jobs' => $request->boolean('failed_jobs'),
            'revenue_milestones' => $request->boolean('revenue_milestones'),
            'email_notifications' => $request->boolean('email_notifications'),
            'browser_notifications' => $request->boolean('browser_notifications'),
        ];

        auth()->user()->update([
            'notification_preferences' => $preferences,
        ]);

        return back()->with('success', 'Préférences de notifications mises à jour.');
    }

    /**
     * Send test notification
     */
    public function sendTest()
    {
        auth()->user()->notify(new \App\Notifications\Admin\TestNotification());

        return back()->with('success', 'Notification de test envoyée.');
    }
}
