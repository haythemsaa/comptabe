<x-app-layout>
    <x-slot name="title">Modifier le workflow - {{ $workflow->name }}</x-slot>
    <x-slot name="header">
        <h2 class="text-2xl font-bold">Modifier le workflow</h2>
    </x-slot>

    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm">
        <ol class="inline-flex items-center space-x-1">
            <li>
                <a href="{{ route('approvals.index') }}" class="text-secondary-400 hover:text-white">Approbations</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li>
                <a href="{{ route('approvals.workflows') }}" class="text-secondary-400 hover:text-white">Workflows</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li class="text-white">{{ $workflow->name }}</li>
        </ol>
    </nav>

    <form action="{{ route('approvals.workflows.update', $workflow) }}" method="POST" class="max-w-4xl">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informations de base
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">
                        Nom du workflow <span class="text-danger-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $workflow->name) }}" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">{{ old('description', $workflow->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Type (Read-only) -->
                <div>
                    <label class="block text-sm font-medium mb-2">Type de document</label>
                    <div class="p-3 rounded-lg bg-secondary-700/50 border border-secondary-600">
                        <p class="font-medium">{{ $workflow->getDocumentTypeLabel() }}</p>
                        <p class="text-xs text-secondary-400 mt-1">Le type ne peut pas être modifié après création</p>
                    </div>
                </div>

                <!-- Timeout -->
                <div>
                    <label class="block text-sm font-medium mb-2">Délai d'expiration (heures)</label>
                    <input type="number" name="timeout_hours" value="{{ old('timeout_hours', $workflow->timeout_hours) }}" min="1" max="720"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
        </div>

        <!-- Amount Range -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Plage de montants
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Montant minimum (€)</label>
                    <input type="number" name="min_amount" value="{{ old('min_amount', $workflow->min_amount) }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Montant maximum (€)</label>
                    <input type="number" name="max_amount" value="{{ old('max_amount', $workflow->max_amount) }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
        </div>

        <!-- Approval Rules (Read-only for now) -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Étapes d'approbation
            </h3>

            <div class="space-y-3">
                @forelse($workflow->rules as $rule)
                    <div class="flex items-center gap-4 p-4 rounded-lg bg-secondary-700/50 border border-secondary-600">
                        <div class="w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0 font-bold text-primary-400">
                            {{ $rule->step_order }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium">{{ $rule->name }}</p>
                            <p class="text-sm text-secondary-400">
                                Type: {{ ucfirst($rule->approver_type) }}
                                @if($rule->approver_type === 'user' && $rule->approver)
                                    • Approbateur: {{ $rule->approver->full_name }}
                                @elseif($rule->approver_type === 'role')
                                    • Rôle: {{ ucfirst($rule->approver_role) }}
                                @endif
                                • {{ $rule->required_approvals }} approbation(s) requise(s)
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 rounded-lg bg-secondary-700/50 text-center text-secondary-400">
                        <p>Aucune étape configurée</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 p-4 rounded-lg bg-warning-500/10 border border-warning-500/30">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-warning-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm">
                        <p class="font-medium text-warning-400">Modification des étapes</p>
                        <p class="text-warning-300/70 mt-1">
                            Pour modifier les étapes d'approbation, veuillez créer un nouveau workflow.
                            La modification des étapes sur un workflow existant pourrait affecter les demandes en cours.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Escalation Settings -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Paramètres d'escalade
            </h3>

            <div class="space-y-4">
                <label class="flex items-start gap-3 p-4 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                    <input type="checkbox" name="escalate_on_timeout" value="1" {{ old('escalate_on_timeout', $workflow->escalate_on_timeout) ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                    <div>
                        <p class="font-medium">Escalader en cas de délai dépassé</p>
                        <p class="text-sm text-secondary-400">Passer automatiquement à l'étape suivante si le délai est écoulé</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                    <input type="checkbox" name="escalate_on_rejection" value="1" {{ old('escalate_on_rejection', $workflow->escalate_on_rejection) ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                    <div>
                        <p class="font-medium">Escalader en cas de rejet</p>
                        <p class="text-sm text-secondary-400">Soumettre au niveau supérieur en cas de rejet</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Status -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Statut du workflow
            </h3>

            <label class="flex items-start gap-3 p-4 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $workflow->is_active) ? 'checked' : '' }}
                    class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                <div>
                    <p class="font-medium">Workflow actif</p>
                    <p class="text-sm text-secondary-400">Désactiver ce workflow empêchera la création de nouvelles demandes d'approbation</p>
                </div>
            </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <form action="{{ route('approvals.workflows.destroy', $workflow) }}" method="POST"
                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce workflow ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-6 py-3 bg-danger-500/20 hover:bg-danger-500/30 text-danger-400 rounded-lg font-medium transition-colors">
                    Supprimer le workflow
                </button>
            </form>

            <div class="flex items-center gap-4">
                <a href="{{ route('approvals.workflows') }}"
                    class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors">
                    Annuler
                </a>
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
</x-app-layout>
