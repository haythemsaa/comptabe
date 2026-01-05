<x-app-layout>
    <x-slot name="title">Commandes E-commerce</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('ecommerce.connections') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">E-commerce</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Commandes</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Commandes E-commerce</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Commandes importees de vos boutiques en ligne</p>
            </div>
            <div class="flex items-center gap-3">
                @if($pendingOrders > 0)
                    <form action="{{ route('ecommerce.orders.create-invoices') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Creer {{ $pendingOrders }} facture(s)
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-secondary-100 text-secondary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Total</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $orders->total() }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-warning-100 text-warning-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">En attente</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $pendingOrders }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-info-100 text-info-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Factures creees</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $invoicedOrders }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">CA ce mois</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($monthlyRevenue, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-primary-100 text-primary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Panier moyen</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($averageCart, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card p-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Boutique</label>
                    <select name="connection" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($connections as $conn)
                            <option value="{{ $conn->id }}" {{ request('connection') == $conn->id ? 'selected' : '' }}>{{ $conn->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>En cours</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Termine</option>
                        <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Facture</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annule</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-select">
                        <option value="">Tout</option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="N° commande, client...">
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if(request()->hasAny(['connection', 'status', 'period', 'search']))
                    <a href="{{ route('ecommerce.orders') }}" class="btn btn-outline-secondary">Reinitialiser</a>
                @endif
            </form>
        </div>

        <!-- Liste des commandes -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" class="form-checkbox" id="select-all">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Commande</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Boutique</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Facture</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($orders as $order)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" class="form-checkbox order-checkbox" value="{{ $order->id }}">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-secondary-800 dark:text-white">#{{ $order->external_order_id }}</div>
                                    <div class="text-xs text-secondary-500">{{ $order->external_order_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-secondary-600 dark:text-secondary-400">{{ $order->connection?->name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-secondary-800 dark:text-white">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-secondary-500">{{ $order->customer_email }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $order->order_date->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusColors = [
                                            'pending' => 'badge-warning',
                                            'processing' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'invoiced' => 'badge-primary',
                                            'cancelled' => 'badge-danger',
                                            'refunded' => 'badge-secondary',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'En attente',
                                            'processing' => 'En cours',
                                            'completed' => 'Termine',
                                            'invoiced' => 'Facture',
                                            'cancelled' => 'Annule',
                                            'refunded' => 'Rembourse',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$order->status] ?? 'badge-secondary' }}">
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">
                                    {{ number_format($order->total_amount, 2, ',', ' ') }} {{ $order->currency }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($order->invoice_id)
                                        <a href="{{ route('invoices.show', $order->invoice_id) }}" class="text-primary-600 hover:text-primary-800">
                                            {{ $order->invoice?->number }}
                                        </a>
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="showOrderDetail({{ $order->id }})" class="text-secondary-500 hover:text-primary-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        @if(!$order->invoice_id)
                                            <form action="{{ route('ecommerce.orders.create-invoice', $order) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-secondary-500 hover:text-success-500" title="Creer facture">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-secondary-500">Aucune commande</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Detail Commande -->
    <div id="order-detail-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('order-detail-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-2xl w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Detail de la commande</h3>
                <div id="order-detail-content">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('order-detail-modal').classList.add('hidden')" class="btn btn-outline-secondary">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
        });

        function showOrderDetail(orderId) {
            document.getElementById('order-detail-content').innerHTML = '<div class="text-center py-8"><svg class="animate-spin h-8 w-8 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
            document.getElementById('order-detail-modal').classList.remove('hidden');

            fetch(`/ecommerce/orders/${orderId}/detail`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('order-detail-content').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('order-detail-content').innerHTML = '<p class="text-danger-500">Erreur lors du chargement</p>';
                });
        }
    </script>
    @endpush
</x-app-layout>
