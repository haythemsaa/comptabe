@props(['title' => null, 'header' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Cabinet' }} - ComptaBE Expert</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .firm-sidebar { width: 280px; transition: width 0.3s ease; }
        .firm-sidebar.collapsed { width: 80px; }
        .firm-main { margin-left: 280px; transition: margin-left 0.3s ease; }
        .firm-main.sidebar-collapsed { margin-left: 80px; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    </style>
</head>
<body class="min-h-screen bg-secondary-900 text-white">
    <div class="flex min-h-screen" x-data="firmLayout()" x-init="init()">
        <!-- Firm Sidebar -->
        <aside
            class="firm-sidebar fixed inset-y-0 left-0 z-30 bg-secondary-800 border-r border-secondary-700 flex flex-col"
            :class="{ 'collapsed': sidebarCollapsed }"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center px-4 border-b border-secondary-700">
                <a href="{{ route('firm.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" x-cloak class="overflow-hidden">
                        <span class="font-bold text-lg whitespace-nowrap">ComptaBE</span>
                        <span class="block text-xs text-primary-400 font-medium">CABINET</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto scrollbar-thin">
                @php $firm = \App\Models\AccountingFirm::current(); @endphp

                <!-- Main Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Principal</p>

                    <a href="{{ route('firm.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('firm.dashboard') ? 'bg-primary-500/20 text-primary-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Tableau de bord</span>
                    </a>
                </div>

                <!-- Clients Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Clients</p>

                    <a href="{{ route('firm.clients.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('firm.clients.*') ? 'bg-primary-500/20 text-primary-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Mes clients</span>
                        @php $clientCount = $firm ? $firm->clientMandates()->where('status', 'active')->count() : 0; @endphp
                        <span x-show="!sidebarCollapsed" x-cloak class="px-2 py-0.5 text-xs bg-secondary-700 rounded-full">{{ $clientCount }}</span>
                    </a>

                    <a href="{{ route('firm.clients.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-secondary-300 hover:bg-secondary-700 hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Ajouter un client</span>
                    </a>
                </div>

                <!-- Tasks Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Taches</p>

                    <a href="{{ route('firm.tasks.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('firm.tasks.index') ? 'bg-primary-500/20 text-primary-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap flex-1">Toutes les taches</span>
                    </a>

                    <a href="{{ route('firm.tasks.my-tasks') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('firm.tasks.my-tasks') ? 'bg-primary-500/20 text-primary-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Mes taches</span>
                    </a>
                </div>

                <!-- Team Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Equipe</p>

                    <a href="{{ route('firm.team.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('firm.team.*') ? 'bg-primary-500/20 text-primary-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="whitespace-nowrap">Collaborateurs</span>
                    </a>
                </div>

                <!-- Settings Section -->
                <div class="mb-4">
                    <p x-show="!sidebarCollapsed" x-cloak class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider mb-2">Configuration</p>

                    <a href="{{ route('firm.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('firm.settings') ? 'bg-primary-500/20 text-primary-400' : 'text-secondary-300 hover:bg-secondary-700 hover:text-white' }}">
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
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center font-bold flex-shrink-0 shadow-lg shadow-primary-500/20">
                        {{ auth()->user()->initials ?? 'U' }}
                    </div>
                    <div x-show="!sidebarCollapsed" x-cloak class="flex-1 min-w-0 overflow-hidden">
                        <p class="font-medium truncate text-sm">{{ auth()->user()->full_name ?? 'Utilisateur' }}</p>
                        <p class="text-xs text-primary-400">Expert-Comptable</p>
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
        <div class="firm-main flex-1 min-h-screen flex flex-col" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
            <!-- Top Bar -->
            <header class="sticky top-0 z-20 h-16 bg-secondary-800/95 backdrop-blur border-b border-secondary-700 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    @if($header)
                        <h1 class="text-xl font-semibold">{{ $header }}</h1>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    @php $currentFirm = \App\Models\AccountingFirm::current(); @endphp
                    @if($currentFirm)
                        <span class="text-sm text-secondary-400">{{ $currentFirm->name }}</span>
                    @endif
                    <span class="hidden lg:inline text-sm text-secondary-500">|</span>
                    <span class="hidden lg:inline text-sm text-secondary-400">{{ now()->format('d/m/Y') }}</span>
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

                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="border-t border-secondary-700 px-6 py-4 text-center text-sm text-secondary-500">
                ComptaBE Expert-Comptable &copy; {{ date('Y') }}
            </footer>
        </div>
    </div>

    @stack('scripts')

    <script>
        function firmLayout() {
            return {
                sidebarCollapsed: localStorage.getItem('firm_sidebar_collapsed') === 'true',

                init() {
                    this.updateMainMargin();
                },

                toggleSidebar() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('firm_sidebar_collapsed', this.sidebarCollapsed);
                    this.updateMainMargin();
                },

                updateMainMargin() {}
            };
        }
    </script>
</body>
</html>
