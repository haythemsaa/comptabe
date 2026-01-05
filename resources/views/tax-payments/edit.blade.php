@extends('layouts.app')

@section('title', 'Modifier paiement d\'impôt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-secondary-900 dark:text-white">Modifier paiement d'impôt</h1>
            <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                {{ $taxPayment->tax_type_label }} - {{ $taxPayment->period_label }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('tax-payments.show', $taxPayment) }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Voir
            </a>
            <a href="{{ route('tax-payments.index') }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <form action="{{ route('tax-payments.update', $taxPayment) }}" method="POST" x-data="taxPaymentForm()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Type d'impôt et Période -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Type et Période</h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Type d'impôt -->
                            <div class="md:col-span-2">
                                <label for="tax_type" class="label">Type d'impôt <span class="text-danger-500">*</span></label>
                                <select name="tax_type" id="tax_type" class="input @error('tax_type') border-danger-500 @enderror" required x-model="taxType">
                                    <option value="">Sélectionner un type</option>
                                    @foreach(\App\Models\TaxPayment::TAX_TYPE_LABELS as $value => $label)
                                        <option value="{{ $value }}" {{ old('tax_type', $taxPayment->tax_type) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tax_type')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Année -->
                            <div>
                                <label for="year" class="label">Année fiscale <span class="text-danger-500">*</span></label>
                                <select name="year" id="year" class="input @error('year') border-danger-500 @enderror" required x-model="year">
                                    @for($y = date('Y') + 1; $y >= date('Y') - 10; $y--)
                                        <option value="{{ $y }}" {{ old('year', $taxPayment->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('year')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Trimestre -->
                            <div>
                                <label for="quarter" class="label">Trimestre</label>
                                <select name="quarter" id="quarter" class="input @error('quarter') border-danger-500 @enderror" x-model="quarter">
                                    <option value="">Annuel</option>
                                    <option value="1" {{ old('quarter', $taxPayment->quarter) == '1' ? 'selected' : '' }}>T1 - Janvier à Mars</option>
                                    <option value="2" {{ old('quarter', $taxPayment->quarter) == '2' ? 'selected' : '' }}>T2 - Avril à Juin</option>
                                    <option value="3" {{ old('quarter', $taxPayment->quarter) == '3' ? 'selected' : '' }}>T3 - Juillet à Septembre</option>
                                    <option value="4" {{ old('quarter', $taxPayment->quarter) == '4' ? 'selected' : '' }}>T4 - Octobre à Décembre</option>
                                </select>
                                @error('quarter')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Mois -->
                            <div x-show="taxType === 'vat' || taxType === 'professional_tax'">
                                <label for="month" class="label">Mois</label>
                                <select name="month" id="month" class="input @error('month') border-danger-500 @enderror">
                                    <option value="">Non applicable</option>
                                    @foreach(['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'] as $index => $monthName)
                                        <option value="{{ $index + 1 }}" {{ old('month', $taxPayment->month) == ($index + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                    @endforeach
                                </select>
                                @error('month')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Libellé période -->
                            <div class="md:col-span-2">
                                <label for="period_label" class="label">Libellé de période</label>
                                <input type="text" name="period_label" id="period_label" class="input @error('period_label') border-danger-500 @enderror"
                                       value="{{ old('period_label', $taxPayment->period_label) }}"
                                       placeholder="Ex: T1 2024, Année 2024, Décembre 2024...">
                                @error('period_label')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Exercice comptable -->
                            @if($fiscalYears->count() > 0)
                            <div class="md:col-span-2">
                                <label for="fiscal_year_id" class="label">Exercice comptable</label>
                                <select name="fiscal_year_id" id="fiscal_year_id" class="input @error('fiscal_year_id') border-danger-500 @enderror">
                                    <option value="">Aucun</option>
                                    @foreach($fiscalYears as $fiscalYear)
                                        <option value="{{ $fiscalYear->id }}" {{ old('fiscal_year_id', $taxPayment->fiscal_year_id) == $fiscalYear->id ? 'selected' : '' }}>
                                            {{ $fiscalYear->name ?? $fiscalYear->start_date->format('d/m/Y') . ' - ' . $fiscalYear->end_date->format('d/m/Y') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fiscal_year_id')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Montants -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Montants</h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Base imposable -->
                            <div>
                                <label for="taxable_base" class="label">Base imposable <span class="text-danger-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="taxable_base" id="taxable_base"
                                           class="input pr-10 @error('taxable_base') border-danger-500 @enderror"
                                           value="{{ old('taxable_base', $taxPayment->taxable_base) }}" required
                                           x-model="taxableBase" @input="calculateAmount()">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                </div>
                                @error('taxable_base')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Taux d'imposition -->
                            <div>
                                <label for="tax_rate" class="label">Taux d'imposition</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="tax_rate"
                                           class="input pr-10 @error('tax_rate') border-danger-500 @enderror"
                                           value="{{ old('tax_rate', $taxPayment->tax_rate) }}"
                                           x-model="taxRate" @input="calculateAmount()">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">%</span>
                                </div>
                                @error('tax_rate')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Montant de l'impôt -->
                            <div>
                                <label for="tax_amount" class="label">Montant de l'impôt <span class="text-danger-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount"
                                           class="input pr-10 @error('tax_amount') border-danger-500 @enderror"
                                           value="{{ old('tax_amount', $taxPayment->tax_amount) }}" required
                                           x-model="taxAmount" @input="calculateAmountDue()">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                </div>
                                @error('tax_amount')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Acomptes versés -->
                            <div>
                                <label for="advance_payments" class="label">Acomptes versés</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="advance_payments" id="advance_payments"
                                           class="input pr-10 @error('advance_payments') border-danger-500 @enderror"
                                           value="{{ old('advance_payments', $taxPayment->advance_payments) }}"
                                           x-model="advancePayments" @input="calculateAmountDue()">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                </div>
                                @error('advance_payments')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Montant dû -->
                            <div>
                                <label for="amount_due" class="label">Montant dû <span class="text-danger-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="amount_due" id="amount_due"
                                           class="input pr-10 text-lg font-bold @error('amount_due') border-danger-500 @enderror"
                                           :class="{ 'text-success-600': amountDue < 0, 'text-danger-600': amountDue > 0 }"
                                           value="{{ old('amount_due', $taxPayment->amount_due) }}" required
                                           x-model="amountDue">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                </div>
                                @error('amount_due')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Montant payé -->
                            <div>
                                <label for="amount_paid" class="label">Montant payé</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="amount_paid" id="amount_paid"
                                           class="input pr-10 @error('amount_paid') border-danger-500 @enderror"
                                           value="{{ old('amount_paid', $taxPayment->amount_paid) }}">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                </div>
                                @error('amount_paid')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pénalités -->
                            <div>
                                <label for="penalties" class="label">Pénalités</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="penalties" id="penalties"
                                           class="input pr-10 @error('penalties') border-danger-500 @enderror"
                                           value="{{ old('penalties', $taxPayment->penalties) }}">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                </div>
                                @error('penalties')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Intérêts -->
                            <div>
                                <label for="interests" class="label">Intérêts de retard</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" name="interests" id="interests"
                                           class="input pr-10 @error('interests') border-danger-500 @enderror"
                                           value="{{ old('interests', $taxPayment->interests) }}">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                </div>
                                @error('interests')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates et Références -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Dates et Références</h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Date d'échéance -->
                            <div>
                                <label for="due_date" class="label">Date d'échéance</label>
                                <input type="date" name="due_date" id="due_date"
                                       class="input @error('due_date') border-danger-500 @enderror"
                                       value="{{ old('due_date', $taxPayment->due_date?->format('Y-m-d')) }}">
                                @error('due_date')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date de paiement -->
                            <div>
                                <label for="payment_date" class="label">Date de paiement</label>
                                <input type="date" name="payment_date" id="payment_date"
                                       class="input @error('payment_date') border-danger-500 @enderror"
                                       value="{{ old('payment_date', $taxPayment->payment_date?->format('Y-m-d')) }}">
                                @error('payment_date')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date de déclaration -->
                            <div>
                                <label for="declaration_date" class="label">Date de déclaration</label>
                                <input type="date" name="declaration_date" id="declaration_date"
                                       class="input @error('declaration_date') border-danger-500 @enderror"
                                       value="{{ old('declaration_date', $taxPayment->declaration_date?->format('Y-m-d')) }}">
                                @error('declaration_date')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Numéro de référence -->
                            <div>
                                <label for="reference_number" class="label">Numéro de référence</label>
                                <input type="text" name="reference_number" id="reference_number"
                                       class="input @error('reference_number') border-danger-500 @enderror"
                                       value="{{ old('reference_number', $taxPayment->reference_number) }}"
                                       placeholder="Référence SPF Finances">
                                @error('reference_number')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Communication structurée -->
                            <div class="md:col-span-2">
                                <label for="structured_communication" class="label">Communication structurée</label>
                                <input type="text" name="structured_communication" id="structured_communication"
                                       class="input @error('structured_communication') border-danger-500 @enderror"
                                       value="{{ old('structured_communication', $taxPayment->structured_communication) }}"
                                       placeholder="+++XXX/XXXX/XXXXX+++">
                                @error('structured_communication')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="label">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="input @error('notes') border-danger-500 @enderror"
                                          placeholder="Informations complémentaires...">{{ old('notes', $taxPayment->notes) }}</textarea>
                                @error('notes')
                                    <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Statut</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'calculated' => 'info',
                                'declared' => 'primary',
                                'pending_payment' => 'warning',
                                'partially_paid' => 'warning',
                                'paid' => 'success',
                                'overdue' => 'danger',
                                'contested' => 'danger',
                            ];
                            $color = $statusColors[$taxPayment->status] ?? 'secondary';
                        @endphp
                        <div class="flex items-center gap-3 mb-4">
                            <span class="badge badge-{{ $color }} text-sm px-3 py-1">{{ $taxPayment->status_label }}</span>
                            @if($taxPayment->isOverdue())
                                <span class="text-danger-600 text-sm font-medium">
                                    En retard de {{ $taxPayment->getOverdueDays() }} jours
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400 space-y-1">
                            <p>Créé le: {{ $taxPayment->created_at->format('d/m/Y H:i') }}</p>
                            @if($taxPayment->creator)
                                <p>Par: {{ $taxPayment->creator->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800">
                    <div class="card-header border-primary-200 dark:border-primary-800">
                        <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-100">Récapitulatif</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary-600 dark:text-secondary-400">Base imposable</span>
                                <span class="font-medium text-secondary-900 dark:text-white" x-text="formatCurrency(taxableBase)">0,00 €</span>
                            </div>
                            <div class="flex justify-between text-sm" x-show="taxRate">
                                <span class="text-secondary-600 dark:text-secondary-400">Taux</span>
                                <span class="font-medium text-secondary-900 dark:text-white" x-text="taxRate + '%'">0%</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary-600 dark:text-secondary-400">Montant impôt</span>
                                <span class="font-medium text-secondary-900 dark:text-white" x-text="formatCurrency(taxAmount)">0,00 €</span>
                            </div>
                            <div class="flex justify-between text-sm" x-show="advancePayments > 0">
                                <span class="text-secondary-600 dark:text-secondary-400">Acomptes</span>
                                <span class="font-medium text-success-600" x-text="'- ' + formatCurrency(advancePayments)">0,00 €</span>
                            </div>
                            <hr class="border-primary-200 dark:border-primary-700">
                            <div class="flex justify-between">
                                <span class="font-semibold text-secondary-900 dark:text-white">Montant dû</span>
                                <span class="font-bold text-lg"
                                      :class="{ 'text-success-600': amountDue < 0, 'text-danger-600': amountDue > 0, 'text-secondary-900 dark:text-white': amountDue == 0 }"
                                      x-text="formatCurrency(amountDue)">0,00 €</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="flex flex-col gap-3">
                            <button type="submit" class="btn btn-primary w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Enregistrer les modifications
                            </button>
                            <a href="{{ route('tax-payments.show', $taxPayment) }}" class="btn btn-secondary w-full">
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card border-danger-200 dark:border-danger-800">
                    <div class="card-header border-danger-200 dark:border-danger-800">
                        <h3 class="text-lg font-semibold text-danger-600">Zone de danger</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tax-payments.destroy', $taxPayment) }}" method="POST"
                              onsubmit="return confirm('Supprimer ce paiement d\'impôt ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function taxPaymentForm() {
    return {
        taxType: '{{ old('tax_type', $taxPayment->tax_type) }}',
        year: '{{ old('year', $taxPayment->year) }}',
        quarter: '{{ old('quarter', $taxPayment->quarter) }}',
        taxableBase: {{ old('taxable_base', $taxPayment->taxable_base ?? 0) }},
        taxRate: {{ old('tax_rate', $taxPayment->tax_rate ?? 0) ?: 0 }},
        taxAmount: {{ old('tax_amount', $taxPayment->tax_amount ?? 0) }},
        advancePayments: {{ old('advance_payments', $taxPayment->advance_payments ?? 0) }},
        amountDue: {{ old('amount_due', $taxPayment->amount_due ?? 0) }},

        calculateAmount() {
            if (this.taxRate && this.taxableBase) {
                this.taxAmount = (parseFloat(this.taxableBase) * parseFloat(this.taxRate) / 100).toFixed(2);
                this.calculateAmountDue();
            }
        },

        calculateAmountDue() {
            this.amountDue = (parseFloat(this.taxAmount || 0) - parseFloat(this.advancePayments || 0)).toFixed(2);
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('fr-BE', {
                style: 'currency',
                currency: 'EUR'
            }).format(value || 0);
        }
    }
}
</script>
@endsection
