<x-admin-layout>
    <x-slot name="title">Facture {{ $subscriptionInvoice->invoice_number }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.subscription-invoices.index') }}" class="text-secondary-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span>Facture {{ $subscriptionInvoice->invoice_number }}</span>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Preview -->
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="p-8">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">FACTURE</h1>
                            <p class="text-gray-600 font-mono">{{ $subscriptionInvoice->invoice_number }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">ComptaBE</div>
                            <div class="text-gray-600 text-sm">
                                Votre adresse<br>
                                1000 Bruxelles<br>
                                BE 0000.000.000
                            </div>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <div class="text-xs text-gray-500 uppercase mb-1">Facturé à</div>
                            <div class="text-gray-900 font-medium">{{ $subscriptionInvoice->company->name }}</div>
                            <div class="text-gray-600 text-sm">
                                {{ $subscriptionInvoice->company->street }} {{ $subscriptionInvoice->company->house_number }}<br>
                                {{ $subscriptionInvoice->company->postal_code }} {{ $subscriptionInvoice->company->city }}<br>
                                {{ $subscriptionInvoice->company->vat_number }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500 uppercase mb-1">Détails</div>
                            <div class="text-gray-600 text-sm">
                                <div>Date: {{ $subscriptionInvoice->created_at->format('d/m/Y') }}</div>
                                @if($subscriptionInvoice->due_date)
                                    <div>Échéance: {{ $subscriptionInvoice->due_date->format('d/m/Y') }}</div>
                                @endif
                                @if($subscriptionInvoice->paid_at)
                                    <div class="text-green-600">Payé le: {{ $subscriptionInvoice->paid_at->format('d/m/Y') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <table class="w-full mb-8">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 text-xs text-gray-500 uppercase">Description</th>
                                <th class="text-right py-3 text-xs text-gray-500 uppercase">Période</th>
                                <th class="text-right py-3 text-xs text-gray-500 uppercase">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="py-4 text-gray-900">
                                    Abonnement {{ $subscriptionInvoice->subscription?->plan?->name ?? 'Standard' }}
                                    <div class="text-gray-500 text-sm">{{ ucfirst($subscriptionInvoice->subscription?->billing_cycle ?? 'monthly') }}</div>
                                </td>
                                <td class="py-4 text-right text-gray-600">
                                    @if($subscriptionInvoice->period_start && $subscriptionInvoice->period_end)
                                        {{ $subscriptionInvoice->period_start->format('d/m/Y') }} - {{ $subscriptionInvoice->period_end->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-4 text-right text-gray-900 font-medium">{{ number_format($subscriptionInvoice->subtotal, 2) }} €</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="flex justify-end">
                        <div class="w-64">
                            <div class="flex justify-between py-2 text-gray-600">
                                <span>Sous-total</span>
                                <span>{{ number_format($subscriptionInvoice->subtotal, 2) }} €</span>
                            </div>
                            <div class="flex justify-between py-2 text-gray-600">
                                <span>TVA ({{ $subscriptionInvoice->vat_rate }}%)</span>
                                <span>{{ number_format($subscriptionInvoice->vat_amount, 2) }} €</span>
                            </div>
                            <div class="flex justify-between py-3 text-lg font-bold text-gray-900 border-t border-gray-200">
                                <span>Total</span>
                                <span>{{ number_format($subscriptionInvoice->total, 2) }} €</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 pt-8 border-t border-gray-200 text-gray-500 text-sm">
                        <p>Merci pour votre confiance.</p>
                        <p class="mt-2">
                            IBAN: BE00 0000 0000 0000<br>
                            BIC: GEBABEBB<br>
                            Communication: {{ $subscriptionInvoice->invoice_number }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h3 class="font-semibold text-white">Statut</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $subscriptionInvoice->status_color }}-500/20 text-{{ $subscriptionInvoice->status_color }}-400">
                            {{ $subscriptionInvoice->status_label }}
                        </span>
                    </div>

                    @if($subscriptionInvoice->status === 'pending')
                        <form action="{{ route('admin.subscription-invoices.mark-paid', $subscriptionInvoice) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm text-secondary-300 mb-2">Méthode de paiement</label>
                                <select name="payment_method" required class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                                    <option value="bank_transfer">Virement bancaire</option>
                                    <option value="card">Carte bancaire</option>
                                    <option value="cash">Espèces</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-secondary-300 mb-2">Référence</label>
                                <input type="text" name="payment_reference" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg transition-colors">
                                Marquer comme payé
                            </button>
                        </form>
                    @elseif($subscriptionInvoice->status === 'paid')
                        <div class="text-sm text-secondary-400">
                            <div class="flex justify-between mb-2">
                                <span>Payé le:</span>
                                <span class="text-white">{{ $subscriptionInvoice->paid_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($subscriptionInvoice->payment_method)
                                <div class="flex justify-between mb-2">
                                    <span>Méthode:</span>
                                    <span class="text-white">{{ ucfirst($subscriptionInvoice->payment_method) }}</span>
                                </div>
                            @endif
                            @if($subscriptionInvoice->payment_reference)
                                <div class="flex justify-between">
                                    <span>Référence:</span>
                                    <span class="text-white">{{ $subscriptionInvoice->payment_reference }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Info -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h3 class="font-semibold text-white">Informations</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-secondary-400 text-sm">Entreprise</div>
                        <a href="{{ route('admin.companies.show', $subscriptionInvoice->company) }}" class="text-white hover:text-primary-400">
                            {{ $subscriptionInvoice->company->name }}
                        </a>
                    </div>

                    @if($subscriptionInvoice->subscription)
                        <div>
                            <div class="text-secondary-400 text-sm">Abonnement</div>
                            <a href="{{ route('admin.subscriptions.show', $subscriptionInvoice->subscription) }}" class="text-white hover:text-primary-400">
                                {{ $subscriptionInvoice->subscription->plan->name }} - {{ ucfirst($subscriptionInvoice->subscription->billing_cycle) }}
                            </a>
                        </div>
                    @endif

                    <div>
                        <div class="text-secondary-400 text-sm">Créée le</div>
                        <div class="text-white">{{ $subscriptionInvoice->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h3 class="font-semibold text-white">Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <button class="flex items-center gap-2 w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Télécharger PDF
                    </button>
                    <button class="flex items-center gap-2 w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Envoyer par email
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
