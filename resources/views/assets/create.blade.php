<x-app-layout>
    <x-slot name="title">Nouvelle immobilisation</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('assets.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Immobilisations</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Nouvelle</span>
    @endsection

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Nouvelle immobilisation</h1>
            <p class="text-secondary-500 dark:text-secondary-400 mt-1">Enregistrez un nouvel actif immobilise</p>
        </div>

        <form action="{{ route('assets.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Informations generales -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Informations generales</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="form-input @error('reference') border-danger-500 @enderror">
                        @error('reference')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Categorie <span class="text-danger-500">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') border-danger-500 @enderror" required>
                            <option value="">Selectionnez une categorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}
                                    data-depreciation-method="{{ $category->depreciation_method }}"
                                    data-useful-life="{{ $category->default_useful_life }}"
                                    data-degressive-rate="{{ $category->degressive_rate }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Designation <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-danger-500 @enderror" required>
                        @error('name')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="2" class="form-input @error('description') border-danger-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Numero de serie</label>
                        <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Emplacement</label>
                        <input type="text" name="location" value="{{ old('location') }}" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Fournisseur</label>
                        <select name="partner_id" class="form-select">
                            <option value="">Selectionnez un fournisseur</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Facture d'achat</label>
                        <select name="invoice_id" class="form-select">
                            <option value="">Selectionnez une facture</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}" {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>{{ $invoice->number }} - {{ number_format($invoice->total_amount, 2, ',', ' ') }} EUR</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Dates et valeurs -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Dates et valeurs</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Date d'acquisition <span class="text-danger-500">*</span></label>
                        <input type="date" name="acquisition_date" value="{{ old('acquisition_date', date('Y-m-d')) }}" class="form-input @error('acquisition_date') border-danger-500 @enderror" required>
                        @error('acquisition_date')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Date de mise en service <span class="text-danger-500">*</span></label>
                        <input type="date" name="service_date" value="{{ old('service_date', date('Y-m-d')) }}" class="form-input @error('service_date') border-danger-500 @enderror" required>
                        @error('service_date')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Cout d'acquisition HT <span class="text-danger-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" name="acquisition_cost" value="{{ old('acquisition_cost') }}" class="form-input pr-10 @error('acquisition_cost') border-danger-500 @enderror" required>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">EUR</span>
                        </div>
                        @error('acquisition_cost')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Valeur residuelle</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="residual_value" value="{{ old('residual_value', 0) }}" class="form-input pr-10">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">EUR</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amortissement -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Parametres d'amortissement</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Methode d'amortissement <span class="text-danger-500">*</span></label>
                        <select name="depreciation_method" id="depreciation_method" class="form-select @error('depreciation_method') border-danger-500 @enderror" required>
                            <option value="linear" {{ old('depreciation_method', 'linear') == 'linear' ? 'selected' : '' }}>Lineaire</option>
                            <option value="degressive" {{ old('depreciation_method') == 'degressive' ? 'selected' : '' }}>Degressif</option>
                            <option value="units_of_production" {{ old('depreciation_method') == 'units_of_production' ? 'selected' : '' }}>Unites de production</option>
                        </select>
                        @error('depreciation_method')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Duree d'utilisation (annees) <span class="text-danger-500">*</span></label>
                        <input type="number" step="0.5" name="useful_life" id="useful_life" value="{{ old('useful_life', 5) }}" class="form-input @error('useful_life') border-danger-500 @enderror" required>
                        @error('useful_life')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div id="degressive_rate_group" class="hidden">
                        <label class="form-label">Coefficient degressif</label>
                        <input type="number" step="0.01" name="degressive_rate" id="degressive_rate" value="{{ old('degressive_rate', 1.75) }}" class="form-input">
                        <p class="text-xs text-secondary-500 mt-1">Belgique: 1.5 (3-4 ans), 1.75 (5-6 ans), 2 (> 6 ans)</p>
                    </div>

                    <div id="units_group" class="hidden md:col-span-2">
                        <label class="form-label">Nombre total d'unites</label>
                        <input type="number" name="total_units" value="{{ old('total_units') }}" class="form-input">
                        <p class="text-xs text-secondary-500 mt-1">Ex: kilometres, heures de fonctionnement, unites produites</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" name="action" value="draft" class="btn btn-secondary">Enregistrer en brouillon</button>
                <button type="submit" name="action" value="activate" class="btn btn-primary">Activer l'immobilisation</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const methodSelect = document.getElementById('depreciation_method');
            const degressiveGroup = document.getElementById('degressive_rate_group');
            const unitsGroup = document.getElementById('units_group');
            const categorySelect = document.querySelector('select[name="category_id"]');
            const usefulLifeInput = document.getElementById('useful_life');
            const degressiveRateInput = document.getElementById('degressive_rate');

            function updateVisibility() {
                const method = methodSelect.value;
                degressiveGroup.classList.toggle('hidden', method !== 'degressive');
                unitsGroup.classList.toggle('hidden', method !== 'units_of_production');
            }

            methodSelect.addEventListener('change', updateVisibility);
            updateVisibility();

            // Auto-fill from category
            categorySelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                if (option.dataset.depreciationMethod) {
                    methodSelect.value = option.dataset.depreciationMethod;
                    updateVisibility();
                }
                if (option.dataset.usefulLife) {
                    usefulLifeInput.value = option.dataset.usefulLife;
                }
                if (option.dataset.degressiveRate) {
                    degressiveRateInput.value = option.dataset.degressiveRate;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
