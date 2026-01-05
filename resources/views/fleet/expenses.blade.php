<x-app-layout>
    <x-slot name="title">Depenses vehicule - {{ $vehicle->brand }} {{ $vehicle->model }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('fleet.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Flotte</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('fleet.show', $vehicle) }}" class="text-secondary-500 hover:text-primary-500 transition-colors">{{ $vehicle->license_plate }}</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Depenses</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Depenses - {{ $vehicle->brand }} {{ $vehicle->model }}</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">{{ $vehicle->license_plate }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('fleet.show', $vehicle) }}" class="btn btn-outline-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
                <button type="button" onclick="document.getElementById('expense-modal').classList.remove('hidden')" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle depense
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-danger-100 text-danger-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Depenses totales</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($totalExpenses, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-warning-100 text-warning-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Carburant</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($fuelExpenses, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-info-100 text-info-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Entretien</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($maintenanceExpenses, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Cout/km</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $vehicle->odometer_current > 0 ? number_format($totalExpenses / $vehicle->odometer_current, 3, ',', ' ') : '0' }} &euro;</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card p-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-select" onchange="toggleCustomDates(this.value)">
                        <option value="all" {{ request('period', 'all') == 'all' ? 'selected' : '' }}>Tout</option>
                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Cette annee</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Personnalise</option>
                    </select>
                </div>
                <div id="custom-dates" class="{{ request('period') == 'custom' ? '' : 'hidden' }} flex gap-2">
                    <div>
                        <label class="form-label">Du</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Au</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        <option value="fuel" {{ request('type') == 'fuel' ? 'selected' : '' }}>Carburant</option>
                        <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Entretien</option>
                        <option value="repair" {{ request('type') == 'repair' ? 'selected' : '' }}>Reparation</option>
                        <option value="insurance" {{ request('type') == 'insurance' ? 'selected' : '' }}>Assurance</option>
                        <option value="tax" {{ request('type') == 'tax' ? 'selected' : '' }}>Taxe</option>
                        <option value="toll" {{ request('type') == 'toll' ? 'selected' : '' }}>Peage</option>
                        <option value="parking" {{ request('type') == 'parking' ? 'selected' : '' }}>Parking</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </form>
        </div>

        <!-- Liste des depenses -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Km</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Litres</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Montant</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $expense->expense_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $typeColors = [
                                            'fuel' => 'badge-warning',
                                            'maintenance' => 'badge-info',
                                            'repair' => 'badge-danger',
                                            'insurance' => 'badge-primary',
                                            'tax' => 'badge-secondary',
                                            'toll' => 'badge-success',
                                            'parking' => 'badge-secondary',
                                            'other' => 'badge-secondary',
                                        ];
                                        $typeLabels = [
                                            'fuel' => 'Carburant',
                                            'maintenance' => 'Entretien',
                                            'repair' => 'Reparation',
                                            'insurance' => 'Assurance',
                                            'tax' => 'Taxe',
                                            'toll' => 'Peage',
                                            'parking' => 'Parking',
                                            'other' => 'Autre',
                                        ];
                                    @endphp
                                    <span class="badge {{ $typeColors[$expense->type] ?? 'badge-secondary' }}">
                                        {{ $typeLabels[$expense->type] ?? $expense->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-800 dark:text-white">
                                    {{ $expense->description ?? '-' }}
                                    @if($expense->supplier)
                                        <div class="text-xs text-secondary-500">{{ $expense->supplier }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">
                                    {{ $expense->odometer ? number_format($expense->odometer, 0, ',', ' ') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">
                                    {{ $expense->fuel_liters ? number_format($expense->fuel_liters, 2, ',', ' ') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">
                                    {{ number_format($expense->amount, 2, ',', ' ') }} &euro;
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="editExpense({{ json_encode($expense) }})" class="text-secondary-500 hover:text-warning-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('fleet.expenses.destroy', [$vehicle, $expense]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette depense ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-secondary-500 hover:text-danger-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-secondary-500">Aucune depense enregistree</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Depense -->
    <div id="expense-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeExpenseModal()"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 id="expense-modal-title" class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Nouvelle depense</h3>
                <form id="expense-form" method="POST" action="{{ route('fleet.expenses.store', $vehicle) }}">
                    @csrf
                    <input type="hidden" name="_method" id="expense-method" value="POST">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Date <span class="text-danger-500">*</span></label>
                                <input type="date" name="expense_date" id="expense_date" value="{{ date('Y-m-d') }}" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                                <select name="type" id="expense_type" class="form-select" required onchange="toggleFuelFields()">
                                    <option value="fuel">Carburant</option>
                                    <option value="maintenance">Entretien</option>
                                    <option value="repair">Reparation</option>
                                    <option value="insurance">Assurance</option>
                                    <option value="tax">Taxe</option>
                                    <option value="toll">Peage</option>
                                    <option value="parking">Parking</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <input type="text" name="description" id="expense_description" class="form-input" placeholder="Ex: Vidange + filtres">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Montant HT <span class="text-danger-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="amount" id="expense_amount" class="form-input pr-10" required>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">EUR</span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Kilometrage</label>
                                <input type="number" name="odometer" id="expense_odometer" value="{{ $vehicle->odometer_current }}" class="form-input">
                            </div>
                        </div>
                        <div id="fuel-fields" class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Litres</label>
                                <input type="number" step="0.01" name="fuel_liters" id="expense_fuel_liters" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Prix/litre</label>
                                <div class="relative">
                                    <input type="number" step="0.001" name="fuel_price_per_liter" id="expense_fuel_price" class="form-input pr-10">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">EUR</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Fournisseur</label>
                            <input type="text" name="supplier" id="expense_supplier" class="form-input" placeholder="Ex: Total, Garage Martin...">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeExpenseModal()" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleCustomDates(value) {
            document.getElementById('custom-dates').classList.toggle('hidden', value !== 'custom');
        }

        function toggleFuelFields() {
            const type = document.getElementById('expense_type').value;
            document.getElementById('fuel-fields').classList.toggle('hidden', type !== 'fuel');
        }

        function closeExpenseModal() {
            document.getElementById('expense-modal').classList.add('hidden');
            resetExpenseForm();
        }

        function resetExpenseForm() {
            const form = document.getElementById('expense-form');
            form.reset();
            form.action = "{{ route('fleet.expenses.store', $vehicle) }}";
            document.getElementById('expense-method').value = 'POST';
            document.getElementById('expense-modal-title').textContent = 'Nouvelle depense';
            document.getElementById('expense_date').value = "{{ date('Y-m-d') }}";
            document.getElementById('expense_odometer').value = "{{ $vehicle->odometer_current }}";
            toggleFuelFields();
        }

        function editExpense(expense) {
            document.getElementById('expense-modal-title').textContent = 'Modifier la depense';
            document.getElementById('expense-form').action = `/fleet/{{ $vehicle->id }}/expenses/${expense.id}`;
            document.getElementById('expense-method').value = 'PUT';
            document.getElementById('expense_date').value = expense.expense_date.split('T')[0];
            document.getElementById('expense_type').value = expense.type;
            document.getElementById('expense_description').value = expense.description || '';
            document.getElementById('expense_amount').value = expense.amount;
            document.getElementById('expense_odometer').value = expense.odometer || '';
            document.getElementById('expense_fuel_liters').value = expense.fuel_liters || '';
            document.getElementById('expense_fuel_price').value = expense.fuel_price_per_liter || '';
            document.getElementById('expense_supplier').value = expense.supplier || '';
            toggleFuelFields();
            document.getElementById('expense-modal').classList.remove('hidden');
        }

        // Init
        toggleFuelFields();
    </script>
    @endpush
</x-app-layout>
