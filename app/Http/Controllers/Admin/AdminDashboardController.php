<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $stats = [
            'total_companies' => Company::count(),
            'active_companies' => Company::whereNull('deleted_at')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'superadmins' => User::where('is_superadmin', true)->count(),
            'total_invoices' => Invoice::count(),
            'invoices_this_month' => Invoice::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Subscription Statistics
        $subscriptionStats = [
            'total' => Subscription::count(),
            'trialing' => Subscription::where('status', 'trialing')->count(),
            'active' => Subscription::where('status', 'active')->count(),
            'past_due' => Subscription::where('status', 'past_due')->count(),
            'mrr' => Subscription::where('status', 'active')
                ->where('billing_cycle', 'monthly')
                ->sum('amount') + (Subscription::where('status', 'active')
                ->where('billing_cycle', 'yearly')
                ->sum('amount') / 12),
            'arr' => (Subscription::where('status', 'active')
                ->where('billing_cycle', 'monthly')
                ->sum('amount') * 12) + Subscription::where('status', 'active')
                ->where('billing_cycle', 'yearly')
                ->sum('amount'),
            'revenue_this_month' => SubscriptionInvoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
            'pending_invoices' => SubscriptionInvoice::where('status', 'pending')->sum('total'),
        ];

        // Expiring trials (next 7 days)
        $expiringTrials = Subscription::with(['company', 'plan'])
            ->where('status', 'trialing')
            ->where('trial_ends_at', '<=', now()->addDays(7))
            ->where('trial_ends_at', '>', now())
            ->orderBy('trial_ends_at')
            ->limit(5)
            ->get();

        // Recent activity
        $recentLogs = AuditLog::with('user', 'company')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // New companies this month
        $newCompanies = Company::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // New users this week
        $newUsers = User::where('created_at', '>=', now()->subWeek())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Monthly stats for chart (last 6 months)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->translatedFormat('M Y'),
                'companies' => Company::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'users' => User::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'invoices' => Invoice::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'stats',
            'subscriptionStats',
            'expiringTrials',
            'recentLogs',
            'newCompanies',
            'newUsers',
            'monthlyStats'
        ));
    }
}
