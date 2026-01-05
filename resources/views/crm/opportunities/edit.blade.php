<x-app-layout>
    <x-slot name="title">Modifier - {{ $opportunity->title }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('crm.pipeline') }}" class="text-secondary-500 hover:text-secondary-700">CRM</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Modifier</span>
    @endsection

    <div class="max-w-3xl mx-auto">
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Modifier l'opportunité</h2>
            </div>
            <form action="{{ route('crm.opportunities.update', $opportunity) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Titre de l'opportunité <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $opportunity->title) }}" required
                            class="form-input @error('title') border-danger-500 @enderror">
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
                                <option value="{{ $partner->id }}" {{ old('partner_id', $opportunity->partner_id) == $partner->id ? 'selected' : '' }}>
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
                                <option value="{{ $stage }}" {{ old('stage', $opportunity->stage) === $stage ? 'selected' : '' }}>
                                    {{ $config['label'] }} ({{ $config['probability'] }}%)
                                </option>
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
                            <input type="number" name="amount" id="amount" value="{{ old('amount', $opportunity->amount) }}" required min="0" step="0.01"
                                class="form-input">
                        </div>

                        <!-- Probability -->
                        <div>
                            <label for="probability" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Probabilité (%) <span class="text-danger-500">*</span>
                            </label>
                            <input type="number" name="probability" id="probability" value="{{ old('probability', $opportunity->probability) }}" required min="0" max="100"
                                class="form-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Expected Close Date -->
                        <div>
                            <label for="expected_close_date" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Date de clôture prévue
                            </label>
                            <input type="date" name="expected_close_date" id="expected_close_date"
                                value="{{ old('expected_close_date', $opportunity->expected_close_date?->format('Y-m-d')) }}"
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
                                <option value="{{ $source }}" {{ old('source', $opportunity->source) === $source ? 'selected' : '' }}>{{ $label }}</option>
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
                            <option value="{{ $user->id }}" {{ old('assigned_to', $opportunity->assigned_to) == $user->id ? 'selected' : '' }}>
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
                        <textarea name="description" id="description" rows="3" class="form-textarea">{{ old('description', $opportunity->description) }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Notes internes
                        </label>
                        <textarea name="notes" id="notes" rows="2" class="form-textarea">{{ old('notes', $opportunity->notes) }}</textarea>
                    </div>

                    @if($opportunity->stage === 'lost')
                    <!-- Lost Reason -->
                    <div>
                        <label for="lost_reason" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Raison de la perte
                        </label>
                        <input type="text" name="lost_reason" id="lost_reason" value="{{ old('lost_reason', $opportunity->lost_reason) }}"
                            class="form-input">
                    </div>
                    @endif
                </div>

                <div class="card-footer flex items-center justify-between">
                    <form action="{{ route('crm.opportunities.destroy', $opportunity) }}" method="POST" onsubmit="return confirm('Supprimer cette opportunité ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost text-danger-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                    <div class="flex gap-3">
                        <a href="{{ route('crm.opportunities.show', $opportunity) }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
