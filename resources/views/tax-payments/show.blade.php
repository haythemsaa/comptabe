@extends('layouts.app')

@section('title', 'Détail paiement d\'impôt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-secondary-900 dark:text-white">{{ $taxPayment->tax_type_label }}</h1>
            <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                {{ $taxPayment->period_label }} - Année {{ $taxPayment->year }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('tax-payments.edit', $taxPayment) }}" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            <a href="{{ route('tax-payments.index') }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations principales -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Informations principales</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Type d'impôt</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white">
                                <span class="badge badge-primary">{{ $taxPayment->tax_type_label }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Période</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white">{{ $taxPayment->period_label }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Année fiscale</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white">{{ $taxPayment->year }}</dd>
                        </div>
                        @if($taxPayment->quarter)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Trimestre</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white">T{{ $taxPayment->quarter }}</dd>
                        </div>
                        @endif
                        @if($taxPayment->month)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Mois</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white">
                                {{ ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'][$taxPayment->month] ?? '' }}
                            </dd>
                        </div>
                        @endif
                        @if($taxPayment->fiscalYear)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Exercice comptable</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white">
                                {{ $taxPayment->fiscalYear->name ?? $taxPayment->fiscalYear->start_date->format('d/m/Y') . ' - ' . $taxPayment->fiscalYear->end_date->format('d/m/Y') }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Montants -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Montants</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Base imposable</dt>
                            <dd class="mt-1 text-lg font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($taxPayment->taxable_base, 2, ',', ' ') }} €
                            </dd>
                        </div>
                        @if($taxPayment->tax_rate)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Taux d'imposition</dt>
                            <dd class="mt-1 text-lg font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($taxPayment->tax_rate, 2, ',', ' ') }} %
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Montant de l'impôt</dt>
                            <dd class="mt-1 text-lg font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($taxPayment->tax_amount, 2, ',', ' ') }} €
                            </dd>
                        </div>
                        @if($taxPayment->advance_payments > 0)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Acomptes versés</dt>
                            <dd class="mt-1 text-lg font-semibold text-success-600">
                                - {{ number_format($taxPayment->advance_payments, 2, ',', ' ') }} €
                            </dd>
                        </div>
                        @endif
                        <div class="md:col-span-2 pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Montant dû</dt>
                            <dd class="mt-1 text-2xl font-bold {{ $taxPayment->amount_due < 0 ? 'text-success-600' : ($taxPayment->amount_due > 0 ? 'text-danger-600' : 'text-secondary-900 dark:text-white') }}">
                                {{ number_format($taxPayment->amount_due, 2, ',', ' ') }} €
                                @if($taxPayment->amount_due < 0)
                                    <span class="text-sm font-normal text-secondary-500">(Crédit)</span>
                                @endif
                            </dd>
                        </div>
                        @if($taxPayment->amount_paid > 0)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Montant payé</dt>
                            <dd class="mt-1 text-lg font-semibold text-success-600">
                                {{ number_format($taxPayment->amount_paid, 2, ',', ' ') }} €
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Reste à payer</dt>
                            <dd class="mt-1 text-lg font-semibold {{ $taxPayment->remaining_amount > 0 ? 'text-warning-600' : 'text-success-600' }}">
                                {{ number_format($taxPayment->remaining_amount, 2, ',', ' ') }} €
                            </dd>
                        </div>
                        @endif
                        @if($taxPayment->penalties > 0)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Pénalités</dt>
                            <dd class="mt-1 text-lg font-semibold text-danger-600">
                                {{ number_format($taxPayment->penalties, 2, ',', ' ') }} €
                            </dd>
                        </div>
                        @endif
                        @if($taxPayment->interests > 0)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Intérêts de retard</dt>
                            <dd class="mt-1 text-lg font-semibold text-danger-600">
                                {{ number_format($taxPayment->interests, 2, ',', ' ') }} €
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Dates et Références -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Dates et Références</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($taxPayment->due_date)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Date d'échéance</dt>
                            <dd class="mt-1 text-sm {{ $taxPayment->isOverdue() ? 'text-danger-600 font-semibold' : 'text-secondary-900 dark:text-white' }}">
                                {{ $taxPayment->due_date->format('d/m/Y') }}
                                @if($taxPayment->isOverdue())
                                    <span class="text-danger-600">(En retard de {{ $taxPayment->getOverdueDays() }} jours)</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        @if($taxPayment->declaration_date)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Date de déclaration</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white">
                                {{ $taxPayment->declaration_date->format('d/m/Y') }}
                            </dd>
                        </div>
                        @endif
                        @if($taxPayment->payment_date)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Date de paiement</dt>
                            <dd class="mt-1 text-sm text-success-600 font-semibold">
                                {{ $taxPayment->payment_date->format('d/m/Y') }}
                            </dd>
                        </div>
                        @endif
                        @if($taxPayment->reference_number)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Numéro de référence</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white font-mono">
                                {{ $taxPayment->reference_number }}
                            </dd>
                        </div>
                        @endif
                        @if($taxPayment->structured_communication)
                        <div>
                            <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Communication structurée</dt>
                            <dd class="mt-1 text-sm text-secondary-900 dark:text-white font-mono">
                                {{ $taxPayment->structured_communication }}
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Notes -->
            @if($taxPayment->notes)
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Notes</h3>
                </div>
                <div class="card-body">
                    <p class="text-secondary-700 dark:text-secondary-300 whitespace-pre-line">{{ $taxPayment->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status Card -->
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
                    <div class="text-center mb-4">
                        <span class="badge badge-{{ $color }} text-lg px-4 py-2">{{ $taxPayment->status_label }}</span>
                    </div>
                    @if($taxPayment->isOverdue())
                        <div class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg p-3 text-center">
                            <p class="text-danger-600 font-semibold">En retard de {{ $taxPayment->getOverdueDays() }} jours</p>
                            <p class="text-danger-500 text-sm">Échéance: {{ $taxPayment->due_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @if($taxPayment->status !== 'paid')
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Actions rapides</h3>
                </div>
                <div class="card-body space-y-3">
                    <button type="button" class="btn btn-success w-full"
                            x-data
                            @click="$dispatch('open-modal', 'mark-paid-modal')">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Marquer comme payé
                    </button>
                    @if(!$taxPayment->structured_communication)
                    <form action="{{ route('tax-payments.update', $taxPayment) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tax_type" value="{{ $taxPayment->tax_type }}">
                        <input type="hidden" name="year" value="{{ $taxPayment->year }}">
                        <input type="hidden" name="taxable_base" value="{{ $taxPayment->taxable_base }}">
                        <input type="hidden" name="tax_amount" value="{{ $taxPayment->tax_amount }}">
                        <input type="hidden" name="amount_due" value="{{ $taxPayment->amount_due }}">
                        <input type="hidden" name="structured_communication" value="{{ $taxPayment->generateStructuredCommunication() }}">
                        <button type="submit" class="btn btn-secondary w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                            Générer communication
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            <!-- Metadata -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Métadonnées</h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-secondary-500 dark:text-secondary-400">Créé le</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ $taxPayment->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($taxPayment->creator)
                        <div class="flex justify-between">
                            <dt class="text-secondary-500 dark:text-secondary-400">Créé par</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ $taxPayment->creator->name }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-secondary-500 dark:text-secondary-400">Modifié le</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ $taxPayment->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($taxPayment->validator)
                        <div class="flex justify-between">
                            <dt class="text-secondary-500 dark:text-secondary-400">Validé par</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ $taxPayment->validator->name }}</dd>
                        </div>
                        @endif
                        @if($taxPayment->validated_at)
                        <div class="flex justify-between">
                            <dt class="text-secondary-500 dark:text-secondary-400">Validé le</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ $taxPayment->validated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Related -->
            @if($taxPayment->paymentTransaction || $taxPayment->journalEntry)
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Documents liés</h3>
                </div>
                <div class="card-body space-y-2">
                    @if($taxPayment->paymentTransaction)
                    <a href="#" class="flex items-center gap-2 p-2 rounded-lg hover:bg-secondary-100 dark:hover:bg-secondary-700 transition">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span class="text-sm text-secondary-900 dark:text-white">Transaction bancaire</span>
                    </a>
                    @endif
                    @if($taxPayment->journalEntry)
                    <a href="#" class="flex items-center gap-2 p-2 rounded-lg hover:bg-secondary-100 dark:hover:bg-secondary-700 transition">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm text-secondary-900 dark:text-white">Écriture comptable</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div x-data="{ open: false }"
     x-show="open"
     x-on:open-modal.window="if ($event.detail === 'mark-paid-modal') open = true"
     x-on:close-modal.window="open = false"
     x-on:keydown.escape.window="open = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="open = false"></div>
        <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Marquer comme payé</h3>
            <form action="{{ route('tax-payments.mark-paid', $taxPayment) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="modal_amount_paid" class="label">Montant payé</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="amount_paid" id="modal_amount_paid"
                                   class="input pr-10" value="{{ $taxPayment->amount_due }}" required>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                        </div>
                    </div>
                    <div>
                        <label for="modal_payment_date" class="label">Date de paiement</label>
                        <input type="date" name="payment_date" id="modal_payment_date"
                               class="input" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn btn-secondary flex-1">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-success flex-1">
                        Confirmer le paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
