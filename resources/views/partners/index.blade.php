<x-app-layout>
    <x-slot name="title">Partenaires</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Partenaires</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Partenaires</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Gérez vos clients et fournisseurs</p>
            </div>
            <a href="{{ route('partners.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau partenaire
            </a>
        </div>

        <!-- Tabs -->
        <div class="border-b border-secondary-200 dark:border-secondary-700">
            <nav class="flex gap-4" x-data="{ tab: '{{ request('type', 'all') }}' }">
                <a
                    href="{{ route('partners.index') }}"
                    class="tab {{ !request('type') ? 'tab-active' : '' }}"
                >
                    Tous
                    <span class="ml-2 px-2 py-0.5 text-xs bg-secondary-100 dark:bg-secondary-800 rounded-full">{{ $stats['total'] }}</span>
                </a>
                <a
                    href="{{ route('partners.index', ['type' => 'customer']) }}"
                    class="tab {{ request('type') === 'customer' ? 'tab-active' : '' }}"
                >
                    Clients
                    <span class="ml-2 px-2 py-0.5 text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full">{{ $stats['customers'] }}</span>
                </a>
                <a
                    href="{{ route('partners.index', ['type' => 'supplier']) }}"
                    class="tab {{ request('type') === 'supplier' ? 'tab-active' : '' }}"
                >
                    Fournisseurs
                    <span class="ml-2 px-2 py-0.5 text-xs bg-warning-100 dark:bg-warning-900/30 text-warning-600 rounded-full">{{ $stats['suppliers'] }}</span>
                </a>
            </nav>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="p-4 flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <form action="{{ route('partners.index') }}" method="GET" class="relative">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher par nom, TVA..."
                            class="form-input pl-10"
                        >
                    </form>
                </div>

                <!-- Peppol Filter -->
                <select name="peppol" onchange="window.location.href=this.value" class="form-select w-auto">
                    <option value="{{ route('partners.index', array_merge(request()->except('peppol'), [])) }}">Tous</option>
                    <option value="{{ route('partners.index', array_merge(request()->except('peppol'), ['peppol' => '1'])) }}" {{ request('peppol') === '1' ? 'selected' : '' }}>
                        Compatible Peppol
                    </option>
                    <option value="{{ route('partners.index', array_merge(request()->except('peppol'), ['peppol' => '0'])) }}" {{ request('peppol') === '0' ? 'selected' : '' }}>
                        Non compatible Peppol
                    </option>
                </select>
            </div>
        </div>

        <!-- Grid/Table -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($partners as $partner)
                <div class="card card-hover animate-fade-in" style="animation-delay: {{ $loop->index * 50 }}ms">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-{{ $partner->is_customer ? 'primary' : 'warning' }}-100 dark:bg-{{ $partner->is_customer ? 'primary' : 'warning' }}-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-lg font-bold text-{{ $partner->is_customer ? 'primary' : 'warning' }}-600">
                                    {{ strtoupper(substr($partner->name, 0, 2)) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('partners.show', $partner) }}" class="font-semibold text-secondary-900 dark:text-white hover:text-primary-600 truncate">
                                        {{ $partner->name }}
                                    </a>
                                    @if($partner->peppol_capable)
                                        <span class="flex-shrink-0 w-2 h-2 bg-success-500 rounded-full" title="Peppol compatible"></span>
                                    @endif
                                </div>
                                @if($partner->vat_number)
                                    <div class="text-sm text-secondary-500 font-mono">{{ $partner->vat_number }}</div>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @if($partner->is_customer)
                                        <span class="badge badge-primary text-xs">Client</span>
                                    @endif
                                    @if($partner->is_supplier)
                                        <span class="badge badge-warning text-xs">Fournisseur</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="mt-4 pt-4 border-t border-secondary-100 dark:border-secondary-800 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-secondary-500">Factures</div>
                                <div class="font-semibold text-secondary-900 dark:text-white">{{ $partner->invoices_count ?? 0 }}</div>
                            </div>
                            <div>
                                <div class="text-secondary-500">CA total</div>
                                <div class="font-semibold text-secondary-900 dark:text-white">@currency($partner->total_revenue ?? 0)</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 flex items-center justify-end gap-2">
                            <a href="{{ route('partners.show', $partner) }}" class="btn-ghost btn-icon btn-sm" title="Voir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('partners.edit', $partner) }}" class="btn-ghost btn-icon btn-sm" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if($partner->is_customer)
                                <a href="{{ route('invoices.create', ['partner' => $partner->id]) }}" class="btn-ghost btn-icon btn-sm text-primary-600" title="Créer une facture">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="card">
                        <div class="card-body text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-secondary-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-secondary-500 text-lg mb-4">Aucun partenaire trouvé</p>
                            <a href="{{ route('partners.create') }}" class="btn btn-primary">
                                Créer votre premier partenaire
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($partners->hasPages())
            <div class="flex justify-center">
                {{ $partners->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
