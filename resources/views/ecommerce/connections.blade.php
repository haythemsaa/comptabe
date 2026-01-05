<x-app-layout>
    <x-slot name="title">Connexions E-commerce</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Connexions E-commerce</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Connexions E-commerce</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Gerez vos connexions WooCommerce, Shopify et autres plateformes</p>
            </div>
            <button type="button" onclick="document.getElementById('connect-modal').classList.remove('hidden')" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle connexion
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-primary-100 text-primary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Connexions actives</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $connections->where('status', 'active')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Commandes ce mois</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $ordersThisMonth }}</p>
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
                        <p class="text-sm text-secondary-500">Derniere synchro</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $lastSync ? $lastSync->diffForHumans() : 'Jamais' }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-info-100 text-info-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Produits mappes</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $productMappingsCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des connexions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($connections as $connection)
                <div class="card p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            @switch($connection->platform)
                                @case('woocommerce')
                                    <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-purple-600" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                        </svg>
                                    </div>
                                    @break
                                @case('shopify')
                                    <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M15.34 15.36c-.14-.04-1.43-.43-1.43-.43-.35-.12-.53.1-.58.21-.06.13-.48.95-.59 1.15-.1.19-.21.22-.39.14-1.06-.53-2.19-1.03-2.97-2.47-.13-.24.08-.36.19-.47.51-.51.57-.67.71-.94.14-.26.07-.49-.04-.68-.1-.19-.56-1.35-.77-1.85-.21-.5-.43-.43-.58-.44-.15-.01-.32-.01-.49-.01-.17 0-.45.06-.69.34-.23.28-.9.87-.9 2.13 0 1.26.92 2.47 1.05 2.64.13.17 1.76 2.82 4.38 3.85 2.62 1.03 2.62.69 3.09.64.47-.04 1.52-.62 1.73-1.22.21-.6.21-1.11.15-1.22-.06-.1-.23-.15-.49-.26z"/>
                                        </svg>
                                    </div>
                                    @break
                                @case('prestashop')
                                    <div class="w-12 h-12 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </div>
                                    @break
                                @default
                                    <div class="w-12 h-12 rounded-lg bg-secondary-100 dark:bg-secondary-700 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                    </div>
                            @endswitch
                            <div>
                                <h3 class="font-medium text-secondary-800 dark:text-white">{{ $connection->name }}</h3>
                                <p class="text-sm text-secondary-500 capitalize">{{ $connection->platform }}</p>
                            </div>
                        </div>
                        <span class="badge {{ $connection->status == 'active' ? 'badge-success' : ($connection->status == 'error' ? 'badge-danger' : 'badge-secondary') }}">
                            {{ $connection->status == 'active' ? 'Actif' : ($connection->status == 'error' ? 'Erreur' : 'Inactif') }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-secondary-500">URL</span>
                            <span class="text-secondary-700 dark:text-secondary-300 truncate max-w-[200px]">{{ $connection->store_url }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-500">Derniere synchro</span>
                            <span class="text-secondary-700 dark:text-secondary-300">{{ $connection->last_sync_at?->format('d/m/Y H:i') ?? 'Jamais' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-500">Commandes importees</span>
                            <span class="text-secondary-700 dark:text-secondary-300">{{ $connection->orders_count ?? 0 }}</span>
                        </div>
                    </div>

                    @if($connection->last_error)
                        <div class="mt-3 p-2 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                            <p class="text-xs text-danger-600 dark:text-danger-400">{{ Str::limit($connection->last_error, 100) }}</p>
                        </div>
                    @endif

                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-secondary-200 dark:border-secondary-700">
                        <form action="{{ route('ecommerce.sync', $connection) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Synchroniser
                            </button>
                        </form>
                        <a href="{{ route('ecommerce.orders', ['connection' => $connection->id]) }}" class="btn btn-sm btn-outline-secondary">Commandes</a>
                        <a href="{{ route('ecommerce.mappings', $connection) }}" class="btn btn-sm btn-outline-secondary">Mappings</a>
                        <div class="ml-auto flex items-center gap-1">
                            <button type="button" onclick="editConnection({{ json_encode($connection) }})" class="text-secondary-500 hover:text-warning-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                            <form action="{{ route('ecommerce.connections.destroy', $connection) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette connexion ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-secondary-500 hover:text-danger-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full card p-12 text-center">
                    <svg class="w-16 h-16 text-secondary-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-2">Aucune connexion</h3>
                    <p class="text-secondary-500 mb-4">Connectez votre boutique WooCommerce, Shopify ou PrestaShop</p>
                    <button type="button" onclick="document.getElementById('connect-modal').classList.remove('hidden')" class="btn btn-primary">
                        Ajouter une connexion
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Connexion -->
    <div id="connect-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeConnectModal()"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 id="connect-modal-title" class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Nouvelle connexion</h3>
                <form id="connect-form" method="POST" action="{{ route('ecommerce.connections.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="connect-method" value="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Nom de la connexion <span class="text-danger-500">*</span></label>
                            <input type="text" name="name" id="conn_name" class="form-input" placeholder="Ma boutique WooCommerce" required>
                        </div>
                        <div>
                            <label class="form-label">Plateforme <span class="text-danger-500">*</span></label>
                            <select name="platform" id="conn_platform" class="form-select" required onchange="updatePlatformFields()">
                                <option value="woocommerce">WooCommerce</option>
                                <option value="shopify">Shopify</option>
                                <option value="prestashop">PrestaShop</option>
                                <option value="magento">Magento</option>
                                <option value="custom">API Personnalisee</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">URL de la boutique <span class="text-danger-500">*</span></label>
                            <input type="url" name="store_url" id="conn_store_url" class="form-input" placeholder="https://maboutique.com" required>
                        </div>
                        <div id="woo-fields">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Consumer Key</label>
                                    <input type="text" name="api_key" id="conn_api_key" class="form-input" placeholder="ck_xxxxx">
                                </div>
                                <div>
                                    <label class="form-label">Consumer Secret</label>
                                    <input type="password" name="api_secret" id="conn_api_secret" class="form-input" placeholder="cs_xxxxx">
                                </div>
                            </div>
                        </div>
                        <div id="shopify-fields" class="hidden">
                            <div>
                                <label class="form-label">Access Token</label>
                                <input type="password" name="access_token" id="conn_access_token" class="form-input" placeholder="shpat_xxxxx">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Synchroniser</label>
                                <div class="space-y-2 mt-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="sync_orders" value="1" checked class="form-checkbox">
                                        <span class="text-sm">Commandes</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="sync_products" value="1" class="form-checkbox">
                                        <span class="text-sm">Produits</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="sync_customers" value="1" class="form-checkbox">
                                        <span class="text-sm">Clients</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Auto-creation factures</label>
                                <select name="auto_invoice" class="form-select mt-2">
                                    <option value="0">Desactive</option>
                                    <option value="1">Creer brouillons</option>
                                    <option value="2">Creer et valider</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeConnectModal()" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Connecter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updatePlatformFields() {
            const platform = document.getElementById('conn_platform').value;
            document.getElementById('woo-fields').classList.toggle('hidden', !['woocommerce', 'prestashop', 'magento', 'custom'].includes(platform));
            document.getElementById('shopify-fields').classList.toggle('hidden', platform !== 'shopify');
        }

        function closeConnectModal() {
            document.getElementById('connect-modal').classList.add('hidden');
            document.getElementById('connect-form').reset();
            document.getElementById('connect-form').action = "{{ route('ecommerce.connections.store') }}";
            document.getElementById('connect-method').value = 'POST';
            document.getElementById('connect-modal-title').textContent = 'Nouvelle connexion';
            updatePlatformFields();
        }

        function editConnection(conn) {
            document.getElementById('connect-modal-title').textContent = 'Modifier la connexion';
            document.getElementById('connect-form').action = `/ecommerce/connections/${conn.id}`;
            document.getElementById('connect-method').value = 'PUT';
            document.getElementById('conn_name').value = conn.name;
            document.getElementById('conn_platform').value = conn.platform;
            document.getElementById('conn_store_url').value = conn.store_url;
            document.getElementById('conn_api_key').value = conn.api_key || '';
            document.getElementById('conn_api_secret').value = '';
            document.getElementById('conn_access_token').value = '';
            updatePlatformFields();
            document.getElementById('connect-modal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
