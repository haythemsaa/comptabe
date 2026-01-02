<x-app-layout>
    <x-slot name="title">{{ $partner->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('partners.index') }}" class="text-secondary-500 hover:text-secondary-700">Partenaires</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">{{ $partner->name }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-{{ $partner->is_customer ? 'primary' : 'warning' }}-100 dark:bg-{{ $partner->is_customer ? 'primary' : 'warning' }}-900/30 rounded-2xl flex items-center justify-center">
                    <span class="text-2xl font-bold text-{{ $partner->is_customer ? 'primary' : 'warning' }}-600">
                        {{ strtoupper(substr($partner->name, 0, 2)) }}
                    </span>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $partner->name }}</h1>
                        @if($partner->peppol_capable)
                            <span class="badge badge-success">Peppol</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-1">
                        @if($partner->vat_number)
                            <span class="font-mono text-secondary-500">{{ $partner->vat_number }}</span>
                        @endif
                        @if($partner->is_customer)
                            <span class="badge badge-primary text-xs">Client</span>
                        @endif
                        @if($partner->is_supplier)
                            <span class="badge badge-warning text-xs">Fournisseur</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if($partner->is_customer)
                    <a href="{{ route('invoices.create', ['partner' => $partner->id]) }}" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle facture
                    </a>
                @endif
                <a href="{{ route('partners.edit', $partner) }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total factures</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['total_invoices'] }}</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Chiffre d'affaires</div>
                <div class="text-2xl font-bold text-primary-600">@currency($stats['total_revenue'])</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Impayés</div>
                <div class="text-2xl font-bold {{ $stats['unpaid_amount'] > 0 ? 'text-danger-600' : 'text-success-600' }}">
                    @currency($stats['unpaid_amount'])
                </div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Délai moyen paiement</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">
                    {{ $stats['avg_payment_days'] ? round($stats['avg_payment_days']) . 'j' : '-' }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recent Invoices -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Dernières factures</h2>
                        <a href="{{ route('invoices.index', ['partner' => $partner->id]) }}" class="text-sm text-primary-600 hover:text-primary-700">
                            Voir tout
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partner->invoices as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>@dateFormat($invoice->invoice_date)</td>
                                        <td class="font-medium">@currency($invoice->total_incl_vat)</td>
                                        <td>
                                            <span class="badge badge-{{ $invoice->status_color }}">
                                                {{ $invoice->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8 text-secondary-500">
                                            Aucune facture
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                @if($partner->notes)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Notes</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary-700 dark:text-secondary-300 whitespace-pre-line">{{ $partner->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Contact Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Contact</h2>
                    </div>
                    <div class="card-body space-y-4">
                        @if($partner->street || $partner->city)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-secondary-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <div class="text-secondary-700 dark:text-secondary-300">
                                    @if($partner->street)
                                        <div>{{ $partner->street }} {{ $partner->house_number }}</div>
                                    @endif
                                    @if($partner->postal_code || $partner->city)
                                        <div>{{ $partner->postal_code }} {{ $partner->city }}</div>
                                    @endif
                                    @if($partner->country_code)
                                        <div>{{ $partner->country_code }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($partner->email)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:{{ $partner->email }}" class="text-primary-600 hover:text-primary-700">
                                    {{ $partner->email }}
                                </a>
                            </div>
                        @endif

                        @if($partner->phone)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <a href="tel:{{ $partner->phone }}" class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600">
                                    {{ $partner->phone }}
                                </a>
                            </div>
                        @endif

                        @if($partner->website)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <a href="{{ $partner->website }}" target="_blank" class="text-primary-600 hover:text-primary-700 truncate">
                                    {{ str_replace(['https://', 'http://'], '', $partner->website) }}
                                </a>
                            </div>
                        @endif

                        @if($partner->contact_person)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-secondary-700 dark:text-secondary-300">{{ $partner->contact_person }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Peppol Status -->
                @if($partner->peppol_capable || $partner->peppol_identifier)
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
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-primary-100">Statut</span>
                                    <span class="font-medium">
                                        @if($partner->peppol_capable)
                                            Compatible
                                        @else
                                            Non vérifié
                                        @endif
                                    </span>
                                </div>
                                @if($partner->peppol_identifier)
                                    <div class="flex justify-between">
                                        <span class="text-primary-100">Identifiant</span>
                                        <span class="font-mono">{{ $partner->peppol_identifier }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Bank Account -->
                @if($partner->iban)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Compte bancaire</h2>
                        </div>
                        <div class="card-body space-y-3">
                            <div>
                                <div class="text-sm text-secondary-500">IBAN</div>
                                <div class="font-mono text-secondary-900 dark:text-white">{{ $partner->iban }}</div>
                            </div>
                            @if($partner->bic)
                                <div>
                                    <div class="text-sm text-secondary-500">BIC</div>
                                    <div class="font-mono text-secondary-900 dark:text-white">{{ $partner->bic }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Payment Terms -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Conditions</h2>
                    </div>
                    <div class="card-body space-y-3">
                        <div class="flex justify-between">
                            <span class="text-secondary-500">Délai de paiement</span>
                            <span class="font-medium text-secondary-900 dark:text-white">
                                {{ $partner->payment_terms_days ?? 30 }} jours
                            </span>
                        </div>
                        @if($partner->default_vat_code)
                            <div class="flex justify-between">
                                <span class="text-secondary-500">Code TVA par défaut</span>
                                <span class="font-medium text-secondary-900 dark:text-white">
                                    {{ $partner->default_vat_code }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card border-danger-200 dark:border-danger-800">
                    <div class="card-header bg-danger-50 dark:bg-danger-900/20">
                        <h2 class="font-semibold text-danger-700 dark:text-danger-300">Zone de danger</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-4">
                            La suppression d'un partenaire est irréversible. Seuls les partenaires sans factures peuvent être supprimés.
                        </p>
                        <form action="{{ route('partners.destroy', $partner) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Supprimer le partenaire
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
