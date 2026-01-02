<x-app-layout>
    <x-slot name="title">Note de crédit {{ $creditNote->credit_note_number }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('credit-notes.index') }}" class="text-secondary-500 hover:text-secondary-700">Notes de crédit</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">{{ $creditNote->credit_note_number }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $creditNote->credit_note_number }}</h1>
                    <span class="badge badge-{{ $creditNote->status_color }} badge-lg">{{ $creditNote->status_label }}</span>
                </div>
                <p class="mt-1 text-secondary-600 dark:text-secondary-400">
                    Créée le @dateFormat($creditNote->created_at)
                    @if($creditNote->creator)
                        par {{ $creditNote->creator->name }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if($creditNote->status === 'draft')
                    <a href="{{ route('credit-notes.edit', $creditNote) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    <form action="{{ route('credit-notes.validate', $creditNote) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Valider
                        </button>
                    </form>
                @endif

                <a href="{{ route('credit-notes.pdf', $creditNote) }}" class="btn btn-secondary" target="_blank">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </a>

                @if($creditNote->status === 'draft')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="btn btn-secondary btn-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition
                            class="dropdown-menu right-0 w-48"
                        >
                            <form action="{{ route('credit-notes.destroy', $creditNote) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger-600 hover:bg-danger-50 w-full text-left" onclick="return confirm('Supprimer cette note de crédit ?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Credit Note Preview Card -->
                <div class="card">
                    <div class="card-body p-8">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h2 class="text-3xl font-bold text-danger-600">NOTE DE CRÉDIT</h2>
                                <p class="text-lg text-secondary-600 dark:text-secondary-400">{{ $creditNote->credit_note_number }}</p>
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
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-secondary-500 uppercase tracking-wider mb-2">Client</h3>
                                <div class="text-secondary-900 dark:text-white">
                                    <div class="font-medium">{{ $creditNote->partner->name }}</div>
                                    @if($creditNote->partner->street)
                                        <div>{{ $creditNote->partner->street }} {{ $creditNote->partner->house_number }}</div>
                                    @endif
                                    @if($creditNote->partner->postal_code || $creditNote->partner->city)
                                        <div>{{ $creditNote->partner->postal_code }} {{ $creditNote->partner->city }}</div>
                                    @endif
                                    @if($creditNote->partner->vat_number)
                                        <div class="mt-2">TVA: {{ $creditNote->partner->vat_number }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="grid grid-cols-3 gap-4 p-4 bg-danger-50 dark:bg-danger-900/20 rounded-xl mb-8">
                            <div>
                                <div class="text-xs text-secondary-500 uppercase">Date</div>
                                <div class="font-medium text-secondary-900 dark:text-white">@dateFormat($creditNote->credit_note_date)</div>
                            </div>
                            <div>
                                <div class="text-xs text-secondary-500 uppercase">Référence</div>
                                <div class="font-medium text-secondary-900 dark:text-white">{{ $creditNote->reference ?? '-' }}</div>
                            </div>
                            @if($creditNote->invoice)
                                <div>
                                    <div class="text-xs text-secondary-500 uppercase">Facture liée</div>
                                    <a href="{{ route('invoices.show', $creditNote->invoice) }}" class="font-medium text-primary-600 hover:underline">
                                        {{ $creditNote->invoice->invoice_number }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if($creditNote->reason)
                            <div class="mb-8 p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl">
                                <div class="text-xs text-secondary-500 uppercase mb-1">Motif</div>
                                <p class="text-secondary-900 dark:text-white">{{ $creditNote->reason }}</p>
                            </div>
                        @endif

                        <!-- Lines -->
                        <table class="w-full mb-8">
                            <thead>
                                <tr class="border-b-2 border-danger-200 dark:border-danger-700">
                                    <th class="text-left py-3 text-sm font-medium text-secondary-500 uppercase">Description</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">Qté</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">P.U. HT</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">TVA</th>
                                    <th class="text-right py-3 text-sm font-medium text-secondary-500 uppercase">Total HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($creditNote->lines as $line)
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
                                        <td class="text-right py-4 font-medium text-danger-600">-@currency($line->line_total)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Totals -->
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                                    <span class="font-medium text-danger-600">-@currency($creditNote->total_excl_vat)</span>
                                </div>
                                @foreach($creditNote->vatSummary() as $rate => $amount)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-secondary-500">TVA {{ $rate }}%</span>
                                        <span class="text-danger-500">-@currency($amount)</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between border-t-2 border-danger-600 pt-2">
                                    <span class="text-lg font-bold text-secondary-900 dark:text-white">Total TTC</span>
                                    <span class="text-lg font-bold text-danger-600">-@currency($creditNote->total_incl_vat)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($creditNote->notes)
                            <div class="mt-8 pt-8 border-t border-secondary-200 dark:border-secondary-700">
                                <h3 class="text-sm font-medium text-secondary-500 uppercase mb-2">Notes</h3>
                                <p class="text-secondary-700 dark:text-secondary-300 whitespace-pre-line">{{ $creditNote->notes }}</p>
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
                                    <div class="w-8 h-8 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-secondary-900 dark:text-white">Note de crédit créée</p>
                                    <p class="text-xs text-secondary-500">{{ $creditNote->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if($creditNote->validated_at)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-secondary-900 dark:text-white">Validée</p>
                                        <p class="text-xs text-secondary-500">{{ $creditNote->validated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($creditNote->sent_at)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-secondary-900 dark:text-white">Envoyée</p>
                                        <p class="text-xs text-secondary-500">{{ $creditNote->sent_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($creditNote->applied_at)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-info-100 dark:bg-info-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-secondary-900 dark:text-white">Appliquée</p>
                                        <p class="text-xs text-secondary-500">{{ $creditNote->applied_at->diffForHumans() }}</p>
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
                <div class="card border-danger-200 dark:border-danger-800">
                    <div class="card-header bg-danger-50 dark:bg-danger-900/20">
                        <h2 class="font-semibold text-danger-800 dark:text-danger-200">Montant à créditer</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-danger-600">-@currency($creditNote->total_excl_vat)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">TVA</span>
                            <span class="font-medium text-danger-600">-@currency($creditNote->total_vat)</span>
                        </div>
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-secondary-900 dark:text-white">Total TTC</span>
                                <span class="text-2xl font-bold text-danger-600">-@currency($creditNote->total_incl_vat)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Card -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Client</h2>
                        <a href="{{ route('partners.show', $creditNote->partner) }}" class="text-sm text-primary-600 hover:text-primary-700">
                            Voir fiche
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                                <span class="text-lg font-bold text-primary-600">{{ strtoupper(substr($creditNote->partner->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="font-medium text-secondary-900 dark:text-white">{{ $creditNote->partner->name }}</div>
                                <div class="text-sm text-secondary-500">{{ $creditNote->partner->vat_number }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linked Invoice Card -->
                @if($creditNote->invoice)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Facture liée</h2>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('invoices.show', $creditNote->invoice) }}" class="flex items-center gap-3 p-3 bg-secondary-50 dark:bg-secondary-800 rounded-lg hover:bg-secondary-100 dark:hover:bg-secondary-700 transition-colors">
                                <div class="w-10 h-10 rounded-md bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center text-primary-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-secondary-900 dark:text-white">{{ $creditNote->invoice->invoice_number }}</div>
                                    <div class="text-sm text-secondary-500">@currency($creditNote->invoice->total_incl_vat)</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Communication -->
                @if($creditNote->structured_communication)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Communication</h2>
                        </div>
                        <div class="card-body">
                            <div class="text-sm text-secondary-500 mb-1">Communication structurée</div>
                            <div class="font-mono text-lg font-medium text-secondary-900 dark:text-white">
                                {{ $creditNote->structured_communication }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
