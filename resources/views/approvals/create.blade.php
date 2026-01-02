<x-app-layout>
    <x-slot name="title">Créer un workflow d'approbation</x-slot>
    <x-slot name="header">
        <h2 class="text-2xl font-bold">Nouveau workflow d'approbation</h2>
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
            <li class="text-white">Créer</li>
        </ol>
    </nav>

    <form action="{{ route('approvals.workflows.store') }}" method="POST" class="max-w-4xl" x-data="workflowForm()">
        @csrf

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
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Ex: Approbation factures > 1000€">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Décrivez l'objectif de ce workflow...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Type -->
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Type de document <span class="text-danger-400">*</span>
                    </label>
                    <select name="document_type" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Sélectionnez un type</option>
                        <option value="invoice_purchase" {{ old('document_type') === 'invoice_purchase' ? 'selected' : '' }}>Factures d'achat</option>
                        <option value="invoice_sale" {{ old('document_type') === 'invoice_sale' ? 'selected' : '' }}>Factures de vente</option>
                        <option value="expense" {{ old('document_type') === 'expense' ? 'selected' : '' }}>Dépenses</option>
                        <option value="payment" {{ old('document_type') === 'payment' ? 'selected' : '' }}>Paiements</option>
                        <option value="journal_entry" {{ old('document_type') === 'journal_entry' ? 'selected' : '' }}>Écritures comptables</option>
                    </select>
                    @error('document_type')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Timeout -->
                <div>
                    <label class="block text-sm font-medium mb-2">Délai d'expiration (heures)</label>
                    <input type="number" name="timeout_hours" value="{{ old('timeout_hours', 48) }}" min="1" max="720"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="48">
                    <p class="mt-1 text-xs text-secondary-400">Temps maximum pour approuver avant escalade</p>
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
                    <input type="number" name="min_amount" value="{{ old('min_amount') }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="0.00">
                    <p class="mt-1 text-xs text-secondary-400">Laisser vide pour aucun minimum</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Montant maximum (€)</label>
                    <input type="number" name="max_amount" value="{{ old('max_amount') }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="999999.99">
                    <p class="mt-1 text-xs text-secondary-400">Laisser vide pour aucun maximum</p>
                </div>
            </div>
        </div>

        <!-- Approval Rules/Steps -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Étapes d'approbation <span class="text-danger-400">*</span>
                </h3>
                <button type="button" @click="addRule"
                    class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter une étape
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(rule, index) in rules" :key="index">
                    <div class="p-4 rounded-lg bg-secondary-700/50 border border-secondary-600">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0 font-bold text-primary-400">
                                <span x-text="index + 1"></span>
                            </div>
                            <div class="flex-1 grid md:grid-cols-3 gap-4">
                                <!-- Approver Type -->
                                <div>
                                    <label class="block text-sm font-medium mb-2">Type d'approbateur</label>
                                    <select :name="`rules[${index}][approver_type]`" x-model="rule.approver_type" required
                                        class="w-full bg-secondary-600 border-secondary-500 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="user">Utilisateur spécifique</option>
                                        <option value="role">Rôle</option>
                                        <option value="manager">Manager</option>
                                    </select>
                                </div>

                                <!-- Approver (if type is user) -->
                                <div x-show="rule.approver_type === 'user'">
                                    <label class="block text-sm font-medium mb-2">Approbateur</label>
                                    <select :name="`rules[${index}][approver_id]`"
                                        class="w-full bg-secondary-600 border-secondary-500 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Sélectionnez</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Role (if type is role) -->
                                <div x-show="rule.approver_type === 'role'">
                                    <label class="block text-sm font-medium mb-2">Rôle</label>
                                    <select :name="`rules[${index}][approver_role]`"
                                        class="w-full bg-secondary-600 border-secondary-500 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Sélectionnez</option>
                                        <option value="owner">Propriétaire</option>
                                        <option value="admin">Administrateur</option>
                                        <option value="accountant">Comptable</option>
                                        <option value="manager">Manager</option>
                                    </select>
                                </div>

                                <!-- Required Approvals -->
                                <div>
                                    <label class="block text-sm font-medium mb-2">Nb d'approbations</label>
                                    <input type="number" :name="`rules[${index}][required_approvals]`" x-model="rule.required_approvals" min="1" required
                                        class="w-full bg-secondary-600 border-secondary-500 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="1">
                                </div>
                            </div>
                            <button type="button" @click="removeRule(index)" x-show="rules.length > 1"
                                class="p-2 hover:bg-danger-500/20 rounded-lg text-danger-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <p class="mt-4 text-sm text-secondary-400">
                Les étapes seront exécutées dans l'ordre. Chaque étape doit être validée avant de passer à la suivante.
            </p>
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
                    <input type="checkbox" name="escalate_on_timeout" value="1" {{ old('escalate_on_timeout') ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                    <div>
                        <p class="font-medium">Escalader en cas de délai dépassé</p>
                        <p class="text-sm text-secondary-400">Passer automatiquement à l'étape suivante si le délai est écoulé</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                    <input type="checkbox" name="escalate_on_rejection" value="1" {{ old('escalate_on_rejection') ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800 mt-0.5">
                    <div>
                        <p class="font-medium">Escalader en cas de rejet</p>
                        <p class="text-sm text-secondary-400">Soumettre au niveau supérieur en cas de rejet</p>
                    </div>
                </label>

                <div>
                    <label class="block text-sm font-medium mb-2">Nombre maximum d'escalades</label>
                    <input type="number" name="max_escalations" value="{{ old('max_escalations', 2) }}" min="1" max="5"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                    <p class="mt-1 text-xs text-secondary-400">Nombre de fois que la demande peut être escaladée avant rejet automatique</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('approvals.workflows') }}"
                class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors">
                Annuler
            </a>
            <button type="submit"
                class="px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Créer le workflow
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        function workflowForm() {
            return {
                rules: [
                    { approver_type: 'user', approver_id: '', approver_role: '', required_approvals: 1 }
                ],

                addRule() {
                    this.rules.push({
                        approver_type: 'user',
                        approver_id: '',
                        approver_role: '',
                        required_approvals: 1
                    });
                },

                removeRule(index) {
                    this.rules.splice(index, 1);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
