<x-app-layout>
    <x-slot name="title">Devis</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Devis</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Devis</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Gerez vos propositions commerciales</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau devis
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Total devis</p>
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

            <!-- Envoyes -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Envoyes</p>
                        <h3 class="text-2xl font-semibold text-info-500">{{ $stats['sent'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Acceptes -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Acceptes</p>
                        <h3 class="text-2xl font-semibold text-success-500">{{ $stats['accepted'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Montant -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">En cours</p>
                        <h3 class="text-xl font-semibold text-secondary-800 dark:text-white">@currency($stats['total_amount'])</h3>
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
                        <a href="{{ route('quotes.index') }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ !request('status') ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Tous
                        </a>
                        <a href="{{ route('quotes.index', ['status' => 'draft']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'draft' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Brouillons
                        </a>
                        <a href="{{ route('quotes.index', ['status' => 'sent']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'sent' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Envoyes
                        </a>
                        <a href="{{ route('quotes.index', ['status' => 'accepted']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'accepted' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Acceptes
                        </a>
                        <a href="{{ route('quotes.index', ['status' => 'expired']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'expired' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Expires
                        </a>
                    </nav>
                    <div class="py-3">
                        <button @click="showFilters = !showFilters"
                                class="text-sm text-secondary-500 hover:text-primary-500 flex items-center gap-1.5 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filtres avances
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search & Actions Bar -->
            <div class="p-4 flex flex-wrap items-center gap-4 border-b border-secondary-100 dark:border-dark-100">
                <div class="flex-1 min-w-[250px]">
                    <form action="{{ route('quotes.index') }}" method="GET" class="relative">
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
                            placeholder="Rechercher par numero, client..."
                            class="form-input pl-9 py-2"
                        >
                    </form>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-secondary-500 dark:text-secondary-400">{{ $quotes->total() }} resultats</span>
                </div>
            </div>

            <!-- Extended Filters -->
            <div x-show="showFilters" x-collapse class="p-4 bg-secondary-50 dark:bg-dark-300 border-b border-secondary-100 dark:border-dark-100">
                <form action="{{ route('quotes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <div>
                        <label class="form-label">Date de</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Date a</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-1">Filtrer</button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary">
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
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Devis</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Client</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Montant</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Validite</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Statut</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100 dark:divide-dark-100">
                        @forelse($quotes as $quote)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-dark-300/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-md bg-info-100 dark:bg-info-500/20 flex items-center justify-center text-info-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('quotes.show', $quote) }}" class="font-medium text-secondary-800 dark:text-white hover:text-primary-500 transition-colors">
                                                {{ $quote->quote_number }}
                                            </a>
                                            @if($quote->reference)
                                                <p class="text-xs text-secondary-400 dark:text-secondary-500">Ref: {{ $quote->reference }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm avatar-info">
                                            {{ strtoupper(substr($quote->partner->name, 0, 2)) }}
                                        </div>
                                        <p class="font-medium text-secondary-700 dark:text-secondary-200">{{ $quote->partner->name }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-secondary-800 dark:text-white">@currency($quote->total_incl_vat)</p>
                                    <p class="text-xs text-secondary-400 dark:text-secondary-500">HT: @currency($quote->total_excl_vat)</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-secondary-700 dark:text-secondary-300">@dateFormat($quote->quote_date)</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if($quote->valid_until)
                                        <p class="text-secondary-700 dark:text-secondary-300 {{ $quote->isExpired() ? 'text-danger-500 line-through' : '' }}">
                                            @dateFormat($quote->valid_until)
                                        </p>
                                        @if($quote->isExpired())
                                            <p class="text-xs text-danger-500 font-medium">Expire</p>
                                        @elseif($quote->days_until_expiry !== null && $quote->days_until_expiry <= 7)
                                            <p class="text-xs text-warning-500">{{ $quote->days_until_expiry }}j restants</p>
                                        @endif
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge badge-pill badge-{{ $quote->status_color }}">
                                        {{ $quote->status_label }}
                                    </span>
                                    @if($quote->converted_invoice_id)
                                        <a href="{{ route('invoices.show', $quote->converted_invoice_id) }}" class="badge badge-pill badge-success mt-1">
                                            Converti
                                        </a>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('quotes.show', $quote) }}" class="btn btn-ghost btn-icon btn-sm" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-ghost btn-icon btn-sm" title="PDF" target="_blank">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        @if($quote->isEditable())
                                            <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-ghost btn-icon btn-sm" title="Modifier">
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
                                                @if($quote->status === 'draft')
                                                    <form action="{{ route('quotes.send', $quote) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                            </svg>
                                                            Marquer envoye
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(in_array($quote->status, ['draft', 'sent']))
                                                    <form action="{{ route('quotes.accept', $quote) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-success-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Accepter
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('quotes.reject', $quote) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-danger-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Refuser
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($quote->canConvert())
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('quotes.convert', $quote) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-primary-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                            </svg>
                                                            Convertir en facture
                                                        </button>
                                                    </form>
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('quotes.duplicate', $quote) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                        Dupliquer
                                                    </button>
                                                </form>
                                                @if($quote->isEditable())
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirm('Supprimer ce devis ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-danger-500">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-secondary-700 dark:text-secondary-300 mb-1">Aucun devis trouve</h3>
                                        <p class="text-secondary-500 dark:text-secondary-400 mb-4">Commencez par creer votre premier devis</p>
                                        <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Creer un devis
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($quotes->hasPages())
                <div class="px-5 py-4 border-t border-secondary-100 dark:border-dark-100 flex items-center justify-between">
                    <p class="text-sm text-secondary-500 dark:text-secondary-400">
                        Affichage de {{ $quotes->firstItem() }} a {{ $quotes->lastItem() }} sur {{ $quotes->total() }} devis
                    </p>
                    {{ $quotes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
