<x-app-layout>
    <x-slot name="title">Factures d'achat</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 dark:text-secondary-400 dark:hover:text-primary-400">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Factures d'achat</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Factures d'achat</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Factures reçues de vos fournisseurs</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle facture
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500 dark:text-secondary-400">Total factures</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['total'] }}</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500 dark:text-secondary-400">A comptabiliser</div>
                <div class="text-2xl font-bold text-warning-600">{{ $stats['pending'] }}</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500 dark:text-secondary-400">Montant dû</div>
                <div class="text-2xl font-bold text-danger-600">@currency($stats['total_due'])</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="p-4 flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <form action="{{ route('purchases.index') }}" method="GET" class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher par numéro ou fournisseur..."
                            class="form-input pl-10"
                        >
                    </form>
                </div>

                <!-- Status Filter -->
                <form action="{{ route('purchases.index') }}" method="GET" id="statusForm">
                    <select name="status" onchange="this.form.submit()" class="form-select w-auto">
                        <option value="">Tous les statuts</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validé</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payé</option>
                    </select>
                </form>

                <!-- Peppol Filter -->
                <form action="{{ route('purchases.index') }}" method="GET">
                    <select name="peppol" onchange="this.form.submit()" class="form-select w-auto">
                        <option value="">Toutes sources</option>
                        <option value="pending" {{ request('peppol') === 'pending' ? 'selected' : '' }}>Peppol non traité</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th>Échéance</th>
                            <th>Montant TTC</th>
                            <th>Statut</th>
                            <th>Comptabilisé</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="animate-fade-in" style="animation-delay: {{ $loop->index * 50 }}ms">
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    @if($invoice->peppol_status === 'received')
                                        <div class="text-xs text-success-600 flex items-center gap-1 mt-1">
                                            <span class="w-1.5 h-1.5 bg-success-500 rounded-full"></span>
                                            Peppol
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm bg-secondary-100 dark:bg-dark-100">
                                            {{ substr($invoice->partner->name ?? '?', 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-secondary-900 dark:text-white">{{ $invoice->partner->name ?? 'Fournisseur inconnu' }}</div>
                                            @if($invoice->partner?->vat_number)
                                                <div class="text-xs text-secondary-500 dark:text-secondary-400">{{ $invoice->partner->vat_number }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>@dateFormat($invoice->invoice_date)</td>
                                <td>
                                    @if($invoice->due_date)
                                        <span class="{{ $invoice->isOverdue() ? 'text-danger-600 font-medium' : '' }}">
                                            @dateFormat($invoice->due_date)
                                        </span>
                                        @if($invoice->isOverdue())
                                            <div class="text-xs text-danger-500">{{ abs($invoice->days_until_due) }}j retard</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="font-medium">@currency($invoice->total_incl_vat)</td>
                                <td>
                                    <span class="badge badge-{{ $invoice->status_color }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($invoice->is_booked)
                                        <span class="badge badge-success">Oui</span>
                                    @else
                                        <span class="badge badge-warning">Non</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn-ghost btn-icon btn-sm" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-secondary-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-secondary-500 dark:text-secondary-400 text-lg">Aucune facture d'achat</p>
                                    <p class="text-secondary-400 dark:text-secondary-500 text-sm mt-2">Les factures Peppol apparaîtront automatiquement ici</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-secondary-200 dark:border-dark-100">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
