<x-app-layout>
    <x-slot name="title">{{ $vehicle->brand }} {{ $vehicle->model }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('fleet.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Flotte</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">{{ $vehicle->license_plate ?? $vehicle->brand }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $vehicle->brand }} {{ $vehicle->model }}</h1>
                    @switch($vehicle->status)
                        @case('active')
                            <span class="badge badge-success">Actif</span>
                            @break
                        @case('maintenance')
                            <span class="badge badge-warning">Maintenance</span>
                            @break
                        @case('disposed')
                            <span class="badge badge-danger">Cede</span>
                            @break
                        @case('sold')
                            <span class="badge badge-info">Vendu</span>
                            @break
                    @endswitch
                </div>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">{{ $vehicle->license_plate }} - {{ $vehicle->year }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('expense-modal').classList.remove('hidden')" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Ajouter depense
                </button>
                <a href="{{ route('fleet.edit', $vehicle) }}" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- ATN Card -->
                @if($atn && $vehicle->assigned_user_id)
                <div class="card p-6 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 border-primary-200 dark:border-primary-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-primary-800 dark:text-primary-200">Avantage de Toute Nature (ATN)</h3>
                        <span class="text-sm text-primary-600 dark:text-primary-400">{{ now()->format('F Y') }}</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-primary-600 dark:text-primary-400 mb-1">ATN mensuel</p>
                            <p class="text-2xl font-bold text-primary-800 dark:text-white">{{ number_format($atn['monthly'], 2, ',', ' ') }} &euro;</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-primary-600 dark:text-primary-400 mb-1">ATN annuel</p>
                            <p class="text-2xl font-bold text-primary-800 dark:text-white">{{ number_format($atn['annual'], 2, ',', ' ') }} &euro;</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-primary-600 dark:text-primary-400 mb-1">Coefficient CO2</p>
                            <p class="text-2xl font-bold text-primary-800 dark:text-white">{{ number_format($atn['coefficient'] * 100, 2) }}%</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-primary-600 dark:text-primary-400 mb-1">Cotisation solidarite</p>
                            <p class="text-2xl font-bold text-primary-800 dark:text-white">{{ number_format($atn['solidarity'] ?? 0, 2, ',', ' ') }} &euro;</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Expense Stats -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Depenses par type</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $expenseTypes = [
                                'fuel' => ['label' => 'Carburant', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'warning'],
                                'maintenance' => ['label' => 'Entretien', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'color' => 'info'],
                                'insurance' => ['label' => 'Assurance', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'success'],
                                'other' => ['label' => 'Autres', 'icon' => 'M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z', 'color' => 'secondary'],
                            ];
                        @endphp
                        @foreach($expenseTypes as $type => $config)
                            <div class="text-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                                <p class="text-sm text-secondary-500 mb-1">{{ $config['label'] }}</p>
                                <p class="text-xl font-semibold text-{{ $config['color'] }}-500">{{ number_format($expenseStats[$type] ?? 0, 0, ',', ' ') }} &euro;</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Expenses -->
                <div class="card">
                    <div class="p-4 border-b border-secondary-200 dark:border-secondary-700 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-secondary-800 dark:text-white">Dernieres depenses</h3>
                        <a href="{{ route('fleet.expenses', $vehicle) }}" class="text-primary-500 hover:text-primary-600 text-sm">Voir tout</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-secondary-50 dark:bg-secondary-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Fournisseur</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                @forelse($vehicle->expenses as $expense)
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                        <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-secondary-800 dark:text-white">{{ ucfirst($expense->type) }}</td>
                                        <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $expense->supplier ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">{{ number_format($expense->amount, 2, ',', ' ') }} &euro;</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-secondary-500">Aucune depense</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Odometer History -->
                <div class="card">
                    <div class="p-4 border-b border-secondary-200 dark:border-secondary-700">
                        <h3 class="text-lg font-medium text-secondary-800 dark:text-white">Historique kilometrique</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-secondary-50 dark:bg-secondary-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Kilometrage</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                @forelse($vehicle->odometerReadings as $reading)
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                        <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $reading->reading_date->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">{{ number_format($reading->odometer_value, 0, ',', ' ') }} km</td>
                                        <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $reading->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-secondary-500">Aucun releve</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Vehicle Info -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Informations</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Type</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">
                                @switch($vehicle->type)
                                    @case('car') Voiture @break
                                    @case('van') Utilitaire @break
                                    @case('truck') Camion @break
                                    @case('motorcycle') Moto @break
                                    @default {{ ucfirst($vehicle->type) }}
                                @endswitch
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Carburant</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">
                                @switch($vehicle->fuel_type)
                                    @case('petrol') Essence @break
                                    @case('diesel') Diesel @break
                                    @case('hybrid') Hybride @break
                                    @case('electric') Electrique @break
                                    @default {{ ucfirst($vehicle->fuel_type) }}
                                @endswitch
                            </dd>
                        </div>
                        @if($vehicle->co2_emission)
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">CO2</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $vehicle->co2_emission }} g/km</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Kilometrage actuel</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ number_format($vehicle->odometer_current, 0, ',', ' ') }} km</dd>
                        </div>
                        @if($vehicle->assignedUser)
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Attribue a</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $vehicle->assignedUser->name }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- ATN Values -->
                @if($vehicle->catalog_value)
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Valeurs ATN</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Valeur catalogue</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ number_format($vehicle->catalog_value, 0, ',', ' ') }} &euro;</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Valeur options</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ number_format($vehicle->options_value, 0, ',', ' ') }} &euro;</dd>
                        </div>
                        <div class="flex justify-between border-t border-secondary-200 dark:border-secondary-700 pt-3">
                            <dt class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Total</dt>
                            <dd class="text-sm font-bold text-secondary-800 dark:text-white">{{ number_format($vehicle->catalog_value + $vehicle->options_value, 0, ',', ' ') }} &euro;</dd>
                        </div>
                    </dl>
                </div>
                @endif

                <!-- Reminders -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Rappels</h3>
                    <div class="space-y-3">
                        @forelse($vehicle->pendingReminders as $reminder)
                            <div class="flex items-center gap-3 p-3 rounded-lg {{ $reminder->status == 'overdue' ? 'bg-danger-50 dark:bg-danger-900/20' : 'bg-secondary-50 dark:bg-secondary-800' }}">
                                <div class="w-8 h-8 rounded-full {{ $reminder->status == 'overdue' ? 'bg-danger-100 text-danger-600' : 'bg-warning-100 text-warning-600' }} flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-secondary-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $reminder->type)) }}</p>
                                    <p class="text-xs {{ $reminder->status == 'overdue' ? 'text-danger-600' : 'text-secondary-500' }}">{{ $reminder->due_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-secondary-500">Aucun rappel en attente</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Depense -->
    <div id="expense-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('expense-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Ajouter une depense</h3>
                <form action="{{ route('fleet.expenses.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="fuel">Carburant</option>
                                <option value="maintenance">Entretien</option>
                                <option value="repair">Reparation</option>
                                <option value="insurance">Assurance</option>
                                <option value="tax">Taxes</option>
                                <option value="parking">Parking</option>
                                <option value="toll">Peage</option>
                                <option value="tyre">Pneus</option>
                                <option value="washing">Lavage</option>
                                <option value="fine">Amende</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Date</label>
                                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Montant TTC</label>
                                <input type="number" step="0.01" name="amount" class="form-input" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Quantite</label>
                                <input type="number" step="0.001" name="quantity" class="form-input" placeholder="Litres...">
                            </div>
                            <div>
                                <label class="form-label">Kilometrage</label>
                                <input type="number" name="odometer" class="form-input" value="{{ $vehicle->odometer_current }}">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Fournisseur</label>
                            <input type="text" name="supplier" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-input"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('expense-modal').classList.add('hidden')" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
