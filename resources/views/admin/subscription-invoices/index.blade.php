<x-admin-layout>
    <x-slot name="title">Facturation abonnements</x-slot>
    <x-slot name="header">Facturation des abonnements</x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">Total facturé</div>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total'], 2) }} €</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">En attente</div>
            <div class="text-2xl font-bold text-warning-400 mt-1">{{ number_format($stats['pending'], 2) }} €</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">Payé</div>
            <div class="text-2xl font-bold text-success-400 mt-1">{{ number_format($stats['paid'], 2) }} €</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">En retard</div>
            <div class="text-2xl font-bold text-danger-400 mt-1">{{ number_format($stats['overdue'], 2) }} €</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.subscription-invoices.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher facture ou entreprise..."
                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400">
            </div>
            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payé</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>En retard</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.subscription-invoices.index') }}" class="text-secondary-400 hover:text-white">Réinitialiser</a>
            @endif
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Facture</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Entreprise</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($invoices as $invoice)
                    <tr class="hover:bg-secondary-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-white font-mono font-medium">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.companies.show', $invoice->company) }}" class="text-white hover:text-primary-400 font-medium">
                                {{ $invoice->company->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            @if($invoice->subscription?->plan)
                                <span class="px-2 py-1 bg-primary-500/20 text-primary-400 text-sm rounded">
                                    {{ $invoice->subscription->plan->name }}
                                </span>
                            @else
                                <span class="text-secondary-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-white font-medium">{{ number_format($invoice->total, 2) }} €</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $invoice->status_color }}-500/20 text-{{ $invoice->status_color }}-400">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-secondary-400">
                            {{ $invoice->created_at->format('d/m/Y') }}
                            @if($invoice->due_date && $invoice->status !== 'paid')
                                <div class="text-xs {{ $invoice->due_date < now() ? 'text-danger-400' : 'text-secondary-500' }}">
                                    Échéance: {{ $invoice->due_date->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.subscription-invoices.show', $invoice) }}" class="text-secondary-400 hover:text-white" title="Voir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if($invoice->status === 'pending')
                                    <button type="button"
                                        onclick="document.getElementById('pay-modal-{{ $invoice->id }}').classList.remove('hidden')"
                                        class="text-success-400 hover:text-success-300" title="Marquer payé">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>

                                    <!-- Payment Modal -->
                                    <div id="pay-modal-{{ $invoice->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                        <div class="absolute inset-0 bg-black/50" onclick="this.parentElement.classList.add('hidden')"></div>
                                        <div class="relative bg-secondary-800 rounded-xl border border-secondary-700 p-6 w-full max-w-md">
                                            <h3 class="text-lg font-semibold text-white mb-4">Marquer comme payé</h3>
                                            <form action="{{ route('admin.subscription-invoices.mark-paid', $invoice) }}" method="POST">
                                                @csrf
                                                <div class="space-y-4">
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
                                                        <label class="block text-sm text-secondary-300 mb-2">Référence (optionnel)</label>
                                                        <input type="text" name="payment_reference" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white" placeholder="Ex: Virement du 15/12">
                                                    </div>
                                                </div>
                                                <div class="flex justify-end gap-3 mt-6">
                                                    <button type="button" onclick="this.closest('[id^=pay-modal]').classList.add('hidden')" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg">
                                                        Annuler
                                                    </button>
                                                    <button type="submit" class="px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg">
                                                        Confirmer
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-secondary-400">
                            Aucune facture trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
