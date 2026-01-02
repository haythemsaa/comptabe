@extends('layouts.app')

@section('title', 'Catégorisation Intelligente')

@section('content')
<div x-data="categorizationApp()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Catégorisation Intelligente
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Classification automatique des dépenses par IA
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($uncategorized->count() > 0)
                <button @click="batchCategorize()"
                        :disabled="processing"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors">
                    <svg x-show="!processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg x-show="processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="processing ? 'Traitement...' : 'Catégoriser tout'"></span>
                </button>
            @endif
        </div>
    </div>

    <!-- Stats Analyse -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @foreach($analysis['monthly_trends'] ?? [] as $category => $data)
            @if($loop->index < 4)
                <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-lg border border-secondary-200 dark:border-secondary-700">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-secondary-500">{{ $data['label'] ?? ucfirst($category) }}</span>
                        @if(isset($data['trend']))
                            <span class="text-xs px-2 py-1 rounded-full {{ $data['trend'] === 'up' ? 'bg-red-100 text-red-600' : ($data['trend'] === 'down' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600') }}">
                                {{ $data['trend'] === 'up' ? '+' : '' }}{{ number_format($data['change'] ?? 0, 1) }}%
                            </span>
                        @endif
                    </div>
                    <div class="text-2xl font-bold text-secondary-900 dark:text-white">
                        {{ number_format($data['current_month'] ?? 0, 0, ',', ' ') }} €
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="flex-1 bg-secondary-200 dark:bg-secondary-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-purple-500"
                                 style="width: {{ min(100, ($data['current_month'] ?? 0) / max(1, $data['average'] ?? 1) * 100) }}%"></div>
                        </div>
                        <span class="text-xs text-secondary-500">vs moy.</span>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Dépenses à catégoriser -->
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Dépenses à catégoriser ({{ $uncategorized->count() }})
                </h3>
                <div class="flex items-center gap-2">
                    <select x-model="confidenceFilter" class="text-sm rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800">
                        <option value="all">Toutes</option>
                        <option value="high">Haute confiance (> 80%)</option>
                        <option value="medium">Moyenne (50-80%)</option>
                        <option value="low">Basse (< 50%)</option>
                    </select>
                </div>
            </div>
        </x-slot:header>

        @if($uncategorized->isEmpty())
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-2">Tout est catégorisé!</h3>
                <p class="text-secondary-600 dark:text-secondary-400">Toutes vos dépenses ont été classifiées.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($uncategorized as $expense)
                    @php $suggestion = $suggestions[$expense->id] ?? null; @endphp
                    <div x-data="{ expanded: false, applied: false }"
                         :class="applied ? 'opacity-50' : ''"
                         class="border border-secondary-200 dark:border-secondary-700 rounded-lg overflow-hidden transition-all duration-300">
                        <!-- Main row -->
                        <div class="p-4 flex items-center gap-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 cursor-pointer"
                             @click="expanded = !expanded">
                            <div class="flex-shrink-0">
                                <input type="checkbox"
                                       x-model="selectedExpenses"
                                       value="{{ $expense->id }}"
                                       @click.stop
                                       class="rounded text-purple-600 focus:ring-purple-500">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-secondary-900 dark:text-white truncate">
                                    {{ $expense->description ?? $expense->label ?? 'Dépense sans description' }}
                                </p>
                                <p class="text-sm text-secondary-500">
                                    {{ $expense->created_at->format('d/m/Y') }}
                                    @if($expense->partner)
                                        &bull; {{ $expense->partner->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-secondary-900 dark:text-white">
                                    {{ number_format($expense->amount, 2, ',', ' ') }} €
                                </p>
                                @if($suggestion)
                                    <div class="flex items-center gap-2 mt-1">
                                        <x-badge :color="$suggestion['confidence'] >= 0.8 ? 'green' : ($suggestion['confidence'] >= 0.5 ? 'yellow' : 'red')">
                                            {{ number_format($suggestion['confidence'] * 100, 0) }}%
                                        </x-badge>
                                        <span class="text-sm text-secondary-500">{{ $suggestion['category_label'] ?? $suggestion['category'] }}</span>
                                    </div>
                                @endif
                            </div>
                            <svg class="w-5 h-5 text-secondary-400 transition-transform duration-200"
                                 :class="expanded ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        <!-- Expanded details -->
                        <div x-show="expanded" x-collapse class="border-t border-secondary-200 dark:border-secondary-700 p-4 bg-secondary-50 dark:bg-secondary-800/50">
                            @if($suggestion)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Catégorie suggérée</label>
                                        <select class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-700 text-sm"
                                                x-model="categories['{{ $expense->id }}']">
                                            <option value="{{ $suggestion['category'] }}" selected>{{ $suggestion['category_label'] ?? $suggestion['category'] }}</option>
                                            <option value="office_supplies">Fournitures de bureau</option>
                                            <option value="telecom">Télécom & Internet</option>
                                            <option value="transport">Transport & Déplacements</option>
                                            <option value="meals">Repas & Restaurants</option>
                                            <option value="insurance">Assurances</option>
                                            <option value="utilities">Énergie & Utilités</option>
                                            <option value="rent">Loyer & Immobilier</option>
                                            <option value="subscriptions">Abonnements & SaaS</option>
                                            <option value="marketing">Marketing & Publicité</option>
                                            <option value="professional_fees">Honoraires professionnels</option>
                                            <option value="bank_fees">Frais bancaires</option>
                                            <option value="other">Autres</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Compte PCMN</label>
                                        <input type="text" value="{{ $suggestion['account_code'] ?? '' }}"
                                               class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-700 text-sm"
                                               x-model="accountCodes['{{ $expense->id }}']">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Code TVA</label>
                                        <select class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-700 text-sm"
                                                x-model="vatCodes['{{ $expense->id }}']">
                                            <option value="{{ $suggestion['vat_code'] ?? 'S21' }}" selected>{{ $suggestion['vat_code'] ?? 'S21' }}</option>
                                            <option value="S21">S21 - TVA 21%</option>
                                            <option value="S12">S12 - TVA 12%</option>
                                            <option value="S06">S06 - TVA 6%</option>
                                            <option value="S00">S00 - TVA 0%</option>
                                            <option value="EXO">EXO - Exonéré</option>
                                        </select>
                                    </div>
                                </div>

                                @if(!empty($suggestion['reasoning']))
                                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <p class="text-sm text-blue-800 dark:text-blue-300">
                                            <span class="font-medium">Raison IA:</span> {{ $suggestion['reasoning'] }}
                                        </p>
                                    </div>
                                @endif

                                <div class="flex items-center justify-end gap-3">
                                    <button @click="rejectSuggestion({{ $expense->id }})"
                                            class="px-4 py-2 text-secondary-600 hover:text-secondary-800 transition-colors">
                                        Ignorer
                                    </button>
                                    <button @click="applySuggestion({{ $expense->id }})"
                                            :disabled="processing"
                                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors">
                                        Appliquer
                                    </button>
                                </div>
                            @else
                                <p class="text-secondary-500 text-center py-4">Aucune suggestion disponible pour cette dépense.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    <!-- Analyse des dépenses -->
    @if(!empty($analysis['predictions']))
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Prédictions du mois prochain
                </h3>
            </x-slot:header>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($analysis['predictions'] as $category => $prediction)
                    <div class="p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                        <p class="text-sm text-secondary-500 mb-1">{{ $prediction['label'] ?? ucfirst($category) }}</p>
                        <p class="text-xl font-bold text-secondary-900 dark:text-white">
                            {{ number_format($prediction['predicted_amount'], 0, ',', ' ') }} €
                        </p>
                        <div class="flex items-center gap-1 mt-1">
                            @if(($prediction['trend'] ?? 0) > 0)
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            @endif
                            <span class="text-xs text-secondary-500">
                                {{ abs($prediction['trend'] ?? 0) }}% vs moyenne
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</div>

@push('scripts')
<script>
function categorizationApp() {
    return {
        processing: false,
        selectedExpenses: [],
        confidenceFilter: 'all',
        categories: {},
        accountCodes: {},
        vatCodes: {},

        async applySuggestion(expenseId) {
            this.processing = true;

            try {
                const response = await fetch(`/ai/categorize/${expenseId}?apply=1`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        category: this.categories[expenseId],
                        account_code: this.accountCodes[expenseId],
                        vat_code: this.vatCodes[expenseId]
                    })
                });

                if (response.ok) {
                    // Remove from list or mark as applied
                    location.reload();
                }
            } catch (error) {
                console.error('Error applying suggestion:', error);
            } finally {
                this.processing = false;
            }
        },

        async batchCategorize() {
            if (this.selectedExpenses.length === 0) {
                alert('Veuillez sélectionner au moins une dépense.');
                return;
            }

            this.processing = true;

            try {
                const response = await fetch('/ai/categorize/batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        expense_ids: this.selectedExpenses
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(`${result.summary.applied} dépenses catégorisées sur ${result.summary.total}.`);
                    location.reload();
                }
            } catch (error) {
                console.error('Error batch categorizing:', error);
            } finally {
                this.processing = false;
            }
        },

        rejectSuggestion(expenseId) {
            // Just collapse the item
            // Could also save rejection for learning
        }
    }
}
</script>
@endpush
@endsection
