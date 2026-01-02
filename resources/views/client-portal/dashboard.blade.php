@extends('client-portal.layouts.portal')

@section('title', 'Tableau de bord')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Bienvenue, {{ auth()->user()->name }}
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Voici un aperçu de votre situation comptable
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Invoices -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total Factures
                            </dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $stats['total_invoices'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unpaid Invoices -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Factures Impayées
                            </dt>
                            <dd class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                                {{ $stats['unpaid_invoices'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unpaid Amount -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Montant Dû
                            </dt>
                            <dd class="text-lg font-semibold text-red-600 dark:text-red-400">
                                {{ number_format($stats['unpaid_amount'], 2, ',', ' ') }} €
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Month Revenue -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                CA {{ now()->format('F Y') }}
                            </dt>
                            <dd class="text-lg font-semibold text-green-600 dark:text-green-400">
                                {{ number_format($stats['current_month_revenue'], 2, ',', ' ') }} €
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        @if($access && $access->hasPermission('view_invoices'))
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Factures Récentes
                    </h3>
                    <a href="{{ route('client-portal.invoices.index', $company) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        Voir tout →
                    </a>
                </div>

                <div class="flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentInvoices as $invoice)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $invoice->invoice_number }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $invoice->invoice_date->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($invoice->total_incl_vat, 2, ',', ' ') }} €
                                    </p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($invoice->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($invoice->status === 'sent') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-4 text-center text-gray-500 dark:text-gray-400">
                            Aucune facture récente
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Documents -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Documents Récents
                    </h3>
                    <a href="{{ route('client-portal.documents.index', $company) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                        Voir tout →
                    </a>
                </div>

                <div class="flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentDocuments as $document)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $document->original_filename }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $document->type_label }} • {{ $document->formatted_file_size }}
                                    </p>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $document->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-4 text-center text-gray-500 dark:text-gray-400">
                            Aucun document
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    @if($access && $access->hasPermission('view_payments'))
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Paiements Récents
            </h3>

            <div class="flow-root">
                <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentPayments as $payment)
                    <li class="py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        Paiement facture {{ $payment->invoice?->invoice_number ?? 'N/A' }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $payment->payment_date->format('d/m/Y') }} • {{ $payment->payment_method_label }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-sm font-medium text-green-600 dark:text-green-400">
                                {{ number_format($payment->amount, 2, ',', ' ') }} €
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="py-4 text-center text-gray-500 dark:text-gray-400">
                        Aucun paiement récent
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-6">
        <h3 class="text-lg font-medium text-primary-900 dark:text-primary-100 mb-4">
            Actions Rapides
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @if($access && $access->hasPermission('view_invoices'))
            <a href="{{ route('client-portal.invoices.index', $company) }}" class="flex items-center p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-md transition">
                <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Voir mes factures</span>
            </a>
            @endif

            @if($access && $access->hasPermission('upload_documents'))
            <a href="{{ route('client-portal.documents.create', $company) }}" class="flex items-center p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-md transition">
                <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Uploader un document</span>
            </a>
            @endif

            <a href="{{ route('client-portal.documents.index', $company) }}" class="flex items-center p-3 bg-white dark:bg-gray-800 rounded-lg hover:shadow-md transition">
                <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Mes documents</span>
            </a>
        </div>
    </div>
</div>
@endsection
