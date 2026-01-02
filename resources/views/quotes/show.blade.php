<x-app-layout>
    <x-slot name="title">Devis {{ $quote->quote_number }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('quotes.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Devis</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">{{ $quote->quote_number }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-info-100 dark:bg-info-500/20 flex items-center justify-center text-info-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $quote->quote_number }}</h1>
                    <p class="text-secondary-500 dark:text-secondary-400">{{ $quote->partner->name }}</p>
                </div>
                <span class="badge badge-pill badge-{{ $quote->status_color }} ml-2">
                    {{ $quote->status_label }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-outline-secondary" target="_blank">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    PDF
                </a>
                @if($quote->isEditable())
                    <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                @endif
                @if($quote->canConvert())
                    <form action="{{ route('quotes.convert', $quote) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Convertir en facture
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
                <!-- Quote Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Informations du devis</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Date du devis</p>
                                <p class="font-medium text-secondary-900 dark:text-white">@dateFormat($quote->quote_date)</p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Valide jusqu'au</p>
                                <p class="font-medium {{ $quote->isExpired() ? 'text-danger-500' : 'text-secondary-900 dark:text-white' }}">
                                    @if($quote->valid_until)
                                        @dateFormat($quote->valid_until)
                                        @if($quote->isExpired())
                                            <span class="text-xs">(Expire)</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Reference</p>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $quote->reference ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Cree par</p>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $quote->creator?->name ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lines -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes du devis</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-secondary-50 dark:bg-dark-300">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">#</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500">Description</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Qte</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">P.U.</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Remise</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">TVA</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500">Total HT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary-100 dark:divide-dark-100">
                                @foreach($quote->lines as $line)
                                    <tr>
                                        <td class="px-5 py-4 text-secondary-500">{{ $line->line_number }}</td>
                                        <td class="px-5 py-4">
                                            <p class="font-medium text-secondary-900 dark:text-white">{{ $line->description }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-right text-secondary-700 dark:text-secondary-300">{{ number_format($line->quantity, 2) }}</td>
                                        <td class="px-5 py-4 text-right text-secondary-700 dark:text-secondary-300">@currency($line->unit_price)</td>
                                        <td class="px-5 py-4 text-right text-secondary-700 dark:text-secondary-300">
                                            @if($line->discount_percent > 0)
                                                {{ number_format($line->discount_percent, 2) }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-right text-secondary-700 dark:text-secondary-300">{{ number_format($line->vat_rate, 0) }}%</td>
                                        <td class="px-5 py-4 text-right font-medium text-secondary-900 dark:text-white">@currency($line->line_total)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                @if($quote->notes || $quote->terms)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Notes et conditions</h2>
                        </div>
                        <div class="card-body space-y-4">
                            @if($quote->notes)
                                <div>
                                    <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Notes</p>
                                    <p class="text-secondary-700 dark:text-secondary-300 whitespace-pre-line">{{ $quote->notes }}</p>
                                </div>
                            @endif
                            @if($quote->terms)
                                <div>
                                    <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Conditions generales</p>
                                    <p class="text-secondary-700 dark:text-secondary-300 whitespace-pre-line">{{ $quote->terms }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Converted Invoice -->
                @if($quote->convertedInvoice)
                    <div class="card bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-800">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center text-success-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-success-900 dark:text-success-100">Converti en facture</h3>
                                        <p class="text-sm text-success-700 dark:text-success-300">
                                            Facture {{ $quote->convertedInvoice->invoice_number }} creee le @dateFormat($quote->converted_at)
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('invoices.show', $quote->convertedInvoice) }}" class="btn btn-success btn-sm">
                                    Voir la facture
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Totals -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Totaux</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-secondary-900 dark:text-white">@currency($quote->total_excl_vat)</span>
                        </div>
                        @if($quote->discount_percent > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-secondary-500">Remise globale ({{ number_format($quote->discount_percent, 2) }}%)</span>
                                <span class="text-danger-500">- @currency($quote->total_excl_vat * $quote->discount_percent / 100)</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between border-t border-secondary-100 dark:border-secondary-800 pt-2">
                            <span class="text-secondary-600 dark:text-secondary-400">Total TVA</span>
                            <span class="font-medium text-secondary-900 dark:text-white">@currency($quote->total_vat)</span>
                        </div>
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-secondary-900 dark:text-white">Total TTC</span>
                                <span class="text-2xl font-bold text-primary-600">@currency($quote->total_incl_vat)</span>
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
                            <div class="avatar avatar-lg avatar-info">
                                {{ strtoupper(substr($quote->partner->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $quote->partner->name }}</p>
                                @if($quote->partner->vat_number)
                                    <p class="text-sm text-secondary-500 dark:text-secondary-400">{{ $quote->partner->vat_number }}</p>
                                @endif
                            </div>
                        </div>
                        @if($quote->partner->address || $quote->partner->city)
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                @if($quote->partner->address)
                                    <p>{{ $quote->partner->address }}</p>
                                @endif
                                @if($quote->partner->postal_code || $quote->partner->city)
                                    <p>{{ $quote->partner->postal_code }} {{ $quote->partner->city }}</p>
                                @endif
                                @if($quote->partner->country)
                                    <p>{{ $quote->partner->country }}</p>
                                @endif
                            </div>
                        @endif
                        <div class="mt-4 pt-4 border-t border-secondary-100 dark:border-secondary-800">
                            <a href="{{ route('partners.show', $quote->partner) }}" class="text-sm text-primary-500 hover:text-primary-600">
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
                        @if($quote->status === 'draft')
                            <form action="{{ route('quotes.send', $quote) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light-info w-full justify-start">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Marquer comme envoye
                                </button>
                            </form>
                        @endif
                        @if(in_array($quote->status, ['draft', 'sent']))
                            <form action="{{ route('quotes.accept', $quote) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light-success w-full justify-start">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marquer comme accepte
                                </button>
                            </form>
                            <form action="{{ route('quotes.reject', $quote) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light-danger w-full justify-start">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marquer comme refuse
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('quotes.duplicate', $quote) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light-secondary w-full justify-start">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Dupliquer
                            </button>
                        </form>
                        @if($quote->isEditable())
                            <form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce devis ?')">
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

                <!-- Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Historique</h2>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="w-2 h-2 mt-2 rounded-full bg-secondary-400"></div>
                                <div>
                                    <p class="text-sm font-medium text-secondary-900 dark:text-white">Cree</p>
                                    <p class="text-xs text-secondary-500">@dateFormat($quote->created_at, 'd/m/Y H:i')</p>
                                </div>
                            </div>
                            @if($quote->sent_at)
                                <div class="flex gap-3">
                                    <div class="w-2 h-2 mt-2 rounded-full bg-info-500"></div>
                                    <div>
                                        <p class="text-sm font-medium text-secondary-900 dark:text-white">Envoye</p>
                                        <p class="text-xs text-secondary-500">@dateFormat($quote->sent_at, 'd/m/Y H:i')</p>
                                    </div>
                                </div>
                            @endif
                            @if($quote->accepted_at)
                                <div class="flex gap-3">
                                    <div class="w-2 h-2 mt-2 rounded-full bg-success-500"></div>
                                    <div>
                                        <p class="text-sm font-medium text-secondary-900 dark:text-white">Accepte</p>
                                        <p class="text-xs text-secondary-500">@dateFormat($quote->accepted_at, 'd/m/Y H:i')</p>
                                    </div>
                                </div>
                            @endif
                            @if($quote->rejected_at)
                                <div class="flex gap-3">
                                    <div class="w-2 h-2 mt-2 rounded-full bg-danger-500"></div>
                                    <div>
                                        <p class="text-sm font-medium text-secondary-900 dark:text-white">Refuse</p>
                                        <p class="text-xs text-secondary-500">@dateFormat($quote->rejected_at, 'd/m/Y H:i')</p>
                                    </div>
                                </div>
                            @endif
                            @if($quote->converted_at)
                                <div class="flex gap-3">
                                    <div class="w-2 h-2 mt-2 rounded-full bg-primary-500"></div>
                                    <div>
                                        <p class="text-sm font-medium text-secondary-900 dark:text-white">Converti en facture</p>
                                        <p class="text-xs text-secondary-500">@dateFormat($quote->converted_at, 'd/m/Y H:i')</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
