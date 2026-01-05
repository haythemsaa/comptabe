<x-app-layout>
    <x-slot name="title">Devises</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Devises</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Gestion des devises</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Configurez les devises et les taux de change</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('currencies.sync-rates') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser les taux
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('currency-modal').classList.remove('hidden')" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle devise
                </button>
            </div>
        </div>

        <!-- Devise principale -->
        <div class="card p-6 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 border-primary-200 dark:border-primary-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-primary-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ $baseCurrency->symbol }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-primary-800 dark:text-primary-200">{{ $baseCurrency->name }}</h3>
                        <p class="text-primary-600 dark:text-primary-400">{{ $baseCurrency->code }} - Devise de base</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-primary-600 dark:text-primary-400">Taux de base</p>
                    <p class="text-2xl font-bold text-primary-800 dark:text-primary-200">1.00</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-primary-100 text-primary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Devises actives</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $currencies->where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Factures multi-devises</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $foreignInvoicesCount }}</p>
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
                        <p class="text-sm text-secondary-500">Derniere MAJ taux</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $lastRateUpdate?->diffForHumans() ?? 'Jamais' }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-info-100 text-info-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Ecarts de change</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($totalExchangeDifference, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des devises -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Devise</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Code</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Symbole</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Taux actuel</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Variation</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($currencies as $currency)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 {{ $currency->is_base ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-secondary-100 dark:bg-secondary-700 flex items-center justify-center font-semibold text-secondary-700 dark:text-secondary-300">
                                            {{ $currency->symbol }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-secondary-800 dark:text-white">{{ $currency->name }}</div>
                                            @if($currency->is_base)
                                                <span class="text-xs text-primary-600">Devise de base</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge badge-secondary">{{ $currency->code }}</span>
                                </td>
                                <td class="px-4 py-3 text-center text-lg font-medium text-secondary-800 dark:text-white">
                                    {{ $currency->symbol }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($currency->is_base)
                                        <span class="text-secondary-500">1.0000</span>
                                    @else
                                        <span class="font-medium text-secondary-800 dark:text-white">{{ number_format($currency->current_rate, 4) }}</span>
                                        <div class="text-xs text-secondary-500">1 {{ $baseCurrency->code }} = {{ number_format($currency->current_rate, 4) }} {{ $currency->code }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if(!$currency->is_base && $currency->rate_change)
                                        @if($currency->rate_change > 0)
                                            <span class="text-success-600 flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                                +{{ number_format($currency->rate_change, 2) }}%
                                            </span>
                                        @elseif($currency->rate_change < 0)
                                            <span class="text-danger-600 flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                </svg>
                                                {{ number_format($currency->rate_change, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-secondary-400">-</span>
                                        @endif
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($currency->is_active)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('currencies.rates', $currency) }}" class="text-secondary-500 hover:text-info-500" title="Historique des taux">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                        </a>
                                        @if(!$currency->is_base)
                                            <button type="button" onclick="editCurrency({{ json_encode($currency) }})" class="text-secondary-500 hover:text-warning-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('currencies.toggle', $currency) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-secondary-500 hover:text-{{ $currency->is_active ? 'danger' : 'success' }}-500" title="{{ $currency->is_active ? 'Desactiver' : 'Activer' }}">
                                                    @if($currency->is_active)
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-secondary-500">Aucune devise configuree</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Convertisseur rapide -->
        <div class="card p-6">
            <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Convertisseur rapide</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="form-label">Montant</label>
                    <input type="number" step="0.01" id="convert_amount" value="100" class="form-input" oninput="convertCurrency()">
                </div>
                <div>
                    <label class="form-label">De</label>
                    <select id="convert_from" class="form-select" onchange="convertCurrency()">
                        @foreach($currencies->where('is_active', true) as $currency)
                            <option value="{{ $currency->code }}" data-rate="{{ $currency->current_rate ?? 1 }}" {{ $currency->is_base ? 'selected' : '' }}>
                                {{ $currency->code }} - {{ $currency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Vers</label>
                    <select id="convert_to" class="form-select" onchange="convertCurrency()">
                        @foreach($currencies->where('is_active', true) as $currency)
                            <option value="{{ $currency->code }}" data-rate="{{ $currency->current_rate ?? 1 }}" {{ !$currency->is_base && $loop->first ? 'selected' : '' }}>
                                {{ $currency->code }} - {{ $currency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Resultat</label>
                    <div class="form-input bg-secondary-100 dark:bg-secondary-700 font-bold text-lg" id="convert_result">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Devise -->
    <div id="currency-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeCurrencyModal()"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 id="currency-modal-title" class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Nouvelle devise</h3>
                <form id="currency-form" method="POST" action="{{ route('currencies.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="currency-method" value="POST">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Code ISO <span class="text-danger-500">*</span></label>
                                <input type="text" name="code" id="curr_code" class="form-input uppercase" maxlength="3" placeholder="USD" required>
                            </div>
                            <div>
                                <label class="form-label">Symbole <span class="text-danger-500">*</span></label>
                                <input type="text" name="symbol" id="curr_symbol" class="form-input" maxlength="5" placeholder="$" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Nom complet <span class="text-danger-500">*</span></label>
                            <input type="text" name="name" id="curr_name" class="form-input" placeholder="Dollar americain" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Taux de change</label>
                                <input type="number" step="0.000001" name="exchange_rate" id="curr_rate" class="form-input" placeholder="1.0856">
                                <p class="text-xs text-secondary-500 mt-1">1 {{ $baseCurrency->code }} = X {{ 'devise' }}</p>
                            </div>
                            <div>
                                <label class="form-label">Decimales</label>
                                <input type="number" name="decimal_places" id="curr_decimals" value="2" min="0" max="4" class="form-input">
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" class="form-checkbox" checked>
                                <span class="text-sm">Devise active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeCurrencyModal()" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const baseCurrencyCode = '{{ $baseCurrency->code }}';

        function closeCurrencyModal() {
            document.getElementById('currency-modal').classList.add('hidden');
            document.getElementById('currency-form').reset();
            document.getElementById('currency-form').action = "{{ route('currencies.store') }}";
            document.getElementById('currency-method').value = 'POST';
            document.getElementById('currency-modal-title').textContent = 'Nouvelle devise';
        }

        function editCurrency(currency) {
            document.getElementById('currency-modal-title').textContent = 'Modifier la devise';
            document.getElementById('currency-form').action = `/currencies/${currency.id}`;
            document.getElementById('currency-method').value = 'PUT';
            document.getElementById('curr_code').value = currency.code;
            document.getElementById('curr_symbol').value = currency.symbol;
            document.getElementById('curr_name').value = currency.name;
            document.getElementById('curr_rate').value = currency.current_rate;
            document.getElementById('curr_decimals').value = currency.decimal_places;
            document.querySelector('input[name="is_active"]').checked = currency.is_active;
            document.getElementById('currency-modal').classList.remove('hidden');
        }

        function convertCurrency() {
            const amount = parseFloat(document.getElementById('convert_amount').value) || 0;
            const fromSelect = document.getElementById('convert_from');
            const toSelect = document.getElementById('convert_to');
            const fromRate = parseFloat(fromSelect.options[fromSelect.selectedIndex].dataset.rate) || 1;
            const toRate = parseFloat(toSelect.options[toSelect.selectedIndex].dataset.rate) || 1;
            const toCode = toSelect.value;

            // Convert to base currency first, then to target
            const inBase = amount / fromRate;
            const result = inBase * toRate;

            document.getElementById('convert_result').textContent = result.toFixed(2) + ' ' + toCode;
        }

        // Init converter
        convertCurrency();
    </script>
    @endpush
</x-app-layout>
