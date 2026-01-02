<x-app-layout>
    <x-slot name="title">Nouvelle écriture comptable</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.index') }}" class="text-secondary-500 hover:text-secondary-700">Comptabilité</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouvelle écriture</span>
    @endsection

    <form
        method="POST"
        action="{{ route('accounting.entries.store') }}"
        x-data="journalEntry()"
        class="space-y-6"
    >
        @csrf

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouvelle écriture comptable</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Créez une écriture dans le journal</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('accounting.entries') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary" :disabled="!isBalanced">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-xl text-danger-700 dark:text-danger-300">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Entry Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Informations</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="journal_id" class="form-label">Journal *</label>
                                <select
                                    name="journal_id"
                                    id="journal_id"
                                    required
                                    class="form-select @error('journal_id') form-input-error @enderror"
                                >
                                    <option value="">Sélectionner...</option>
                                    @foreach($journals as $journal)
                                        <option value="{{ $journal->id }}" {{ old('journal_id') == $journal->id ? 'selected' : '' }}>
                                            {{ $journal->code }} - {{ $journal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="entry_date" class="form-label">Date *</label>
                                <input
                                    type="date"
                                    name="entry_date"
                                    id="entry_date"
                                    value="{{ old('entry_date', date('Y-m-d')) }}"
                                    required
                                    class="form-input @error('entry_date') form-input-error @enderror"
                                >
                            </div>
                            <div>
                                <label for="reference" class="form-label">Référence</label>
                                <input
                                    type="text"
                                    name="reference"
                                    id="reference"
                                    value="{{ old('reference') }}"
                                    class="form-input"
                                    placeholder="Facture, pièce..."
                                >
                            </div>
                        </div>
                        <div>
                            <label for="description" class="form-label">Description *</label>
                            <input
                                type="text"
                                name="description"
                                id="description"
                                value="{{ old('description') }}"
                                required
                                class="form-input @error('description') form-input-error @enderror"
                                placeholder="Libellé de l'écriture"
                            >
                        </div>
                    </div>
                </div>

                <!-- Entry Lines -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes d'écriture</h2>
                        <button
                            type="button"
                            @click="addLine()"
                            class="btn btn-secondary btn-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Table Header -->
                        <div class="hidden md:grid grid-cols-12 gap-4 pb-2 border-b border-secondary-200 dark:border-secondary-700 text-sm font-medium text-secondary-500">
                            <div class="col-span-5">Compte</div>
                            <div class="col-span-3">Libellé</div>
                            <div class="col-span-2 text-right">Débit</div>
                            <div class="col-span-2 text-right">Crédit</div>
                        </div>

                        <!-- Lines -->
                        <div class="space-y-3 mt-3">
                            <template x-for="(line, index) in lines" :key="line.id">
                                <div class="grid grid-cols-12 gap-4 items-start p-3 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl">
                                    <div class="col-span-12 md:col-span-5">
                                        <label class="form-label md:hidden">Compte *</label>
                                        <select
                                            :name="`lines[${index}][account_id]`"
                                            x-model="line.account_id"
                                            required
                                            class="form-select text-sm"
                                        >
                                            <option value="">Sélectionner un compte...</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-12 md:col-span-3">
                                        <label class="form-label md:hidden">Libellé</label>
                                        <input
                                            type="text"
                                            :name="`lines[${index}][description]`"
                                            x-model="line.description"
                                            class="form-input text-sm"
                                            placeholder="Optionnel"
                                        >
                                    </div>
                                    <div class="col-span-5 md:col-span-2">
                                        <label class="form-label md:hidden">Débit</label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                :name="`lines[${index}][debit]`"
                                                x-model.number="line.debit"
                                                @input="line.credit = line.debit > 0 ? 0 : line.credit"
                                                step="0.01"
                                                min="0"
                                                class="form-input text-sm text-right pr-8"
                                            >
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 text-sm">€</span>
                                        </div>
                                    </div>
                                    <div class="col-span-5 md:col-span-2">
                                        <label class="form-label md:hidden">Crédit</label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                :name="`lines[${index}][credit]`"
                                                x-model.number="line.credit"
                                                @input="line.debit = line.credit > 0 ? 0 : line.debit"
                                                step="0.01"
                                                min="0"
                                                class="form-input text-sm text-right pr-8"
                                            >
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 text-sm">€</span>
                                        </div>
                                    </div>
                                    <div class="col-span-2 md:col-span-12 md:hidden flex justify-end">
                                        <button
                                            type="button"
                                            @click="removeLine(index)"
                                            x-show="lines.length > 2"
                                            class="btn-ghost btn-icon btn-sm text-danger-500"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add Line Button -->
                        <button
                            type="button"
                            @click="addLine()"
                            class="mt-4 w-full py-3 border-2 border-dashed border-secondary-300 dark:border-secondary-600 rounded-xl text-secondary-500 hover:text-primary-600 hover:border-primary-300 transition-colors flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter une ligne
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Totals -->
            <div class="space-y-6">
                <div class="card sticky top-24">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Totaux</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-secondary-600 dark:text-secondary-400">Total débit</span>
                            <span class="font-mono font-bold text-secondary-900 dark:text-white" x-text="formatCurrency(totalDebit)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-secondary-600 dark:text-secondary-400">Total crédit</span>
                            <span class="font-mono font-bold text-secondary-900 dark:text-white" x-text="formatCurrency(totalCredit)"></span>
                        </div>
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-secondary-600 dark:text-secondary-400">Différence</span>
                                <span
                                    class="font-mono font-bold"
                                    :class="isBalanced ? 'text-success-600' : 'text-danger-600'"
                                    x-text="formatCurrency(Math.abs(totalDebit - totalCredit))"
                                ></span>
                            </div>
                        </div>

                        <!-- Balance Indicator -->
                        <div
                            class="p-4 rounded-xl"
                            :class="isBalanced ? 'bg-success-50 dark:bg-success-900/20' : 'bg-danger-50 dark:bg-danger-900/20'"
                        >
                            <div class="flex items-center gap-3">
                                <template x-if="isBalanced">
                                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                <template x-if="!isBalanced">
                                    <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                <span
                                    class="font-medium"
                                    :class="isBalanced ? 'text-success-700 dark:text-success-300' : 'text-danger-700 dark:text-danger-300'"
                                    x-text="isBalanced ? 'Écriture équilibrée' : 'Écriture non équilibrée'"
                                ></span>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-full" :disabled="!isBalanced">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer l'écriture
                        </button>
                    </div>
                </div>

                <!-- Help -->
                <div class="card bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800">
                    <div class="card-body">
                        <h3 class="font-medium text-primary-900 dark:text-primary-100 mb-2">Aide</h3>
                        <ul class="text-sm text-primary-700 dark:text-primary-300 space-y-1">
                            <li>• Une écriture doit être équilibrée</li>
                            <li>• Minimum 2 lignes par écriture</li>
                            <li>• Utilisez les classes PCMN belge</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function journalEntry() {
            return {
                lines: [
                    { id: 1, account_id: '', description: '', debit: 0, credit: 0 },
                    { id: 2, account_id: '', description: '', debit: 0, credit: 0 },
                ],
                nextId: 3,

                addLine() {
                    this.lines.push({
                        id: this.nextId++,
                        account_id: '',
                        description: '',
                        debit: 0,
                        credit: 0
                    });
                },

                removeLine(index) {
                    if (this.lines.length > 2) {
                        this.lines.splice(index, 1);
                    }
                },

                get totalDebit() {
                    return this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
                },

                get totalCredit() {
                    return this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
                },

                get isBalanced() {
                    return Math.abs(this.totalDebit - this.totalCredit) < 0.01 && this.totalDebit > 0;
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('{{ $companyCountryCode === "TN" ? "fr-TN" : "fr-BE" }}', {
                        style: 'currency',
                        currency: '{{ $companyCurrency }}',
                        minimumFractionDigits: {{ $companyDecimalPlaces }},
                        maximumFractionDigits: {{ $companyDecimalPlaces }}
                    }).format(amount);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
