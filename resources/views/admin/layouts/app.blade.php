<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - ComptaBE Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        /* Sidebar styles */
        .admin-sidebar {
            width: 280px;
            transition: width 0.3s ease;
        }
        .admin-sidebar.collapsed {
            width: 80px;
        }

        /* Main content margin */
        .admin-main {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        .admin-main.sidebar-collapsed {
            margin-left: 80px;
        }

        /* Scrollbar */
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body class="min-h-screen bg-secondary-900 text-white">
    <div class="flex min-h-screen" x-data="adminLayout()" x-init="init()">
        <!-- Admin Sidebar -->
        <aside
            class="admin-sidebar fixed inset-y-0 left-0 z-30 bg-secondary-800 border-r border-secondary-700 flex flex-col"
            :class="{ 'collapsed': sidebarCollapsed }"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center px-4 border-b border-secondary-700">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-danger-500 to-danger-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-danger-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" x-cloak class="overflow-hidden">
                        <span class="font-bold text-lg whitespace-nowrap">ComptaBE</span>
                        <span class="block text-xs text-danger-400 font-medium">SUPERADMIN</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto scrollbar-thin">
                <!-- Main Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Principal</p>

                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.analytics.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Analytics</span>
                    </a>
                </div>

                <!-- Gestion Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Gestion</p>

                    <a href="{{ route('admin.companies.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.companies.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Entreprises</span>
                        @php $companyCount = \App\Models\Company::count(); @endphp
                        <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-secondary-700 rounded-full">{{ $companyCount }}</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Utilisateurs</span>
                        @php $userCount = \App\Models\User::count(); @endphp
                        <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-secondary-700 rounded-full">{{ $userCount }}</span>
                    </a>

                    <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.audit-logs.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Logs d'Audit</span>
                    </a>
                </div>

                <!-- Modules Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Modules</p>

                    <a href="{{ route('admin.modules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.modules.index') || request()->routeIs('admin.modules.show') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Catalogue</span>
                        @php $moduleCount = \App\Models\Module::active()->count(); @endphp
                        <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-secondary-700 rounded-full">{{ $moduleCount }}</span>
                    </a>

                    <a href="{{ route('admin.modules.requests') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.modules.requests') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Demandes</span>
                        @php $pendingModuleRequests = \App\Models\ModuleRequest::where('status', 'pending')->count(); @endphp
                        @if($pendingModuleRequests > 0)
                            <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-warning-500/20 text-warning-400 rounded-full animate-pulse">{{ $pendingModuleRequests }}</span>
                        @endif
                    </a>
                </div>

                <!-- Abonnements Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Abonnements</p>

                    <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.subscriptions.index') || request()->routeIs('admin.subscriptions.show') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Abonnements</span>
                        @php $activeSubCount = \App\Models\Subscription::whereIn('status', ['active', 'trialing'])->count(); @endphp
                        <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-success-500/20 text-success-400 rounded-full">{{ $activeSubCount }}</span>
                    </a>

                    <a href="{{ route('admin.subscriptions.expiring-trials') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.subscriptions.expiring-trials') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Essais expirants</span>
                        @php $expiringCount = \App\Models\Subscription::where('status', 'trialing')->where('trial_ends_at', '<=', now()->addDays(7))->count(); @endphp
                        @if($expiringCount > 0)
                            <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-warning-500/20 text-warning-400 rounded-full animate-pulse">{{ $expiringCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('admin.subscription-plans.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.subscription-plans.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Plans tarifaires</span>
                    </a>

                    <a href="{{ route('admin.subscription-invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.subscription-invoices.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Facturation</span>
                        @php $pendingInvoices = \App\Models\SubscriptionInvoice::where('status', 'pending')->count(); @endphp
                        @if($pendingInvoices > 0)
                            <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-danger-500/20 text-danger-400 rounded-full">{{ $pendingInvoices }}</span>
                        @endif
                    </a>
                </div>

                <!-- Systeme Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Systeme</p>

                    <a href="{{ route('admin.system.health') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.system.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Sante Systeme</span>
                        <span x-show="!sidebarCollapsed" x-cloak class="w-2 h-2 bg-success-400 rounded-full"></span>
                    </a>

                    <a href="{{ route('admin.exports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.exports.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Exports</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-danger-500/20 text-danger-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Parametres</span>
                    </a>
                </div>

                <!-- Return to App -->
                <div class="pt-4 border-t border-secondary-700">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-secondary-400 hover:bg-secondary-700 hover:text-white transition-all">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Retour a l'app</span>
                    </a>
                </div>
            </nav>

            <!-- Collapse Button -->
            <button
                @click="toggleSidebar()"
                class="absolute -right-3 top-20 w-6 h-6 bg-secondary-700 border border-secondary-600 rounded-full flex items-center justify-center text-secondary-400 hover:text-white hover:bg-secondary-600 transition-colors z-50"
            >
                <svg class="w-4 h-4 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <!-- User Info -->
            <div class="p-3 border-t border-secondary-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-danger-500 to-danger-600 rounded-full flex items-center justify-center font-bold flex-shrink-0 shadow-lg shadow-danger-500/20">
                        {{ auth()->user()->initials }}
                    </div>
                    <div x-show="!sidebarCollapsed" x-cloak class="flex-1 min-w-0 overflow-hidden">
                        <p class="font-medium truncate text-sm">{{ auth()->user()->full_name }}</p>
                        <p class="text-xs text-danger-400">Superadmin</p>
                    </div>
                    <form x-show="!sidebarCollapsed" x-cloak method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-secondary-400 hover:text-white p-1" title="Deconnexion">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-main flex-1 min-h-screen flex flex-col" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
            <!-- Top Bar -->
            <header class="sticky top-0 z-20 h-16 bg-secondary-800/95 backdrop-blur border-b border-secondary-700 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    @isset($header)
                        <h1 class="text-xl font-semibold">{{ $header }}</h1>
                    @endisset
                </div>

                <div class="flex items-center gap-3">
                    <!-- Global Search -->
                    <div class="relative" x-data="globalSearch()">
                        <div class="relative">
                            <input
                                type="text"
                                x-model="query"
                                @input.debounce.300ms="search()"
                                @focus="showResults = true"
                                @click.away="showResults = false"
                                @keydown.escape="showResults = false"
                                placeholder="Rechercher... (Ctrl+K)"
                                class="w-64 bg-secondary-700 border border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500 pl-10 pr-4 py-2 text-sm"
                            >
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Search Results -->
                        <div
                            x-show="showResults && (results.length > 0 || query.length >= 2)"
                            x-cloak
                            class="absolute top-full mt-2 w-full bg-secondary-700 border border-secondary-600 rounded-xl shadow-xl overflow-hidden"
                        >
                            <template x-if="loading">
                                <div class="p-4 text-center text-secondary-400">
                                    <svg class="w-5 h-5 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </div>
                            </template>

                            <template x-if="!loading && results.length === 0 && query.length >= 2">
                                <div class="p-4 text-center text-secondary-400 text-sm">
                                    Aucun resultat
                                </div>
                            </template>

                            <template x-for="item in results" :key="item.id">
                                <a :href="item.url" class="flex items-center gap-3 px-4 py-2.5 hover:bg-secondary-600 transition-colors">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-medium"
                                        :class="item.type === 'company' ? 'bg-primary-500/20 text-primary-400' : 'bg-success-500/20 text-success-400'"
                                        x-text="item.initials">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-white truncate text-sm" x-text="item.name"></div>
                                        <div class="text-xs text-secondary-400 truncate" x-text="item.subtitle"></div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 rounded-lg hover:bg-secondary-700 transition-colors text-secondary-400 hover:text-white" title="Actions rapides">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            @click.away="open = false"
                            class="absolute right-0 top-full mt-2 w-56 bg-secondary-700 border border-secondary-600 rounded-xl shadow-xl py-2"
                        >
                            <p class="px-4 py-2 text-xs font-semibold text-secondary-400 uppercase">Actions rapides</p>
                            <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-secondary-600 transition-colors">
                                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="text-sm">Nouvel utilisateur</span>
                            </a>
                            <a href="{{ route('admin.subscription-plans.create') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-secondary-600 transition-colors">
                                <svg class="w-4 h-4 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span class="text-sm">Nouveau plan</span>
                            </a>
                            <hr class="my-2 border-secondary-600">
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 hover:bg-secondary-600 transition-colors text-left">
                                    <svg class="w-4 h-4 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span class="text-sm">Vider le cache</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 rounded-lg hover:bg-secondary-700 transition-colors text-secondary-400 hover:text-white relative" title="Notifications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @php
                                $alertCount = \App\Models\Subscription::where('status', 'trialing')
                                    ->where('trial_ends_at', '<=', now()->addDays(3))
                                    ->count() + \App\Models\SubscriptionInvoice::where('status', 'pending')
                                    ->where('due_date', '<', now())
                                    ->count();
                            @endphp
                            @if($alertCount > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-danger-500 rounded-full text-xs flex items-center justify-center font-medium">{{ min($alertCount, 9) }}</span>
                            @endif
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            @click.away="open = false"
                            class="absolute right-0 top-full mt-2 w-80 bg-secondary-700 border border-secondary-600 rounded-xl shadow-xl overflow-hidden"
                        >
                            <div class="px-4 py-3 border-b border-secondary-600 flex items-center justify-between">
                                <span class="font-semibold">Notifications</span>
                                <span class="text-xs text-secondary-400">{{ $alertCount }} alertes</span>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                @php
                                    $expiringTrials = \App\Models\Subscription::with('company')
                                        ->where('status', 'trialing')
                                        ->where('trial_ends_at', '<=', now()->addDays(3))
                                        ->orderBy('trial_ends_at')
                                        ->take(3)
                                        ->get();
                                @endphp

                                @forelse($expiringTrials as $trial)
                                    <a href="{{ route('admin.subscriptions.show', $trial) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-secondary-600 transition-colors border-b border-secondary-600/50">
                                        <div class="w-8 h-8 bg-warning-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-white">Essai expire bientot</p>
                                            <p class="text-xs text-secondary-400 truncate">{{ $trial->company->name ?? 'N/A' }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <div class="px-4 py-8 text-center text-secondary-400 text-sm">
                                        Aucune alerte
                                    </div>
                                @endforelse
                            </div>
                            @if($alertCount > 0)
                                <a href="{{ route('admin.subscriptions.expiring-trials') }}" class="block px-4 py-3 text-center text-sm text-primary-400 hover:bg-secondary-600 border-t border-secondary-600">
                                    Voir tout
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Date -->
                    <span class="hidden lg:inline text-sm text-secondary-400">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 bg-success-500/20 border border-success-500/50 rounded-xl text-success-400 flex items-center gap-3" x-data="{ show: true }" x-show="show">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button @click="show = false" class="text-success-400 hover:text-success-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 bg-danger-500/20 border border-danger-500/50 rounded-xl text-danger-400 flex items-center gap-3" x-data="{ show: true }" x-show="show">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button @click="show = false" class="text-danger-400 hover:text-danger-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 p-4 bg-warning-500/20 border border-warning-500/50 rounded-xl text-warning-400 flex items-center gap-3" x-data="{ show: true }" x-show="show">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="flex-1">{{ session('warning') }}</span>
                        <button @click="show = false" class="text-warning-400 hover:text-warning-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="border-t border-secondary-700 px-6 py-4 text-center text-sm text-secondary-500">
                ComptaBE Admin &copy; {{ date('Y') }} - Laravel {{ app()->version() }}
            </footer>
        </div>
    </div>

    @stack('scripts')

    <script>
        function adminLayout() {
            return {
                sidebarCollapsed: localStorage.getItem('admin_sidebar_collapsed') === 'true',

                init() {
                    // Update CSS classes based on initial state
                    this.updateMainMargin();

                    // Keyboard shortcut for search
                    document.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                            e.preventDefault();
                            document.querySelector('input[placeholder*="Rechercher"]')?.focus();
                        }
                    });
                },

                toggleSidebar() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('admin_sidebar_collapsed', this.sidebarCollapsed);
                    this.updateMainMargin();
                },

                updateMainMargin() {
                    // This is handled by Alpine reactivity, but we can force update if needed
                }
            };
        }

        function globalSearch() {
            return {
                query: '',
                results: [],
                loading: false,
                showResults: false,

                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(`/admin/search?q=${encodeURIComponent(this.query)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        this.results = await response.json();
                    } catch (error) {
                        console.error('Search error:', error);
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
