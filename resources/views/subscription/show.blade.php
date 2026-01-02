@extends('layouts.app')

@section('title', 'Mon abonnement')

<x-slot name="header">
    <h2 class="text-xl font-semibold">Mon abonnement</h2>
</x-slot>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Current Plan -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <div class="p-6 border-b border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-white">Plan actuel</h3>
                    @if($subscription)
                        <p class="text-secondary-400 text-sm mt-1">
                            @if($subscription->onTrial())
                                Période d'essai jusqu'au {{ $subscription->trial_ends_at->format('d/m/Y') }}
                            @elseif($subscription->current_period_end)
                                Renouvellement le {{ $subscription->current_period_end->format('d/m/Y') }}
                            @endif
                        </p>
                    @endif
                </div>
                @if($subscription)
                    <span class="px-3 py-1 text-sm rounded-full bg-{{ $subscription->status_color }}-500/20 text-{{ $subscription->status_color }}-400">
                        {{ $subscription->status_label }}
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6">
            @if($plan)
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $plan->name }}</div>
                        <p class="text-secondary-400 mt-1">{{ $plan->description }}</p>

                        @if(!$plan->isFree() && $subscription)
                            <div class="mt-4">
                                <span class="text-3xl font-bold text-white">{{ number_format($subscription->amount, 2) }} €</span>
                                <span class="text-secondary-400">/{{ $subscription->billing_cycle === 'yearly' ? 'an' : 'mois' }}</span>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('subscription.upgrade') }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                        Changer de plan
                    </a>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-secondary-400 mb-4">Aucun abonnement actif</p>
                    <a href="{{ route('subscription.required') }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                        Choisir un plan
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Usage Stats -->
    @if($plan && $usage)
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
            <div class="p-6 border-b border-secondary-700">
                <h3 class="text-lg font-semibold text-white">Utilisation ce mois</h3>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <!-- Invoices -->
                    <div>
                        <div class="text-secondary-400 text-sm mb-2">Factures</div>
                        <div class="text-2xl font-bold text-white">{{ $usage['invoices'] ?? 0 }}</div>
                        <div class="text-xs text-secondary-500">/ {{ $plan->max_invoices_per_month < 0 ? '∞' : $plan->max_invoices_per_month }}</div>
                        @if($plan->max_invoices_per_month > 0)
                            <div class="mt-2 h-2 bg-secondary-700 rounded-full overflow-hidden">
                                @php
                                    $percent = min(100, ($usage['invoices'] ?? 0) / $plan->max_invoices_per_month * 100);
                                @endphp
                                <div class="h-full {{ $percent > 80 ? 'bg-warning-500' : 'bg-primary-500' }} rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        @endif
                    </div>

                    <!-- Clients -->
                    <div>
                        <div class="text-secondary-400 text-sm mb-2">Clients</div>
                        <div class="text-2xl font-bold text-white">{{ $usage['clients'] ?? 0 }}</div>
                        <div class="text-xs text-secondary-500">/ {{ $plan->max_clients < 0 ? '∞' : $plan->max_clients }}</div>
                        @if($plan->max_clients > 0)
                            <div class="mt-2 h-2 bg-secondary-700 rounded-full overflow-hidden">
                                @php
                                    $percent = min(100, ($usage['clients'] ?? 0) / $plan->max_clients * 100);
                                @endphp
                                <div class="h-full {{ $percent > 80 ? 'bg-warning-500' : 'bg-primary-500' }} rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        @endif
                    </div>

                    <!-- Users -->
                    <div>
                        <div class="text-secondary-400 text-sm mb-2">Utilisateurs</div>
                        <div class="text-2xl font-bold text-white">{{ $usage['users'] ?? 0 }}</div>
                        <div class="text-xs text-secondary-500">/ {{ $plan->max_users < 0 ? '∞' : $plan->max_users }}</div>
                        @if($plan->max_users > 0)
                            <div class="mt-2 h-2 bg-secondary-700 rounded-full overflow-hidden">
                                @php
                                    $percent = min(100, ($usage['users'] ?? 0) / $plan->max_users * 100);
                                @endphp
                                <div class="h-full {{ $percent > 80 ? 'bg-warning-500' : 'bg-primary-500' }} rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        @endif
                    </div>

                    <!-- Storage -->
                    <div>
                        <div class="text-secondary-400 text-sm mb-2">Stockage</div>
                        <div class="text-2xl font-bold text-white">{{ number_format(($usage['storage_mb'] ?? 0), 0) }} MB</div>
                        <div class="text-xs text-secondary-500">/ {{ $plan->max_storage_mb >= 1000 ? ($plan->max_storage_mb / 1000) . ' GB' : $plan->max_storage_mb . ' MB' }}</div>
                        @if($plan->max_storage_mb > 0)
                            <div class="mt-2 h-2 bg-secondary-700 rounded-full overflow-hidden">
                                @php
                                    $percent = min(100, ($usage['storage_mb'] ?? 0) / $plan->max_storage_mb * 100);
                                @endphp
                                <div class="h-full {{ $percent > 80 ? 'bg-warning-500' : 'bg-primary-500' }} rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Features -->
    @if($plan)
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
            <div class="p-6 border-b border-secondary-700">
                <h3 class="text-lg font-semibold text-white">Fonctionnalités incluses</h3>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $plan->feature_peppol ? 'text-success-400' : 'text-secondary-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="{{ $plan->feature_peppol ? 'text-white' : 'text-secondary-500' }}">Envoi Peppol</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $plan->feature_recurring_invoices ? 'text-success-400' : 'text-secondary-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="{{ $plan->feature_recurring_invoices ? 'text-white' : 'text-secondary-500' }}">Factures récurrentes</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $plan->feature_quotes ? 'text-success-400' : 'text-secondary-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="{{ $plan->feature_quotes ? 'text-white' : 'text-secondary-500' }}">Devis</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $plan->feature_api_access ? 'text-success-400' : 'text-secondary-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="{{ $plan->feature_api_access ? 'text-white' : 'text-secondary-500' }}">Accès API</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $plan->feature_priority_support ? 'text-success-400' : 'text-secondary-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="{{ $plan->feature_priority_support ? 'text-white' : 'text-secondary-500' }}">Support prioritaire</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Invoices -->
    @if($invoices->isNotEmpty())
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
            <div class="p-6 border-b border-secondary-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Historique de facturation</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Facture</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-700">
                        @foreach($invoices as $invoice)
                            <tr class="hover:bg-secondary-700/50 transition-colors">
                                <td class="px-6 py-4 text-white font-medium">{{ $invoice->invoice_number }}</td>
                                <td class="px-6 py-4 text-secondary-400">{{ $invoice->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-white">{{ number_format($invoice->total, 2) }} €</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $invoice->status_color }}-500/20 text-{{ $invoice->status_color }}-400">
                                        {{ $invoice->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('subscription.invoice.download', $invoice) }}" class="text-primary-400 hover:text-primary-300 text-sm">
                                        Télécharger
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Cancel Subscription -->
    @if($subscription && !in_array($subscription->status, ['cancelled', 'expired']))
        <div class="bg-secondary-800 rounded-xl border border-danger-500/30 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-2">Annuler l'abonnement</h3>
                <p class="text-secondary-400 text-sm mb-4">
                    L'annulation prendra effet à la fin de votre période de facturation actuelle.
                    Vos données seront conservées pendant 90 jours.
                </p>
                <form action="{{ route('subscription.cancel') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre abonnement ?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-danger-500/20 hover:bg-danger-500/30 text-danger-400 rounded-lg transition-colors">
                        Annuler mon abonnement
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
