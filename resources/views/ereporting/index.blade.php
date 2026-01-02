<x-app-layout>
    <x-slot name="title">E-Reporting</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">E-Reporting</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">E-Reporting (5 coins)</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Déclaration automatique au SPF Finances - Mandat B2B 2028</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ereporting.pending') }}" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Factures en attente ({{ $pendingInvoices }})
                </a>
                <a href="{{ route('ereporting.settings') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Paramètres
                </a>
            </div>
        </div>

        <!-- Information Banner -->
        <div class="card p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Modèle 5 coins - E-Reporting belge 2028</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        À partir de 2028, les factures B2B belges devront être déclarées en temps réel au SPF Finances (5ème coin).
                        Ce module prépare votre entreprise à cette obligation tout en restant compatible avec Peppol (4 coins).
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Total soumis</p>
                        <h3 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $statistics['total'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Accepted -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Acceptées</p>
                        <h3 class="text-2xl font-semibold text-green-600">{{ $statistics['accepted'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">En attente</p>
                        <h3 class="text-2xl font-semibold text-yellow-600">{{ $statistics['pending'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Rejected -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-danger">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Rejetées</p>
                        <h3 class="text-2xl font-semibold text-red-600">{{ $statistics['rejected'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Taux acceptation</p>
                        <h3 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $statistics['acceptance_rate'] }}%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white">Soumissions récentes</h3>
                <a href="{{ route('ereporting.compliance-report') }}?start_date={{ now()->startOfMonth()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}" class="btn btn-outline-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Rapport de conformité
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Facture</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Soumis le</th>
                            <th>Réf. Gouvernement</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                            <tr>
                                <td class="font-mono text-sm">{{ Str::limit($submission->submission_id, 8) }}...</td>
                                <td>
                                    @if($submission->invoice)
                                        <a href="{{ route('invoices.show', $submission->invoice) }}" class="text-primary-600 hover:underline">
                                            {{ $submission->invoice->invoice_number }}
                                        </a>
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $submission->type === 'sales' ? 'badge-primary' : 'badge-secondary' }}">
                                        {{ $submission->type === 'sales' ? 'Vente' : 'Achat' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $submission->status_color }}">
                                        {{ $submission->status_label }}
                                    </span>
                                </td>
                                <td>{{ $submission->submitted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="font-mono text-sm">{{ $submission->government_reference ?? '-' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('ereporting.show', $submission) }}" class="btn btn-icon btn-sm btn-ghost" title="Détails">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($submission->isPending())
                                            <form action="{{ route('ereporting.check-status', $submission) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-icon btn-sm btn-ghost" title="Vérifier statut">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if($submission->isFailed())
                                            <form action="{{ route('ereporting.retry', $submission) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-icon btn-sm btn-ghost text-warning-500" title="Réessayer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-secondary-500">
                                    Aucune soumission e-Reporting pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($submissions->hasPages())
                <div class="card-footer">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
