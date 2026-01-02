<x-app-layout>
    <x-slot name="title">Notes de crédit</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Notes de crédit</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Notes de crédit</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Gérez vos notes de crédit et avoirs clients</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Exporter
                </button>
                <a href="{{ route('credit-notes.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle note de crédit
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Total notes</p>
                        <h3 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Brouillons -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Brouillons</p>
                        <h3 class="text-2xl font-semibold text-warning-500">{{ $stats['draft'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Validées -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Validées</p>
                        <h3 class="text-2xl font-semibold text-success-500">{{ $stats['validated'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Montant total -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Montant total</p>
                        <h3 class="text-2xl font-semibold text-info-500">@currency($stats['total_amount'])</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table Card -->
        <div class="card" x-data="{ showFilters: false }">
            <!-- Quick Filters Tabs -->
            <div class="border-b border-secondary-100 dark:border-dark-100">
                <div class="flex items-center justify-between px-5">
                    <nav class="flex gap-1 -mb-px">
                        <a href="{{ route('credit-notes.index') }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ !request('status') ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Toutes
                        </a>
                        <a href="{{ route('credit-notes.index', ['status' => 'draft']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'draft' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Brouillons
                        </a>
                        <a href="{{ route('credit-notes.index', ['status' => 'validated']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'validated' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Validées
                        </a>
                        <a href="{{ route('credit-notes.index', ['status' => 'sent']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'sent' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Envoyées
                        </a>
                        <a href="{{ route('credit-notes.index', ['status' => 'applied']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'applied' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Appliquées
                        </a>
                    </nav>
                    <div class="py-3">
                        <button @click="showFilters = !showFilters"
                                class="text-sm text-secondary-500 hover:text-primary-500 flex items-center gap-1.5 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filtres avancés
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="p-4 flex flex-wrap items-center gap-4 border-b border-secondary-100 dark:border-dark-100">
                <div class="flex-1 min-w-[250px]">
                    <form action="{{ route('credit-notes.index') }}" method="GET" class="relative">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher par numéro, client..."
                            class="form-input pl-9 py-2"
                        >
                    </form>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-secondary-500 dark:text-secondary-400">{{ $creditNotes->total() }} résultats</span>
                </div>
            </div>

            <!-- Extended Filters -->
            <div x-show="showFilters" x-collapse class="p-4 bg-secondary-50 dark:bg-dark-300 border-b border-secondary-100 dark:border-dark-100">
                <form action="{{ route('credit-notes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div>
                        <label class="form-label">Client</label>
                        <select name="partner" class="form-select">
                            <option value="">Tous les clients</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ request('partner') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filtrer
                        </button>
                        <a href="{{ route('credit-notes.index') }}" class="btn btn-outline-secondary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-dark-300">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Note de crédit</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Client</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Facture liée</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Montant</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Statut</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100 dark:divide-dark-100">
                        @forelse($creditNotes as $creditNote)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-dark-300/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-md bg-danger-100 dark:bg-danger-500/20 flex items-center justify-center text-danger-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('credit-notes.show', $creditNote) }}" class="font-medium text-secondary-800 dark:text-white hover:text-primary-500 transition-colors">
                                                {{ $creditNote->credit_note_number }}
                                            </a>
                                            @if($creditNote->reference)
                                                <p class="text-xs text-secondary-400 dark:text-secondary-500">{{ $creditNote->reference }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm avatar-primary">
                                            {{ strtoupper(substr($creditNote->partner->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-secondary-700 dark:text-secondary-200">{{ $creditNote->partner->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($creditNote->invoice)
                                        <a href="{{ route('invoices.show', $creditNote->invoice) }}" class="text-primary-500 hover:underline">
                                            {{ $creditNote->invoice->invoice_number }}
                                        </a>
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-danger-600">-@currency($creditNote->total_incl_vat)</p>
                                    <p class="text-xs text-secondary-400 dark:text-secondary-500">HT: -@currency($creditNote->total_excl_vat)</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-secondary-700 dark:text-secondary-300">@dateFormat($creditNote->credit_note_date)</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge badge-pill badge-{{ $creditNote->status_color }}">
                                        {{ $creditNote->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('credit-notes.show', $creditNote) }}" class="btn btn-ghost btn-icon btn-sm" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('credit-notes.pdf', $creditNote) }}" class="btn btn-ghost btn-icon btn-sm" title="PDF" target="_blank">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        @if($creditNote->isEditable())
                                            <a href="{{ route('credit-notes.edit', $creditNote) }}" class="btn btn-ghost btn-icon btn-sm" title="Modifier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        <!-- Dropdown Menu -->
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="btn btn-ghost btn-icon btn-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-transition
                                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-dark-200 rounded-md shadow-lg z-10 py-1">
                                                <a href="{{ route('credit-notes.show', $creditNote) }}" class="dropdown-item flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    Voir détails
                                                </a>
                                                <a href="{{ route('credit-notes.pdf', $creditNote) }}" class="dropdown-item flex items-center gap-2" target="_blank">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                                    </svg>
                                                    Télécharger PDF
                                                </a>
                                                @if($creditNote->isEditable())
                                                    <a href="{{ route('credit-notes.edit', $creditNote) }}" class="dropdown-item flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Modifier
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('credit-notes.destroy', $creditNote) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 text-danger-500 w-full text-left" onclick="return confirm('Supprimer cette note de crédit ?')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-full bg-secondary-100 dark:bg-dark-100 flex items-center justify-center mb-4">
                                            <svg class="w-10 h-10 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-secondary-700 dark:text-secondary-300 mb-1">Aucune note de crédit</h3>
                                        <p class="text-secondary-500 dark:text-secondary-400 mb-4">Créez votre première note de crédit</p>
                                        <a href="{{ route('credit-notes.create') }}" class="btn btn-primary">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Créer une note de crédit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($creditNotes->hasPages())
                <div class="px-5 py-4 border-t border-secondary-100 dark:border-dark-100 flex items-center justify-between">
                    <p class="text-sm text-secondary-500 dark:text-secondary-400">
                        Affichage de {{ $creditNotes->firstItem() }} à {{ $creditNotes->lastItem() }} sur {{ $creditNotes->total() }} notes
                    </p>
                    <div class="flex items-center gap-1">
                        @if($creditNotes->onFirstPage())
                            <span class="btn btn-ghost btn-sm opacity-50 cursor-not-allowed">Précédent</span>
                        @else
                            <a href="{{ $creditNotes->previousPageUrl() }}" class="btn btn-ghost btn-sm">Précédent</a>
                        @endif

                        @foreach($creditNotes->getUrlRange(1, $creditNotes->lastPage()) as $page => $url)
                            @if($page == $creditNotes->currentPage())
                                <span class="btn btn-primary btn-sm">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="btn btn-ghost btn-sm">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($creditNotes->hasMorePages())
                            <a href="{{ $creditNotes->nextPageUrl() }}" class="btn btn-ghost btn-sm">Suivant</a>
                        @else
                            <span class="btn btn-ghost btn-sm opacity-50 cursor-not-allowed">Suivant</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
