<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'ComptaBE' }} - Logiciel de comptabilite belge</title>
    <meta name="description" content="{{ $description ?? 'ComptaBE - Solution de comptabilite belge 100% conforme Peppol pour vos obligations B2B. Facturation electronique, TVA, CODA.' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white dark:bg-secondary-950">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-secondary-900/80 backdrop-blur-lg border-b border-gray-200 dark:border-secondary-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">ComptaBE</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('pricing') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400 font-medium">Tarifs</a>
                    <a href="#features" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400 font-medium">Fonctionnalites</a>
                    <a href="#faq" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400 font-medium">FAQ</a>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                            Mon compte
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="px-5 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition-colors">
                            Essai gratuit
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 dark:bg-secondary-900 border-t border-gray-200 dark:border-secondary-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">ComptaBE</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4 max-w-md">
                        La solution de comptabilite belge 100% conforme pour vos obligations de facturation electronique B2B 2026.
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">
                        &copy; {{ date('Y') }} ComptaBE. Tous droits reserves.
                    </p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Produit</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('pricing') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Tarifs</a></li>
                        <li><a href="#features" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Fonctionnalites</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Peppol</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Securite</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Conditions</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Confidentialite</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Cookies</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-500">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
