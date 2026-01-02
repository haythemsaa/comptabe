@extends('layouts.app')

@section('title', 'Changer de plan')

@push('styles')
<style>
    .plan-card {
        transition: all 0.3s ease;
    }
    .plan-card:hover {
        transform: translateY(-4px);
    }
    .plan-card.selected {
        border-color: rgb(var(--color-primary-500));
        box-shadow: 0 0 0 2px rgba(var(--color-primary-500), 0.2);
    }
</style>
@endpush

<x-slot name="header">
    <h2 class="text-xl font-semibold">Changer de plan</h2>
</x-slot>

<div class="max-w-6xl mx-auto" x-data="{ billingCycle: 'monthly', selectedPlan: null }">
    <!-- Current Plan Info -->
    @if($currentPlan)
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Plan actuel</p>
                    <p class="text-xl font-bold text-white">{{ $currentPlan->name }}</p>
                    @if($subscription)
                        <p class="text-sm text-secondary-400 mt-1">
                            {{ ucfirst($subscription->billing_cycle) }} -
                            @if($subscription->onTrial())
                                Essai jusqu'au {{ $subscription->trial_ends_at->format('d/m/Y') }}
                            @elseif($subscription->current_period_end)
                                Renouvellement le {{ $subscription->current_period_end->format('d/m/Y') }}
                            @endif
                        </p>
                    @endif
                </div>
                <span class="px-3 py-1 bg-primary-500/20 text-primary-400 rounded-full text-sm">
                    {{ $subscription?->status_label ?? 'Actif' }}
                </span>
            </div>
        </div>
    @endif

    <!-- Feature Required Alert -->
    @if($featureRequired)
        <div class="bg-warning-500/20 border border-warning-500/30 rounded-xl p-4 mb-8">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-warning-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-warning-400 font-medium">Fonctionnalité requise</p>
                    <p class="text-secondary-400 text-sm">Passez à un plan supérieur pour accéder à cette fonctionnalité.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Billing Toggle -->
    <div class="flex justify-center mb-8">
        <div class="bg-secondary-800 rounded-lg p-1 inline-flex">
            <button
                @click="billingCycle = 'monthly'"
                :class="billingCycle === 'monthly' ? 'bg-primary-500 text-white' : 'text-secondary-400 hover:text-white'"
                class="px-6 py-2 rounded-lg font-medium transition-colors"
            >
                Mensuel
            </button>
            <button
                @click="billingCycle = 'yearly'"
                :class="billingCycle === 'yearly' ? 'bg-primary-500 text-white' : 'text-secondary-400 hover:text-white'"
                class="px-6 py-2 rounded-lg font-medium transition-colors"
            >
                Annuel <span class="text-success-400 text-xs ml-1">-20%</span>
            </button>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($plans as $plan)
            <div
                class="plan-card bg-secondary-800 rounded-xl border {{ $plan->is_featured ? 'border-primary-500' : 'border-secondary-700' }} overflow-hidden relative cursor-pointer"
                :class="{ 'selected': selectedPlan === '{{ $plan->id }}' }"
                @click="selectedPlan = '{{ $plan->id }}'"
            >
                @if($plan->is_featured)
                    <div class="absolute top-0 right-0 bg-primary-500 text-xs px-3 py-1 rounded-bl-lg font-medium text-white">
                        Recommandé
                    </div>
                @endif

                @if($currentPlan && $currentPlan->id === $plan->id)
                    <div class="absolute top-0 left-0 bg-success-500 text-xs px-3 py-1 rounded-br-lg font-medium text-white">
                        Actuel
                    </div>
                @endif

                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-2">{{ $plan->name }}</h3>

                    <div class="mb-4">
                        @if($plan->isFree())
                            <div class="text-3xl font-bold text-white">Gratuit</div>
                        @else
                            <div x-show="billingCycle === 'monthly'">
                                <div class="text-3xl font-bold text-white">{{ number_format($plan->price_monthly, 2) }} €</div>
                                <div class="text-secondary-400 text-sm">/mois</div>
                            </div>
                            <div x-show="billingCycle === 'yearly'" x-cloak>
                                <div class="text-3xl font-bold text-white">{{ number_format($plan->price_yearly / 12, 2) }} €</div>
                                <div class="text-secondary-400 text-sm">/mois (facturé annuellement)</div>
                                <div class="text-success-400 text-xs mt-1">
                                    {{ number_format($plan->price_yearly, 2) }} €/an
                                </div>
                            </div>
                        @endif
                    </div>

                    <p class="text-secondary-400 text-sm mb-4">{{ $plan->description }}</p>

                    <!-- Limits -->
                    <ul class="space-y-2 text-sm mb-6">
                        <li class="flex items-center gap-2 text-secondary-300">
                            <svg class="w-4 h-4 text-success-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $plan->getLimitLabel('max_users') }} utilisateurs
                        </li>
                        <li class="flex items-center gap-2 text-secondary-300">
                            <svg class="w-4 h-4 text-success-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $plan->getLimitLabel('max_invoices_per_month') }} factures/mois
                        </li>
                        <li class="flex items-center gap-2 text-secondary-300">
                            <svg class="w-4 h-4 text-success-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $plan->getLimitLabel('max_clients') }} clients
                        </li>
                    </ul>

                    <!-- Features -->
                    <div class="border-t border-secondary-700 pt-4">
                        <div class="flex flex-wrap gap-1">
                            @if($plan->feature_peppol)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Peppol</span>
                            @endif
                            @if($plan->feature_recurring_invoices)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Récurrent</span>
                            @endif
                            @if($plan->feature_quotes)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Devis</span>
                            @endif
                            @if($plan->feature_api_access)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">API</span>
                            @endif
                            @if($plan->feature_priority_support)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Support VIP</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Action Button -->
    <div class="flex justify-center">
        <form action="{{ route('subscription.select-plan') }}" method="POST" x-ref="planForm">
            @csrf
            <input type="hidden" name="plan_id" x-bind:value="selectedPlan">
            <input type="hidden" name="billing_cycle" x-bind:value="billingCycle">
            <button
                type="submit"
                :disabled="!selectedPlan"
                class="px-8 py-3 bg-primary-500 hover:bg-primary-600 disabled:bg-secondary-700 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
            >
                <span x-show="!selectedPlan">Sélectionnez un plan</span>
                <span x-show="selectedPlan">Continuer avec ce plan</span>
            </button>
        </form>
    </div>

    <!-- Comparison Table Link -->
    <div class="text-center mt-6">
        <p class="text-secondary-500 text-sm">
            Besoin d'aide pour choisir ?
            <a href="#comparison" class="text-primary-400 hover:text-primary-300">Voir la comparaison détaillée</a>
        </p>
    </div>
</div>
