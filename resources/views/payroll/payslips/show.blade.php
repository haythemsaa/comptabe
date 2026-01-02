@php
    $title = 'Fiche de paie ' . $payslip->payslip_number;
@endphp

<x-app-layout :title="$title">
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('payroll.payslips.index') }}" class="btn btn-ghost">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                        Fiche de paie {{ $payslip->period_name }}
                    </h1>
                    <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                        {{ $payslip->employee->full_name }} • {{ $payslip->payslip_number }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($payslip->status === 'draft')
                    <span class="badge badge-warning">Brouillon</span>
                @elseif($payslip->status === 'validated')
                    <span class="badge badge-info">Validée</span>
                @elseif($payslip->status === 'paid')
                    <span class="badge badge-success">Payée</span>
                @else
                    <span class="badge badge-danger">Annulée</span>
                @endif
                <button class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Télécharger PDF
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Employee & Period Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Informations générales</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Employé</label>
                                <p class="font-medium">{{ $payslip->employee->full_name }}</p>
                                <p class="text-sm text-secondary-500">{{ $payslip->employee->employee_number }}</p>
                            </div>
                            <div>
                                <label class="label">Période</label>
                                <p class="font-medium">{{ $payslip->period_name }}</p>
                                <p class="text-sm text-secondary-500">{{ $payslip->worked_days }} jours travaillés</p>
                            </div>
                            <div>
                                <label class="label">Heures travaillées</label>
                                <p class="font-medium">{{ number_format($payslip->worked_hours, 2) }}h</p>
                                @if($payslip->overtime_hours > 0)
                                    <p class="text-sm text-orange-600">+ {{ number_format($payslip->overtime_hours, 2) }}h supplémentaires</p>
                                @endif
                            </div>
                            <div>
                                <label class="label">Date de paiement</label>
                                <p class="font-medium">@dateFormat($payslip->payment_date)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gross Salary Breakdown -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Rémunération brute</h2>
                    </div>
                    <div class="card-body">
                        <table class="w-full">
                            <tbody class="divide-y divide-secondary-200 dark:divide-dark-100">
                                <tr>
                                    <td class="py-2">Salaire de base</td>
                                    <td class="py-2 text-right font-medium">@currency($payslip->base_salary)</td>
                                </tr>
                                @if($payslip->overtime_pay > 0)
                                    <tr>
                                        <td class="py-2">Heures supplémentaires</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->overtime_pay)</td>
                                    </tr>
                                @endif
                                @if($payslip->night_premium > 0)
                                    <tr>
                                        <td class="py-2">Prime de nuit</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->night_premium)</td>
                                    </tr>
                                @endif
                                @if($payslip->weekend_premium > 0)
                                    <tr>
                                        <td class="py-2">Prime week-end</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->weekend_premium)</td>
                                    </tr>
                                @endif
                                @if($payslip->bonuses > 0)
                                    <tr>
                                        <td class="py-2">Bonus</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->bonuses)</td>
                                    </tr>
                                @endif
                                @if($payslip->commissions > 0)
                                    <tr>
                                        <td class="py-2">Commissions</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->commissions)</td>
                                    </tr>
                                @endif
                                @if($payslip->holiday_pay > 0)
                                    <tr>
                                        <td class="py-2">Pécule de vacances</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->holiday_pay)</td>
                                    </tr>
                                @endif
                                @if($payslip->{'13th_month'} > 0)
                                    <tr>
                                        <td class="py-2">13e mois</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->{'13th_month'})</td>
                                    </tr>
                                @endif
                                @if($payslip->other_taxable > 0)
                                    <tr>
                                        <td class="py-2">Autres rémunérations</td>
                                        <td class="py-2 text-right font-medium">@currency($payslip->other_taxable)</td>
                                    </tr>
                                @endif
                                <tr class="font-bold text-lg border-t-2 border-secondary-300 dark:border-dark-200">
                                    <td class="py-3">Total brut</td>
                                    <td class="py-3 text-right text-primary-600 dark:text-primary-400">
                                        @currency($payslip->gross_total)
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Deductions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Retenues</h2>
                    </div>
                    <div class="card-body">
                        <table class="w-full">
                            <tbody class="divide-y divide-secondary-200 dark:divide-dark-100">
                                <tr>
                                    <td class="py-2">
                                        {{ $companySocialSecurityOrg }} ({{ number_format($payslip->employee_social_security_rate, 2) }}%)
                                    </td>
                                    <td class="py-2 text-right font-medium text-red-600">-@currency($payslip->employee_social_security)</td>
                                </tr>
                                @if($payslip->special_social_contribution > 0)
                                    <tr>
                                        <td class="py-2">Cotisation spéciale sécurité sociale</td>
                                        <td class="py-2 text-right font-medium text-red-600">-@currency($payslip->special_social_contribution)</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="py-2">
                                        @if($payslip->company->country_code === 'TN')
                                            IRPP
                                        @else
                                            Précompte professionnel ({{ number_format($payslip->professional_tax_rate ?? 0, 2) }}%)
                                        @endif
                                    </td>
                                    <td class="py-2 text-right font-medium text-red-600">-@currency($payslip->professional_tax)</td>
                                </tr>
                                @if($payslip->meal_voucher_deduction > 0)
                                    <tr>
                                        <td class="py-2">Chèques-repas (part employé)</td>
                                        <td class="py-2 text-right font-medium text-red-600">-@currency($payslip->meal_voucher_deduction)</td>
                                    </tr>
                                @endif
                                @if($payslip->other_deductions > 0)
                                    <tr>
                                        <td class="py-2">Autres retenues</td>
                                        <td class="py-2 text-right font-medium text-red-600">-@currency($payslip->other_deductions)</td>
                                    </tr>
                                @endif
                                <tr class="font-bold border-t-2 border-secondary-300 dark:border-dark-200">
                                    <td class="py-3">Total retenues</td>
                                    <td class="py-3 text-right text-red-600">
                                        -@currency($payslip->total_deductions)
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Net Salary -->
                <div class="card bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200 dark:border-green-800">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Salaire net à payer</p>
                                <p class="text-4xl font-bold text-green-600 dark:text-green-400 mt-1">
                                    @currency($payslip->net_salary)
                                </p>
                            </div>
                            <svg class="w-16 h-16 text-green-600 dark:text-green-400 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="mt-4 pt-4 border-t border-green-200 dark:border-green-800">
                            <p class="text-xs text-secondary-600 dark:text-secondary-400">
                                Taux effectif de taxation: {{ number_format(($payslip->total_deductions / $payslip->gross_total) * 100, 2) }}%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Benefits -->
                @if($payslip->company_car_benefit > 0 || $payslip->meal_vouchers_count > 0 || $payslip->eco_vouchers_value > 0)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Avantages extralégaux</h2>
                        </div>
                        <div class="card-body">
                            <table class="w-full">
                                <tbody class="divide-y divide-secondary-200 dark:divide-dark-100">
                                    @if($payslip->company_car_benefit > 0)
                                        <tr>
                                            <td class="py-2">Voiture de société (avantage en nature)</td>
                                            <td class="py-2 text-right font-medium">@currency($payslip->company_car_benefit)</td>
                                        </tr>
                                    @endif
                                    @if($payslip->meal_vouchers_count > 0)
                                        <tr>
                                            <td class="py-2">Chèques-repas</td>
                                            <td class="py-2 text-right font-medium">
                                                {{ $payslip->meal_vouchers_count }} × @currency($payslip->meal_vouchers_value)
                                                = @currency($payslip->meal_vouchers_count * $payslip->meal_vouchers_value)
                                            </td>
                                        </tr>
                                    @endif
                                    @if($payslip->eco_vouchers_value > 0)
                                        <tr>
                                            <td class="py-2">Éco-chèques</td>
                                            <td class="py-2 text-right font-medium">@currency($payslip->eco_vouchers_value)</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Employer Cost -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Coût employeur</h2>
                    </div>
                    <div class="card-body">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-secondary-200 dark:divide-dark-100">
                                <tr>
                                    <td class="py-2">Salaire brut</td>
                                    <td class="py-2 text-right">@currency($payslip->gross_total)</td>
                                </tr>
                                <tr>
                                    <td class="py-2">{{ $companySocialSecurityOrg }} patronale ({{ number_format($payslip->employer_social_security_rate, 2) }}%)</td>
                                    <td class="py-2 text-right">@currency($payslip->employer_social_security)</td>
                                </tr>
                                <tr class="font-bold border-t-2 border-secondary-300 dark:border-dark-200">
                                    <td class="py-3">Coût total</td>
                                    <td class="py-3 text-right text-orange-600 dark:text-orange-400">
                                        @currency($payslip->total_employer_cost)
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Status & Validation -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Statut</h2>
                    </div>
                    <div class="card-body space-y-3">
                        <div>
                            <label class="label">Statut actuel</label>
                            @if($payslip->status === 'draft')
                                <span class="badge badge-warning">Brouillon</span>
                            @elseif($payslip->status === 'validated')
                                <span class="badge badge-info">Validée</span>
                            @elseif($payslip->status === 'paid')
                                <span class="badge badge-success">Payée</span>
                            @else
                                <span class="badge badge-danger">Annulée</span>
                            @endif
                        </div>

                        @if($payslip->validated_at)
                            <div>
                                <label class="label">Validée le</label>
                                <p class="text-sm">@dateFormat($payslip->validated_at)</p>
                            </div>
                        @endif

                        @if($payslip->validator)
                            <div>
                                <label class="label">Validée par</label>
                                <p class="text-sm">{{ $payslip->validator->full_name }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Actions</h2>
                    </div>
                    <div class="card-body space-y-2">
                        @if($payslip->status === 'draft')
                            <form method="POST" action="{{ route('payroll.payslips.validate', $payslip) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-full justify-start">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Valider
                                </button>
                            </form>
                        @endif

                        @if($payslip->status === 'validated')
                            <form method="POST" action="{{ route('payroll.payslips.mark-paid', $payslip) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-full justify-start">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marquer comme payée
                                </button>
                            </form>
                        @endif

                        <button class="btn btn-ghost w-full justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Télécharger PDF
                        </button>

                        <button class="btn btn-ghost w-full justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Envoyer par email
                        </button>

                        <a href="{{ route('payroll.employees.show', $payslip->employee) }}" class="btn btn-ghost w-full justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Voir l'employé
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
