<x-app-layout>
    <x-slot name="title">Facture {{ $invoice->invoice_number }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('invoices.index') }}" class="text-secondary-500 hover:text-secondary-700">Factures</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">{{ $invoice->invoice_number }}</span>
    @endsection

    <div class="space-y-6" x-data="invoiceShow()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $invoice->invoice_number }}</h1>
                    <span class="badge badge-{{ $invoice->status_color }} badge-lg">{{ $invoice->status_label }}</span>
                </div>
                <p class="mt-1 text-secondary-600 dark:text-secondary-400">
                    Créée le @dateFormat($invoice->created_at)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Actions based on status -->
                @if($invoice->status === 'draft')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    <form action="{{ route('invoices.validate', $invoice) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Valider
                        </button>
                    </form>
                @endif

                @if($invoice->status === 'validated')
                    <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Envoyer par email
                        </button>
                    </form>
                    @if($invoice->partner->peppol_capable)
                        <button
                            type="button"
                            @click="showPeppolModal = true"
                            class="btn btn-peppol"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Envoyer via Peppol
                        </button>
                    @endif
                @endif

                @if(in_array($invoice->status, ['sent', 'overdue']))
                    <button
                        type="button"
                        @click="showPaymentModal = true"
                        class="btn btn-success"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Enregistrer paiement
                    </button>
                @endif

                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-secondary" target="_blank">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </a>

                <!-- More Actions Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-secondary btn-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="dropdown-menu right-0 w-48"
                    >
                        <a href="{{ route('invoices.duplicate', $invoice) }}" class="dropdown-item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Dupliquer
                        </a>
                        @if($invoice->status !== 'paid')
                            <a href="{{ route('invoices.credit-note', $invoice) }}" class="dropdown-item">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                                Créer note de crédit
                            </a>
                        @endif
                        @if($invoice->status === 'draft')
                            <hr class="my-2 border-secondary-200 dark:border-secondary-700">
                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger-600 hover:bg-danger-50" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Preview Card -->
                <div class="card">
                    <div class="card-body p-8">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h2 class="text-3xl font-bold text-secondary-900 dark:text-white">FACTURE</h2>
                                <p class="text-lg text-secondary-600 dark:text-secondary-400">{{ $invoice->invoice_number }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold text-secondary-900 dark:text-white">{{ $currentTenant->name }}</div>
                                <div class="text-secondary-600 dark:text-secondary-400">
                                    {{ $currentTenant->formatted_vat_number }}
                                </div>
                            </div>
                        </div>

                        <!-- Addresses -->
                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div>
                                <h3 class="text-sm font-medium text-secondary-500 uppercase tracking-wider mb-2">Émetteur</h3>
                                <div class="text-secondary-900 dark:text-white">
                                    <div class="font-medium">{{ $currentTenant->name }}</div>
                                    @if($currentTenant->street)
                                        <div>{{ $currentTenant->street }} {{ $currentTenant->house_number }}</div>
                                    @endif
                                    @if($currentTenant->postal_code || $currentTenant->city)
                                        <div>{{ $currentTenant->postal_code }} {{ $currentTenant->city }}</div>
                                    @endif
                                    @if($currentTenant->email)
                                        <div class="mt-2">{{ $currentTenant->email }}</div>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-secondary-500 uppercase tracking-wider mb-2">Client</h3>
                                <div class="text-secondary-900 dark:text-white">
                                    <div class="font-medium">{{ $invoice->partner->name }}</div>
                                    @if($invoice->partner->street)
                                        <div>{{ $invoice->partner->street }} {{ $invoice->partner->house_number }}</div>
                                    @endif
                                    @if($invoice->partner->postal_code || $invoice->partner->city)
                                        <div>{{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}</div>
                                    @endif
                                    @if($invoice->partner->vat_number)
                                        <div class="mt-2">TVA: {{ $invoice->partner->vat_number }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Info -->
                        <div class="grid grid-cols-4 gap-4 p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl mb-8">
                            <div>
                                <div class="text-xs text-secondary-500 uppercase">Date facture</div>
                                <div class="font-medium text-secondary-900 dark:text-white">@dateFormat($invoice->invoice_date)</div>
                            </div>
                            <div>
                                <div class="text-xs text-secondary-500 uppercase">Échéance</div>
                                <div class="font-medium text-secondary-900 dark:text-white">
                                    @if($invoice->due_date)
                                        @dateFormat($invoice->due_date)
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-secondary-500 uppercase">Référence</div>
                                <div class="font-medium text-secondary-900 dark:text-white">{{ $invoice->reference ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-secondary-500 uppercase">Communication</div>
                                <div class="font-mono font-medium text-secondary-900 dark:text-white">{{ $invoice->structured_communication }}</div>
                            </div>
                        </div>

                        <!-- Lines -->
                        <table class="w-full mb-8">
                            <thead>
                                <tr class="border-b-2 border-secondary-200 dark:border-secondary-700">
                                    <th class="text-left py-3 text-sm font-medium text-secondary-500 uppercase">Description</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">Qté</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">P.U. HT</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">TVA</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">Total HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->lines as $line)
                                    <tr class="border-b border-secondary-100 dark:border-secondary-800">
                                        <td class="py-4">
                                            <div class="font-medium text-secondary-900 dark:text-white">{{ $line->description }}</div>
                                            @if($line->discount_percent > 0)
                                                <div class="text-sm text-secondary-500">Remise: {{ number_format($line->discount_percent, 2) }}%</div>
                                            @endif
                                        </td>
                                        <td class="text-right py-4 text-secondary-900 dark:text-white">{{ number_format($line->quantity, 2) }}</td>
                                        <td class="text-right py-4 text-secondary-900 dark:text-white">@currency($line->unit_price)</td>
                                        <td class="text-right py-4 text-secondary-900 dark:text-white">{{ number_format($line->vat_rate, 0) }}%</td>
                                        <td class="text-right py-4 font-medium text-secondary-900 dark:text-white">@currency($line->line_amount)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Totals -->
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                                    <span class="font-medium text-secondary-900 dark:text-white">@currency($invoice->total_excl_vat)</span>
                                </div>
                                @foreach($invoice->vatSummary() as $rate => $amount)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-secondary-500">TVA {{ $rate }}%</span>
                                        <span class="text-secondary-700 dark:text-secondary-300">@currency($amount)</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between border-t-2 border-secondary-900 dark:border-white pt-2">
                                    <span class="text-lg font-bold text-secondary-900 dark:text-white">Total TTC</span>
                                    <span class="text-lg font-bold text-primary-600">@currency($invoice->total_incl_vat)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($invoice->notes)
                            <div class="mt-8 pt-8 border-t border-secondary-200 dark:border-secondary-700">
                                <h3 class="text-sm font-medium text-secondary-500 uppercase mb-2">Notes</h3>
                                <p class="text-secondary-700 dark:text-secondary-300 whitespace-pre-line">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Historique</h2>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-secondary-900 dark:text-white">Facture créée</p>
                                    <p class="text-xs text-secondary-500">{{ $invoice->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if($invoice->peppol_sent_at)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-info-100 dark:bg-info-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-secondary-900 dark:text-white">Envoyée via Peppol</p>
                                        <p class="text-xs text-secondary-500">{{ $invoice->peppol_sent_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($invoice->status === 'paid')
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-secondary-900 dark:text-white">Payée</p>
                                        <p class="text-xs text-secondary-500">{{ $invoice->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Summary Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Récapitulatif</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-secondary-900 dark:text-white">@currency($invoice->total_excl_vat)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">TVA</span>
                            <span class="font-medium text-secondary-900 dark:text-white">@currency($invoice->total_vat)</span>
                        </div>
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-secondary-900 dark:text-white">Total TTC</span>
                                <span class="text-2xl font-bold text-primary-600">@currency($invoice->total_incl_vat)</span>
                            </div>
                        </div>

                        @if($invoice->amount_paid > 0)
                            <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700 space-y-2">
                                <div class="flex justify-between text-success-600">
                                    <span>Payé</span>
                                    <span class="font-medium">@currency($invoice->amount_paid)</span>
                                </div>
                                @if($invoice->balance > 0)
                                    <div class="flex justify-between text-danger-600">
                                        <span>Solde dû</span>
                                        <span class="font-bold">@currency($invoice->balance)</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Client Card -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Client</h2>
                        <a href="{{ route('partners.show', $invoice->partner) }}" class="text-sm text-primary-600 hover:text-primary-700">
                            Voir fiche
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                                <span class="text-lg font-bold text-primary-600">{{ strtoupper(substr($invoice->partner->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="font-medium text-secondary-900 dark:text-white">{{ $invoice->partner->name }}</div>
                                <div class="text-sm text-secondary-500">{{ $invoice->partner->vat_number }}</div>
                            </div>
                        </div>
                        @if($invoice->partner->peppol_capable)
                            <div class="mt-4 p-3 bg-success-50 dark:bg-success-900/20 rounded-lg flex items-center gap-2 text-sm text-success-700 dark:text-success-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Compatible Peppol
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Peppol Status Card -->
                @if($invoice->peppol_sent_at || $invoice->partner->peppol_capable)
                    <div class="card bg-gradient-to-br from-primary-500 to-primary-600 text-white">
                        <div class="card-body">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Peppol</h3>
                                    <p class="text-sm text-primary-100">Facturation électronique</p>
                                </div>
                            </div>

                            @if($invoice->peppol_delivered_at)
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Délivré le @dateFormat($invoice->peppol_delivered_at)</span>
                                    </div>
                                </div>
                            @elseif($invoice->peppol_sent_at)
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        <span>Envoyé le @dateFormat($invoice->peppol_sent_at)</span>
                                    </div>
                                    <p class="text-primary-100">En attente de confirmation...</p>
                                </div>
                            @else
                                <p class="text-sm text-primary-100">Cette facture peut être envoyée via le réseau Peppol.</p>
                                @if($invoice->status === 'validated')
                                    <button
                                        type="button"
                                        @click="showPeppolModal = true"
                                        class="mt-4 w-full py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors text-sm font-medium"
                                    >
                                        Envoyer via Peppol
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Payment Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Paiement</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <div class="text-sm text-secondary-500">Communication structurée</div>
                            <div class="font-mono text-lg font-medium text-secondary-900 dark:text-white">{{ $invoice->structured_communication }}</div>
                        </div>
                        @if($currentTenant->iban)
                            <div>
                                <div class="text-sm text-secondary-500">IBAN</div>
                                <div class="font-mono text-secondary-900 dark:text-white">{{ $currentTenant->formatted_iban }}</div>
                            </div>
                        @endif
                        @if($invoice->due_date)
                            <div>
                                <div class="text-sm text-secondary-500">Échéance</div>
                                <div class="font-medium {{ $invoice->isOverdue() ? 'text-danger-600' : 'text-secondary-900 dark:text-white' }}">
                                    @dateFormat($invoice->due_date)
                                    @if($invoice->isOverdue())
                                        <span class="text-sm">({{ abs($invoice->days_until_due) }} jours de retard)</span>
                                    @elseif($invoice->days_until_due > 0)
                                        <span class="text-sm text-secondary-500">(dans {{ $invoice->days_until_due }} jours)</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Peppol Send Modal -->
        @if($invoice->partner->peppol_capable)
        <div
            x-show="showPeppolModal"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showPeppolModal = false"
            @keydown.escape.window="showPeppolModal = false"
        >
            <div
                x-show="showPeppolModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative w-full max-w-lg mx-4 bg-white dark:bg-dark-400 rounded-2xl shadow-xl"
                @click.stop
            >
                <div class="modal-header">
                    <h3 class="modal-title">Envoyer via Peppol</h3>
                    <button type="button" @click="showPeppolModal = false" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="flex items-start gap-4 p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl mb-4">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center text-primary-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium text-primary-900 dark:text-primary-100">Réseau Peppol</h4>
                            <p class="text-sm text-primary-700 dark:text-primary-300">
                                La facture sera convertie au format UBL 2.1 et transmise via le réseau Peppol au destinataire.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-500">Destinataire</span>
                            <span class="font-medium text-secondary-900 dark:text-white">{{ $invoice->partner->name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-500">Identifiant Peppol</span>
                            <span class="font-mono text-secondary-900 dark:text-white">{{ $invoice->partner->peppol_identifier }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-500">Montant</span>
                            <span class="font-medium text-secondary-900 dark:text-white">@currency($invoice->total_incl_vat)</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="showPeppolModal = false" class="btn btn-secondary">Annuler</button>
                    <form action="{{ route('invoices.send-peppol', $invoice) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-peppol">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Confirmer l'envoi
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Payment Modal -->
        <div
            x-show="showPaymentModal"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showPaymentModal = false"
            @keydown.escape.window="showPaymentModal = false"
        >
            <div
                x-show="showPaymentModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative w-full max-w-lg mx-4 bg-white dark:bg-dark-400 rounded-2xl shadow-xl"
                @click.stop
            >
                <form action="{{ route('invoices.payment', $invoice) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h3 class="modal-title">Enregistrer un paiement</h3>
                        <button type="button" @click="showPaymentModal = false" class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body space-y-4">
                        <div>
                            <label class="form-label">Montant reçu *</label>
                            <div class="relative">
                                <input
                                    type="number"
                                    name="amount"
                                    value="{{ $invoice->balance }}"
                                    step="0.01"
                                    min="0.01"
                                    max="{{ $invoice->balance }}"
                                    required
                                    class="form-input pr-8"
                                >
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                            </div>
                            <p class="form-helper">Solde dû: @currency($invoice->balance)</p>
                        </div>
                        <div>
                            <label class="form-label">Date du paiement *</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Mode de paiement</label>
                            <select name="payment_method" class="form-select">
                                <option value="bank_transfer">Virement bancaire</option>
                                <option value="cash">Espèces</option>
                                <option value="card">Carte bancaire</option>
                                <option value="check">Chèque</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Référence</label>
                            <input type="text" name="payment_reference" class="form-input" placeholder="Référence du paiement">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showPaymentModal = false" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function invoiceShow() {
            return {
                showPeppolModal: false,
                showPaymentModal: false
            }
        }
    </script>
    @endpush
</x-app-layout>
