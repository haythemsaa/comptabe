<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-bind:class="$store.app.darkMode ? 'dark' : ''">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }} - ComptaBE</title>

    <!-- PWA Meta Tags -->
    <meta name="description" content="Application de comptabilité belge conforme Peppol 2026. Facturation, TVA, e-Reporting et bien plus.">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="ComptaBE">

    <!-- iOS PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ComptaBE">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/images/icons/icon-192x192.png">

    <!-- Android PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icons/icon-512x512.png">

    <!-- Microsoft PWA Meta Tags -->
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="msapplication-TileImage" content="/images/icons/icon-144x144.png">
    <meta name="msapplication-config" content="/browserconfig.xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Force reload - cache buster -->
    <script>
        // EMERGENCY CACHE CLEAR - Clear any residual Inertia state
        if (window.history && window.history.scrollRestoration) {
            window.history.scrollRestoration = 'manual';
        }

        // FORCE UNREGISTER SERVICE WORKERS IMMEDIATELY
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for (let registration of registrations) {
                    registration.unregister();
                    console.log('[EMERGENCY] Service Worker unregistered');
                }
            });
        }

        // FORCE CLEAR ALL CACHES
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                    console.log('[EMERGENCY] Cache deleted:', name);
                }
            });
        }

        // DETECT IF LOADING OLD CACHED VERSION
        setTimeout(function() {
            if (typeof window.createChart === 'undefined') {
                // Check if we've already tried to reload (prevent infinite loop)
                const reloadCount = parseInt(sessionStorage.getItem('emergency_reload_count') || '0');
                if (reloadCount < 3) {
                    console.error('[EMERGENCY] createChart not loaded - FORCING HARD RELOAD (attempt ' + (reloadCount + 1) + ')');
                    sessionStorage.setItem('emergency_reload_count', (reloadCount + 1).toString());
                    // Force reload from server (bypass cache)
                    setTimeout(function() {
                        window.location.reload(true);
                    }, 500);
                } else {
                    console.error('[EMERGENCY] Failed to load after 3 attempts. Please clear browser cache manually.');
                    alert('⚠️ ERREUR DE CACHE\n\nLe navigateur charge une ancienne version.\n\nACTIONS REQUISES:\n1. Appuyez sur Ctrl+Shift+Delete (Cmd+Shift+Delete sur Mac)\n2. Sélectionnez "Tout" pour la période\n3. Cochez "Images et fichiers en cache"\n4. Cliquez "Effacer les données"\n5. Fermez COMPLÈTEMENT le navigateur\n6. Rouvrez et revenez sur cette page');
                    sessionStorage.removeItem('emergency_reload_count');
                }
            } else {
                // Success! Clear the reload counter
                sessionStorage.removeItem('emergency_reload_count');
                console.log('[SUCCESS] createChart loaded correctly!');
            }
        }, 2000);
    </script>
</head>
<body class="min-h-screen bg-light-100 dark:bg-dark-500 text-secondary-700 dark:text-secondary-300" x-init="$store.app.init()">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
            :class="$store.app.sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            @include('layouts.partials.sidebar')
        </aside>

        <!-- Sidebar Overlay (mobile) -->
        <div
            x-show="$store.app.sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="$store.app.toggleSidebar()"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"
        ></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="alert alert-success mb-6 animate-fade-in-down" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mb-6 animate-fade-in-down" x-data="{ show: true }" x-show="show">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="ml-auto">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content', $slot ?? '')
            </main>
        </div>
    </div>

    <!-- Command Palette (Ctrl+K) -->
    {{-- <x-command-palette /> --}}

    <!-- Keyboard Shortcuts Modal -->
    {{-- <x-keyboard-shortcuts-modal /> --}}

    <!-- Toast Notifications Container -->
    <div
        x-data="toastNotifications()"
        @toast.window="addToast($event.detail)"
        class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-8"
                class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg max-w-sm"
                :class="{
                    'bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700': toast.type === 'default',
                    'bg-success-50 dark:bg-success-900/50 border border-success-200 dark:border-success-800': toast.type === 'success',
                    'bg-danger-50 dark:bg-danger-900/50 border border-danger-200 dark:border-danger-800': toast.type === 'error',
                    'bg-warning-50 dark:bg-warning-900/50 border border-warning-200 dark:border-warning-800': toast.type === 'warning',
                    'bg-info-50 dark:bg-info-900/50 border border-info-200 dark:border-info-800': toast.type === 'info'
                }"
            >
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 text-danger-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 text-warning-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 text-info-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
                <span class="text-sm text-secondary-700 dark:text-secondary-200" x-text="toast.message"></span>
                <button @click="removeToast(toast.id)" class="ml-auto text-secondary-400 hover:text-secondary-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        function toastNotifications() {
            return {
                toasts: [],
                addToast(detail) {
                    const toast = {
                        id: Date.now(),
                        message: detail.message,
                        type: detail.type || 'default',
                        visible: true
                    };
                    this.toasts.push(toast);

                    // Auto dismiss after 5 seconds
                    setTimeout(() => {
                        this.removeToast(toast.id);
                    }, detail.duration || 5000);
                },
                removeToast(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) {
                        toast.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                }
            };
        }

        // Global function to show toast
        window.showToast = function(message, type = 'default', duration = 5000) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message, type, duration }
            }));
        };
    </script>

    <!-- AI Chat Assistant Widget -->
    {{-- <x-chat.chat-widget /> --}}

    <!-- Onboarding Components -->
    {{--
    @auth
        @if(!auth()->user()->is_superadmin)
            <div x-data="onboardingTour()" x-init="init()"></div>
            <x-onboarding.checklist />
            <x-onboarding.survey />
        @endif
    @endauth
    --}}

    @stack('scripts')

    <!-- PWA Service Worker & Installation -->
    <script src="/js/pwa.js"></script>
</body>
</html>
