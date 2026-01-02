@extends('layouts.app')

@section('title', 'Clients - ' . $firm->name)

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900" x-data="clientsTable()">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        👥 Portfolio Clients
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $clientsData->count() }} clients • {{ $firm->name }}
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <a href="{{ route('firm.dashboard') }}" class="btn btn-secondary mr-2">
                        ← Retour au dashboard
                    </a>
                    <a href="{{ route('firm.clients.create') }}" class="btn btn-primary">
                        + Nouveau client
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters Bar -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Rechercher
                    </label>
                    <input type="text"
                           id="search"
                           x-model="search"
                           @input="applyFilters"
                           placeholder="Nom, N° TVA..."
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Statut
                    </label>
                    <select id="status"
                            x-model="statusFilter"
                            @change="applyFilters"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">Tous</option>
                        <option value="active">Actifs</option>
                        <option value="pending">En attente</option>
                        <option value="suspended">Suspendus</option>
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Trier par
                    </label>
                    <select id="sort"
                            x-model="sortBy"
                            @change="applyFilters"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="name">Nom (A-Z)</option>
                        <option value="revenue">CA (décroissant)</option>
                        <option value="health">Score santé</option>
                        <option value="outstanding">Impayés</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Client
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Score Santé
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                CA ({{ $period }})
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                TVA Balance
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Impayés
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Factures
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Manager
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($clientsData as $client)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition"
                                onclick="window.location.href='{{ route('firm.clients.show', $client['mandate']->id) }}'">
                                <!-- Client Name & Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                                <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
                                                    {{ strtoupper(substr($client['company']->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $client['company']->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $client['company']->vat_number ?? 'N° TVA non renseigné' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Health Score -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if($client['health_score']['color'] === 'green') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($client['health_score']['color'] === 'blue') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($client['health_score']['color'] === 'yellow') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($client['health_score']['color'] === 'orange') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @endif">
                                            {{ $client['health_score']['overall'] }}/100
                                        </span>
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $client['health_score']['status'] }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Revenue -->
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($client['metrics']['revenue'], 0, ',', ' ') }} €
                                    </div>
                                    @if($client['metrics']['margin'] > 0)
                                        <div class="text-xs text-green-600 dark:text-green-400">
                                            Marge: {{ number_format($client['metrics']['margin'], 0, ',', ' ') }} €
                                        </div>
                                    @endif
                                </td>

                                <!-- VAT Balance -->
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm {{ $client['metrics']['vat_balance'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ number_format($client['metrics']['vat_balance'], 0, ',', ' ') }} €
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        @if($client['metrics']['vat_balance'] > 0)
                                            À payer
                                        @elseif($client['metrics']['vat_balance'] < 0)
                                            À récupérer
                                        @else
                                            Équilibré
                                        @endif
                                    </div>
                                </td>

                                <!-- Outstanding -->
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if($client['metrics']['outstanding_amount'] > 0)
                                        <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ number_format($client['metrics']['outstanding_amount'], 0, ',', ' ') }} €
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $client['metrics']['outstanding_count'] }} facture(s)
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-400 dark:text-gray-500">
                                            -
                                        </div>
                                    @endif
                                </td>

                                <!-- Invoices Count -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $client['metrics']['invoices_count'] }}
                                    </span>
                                </td>

                                <!-- Manager -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $client['mandate']->manager?->name ?? 'Non assigné' }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('firm.clients.show', $client['mandate']->id) }}"
                                       class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300"
                                       onclick="event.stopPropagation()">
                                        Voir →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Aucun client trouvé
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Cards Below Table -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">CA Total</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ number_format($clientsData->sum('metrics.revenue'), 0, ',', ' ') }} €
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">TVA Nette</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ number_format($clientsData->sum('metrics.vat_balance'), 0, ',', ' ') }} €
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Impayés Totaux</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                    {{ number_format($clientsData->sum('metrics.outstanding_amount'), 0, ',', ' ') }} €
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function clientsTable() {
    return {
        search: '{{ $search ?? '' }}',
        statusFilter: '{{ $statusFilter }}',
        sortBy: '{{ $sortBy }}',

        init() {
            console.log('Clients table initialized');
        },

        applyFilters() {
            // Build query string
            const params = new URLSearchParams();

            if (this.search) params.append('search', this.search);
            if (this.statusFilter && this.statusFilter !== 'all') params.append('status', this.statusFilter);
            if (this.sortBy) params.append('sort', this.sortBy);

            // Reload page with filters
            window.location.href = '{{ route('firm.clients.list') }}?' + params.toString();
        }
    };
}
</script>
@endpush
@endsection
