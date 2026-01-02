@php
    $title = 'Fiches de paie';
@endphp

<x-app-layout :title="$title">
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Fiches de paie</h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    {{ $payslips->total() }} fiche(s) de paie au total
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="label">Employé</label>
                        <select name="employee_id" class="input">
                            <option value="">Tous les employés</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') === $emp->id ? 'selected' : '' }}>
                                    {{ $emp->employee_number }} - {{ $emp->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Année</label>
                        <select name="year" class="input">
                            <option value="">Toutes les années</option>
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="label">Mois</label>
                        <select name="month" class="input">
                            <option value="">Tous les mois</option>
                            @foreach(['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $m => $label)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Statut</label>
                        <select name="status" class="input">
                            <option value="">Tous les statuts</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validée</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <a href="{{ route('payroll.payslips.index') }}" class="btn btn-ghost ml-2">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payslips Table -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Employé</th>
                            <th>Période</th>
                            <th>Brut</th>
                            <th>Net</th>
                            <th>Coût employeur</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $payslip)
                            <tr>
                                <td class="font-medium">{{ $payslip->payslip_number }}</td>
                                <td>
                                    <div class="font-medium text-secondary-900 dark:text-white">
                                        {{ $payslip->employee->full_name }}
                                    </div>
                                    <div class="text-sm text-secondary-500">
                                        {{ $payslip->employee->employee_number }}
                                    </div>
                                </td>
                                <td>{{ $payslip->period_name }}</td>
                                <td>@currency($payslip->gross_total)</td>
                                <td class="font-medium">@currency($payslip->net_salary)</td>
                                <td>@currency($payslip->total_employer_cost)</td>
                                <td>
                                    @if($payslip->status === 'draft')
                                        <span class="badge badge-warning">Brouillon</span>
                                    @elseif($payslip->status === 'validated')
                                        <span class="badge badge-info">Validée</span>
                                    @elseif($payslip->status === 'paid')
                                        <span class="badge badge-success">Payée</span>
                                    @else
                                        <span class="badge badge-danger">Annulée</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('payroll.payslips.show', $payslip) }}" class="btn btn-sm btn-ghost">
                                            Voir
                                        </a>
                                        @if($payslip->status === 'draft')
                                            <form method="POST" action="{{ route('payroll.payslips.validate', $payslip) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Valider</button>
                                            </form>
                                        @endif
                                        @if($payslip->status === 'validated')
                                            <form method="POST" action="{{ route('payroll.payslips.mark-paid', $payslip) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Marquer payée</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-secondary-500">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium">Aucune fiche de paie trouvée</p>
                                    <p class="mt-2">Utilisez l'AI Chat pour générer les fiches de paie</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($payslips->hasPages())
            <div class="card">
                <div class="card-body">
                    {{ $payslips->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
