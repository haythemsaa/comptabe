@props(['active' => ''])

<nav class="space-y-1">
    <!-- Entreprise -->
    <a href="{{ route('settings.company') }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'company' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium' : 'text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800' }} transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Entreprise
    </a>

    <!-- Peppol -->
    <a href="{{ route('settings.peppol') }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'peppol' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium' : 'text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800' }} transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Peppol
    </a>

    <!-- Facturation -->
    <a href="{{ route('settings.invoices') }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'invoices' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium' : 'text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800' }} transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Facturation
    </a>

    <!-- Utilisateurs -->
    <a href="{{ route('settings.users') }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'users' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium' : 'text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800' }} transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
        Utilisateurs
    </a>

    <!-- Separator -->
    <div class="my-4 border-t border-secondary-200 dark:border-secondary-700"></div>
    <p class="px-4 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Catalogue</p>

    <!-- Types de produits -->
    <a href="{{ route('settings.product-types.index') }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'product-types' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium' : 'text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800' }} transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        Types de produits
    </a>

    <!-- Catégories -->
    <a href="{{ route('settings.product-categories.index') }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'product-categories' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium' : 'text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800' }} transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        Catégories
    </a>

    <!-- Separator -->
    <div class="my-4 border-t border-secondary-200 dark:border-secondary-700"></div>

    <!-- Export -->
    <a href="{{ route('settings.export') }}"
       class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'export' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium' : 'text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800' }} transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export de données
    </a>
</nav>
