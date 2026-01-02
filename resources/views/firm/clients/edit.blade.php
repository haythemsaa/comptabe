<x-firm-layout>
    <x-slot name="title">Modifier le mandat - {{ $mandate->company->name }}</x-slot>
    <x-slot name="header">Modifier le mandat</x-slot>

    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm">
        <ol class="inline-flex items-center space-x-1">
            <li>
                <a href="{{ route('firm.dashboard') }}" class="text-secondary-400 hover:text-white">Tableau de bord</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li>
                <a href="{{ route('firm.clients.index') }}" class="text-secondary-400 hover:text-white">Clients</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li>
                <a href="{{ route('firm.clients.show', $mandate) }}" class="text-secondary-400 hover:text-white">{{ $mandate->company->name }}</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li class="text-white">Modifier</li>
        </ol>
    </nav>

    <form action="{{ route('firm.clients.update', $mandate) }}" method="POST" class="max-w-4xl" x-data="editMandateForm()">
        @csrf
        @method('PUT')

        <!-- Client Info (Read-only) -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Informations du client
            </h3>

            <div class="flex items-center gap-4 p-4 rounded-lg bg-secondary-700/50">
                <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center font-bold text-primary-400">
                    {{ strtoupper(substr($mandate->company->name, 0, 2)) }}
                </div>
                <div>
                    <p class="font-semibold text-lg">{{ $mandate->company->name }}</p>
                    <p class="text-sm text-secondary-400">{{ $mandate->company->vat_number }} • {{ $mandate->company->email }}</p>
                </div>
            </div>

            <p class="mt-4 text-sm text-secondary-400">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pour modifier les informations de l'entreprise, contactez le client directement.
            </p>
        </div>

        <!-- Mandate Configuration -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Configuration du mandat
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Mandate Type -->
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Type de mandat <span class="text-danger-400">*</span>
                    </label>
                    <select name="mandate_type" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @foreach(\App\Models\ClientMandate::TYPE_LABELS as $value => $label)
                            <option value="{{ $value }}" {{ old('mandate_type', $mandate->mandate_type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('mandate_type')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Manager -->
                <div>
                    <label class="block text-sm font-medium mb-2">Gestionnaire du dossier</label>
                    <select name="manager_user_id"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Non assigné</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_user_id', $mandate->manager_user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_user_id')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium mb-2">Statut du mandat</label>
                    <select name="status"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @foreach(\App\Models\ClientMandate::STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $mandate->status) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Start/End Dates -->
                <div>
                    <label class="block text-sm font-medium mb-2">Date de fin (optionnelle)</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $mandate->end_date?->format('Y-m-d')) }}"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                    <p class="mt-1 text-xs text-secondary-400">Laisser vide si le mandat est sans fin</p>
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Services inclus
            </h3>

            <div class="grid md:grid-cols-2 gap-4">
                @foreach(\App\Models\ClientMandate::DEFAULT_SERVICES as $service => $defaultValue)
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                        <input type="checkbox" name="services[{{ $service }}]" value="1"
                            {{ old("services.{$service}", $mandate->services[$service] ?? false) ? 'checked' : '' }}
                            class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800">
                        <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $service)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Billing Configuration -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Facturation
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Billing Type -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">
                        Type de facturation <span class="text-danger-400">*</span>
                    </label>
                    <select name="billing_type" required x-model="billingType"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="hourly">Horaire</option>
                        <option value="monthly">Mensuelle (forfait)</option>
                        <option value="annual">Annuelle (forfait)</option>
                        <option value="project">Par projet</option>
                    </select>
                    @error('billing_type')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hourly Rate -->
                <div x-show="billingType === 'hourly'">
                    <label class="block text-sm font-medium mb-2">Taux horaire (€)</label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $mandate->hourly_rate) }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="75.00">
                    @error('hourly_rate')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Monthly Fee -->
                <div x-show="billingType === 'monthly'">
                    <label class="block text-sm font-medium mb-2">Forfait mensuel (€)</label>
                    <input type="number" name="monthly_fee" value="{{ old('monthly_fee', $mandate->monthly_fee) }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="500.00">
                    @error('monthly_fee')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Annual Fee -->
                <div x-show="billingType === 'annual'" class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Forfait annuel (€)</label>
                    <input type="number" name="annual_fee" value="{{ old('annual_fee', $mandate->annual_fee) }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="5000.00">
                    @error('annual_fee')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Client Permissions -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Permissions du client
            </h3>

            <div class="space-y-4">
                <label class="flex items-start gap-3 p-4 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                    <input type="checkbox" name="client_can_view" value="1"
                        {{ old('client_can_view', $mandate->client_can_view) ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                    <div>
                        <p class="font-medium">Consultation</p>
                        <p class="text-sm text-secondary-400">Le client peut consulter ses documents et factures</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                    <input type="checkbox" name="client_can_edit" value="1"
                        {{ old('client_can_edit', $mandate->client_can_edit) ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                    <div>
                        <p class="font-medium">Édition</p>
                        <p class="text-sm text-secondary-400">Le client peut modifier certaines informations</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                    <input type="checkbox" name="client_can_validate" value="1"
                        {{ old('client_can_validate', $mandate->client_can_validate) ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                    <div>
                        <p class="font-medium">Validation</p>
                        <p class="text-sm text-secondary-400">Le client peut valider les documents avant soumission</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Internal Notes -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Notes internes
            </h3>

            <textarea name="internal_notes" rows="4"
                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                placeholder="Notes internes visibles uniquement par l'équipe du cabinet...">{{ old('internal_notes', $mandate->internal_notes) }}</textarea>
            <p class="mt-2 text-xs text-secondary-400">Ces notes ne sont pas visibles par le client</p>
            @error('internal_notes')
                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('firm.clients.show', $mandate) }}"
                class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors">
                Annuler
            </a>

            <div class="flex items-center gap-4">
                <button type="button" @click="confirmSuspend" x-show="'{{ $mandate->status }}' !== 'terminated'"
                    class="px-6 py-3 bg-warning-500/20 hover:bg-warning-500/30 text-warning-400 rounded-lg font-medium transition-colors">
                    Suspendre le mandat
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function editMandateForm() {
            return {
                billingType: '{{ old('billing_type', $mandate->billing_type) }}',

                confirmSuspend() {
                    if (confirm('Êtes-vous sûr de vouloir suspendre ce mandat ?')) {
                        // Update status field and submit
                        document.querySelector('select[name="status"]').value = 'suspended';
                        document.querySelector('form').submit();
                    }
                }
            }
        }
    </script>
    @endpush
</x-firm-layout>
