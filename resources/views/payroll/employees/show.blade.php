@php
    $title = $employee->full_name;
@endphp

<x-app-layout :title="$title">
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('payroll.employees.index') }}" class="btn btn-ghost">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $employee->full_name }}</h1>
                    <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                        {{ $employee->employee_number }} • {{ $employee->age }} ans • {{ $employee->seniority_years }} ans d'ancienneté
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($employee->status === 'active')
                    <span class="badge badge-success">Actif</span>
                @elseif($employee->status === 'on_leave')
                    <span class="badge badge-warning">En congé</span>
                @else
                    <span class="badge badge-danger">Terminé</span>
                @endif

                <a href="{{ route('payroll.employees.edit', $employee) }}" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>

                <form method="POST" action="{{ route('payroll.employees.destroy', $employee) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Informations personnelles</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Nom complet</label>
                                <p class="font-medium">{{ $employee->full_name }}</p>
                            </div>
                            <div>
                                <label class="label">Date de naissance</label>
                                <p class="font-medium">@dateFormat($employee->birth_date) ({{ $employee->age }} ans)</p>
                            </div>
                            <div>
                                @if($employee->company->country_code === 'TN')
                                    <label class="label">CIN</label>
                                    <p class="font-mono text-sm">{{ $employee->cin ?? '-' }}</p>
                                @else
                                    <label class="label">Numéro national</label>
                                    <p class="font-mono text-sm">{{ $employee->national_number ?? '-' }}</p>
                                @endif
                            </div>
                            <div>
                                <label class="label">Genre</label>
                                <p>{{ $employee->gender === 'M' ? 'Masculin' : 'Féminin' }}</p>
                            </div>
                            <div>
                                <label class="label">Nationalité</label>
                                <p>{{ $employee->nationality }}</p>
                            </div>
                            <div>
                                <label class="label">Lieu de naissance</label>
                                <p>{{ $employee->birth_place }}, {{ $employee->birth_country }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Coordonnées</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Email</label>
                                <p>{{ $employee->email ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="label">Téléphone</label>
                                <p>{{ $employee->phone ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="label">Mobile</label>
                                <p>{{ $employee->mobile ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="label">Adresse</label>
                                <p>
                                    {{ $employee->street }} {{ $employee->house_number }}{{ $employee->box ? '/' . $employee->box : '' }}<br>
                                    {{ $employee->postal_code }} {{ $employee->city }}<br>
                                    {{ $employee->country_code }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Informations bancaires et sociales</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($employee->company->country_code === 'TN')
                                <div>
                                    <label class="label">Numéro CNSS</label>
                                    <p class="font-mono text-sm">{{ $employee->cnss_number ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="label">RIB</label>
                                    <p class="font-mono text-sm">{{ $employee->rib ?? '-' }}</p>
                                </div>
                            @else
                                <div>
                                    <label class="label">IBAN</label>
                                    <p class="font-mono text-sm">{{ $employee->iban ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="label">BIC</label>
                                    <p class="font-mono text-sm">{{ $employee->bic ?? '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Employment Contract -->
                @if($employee->activeContract)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Contrat actuel</h2>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="label">Numéro de contrat</label>
                                    <p class="font-mono text-sm">{{ $employee->activeContract->contract_number }}</p>
                                </div>
                                <div>
                                    <label class="label">Type de contrat</label>
                                    <p>
                                        @if($employee->activeContract->contract_type === 'cdi')
                                            CDI (Contrat à durée indéterminée)
                                        @elseif($employee->activeContract->contract_type === 'cdd')
                                            CDD (Contrat à durée déterminée)
                                        @elseif($employee->activeContract->contract_type === 'student')
                                            Étudiant
                                        @else
                                            {{ ucfirst($employee->activeContract->contract_type) }}
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="label">Fonction</label>
                                    <p class="font-medium">{{ $employee->activeContract->job_title }}</p>
                                </div>
                                <div>
                                    <label class="label">Catégorie</label>
                                    <p>{{ $employee->activeContract->job_category ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="label">Régime de travail</label>
                                    <p>{{ $employee->activeContract->work_regime === 'full_time' ? 'Temps plein' : 'Temps partiel' }} ({{ $employee->activeContract->weekly_hours }}h/semaine)</p>
                                </div>
                                <div>
                                    <label class="label">Salaire brut mensuel</label>
                                    <p class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                        @currency($employee->activeContract->gross_monthly_salary)
                                    </p>
                                </div>
                                <div>
                                    <label class="label">Date de début</label>
                                    <p>@dateFormat($employee->activeContract->start_date)</p>
                                </div>
                                @if($employee->activeContract->end_date)
                                    <div>
                                        <label class="label">Date de fin</label>
                                        <p>@dateFormat($employee->activeContract->end_date)</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Benefits -->
                            @if($employee->activeContract->company_car || $employee->activeContract->meal_vouchers || $employee->activeContract->group_insurance)
                                <div class="mt-4 pt-4 border-t border-secondary-200 dark:border-dark-100">
                                    <label class="label mb-2">Avantages extralégaux</label>
                                    <div class="flex flex-wrap gap-2">
                                        @if($employee->activeContract->company_car)
                                            <span class="badge badge-info">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                </svg>
                                                Voiture de société
                                            </span>
                                        @endif
                                        @if($employee->activeContract->meal_vouchers)
                                            <span class="badge badge-info">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Chèques-repas ({{ number_format($employee->activeContract->meal_voucher_value ?? 0, 2) }}€)
                                            </span>
                                        @endif
                                        @if($employee->activeContract->group_insurance)
                                            <span class="badge badge-info">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                                Assurance groupe
                                            </span>
                                        @endif
                                        @if($employee->activeContract->mobile_phone)
                                            <span class="badge badge-info">📱 GSM</span>
                                        @endif
                                        @if($employee->activeContract->internet_allowance)
                                            <span class="badge badge-info">🌐 Internet</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Payslip History -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Historique des fiches de paie</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Période</th>
                                    <th>Brut</th>
                                    <th>Net</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->payslips as $payslip)
                                    <tr>
                                        <td>{{ $payslip->period_name }}</td>
                                        <td>@currency($payslip->gross_total)</td>
                                        <td class="font-medium">@currency($payslip->net_salary)</td>
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
                                            <a href="{{ route('payroll.payslips.show', $payslip) }}" class="btn btn-sm btn-ghost">
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-secondary-500">
                                            Aucune fiche de paie
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Statistiques</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label">Date d'embauche</label>
                            <p class="font-medium">@dateFormat($employee->hire_date)</p>
                        </div>
                        <div>
                            <label class="label">Ancienneté</label>
                            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                {{ $employee->seniority_years }} ans
                            </p>
                        </div>
                        @if($employee->activeContract)
                            <div>
                                <label class="label">Congés annuels</label>
                                <p class="font-medium">{{ $employee->activeContract->annual_leave_days ?? 20 }} jours</p>
                            </div>
                        @endif
                        <div>
                            <label class="label">Fiches de paie</label>
                            <p class="font-medium">{{ $employee->payslips->count() }} fiche(s)</p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                @if($employee->emergency_contact_name)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Contact d'urgence</h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-2">
                                <div>
                                    <label class="label">Nom</label>
                                    <p class="font-medium">{{ $employee->emergency_contact_name }}</p>
                                </div>
                                <div>
                                    <label class="label">Téléphone</label>
                                    <p>{{ $employee->emergency_contact_phone }}</p>
                                </div>
                                <div>
                                    <label class="label">Relation</label>
                                    <p>{{ $employee->emergency_contact_relationship }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Actions</h2>
                    </div>
                    <div class="card-body space-y-2">
                        <button class="btn btn-secondary w-full justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Générer fiche de paie
                        </button>
                        <button class="btn btn-ghost w-full justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </button>
                        <button class="btn btn-ghost w-full justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Voir contrat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
