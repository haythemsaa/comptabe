<x-app-layout>
    <x-slot name="title">{{ $recurringInvoice->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('recurring-invoices.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Factures recurrentes</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">{{ $recurringInvoice->name }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center text-primary-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $recurringInvoice->name }}</h1>
                    <p class="text-secondary-500 dark:text-secondary-400">{{ $recurringInvoice->partner->name }}</p>
                </div>
                <span class="badge badge-pill badge-{{ $recurringInvoice->status_color }} ml-2">
                    {{ $recurringInvoice->status_label }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                @if($recurringInvoice->status !== 'completed')
                    <a href="{{ route('recurring-invoices.edit', $recurringInvoice) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                @endif
                @if($recurringInvoice->status === 'active')
                    <form action="{{ route('recurring-invoices.generate', $recurringInvoice) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Generer maintenant
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Schedule Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Planification</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Frequence</p>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $recurringInvoice->frequency_label }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Prochaine generation</p>
                                <p class="font-medium text-secondary-900 dark:text-white">
                                    @if($recurringInvoice->next_invoice_date)
                                        @dateFormat($recurringInvoice->next_invoice_date)
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Date de debut</p>
                                <p class="font-medium text-secondary-900 dark:text-white">@dateFormat($recurringInvoice->start_date)</p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Date de fin</p>
                                <p class="font-medium text-secondary-900 dark:text-white">
                                    {{ $recurringInvoice->end_date ? $recurringInvoice->end_date->format('d/m/Y') : 'Illimitee' }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-6 pt-6 border-t border-secondary-100 dark:border-secondary-800">
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Factures generees</p>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $recurringInvoice->invoices_generated }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Maximum</p>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $recurringInvoice->max_invoices ?? 'Illimite' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Delai paiement</p>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $recurringInvoice->payment_terms_days }} jours</p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Cree par</p>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $recurringInvoice->creator?->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lines -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes de facture</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-secondary-50 dark:bg-dark-300">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">#</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Description</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Qte</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">P.U.</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">TVA</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Total HT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary-100 dark:divide-dark-100">
                                @foreach($recurringInvoice->lines as $line)
                                    <tr>
                                        <td class="px-5 py-4 text-secondary-500">{{ $line->line_number }}</td>
                                        <td class="px-5 py-4">
                                            <p class="font-medium text-secondary-900 dark:text-white">{{ $line->description }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-right text-secondary-700 dark:text-secondary-300">{{ number_format($line->quantity, 2) }}</td>
                                        <td class="px-5 py-4 text-right text-secondary-700 dark:text-secondary-300">@currency($line->unit_price)</td>
                                        <td class="px-5 py-4 text-right text-secondary-700 dark:text-secondary-300">{{ number_format($line->vat_rate, 0) }}%</td>
                                        <td class="px-5 py-4 text-right font-medium text-secondary-900 dark:text-white">@currency($line->line_total)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- History -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Historique des factures generees</h2>
                    </div>
                    @if($recurringInvoice->generatedInvoices->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-secondary-50 dark:bg-dark-300">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Facture</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Date</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Statut</th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-secondary-100 dark:divide-dark-100">
                                    @foreach($recurringInvoice->generatedInvoices as $history)
                                        <tr>
                                            <td class="px-5 py-4">
                                                @if($history->invoice)
                                                    <a href="{{ route('invoices.show', $history->invoice) }}" class="font-medium text-primary-500 hover:text-primary-600">
                                                        {{ $history->invoice->invoice_number }}
                                                    </a>
                                                @else
                                                    <span class="text-secondary-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-secondary-700 dark:text-secondary-300">@dateFormat($history->generated_date)</td>
                                            <td class="px-5 py-4">
                                                @if($history->status === 'success')
                                                    <span class="badge badge-pill badge-success">Succes</span>
                                                @else
                                                    <span class="badge badge-pill badge-danger">Erreur</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                @if($history->invoice)
                                                    <a href="{{ route('invoices.show', $history->invoice) }}" class="btn btn-ghost btn-icon btn-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="card-body text-center py-8">
                            <p class="text-secondary-500">Aucune facture generee pour le moment</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Totals -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Montant par facture</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-secondary-900 dark:text-white">@currency($recurringInvoice->total_excl_vat)</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-secondary-100 dark:border-secondary-800 pt-2">
                            <span class="text-secondary-600 dark:text-secondary-400">Total TVA</span>
                            <span class="font-medium text-secondary-900 dark:text-white">@currency($recurringInvoice->total_vat)</span>
                        </div>
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-secondary-900 dark:text-white">Total TTC</span>
                                <span class="text-2xl font-bold text-primary-600">@currency($recurringInvoice->total_incl_vat)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Client</h2>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="avatar avatar-lg avatar-primary">
                                {{ strtoupper(substr($recurringInvoice->partner->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $recurringInvoice->partner->name }}</p>
                                @if($recurringInvoice->partner->vat_number)
                                    <p class="text-sm text-secondary-500 dark:text-secondary-400">{{ $recurringInvoice->partner->vat_number }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-secondary-100 dark:border-secondary-800">
                            <a href="{{ route('partners.show', $recurringInvoice->partner) }}" class="text-sm text-primary-500 hover:text-primary-600">
                                Voir la fiche client
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Actions</h2>
                    </div>
                    <div class="card-body space-y-2">
                        @if($recurringInvoice->status === 'active')
                            <form action="{{ route('recurring-invoices.pause', $recurringInvoice) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light-warning w-full justify-start">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Mettre en pause
                                </button>
                            </form>
                        @endif
                        @if($recurringInvoice->status === 'paused')
                            <form action="{{ route('recurring-invoices.resume', $recurringInvoice) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light-success w-full justify-start">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Reprendre
                                </button>
                            </form>
                        @endif
                        @if(!in_array($recurringInvoice->status, ['completed', 'cancelled']))
                            <form action="{{ route('recurring-invoices.cancel', $recurringInvoice) }}" method="POST" onsubmit="return confirm('Annuler cette recurrence ?')">
                                @csrf
                                <button type="submit" class="btn btn-light-danger w-full justify-start">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Annuler la recurrence
                                </button>
                            </form>
                        @endif
                        @if($recurringInvoice->invoices_generated === 0)
                            <form action="{{ route('recurring-invoices.destroy', $recurringInvoice) }}" method="POST" onsubmit="return confirm('Supprimer cette recurrence ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light-danger w-full justify-start">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Options -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Options d'envoi</h2>
                    </div>
                    <div class="card-body space-y-3">
                        <div class="flex items-center gap-2">
                            @if($recurringInvoice->auto_send)
                                <span class="w-2 h-2 bg-success-500 rounded-full"></span>
                                <span class="text-secondary-700 dark:text-secondary-300">Envoi automatique par email</span>
                            @else
                                <span class="w-2 h-2 bg-secondary-300 rounded-full"></span>
                                <span class="text-secondary-500">Envoi email desactive</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($recurringInvoice->auto_send_peppol)
                                <span class="w-2 h-2 bg-success-500 rounded-full"></span>
                                <span class="text-secondary-700 dark:text-secondary-300">Envoi Peppol active</span>
                            @else
                                <span class="w-2 h-2 bg-secondary-300 rounded-full"></span>
                                <span class="text-secondary-500">Envoi Peppol desactive</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($recurringInvoice->include_structured_communication)
                                <span class="w-2 h-2 bg-success-500 rounded-full"></span>
                                <span class="text-secondary-700 dark:text-secondary-300">Communication structuree</span>
                            @else
                                <span class="w-2 h-2 bg-secondary-300 rounded-full"></span>
                                <span class="text-secondary-500">Communication structuree desactivee</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
