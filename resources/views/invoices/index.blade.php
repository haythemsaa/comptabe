<x-app-layout>
    <x-slot name="title">Factures de vente</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Factures de vente</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Factures de vente</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Gérez et suivez vos factures clients</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Exporter
                </button>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle facture
                </a>
            </div>
        </div>

        <!-- Stats Cards - Vuexy Style -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Total factures</p>
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

            <!-- En attente -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">En attente</p>
                        <h3 class="text-2xl font-semibold text-info-500">{{ $stats['sent'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- En retard -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-danger">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">En retard</p>
                        <h3 class="text-2xl font-semibold text-danger-500">{{ $stats['overdue'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table Card -->
        <div class="card" x-data="{ showFilters: false, selectedInvoices: [] }">
            <!-- Quick Filters Tabs -->
            <div class="border-b border-secondary-100 dark:border-dark-100">
                <div class="flex items-center justify-between px-5">
                    <nav class="flex gap-1 -mb-px">
                        <a href="{{ route('invoices.index') }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ !request('status') ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Toutes
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'draft']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'draft' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Brouillons
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'sent']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'sent' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Envoyées
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'paid']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'paid' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            Payées
                        </a>
                        <a href="{{ route('invoices.index', ['status' => 'overdue']) }}"
                           class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'overdue' ? 'border-primary-500 text-primary-500' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                            <span class="flex items-center gap-1.5">
                                En retard
                                @if($stats['overdue'] > 0)
                                    <span class="bg-danger-500 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $stats['overdue'] }}</span>
                                @endif
                            </span>
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

            <!-- Search & Actions Bar -->
            <div class="p-4 flex flex-wrap items-center gap-4 border-b border-secondary-100 dark:border-dark-100">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <form action="{{ route('invoices.index') }}" method="GET" class="relative">
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

                <!-- Bulk Actions (shown when items selected) -->
                <template x-if="selectedInvoices.length > 0">
                    <div class="flex items-center gap-2" x-data="{ batchMenuOpen: false }">
                        <span class="text-sm text-secondary-500 dark:text-secondary-400">
                            <span x-text="selectedInvoices.length"></span> sélectionnée(s)
                        </span>

                        <!-- Quick actions -->
                        <form method="POST" action="{{ route('invoices.batch.mark-sent') }}" class="inline">
                            @csrf
                            <template x-for="id in selectedInvoices" :key="id">
                                <input type="hidden" name="invoice_ids[]" :value="id">
                            </template>
                            <button type="submit" class="btn btn-light-info btn-sm" title="Marquer comme envoyées">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <span class="hidden sm:inline">Envoyer</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('invoices.batch.mark-paid') }}" class="inline">
                            @csrf
                            <template x-for="id in selectedInvoices" :key="id">
                                <input type="hidden" name="invoice_ids[]" :value="id">
                            </template>
                            <button type="submit" class="btn btn-light-success btn-sm" title="Marquer comme payées">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="hidden sm:inline">Payées</span>
                            </button>
                        </form>

                        <!-- More actions dropdown -->
                        <div class="relative">
                            <button @click="batchMenuOpen = !batchMenuOpen" type="button" class="btn btn-light-secondary btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            <div x-show="batchMenuOpen"
                                 @click.away="batchMenuOpen = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-secondary-800 rounded-lg shadow-xl border border-secondary-200 dark:border-secondary-700 z-20">
                                <div class="py-1">
                                    <form method="POST" action="{{ route('invoices.batch.send-reminders') }}">
                                        @csrf
                                        <template x-for="id in selectedInvoices" :key="id">
                                            <input type="hidden" name="invoice_ids[]" :value="id">
                                        </template>
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-700">
                                            <svg class="w-4 h-4 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                            Envoyer des rappels
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('invoices.batch.duplicate') }}">
                                        @csrf
                                        <template x-for="id in selectedInvoices" :key="id">
                                            <input type="hidden" name="invoice_ids[]" :value="id">
                                        </template>
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-700">
                                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            Dupliquer
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('invoices.batch.export-pdf') }}">
                                        @csrf
                                        <template x-for="id in selectedInvoices" :key="id">
                                            <input type="hidden" name="invoice_ids[]" :value="id">
                                        </template>
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-700">
                                            <svg class="w-4 h-4 text-info-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Exporter PDF
                                        </button>
                                    </form>

                                    <div class="border-t border-secondary-200 dark:border-secondary-700 my-1"></div>

                                    <form method="POST" action="{{ route('invoices.batch.destroy') }}" @submit.prevent="if(confirm('Supprimer les brouillons sélectionnés ?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <template x-for="id in selectedInvoices" :key="id">
                                            <input type="hidden" name="invoice_ids[]" :value="id">
                                        </template>
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Supprimer brouillons
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Clear selection -->
                        <button @click="selectedInvoices = []" class="text-secondary-400 hover:text-secondary-600 p-1" title="Désélectionner tout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- View Options -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-secondary-500 dark:text-secondary-400">{{ $invoices->total() }} résultats</span>
                </div>
            </div>

            <!-- Extended Filters -->
            <div x-show="showFilters" x-collapse class="p-4 bg-secondary-50 dark:bg-dark-300 border-b border-secondary-100 dark:border-dark-100">
                <form id="filterForm" action="{{ route('invoices.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <label class="form-label">Date à</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filtrer
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
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
                            <th class="w-10 px-5 py-3">
                                <input type="checkbox" class="form-checkbox" @change="selectedInvoices = $event.target.checked ? {{ $invoices->pluck('id') }} : []">
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Facture</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Client</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Montant</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Statut</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100 dark:divide-dark-100">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-dark-300/50 transition-colors">
                                <td class="px-5 py-4">
                                    <input type="checkbox" class="form-checkbox" value="{{ $invoice->id }}" x-model="selectedInvoices">
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-md bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center text-primary-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-secondary-800 dark:text-white hover:text-primary-500 transition-colors">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                            @if($invoice->reference)
                                                <p class="text-xs text-secondary-400 dark:text-secondary-500">Réf: {{ $invoice->reference }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm avatar-primary">
                                            {{ strtoupper(substr($invoice->partner->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-secondary-700 dark:text-secondary-200">{{ $invoice->partner->name }}</p>
                                            @if($invoice->partner->peppol_capable)
                                                <span class="inline-flex items-center gap-1 text-xs text-success-500">
                                                    <span class="w-1.5 h-1.5 bg-success-500 rounded-full"></span>
                                                    Peppol
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-secondary-800 dark:text-white">@currency($invoice->total_incl_vat)</p>
                                    <p class="text-xs text-secondary-400 dark:text-secondary-500">HT: @currency($invoice->total_excl_vat)</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-secondary-700 dark:text-secondary-300">@dateFormat($invoice->invoice_date)</p>
                                    @if($invoice->due_date)
                                        <p class="text-xs {{ $invoice->isOverdue() ? 'text-danger-500 font-medium' : 'text-secondary-400' }}">
                                            Éch: @dateFormat($invoice->due_date)
                                            @if($invoice->isOverdue())
                                                <span class="ml-1">({{ abs($invoice->days_until_due) }}j)</span>
                                            @endif
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge badge-pill badge-{{ $invoice->status_color }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                    @if($invoice->peppol_sent_at)
                                        <span class="badge badge-pill badge-info mt-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            Peppol
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-ghost btn-icon btn-sm" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-ghost btn-icon btn-sm" title="PDF" target="_blank">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        @if($invoice->isEditable())
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-ghost btn-icon btn-sm" title="Modifier">
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
                                                <a href="{{ route('invoices.show', $invoice) }}" class="dropdown-item flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    Voir détails
                                                </a>
                                                <a href="{{ route('invoices.pdf', $invoice) }}" class="dropdown-item flex items-center gap-2" target="_blank">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                                    </svg>
                                                    Télécharger PDF
                                                </a>
                                                @if($invoice->isEditable())
                                                    <a href="{{ route('invoices.edit', $invoice) }}" class="dropdown-item flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Modifier
                                                    </a>
                                                @endif
                                                @if($invoice->partner->peppol_capable && !$invoice->peppol_sent_at && $invoice->status === 'validated')
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('invoices.send-peppol', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Envoyer cette facture via le réseau Peppol à {{ $invoice->partner->name }} ?')">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 text-primary-500 w-full text-left">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                            </svg>
                                                            Envoyer via Peppol
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($invoice->isEditable())
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette facture ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item flex items-center gap-2 text-danger-500 w-full text-left">
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
                                        <h3 class="text-lg font-medium text-secondary-700 dark:text-secondary-300 mb-1">Aucune facture trouvée</h3>
                                        <p class="text-secondary-500 dark:text-secondary-400 mb-4">Commencez par créer votre première facture</p>
                                        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Créer une facture
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="px-5 py-4 border-t border-secondary-100 dark:border-dark-100 flex items-center justify-between">
                    <p class="text-sm text-secondary-500 dark:text-secondary-400">
                        Affichage de {{ $invoices->firstItem() }} à {{ $invoices->lastItem() }} sur {{ $invoices->total() }} factures
                    </p>
                    <div class="flex items-center gap-1">
                        @if($invoices->onFirstPage())
                            <span class="btn btn-ghost btn-sm opacity-50 cursor-not-allowed">Précédent</span>
                        @else
                            <a href="{{ $invoices->previousPageUrl() }}" class="btn btn-ghost btn-sm">Précédent</a>
                        @endif

                        @foreach($invoices->getUrlRange(1, $invoices->lastPage()) as $page => $url)
                            @if($page == $invoices->currentPage())
                                <span class="btn btn-primary btn-sm">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="btn btn-ghost btn-sm">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($invoices->hasMorePages())
                            <a href="{{ $invoices->nextPageUrl() }}" class="btn btn-ghost btn-sm">Suivant</a>
                        @else
                            <span class="btn btn-ghost btn-sm opacity-50 cursor-not-allowed">Suivant</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
