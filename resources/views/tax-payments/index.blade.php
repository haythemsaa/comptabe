@extends('layouts.app')

@section('title', 'Gestion des Impôts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-secondary-900 dark:text-white">Gestion des Impôts</h1>
            <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                Suivi des paiements fiscaux (ISOC, IPP, Précompte, etc.)
            </p>
        </div>
        <a href="{{ route('tax-payments.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nouveau paiement d'impôt
        </a>
    </div>

    <!-- Statistics Cards -->
    @php
        $stats = [
            'total' => $taxPayments->total(),
            'pending' => $taxPayments->where('status', 'pending_payment')->count(),
            'overdue' => $taxPayments->where('status', 'overdue')->count(),
            'paid' => $taxPayments->where('status', 'paid')->count(),
        ];
        $totalDue = $taxPayments->where('status', '!=', 'paid')->sum('amount_due');
        $totalPaid = $taxPayments->where('status', 'paid')->sum('amount_paid');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Total</p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">En attente</p>
                        <p class="text-2xl font-bold text-warning-600 mt-1">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-secondary-500 dark:text-secondary-500 mt-2">
                    {{ number_format($totalDue, 2, ',', ' ') }} € à payer
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">En retard</p>
                        <p class="text-2xl font-bold text-danger-600 mt-1">{{ $stats['overdue'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-danger-100 dark:bg-danger-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Payés</p>
                        <p class="text-2xl font-bold text-success-600 mt-1">{{ $stats['paid'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-secondary-500 dark:text-secondary-500 mt-2">
                    {{ number_format($totalPaid, 2, ',', ' ') }} € payés
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('tax-payments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="label">Type d'impôt</label>
                    <select name="tax_type" class="input">
                        <option value="">Tous les types</option>
                        @foreach(\App\Models\TaxPayment::TAX_TYPE_LABELS as $value => $label)
                            <option value="{{ $value }}" {{ request('tax_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Statut</label>
                    <select name="status" class="input">
                        <option value="">Tous les statuts</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="calculated" {{ request('status') == 'calculated' ? 'selected' : '' }}>Calculé</option>
                        <option value="declared" {{ request('status') == 'declared' ? 'selected' : '' }}>Déclaré</option>
                        <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>En attente de paiement</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payé</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>En retard</option>
                    </select>
                </div>

                <div>
                    <label class="label">Année</label>
                    <select name="year" class="input">
                        <option value="">Toutes les années</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-secondary flex-1">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('tax-payments.index') }}" class="btn btn-secondary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th>Type d'impôt</th>
                            <th>Base imposable</th>
                            <th>Montant</th>
                            <th>Montant dû</th>
                            <th>Échéance</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taxPayments as $payment)
                            <tr>
                                <td>
                                    <div class="font-medium text-secondary-900 dark:text-white">
                                        {{ $payment->period_label }}
                                    </div>
                                    <div class="text-xs text-secondary-500">
                                        Année {{ $payment->year }}
                                        @if($payment->quarter)
                                            - T{{ $payment->quarter }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $payment->tax_type_label }}
                                    </span>
                                </td>
                                <td>{{ number_format($payment->taxable_base, 2, ',', ' ') }} €</td>
                                <td>
                                    <div class="font-medium">{{ number_format($payment->tax_amount, 2, ',', ' ') }} €</div>
                                    @if($payment->tax_rate)
                                        <div class="text-xs text-secondary-500">Taux: {{ $payment->tax_rate }}%</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-semibold {{ $payment->amount_due < 0 ? 'text-success-600' : 'text-secondary-900 dark:text-white' }}">
                                        {{ number_format($payment->amount_due, 2, ',', ' ') }} €
                                    </div>
                                    @if($payment->advance_payments > 0)
                                        <div class="text-xs text-secondary-500">
                                            Acomptes: {{ number_format($payment->advance_payments, 2, ',', ' ') }} €
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->due_date)
                                        <div class="{{ $payment->isOverdue() ? 'text-danger-600 font-semibold' : '' }}">
                                            {{ $payment->due_date->format('d/m/Y') }}
                                        </div>
                                        @if($payment->isOverdue())
                                            <div class="text-xs text-danger-600">
                                                En retard de {{ $payment->due_date->diffInDays(now()) }} jours
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'calculated' => 'info',
                                            'declared' => 'primary',
                                            'pending_payment' => 'warning',
                                            'partially_paid' => 'warning',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'contested' => 'danger',
                                        ];
                                        $color = $statusColors[$payment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $color }}">
                                        {{ $payment->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('tax-payments.show', $payment) }}" class="btn btn-sm btn-secondary" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('tax-payments.edit', $payment) }}" class="btn btn-sm btn-primary" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-secondary-500">
                                    Aucun paiement d'impôt trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($taxPayments->hasPages())
        <div class="mt-6">
            {{ $taxPayments->links() }}
        </div>
    @endif
</div>
@endsection
