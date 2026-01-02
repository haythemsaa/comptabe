<x-app-layout>
    <x-slot name="title">Factures recurrentes</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Factures recurrentes</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Factures recurrentes</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Automatisez la generation de vos factures</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('recurring-invoices.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle recurrence
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Total</p>
                        <h3 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Active -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Actives</p>
                        <h3 class="text-2xl font-semibold text-success-500">{{ $stats['active'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Paused -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">En pause</p>
                        <h3 class="text-2xl font-semibold text-warning-500">{{ $stats['paused'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Due Today -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">A generer</p>
                        <h3 class="text-2xl font-semibold text-info-500">{{ $stats['due_today'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Revenu mensuel</p>
                        <h3 class="text-xl font-semibold text-secondary-800 dark:text-white">@currency($stats['monthly_revenue'])</h3>
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
                        <a href="{{ route('recurring-invoices.index') }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ !request('status') ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Toutes
                        </a>
                        <a href="{{ route('recurring-invoices.index', ['status' => 'active']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'active' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Actives
                        </a>
                        <a href="{{ route('recurring-invoices.index', ['status' => 'paused']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'paused' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            En pause
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

            <!-- Extended Filters -->
            <div x-show="showFilters" x-collapse class="p-4 bg-secondary-50 dark:bg-dark-300 border-b border-secondary-100 dark:border-dark-100">
                <form action="{{ route('recurring-invoices.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <label class="form-label">Frequence</label>
                        <select name="frequency" class="form-select">
                            <option value="">Toutes</option>
                            <option value="weekly" {{ request('frequency') === 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                            <option value="monthly" {{ request('frequency') === 'monthly' ? 'selected' : '' }}>Mensuelle</option>
                            <option value="quarterly" {{ request('frequency') === 'quarterly' ? 'selected' : '' }}>Trimestrielle</option>
                            <option value="yearly" {{ request('frequency') === 'yearly' ? 'selected' : '' }}>Annuelle</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-1">Filtrer</button>
                        <a href="{{ route('recurring-invoices.index') }}" class="btn btn-outline-secondary">
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
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Nom</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Client</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Frequence</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Montant</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Prochaine</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Statut</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100 dark:divide-dark-100">
                        @forelse($recurringInvoices as $recurring)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-dark-300/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-md bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center text-primary-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('recurring-invoices.show', $recurring) }}" class="font-medium text-secondary-800 dark:text-white hover:text-primary-500 transition-colors">
                                                {{ $recurring->name }}
                                            </a>
                                            <p class="text-xs text-secondary-400">{{ $recurring->invoices_generated }} factures generees</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm avatar-primary">
                                            {{ strtoupper(substr($recurring->partner->name, 0, 2)) }}
                                        </div>
                                        <p class="font-medium text-secondary-700 dark:text-secondary-200">{{ $recurring->partner->name }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge badge-light-primary">{{ $recurring->frequency_label }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-secondary-800 dark:text-white">@currency($recurring->total_incl_vat)</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if($recurring->next_invoice_date)
                                        <p class="text-secondary-700 dark:text-secondary-300">@dateFormat($recurring->next_invoice_date)</p>
                                        @if($recurring->isDueToday())
                                            <span class="text-xs text-success-500 font-medium">Aujourd'hui</span>
                                        @elseif($recurring->next_invoice_date->isPast())
                                            <span class="text-xs text-danger-500 font-medium">En retard</span>
                                        @endif
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge badge-pill badge-{{ $recurring->status_color }}">
                                        {{ $recurring->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('recurring-invoices.show', $recurring) }}" class="btn btn-ghost btn-icon btn-sm" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($recurring->status !== 'completed')
                                            <a href="{{ route('recurring-invoices.edit', $recurring) }}" class="btn btn-ghost btn-icon btn-sm" title="Modifier">
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
                                                @if($recurring->status === 'active')
                                                    <form action="{{ route('recurring-invoices.generate', $recurring) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-primary-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            Generer maintenant
                                                        </button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('recurring-invoices.pause', $recurring) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-warning-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Mettre en pause
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($recurring->status === 'paused')
                                                    <form action="{{ route('recurring-invoices.resume', $recurring) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-success-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Reprendre
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(!in_array($recurring->status, ['completed', 'cancelled']))
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('recurring-invoices.cancel', $recurring) }}" method="POST" onsubmit="return confirm('Annuler cette recurrence ?')">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 w-full text-left text-danger-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            Annuler
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-secondary-700 dark:text-secondary-300 mb-1">Aucune recurrence trouvee</h3>
                                        <p class="text-secondary-500 dark:text-secondary-400 mb-4">Creez votre premiere facture recurrente</p>
                                        <a href="{{ route('recurring-invoices.create') }}" class="btn btn-primary">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Creer une recurrence
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($recurringInvoices->hasPages())
                <div class="px-5 py-4 border-t border-secondary-100 dark:border-dark-100 flex items-center justify-between">
                    <p class="text-sm text-secondary-500 dark:text-secondary-400">
                        Affichage de {{ $recurringInvoices->firstItem() }} a {{ $recurringInvoices->lastItem() }} sur {{ $recurringInvoices->total() }}
                    </p>
                    {{ $recurringInvoices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
