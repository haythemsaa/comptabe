<x-app-layout>
    <x-slot name="title">Nouvelle Opportunité</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('crm.pipeline') }}" class="text-secondary-500 hover:text-secondary-700">CRM</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouvelle Opportunité</span>
    @endsection

    <div class="max-w-3xl mx-auto">
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Nouvelle Opportunité</h2>
            </div>
            <form action="{{ route('crm.opportunities.store') }}" method="POST">
                @csrf
                <div class="card-body space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Titre de l'opportunité <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="form-input @error('title') border-danger-500 @enderror"
                            placeholder="Ex: Contrat annuel - Entreprise XYZ">
                        @error('title')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Partner -->
                        <div>
                            <label for="partner_id" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Client
                            </label>
                            <select name="partner_id" id="partner_id" class="form-select">
                                <option value="">Sélectionner un client</option>
                                @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id', $selectedPartnerId) == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stage -->
                        <div>
                            <label for="stage" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Étape <span class="text-danger-500">*</span>
                            </label>
                            <select name="stage" id="stage" required class="form-select">
                                @foreach(\App\Models\Opportunity::STAGES as $stage => $config)
                                    @if(!in_array($stage, ['won', 'lost']))
                                    <option value="{{ $stage }}" {{ old('stage', 'lead') === $stage ? 'selected' : '' }}>
                                        {{ $config['label'] }} ({{ $config['probability'] }}%)
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Montant (EUR) <span class="text-danger-500">*</span>
                            </label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount', 0) }}" required min="0" step="0.01"
                                class="form-input @error('amount') border-danger-500 @enderror">
                            @error('amount')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Probability -->
                        <div>
                            <label for="probability" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Probabilité (%) <span class="text-danger-500">*</span>
                            </label>
                            <input type="number" name="probability" id="probability" value="{{ old('probability', 10) }}" required min="0" max="100"
                                class="form-input @error('probability') border-danger-500 @enderror">
                            @error('probability')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Expected Close Date -->
                        <div>
                            <label for="expected_close_date" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Date de clôture prévue
                            </label>
                            <input type="date" name="expected_close_date" id="expected_close_date" value="{{ old('expected_close_date') }}"
                                class="form-input">
                        </div>

                        <!-- Source -->
                        <div>
                            <label for="source" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Source
                            </label>
                            <select name="source" id="source" class="form-select">
                                <option value="">Sélectionner une source</option>
                                @foreach(\App\Models\Opportunity::SOURCES as $source => $label)
                                <option value="{{ $source }}" {{ old('source') === $source ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Assigned To -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Responsable
                        </label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">Non assigné</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to', auth()->id()) == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3" class="form-textarea"
                            placeholder="Décrivez l'opportunité...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Notes internes
                        </label>
                        <textarea name="notes" id="notes" rows="2" class="form-textarea"
                            placeholder="Notes privées...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="card-footer flex items-center justify-end gap-3">
                    <a href="{{ route('crm.pipeline') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Créer l'opportunité
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-update probability when stage changes
        document.getElementById('stage').addEventListener('change', function() {
            const probabilities = {
                'lead': 10,
                'qualified': 25,
                'proposal': 50,
                'negotiation': 75
            };
            const prob = probabilities[this.value] || 50;
            document.getElementById('probability').value = prob;
        });
    </script>
    @endpush
</x-app-layout>
