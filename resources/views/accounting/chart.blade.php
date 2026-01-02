<x-app-layout>
    <x-slot name="title">Plan comptable</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.index') }}" class="text-secondary-500 hover:text-secondary-700">Comptabilité</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Plan comptable</span>
    @endsection

    <div class="space-y-6" x-data="{ search: '', expandedClasses: [] }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Plan comptable</h1>
                <p class="text-secondary-600 dark:text-secondary-400">PCMN - Plan Comptable Minimum Normalisé belge</p>
            </div>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Rechercher un compte..."
                    class="form-input pl-10 w-64"
                >
            </div>
        </div>

        <!-- Classes -->
        @php
            $classNames = [
                '1' => 'Fonds propres et dettes à long terme',
                '2' => 'Actifs immobilisés',
                '3' => 'Stocks',
                '4' => 'Créances et dettes à court terme',
                '5' => 'Placements et disponibilités',
                '6' => 'Charges',
                '7' => 'Produits',
            ];
            $classColors = [
                '1' => 'primary',
                '2' => 'success',
                '3' => 'warning',
                '4' => 'danger',
                '5' => 'info',
                '6' => 'danger',
                '7' => 'success',
            ];
        @endphp

        <div class="space-y-4">
            @foreach($accounts as $classNumber => $classAccounts)
                <div class="card overflow-hidden">
                    <button
                        type="button"
                        @click="expandedClasses.includes('{{ $classNumber }}') ? expandedClasses = expandedClasses.filter(c => c !== '{{ $classNumber }}') : expandedClasses.push('{{ $classNumber }}')"
                        class="w-full p-4 flex items-center justify-between bg-{{ $classColors[$classNumber] ?? 'secondary' }}-50 dark:bg-{{ $classColors[$classNumber] ?? 'secondary' }}-900/20 hover:bg-{{ $classColors[$classNumber] ?? 'secondary' }}-100 dark:hover:bg-{{ $classColors[$classNumber] ?? 'secondary' }}-900/30 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-{{ $classColors[$classNumber] ?? 'secondary' }}-500 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                {{ $classNumber }}
                            </div>
                            <div class="text-left">
                                <div class="font-semibold text-secondary-900 dark:text-white">Classe {{ $classNumber }}</div>
                                <div class="text-sm text-secondary-600 dark:text-secondary-400">{{ $classNames[$classNumber] ?? '' }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-secondary-500">{{ $classAccounts->count() }} comptes</span>
                            <svg
                                class="w-5 h-5 text-secondary-400 transition-transform"
                                :class="expandedClasses.includes('{{ $classNumber }}') ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div
                        x-show="expandedClasses.includes('{{ $classNumber }}') || search.length > 0"
                        x-collapse
                    >
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-24">Code</th>
                                    <th>Libellé</th>
                                    <th class="w-32">Type</th>
                                    <th class="w-24 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classAccounts->sortBy('code') as $account)
                                    <tr
                                        class="{{ $account->is_group ? 'bg-secondary-50 dark:bg-secondary-800/50 font-medium' : '' }}"
                                        x-show="search.length === 0 || '{{ strtolower($account->code . ' ' . $account->name) }}'.includes(search.toLowerCase())"
                                    >
                                        <td>
                                            <span class="font-mono {{ $account->is_group ? 'text-secondary-900 dark:text-white' : 'text-secondary-600 dark:text-secondary-400' }}">
                                                {{ $account->code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="{{ $account->parent ? 'pl-4' : '' }}">
                                                {{ $account->name }}
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $typeBadges = [
                                                    'asset' => ['Actif', 'success'],
                                                    'liability' => ['Passif', 'danger'],
                                                    'equity' => ['Capitaux', 'primary'],
                                                    'income' => ['Produit', 'success'],
                                                    'expense' => ['Charge', 'danger'],
                                                ];
                                                $badge = $typeBadges[$account->type] ?? ['Autre', 'secondary'];
                                            @endphp
                                            <span class="badge badge-{{ $badge[1] }} text-xs">{{ $badge[0] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if(!$account->is_group)
                                                <a
                                                    href="{{ route('accounting.ledger', ['account' => $account->id]) }}"
                                                    class="btn-ghost btn-icon btn-sm"
                                                    title="Voir le grand livre"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
