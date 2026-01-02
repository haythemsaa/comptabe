@php
    $title = 'Employés';
@endphp

<x-app-layout :title="$title">
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Employés</h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    {{ $employees->total() }} employé(s) au total
                </p>
            </div>
            <a href="{{ route('payroll.employees.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvel employé
            </a>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" class="flex gap-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher par nom, numéro, email..."
                            class="input w-full"
                        />
                    </div>
                    <div>
                        <select name="status" class="input">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                            <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>En congé</option>
                            <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminés</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </form>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Date d'embauche</th>
                            <th>Salaire brut</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td class="font-medium">{{ $employee->employee_number }}</td>
                                <td>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mr-3">
                                            <span class="font-semibold text-primary-600 dark:text-primary-400">
                                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-secondary-900 dark:text-white">
                                                {{ $employee->full_name }}
                                            </div>
                                            <div class="text-sm text-secondary-500">
                                                {{ $employee->age }} ans, {{ $employee->seniority_years }} ans d'ancienneté
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $employee->email ?? '-' }}</td>
                                <td>@dateFormat($employee->hire_date)</td>
                                <td>
                                    @if($employee->activeContract)
                                        @currency($employee->activeContract->gross_monthly_salary)
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->status === 'active')
                                        <span class="badge badge-success">Actif</span>
                                    @elseif($employee->status === 'on_leave')
                                        <span class="badge badge-warning">En congé</span>
                                    @elseif($employee->status === 'terminated')
                                        <span class="badge badge-danger">Terminé</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $employee->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('payroll.employees.show', $employee) }}" class="btn btn-sm btn-ghost">
                                        Voir détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-secondary-500">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-lg font-medium">Aucun employé trouvé</p>
                                    <p class="mt-2">Utilisez l'AI Chat pour ajouter votre premier employé</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($employees->hasPages())
            <div class="card">
                <div class="card-body">
                    {{ $employees->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
