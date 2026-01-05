<x-app-layout>
    <x-slot name="title">Opportunités</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('crm.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">CRM</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Opportunités</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Opportunités</h1>
                <p class="text-secondary-600 dark:text-secondary-400">{{ $opportunities->total() }} opportunité(s)</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('crm.pipeline') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    Pipeline
                </a>
                <a href="{{ route('crm.opportunities.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('crm.opportunities.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="form-input">
                    </div>
                    <select name="stage" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">Toutes les étapes</option>
                        @foreach(\App\Models\Opportunity::STAGES as $stage => $config)
                        <option value="{{ $stage }}" {{ request('stage') === $stage ? 'selected' : '' }}>{{ $config['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="assigned_to" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">Tous les responsables</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary">Filtrer</button>
                    @if(request()->hasAny(['search', 'stage', 'assigned_to', 'source']))
                    <a href="{{ route('crm.opportunities.index') }}" class="text-sm text-secondary-500 hover:text-secondary-700">Réinitialiser</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Opportunité</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Étape</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Montant</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Prob.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Clôture</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Responsable</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @forelse($opportunities as $opportunity)
                        <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('crm.opportunities.show', $opportunity) }}" class="font-medium text-secondary-900 dark:text-white hover:text-primary-600">
                                    {{ $opportunity->title }}
                                </a>
                                @if($opportunity->isOverdue())
                                <span class="ml-2 px-1.5 py-0.5 text-xs bg-danger-100 text-danger-700 rounded">En retard</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-secondary-600 dark:text-secondary-400">
                                {{ $opportunity->partner?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $opportunity->getStageColor() }}-100 dark:bg-{{ $opportunity->getStageColor() }}-900/30 text-{{ $opportunity->getStageColor() }}-700 dark:text-{{ $opportunity->getStageColor() }}-300">
                                    {{ $opportunity->getStageLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($opportunity->amount, 0, ',', ' ') }} EUR
                            </td>
                            <td class="px-4 py-3 text-center text-secondary-600 dark:text-secondary-400">
                                {{ $opportunity->probability }}%
                            </td>
                            <td class="px-4 py-3 text-secondary-600 dark:text-secondary-400">
                                {{ $opportunity->expected_close_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-secondary-600 dark:text-secondary-400">
                                {{ $opportunity->assignedTo?->full_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('crm.opportunities.edit', $opportunity) }}" class="btn btn-sm btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                Aucune opportunité trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($opportunities->hasPages())
            <div class="px-4 py-3 border-t border-secondary-100 dark:border-secondary-700">
                {{ $opportunities->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
