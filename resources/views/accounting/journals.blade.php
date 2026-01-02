<x-app-layout>
    <x-slot name="title">Journaux comptables</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.index') }}" class="text-secondary-500 hover:text-secondary-700">Comptabilité</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Journaux</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Journaux comptables</h1>
                <p class="text-secondary-600 dark:text-secondary-400">{{ $journals->count() }} journal(aux) configuré(s)</p>
            </div>
            <button
                type="button"
                onclick="document.getElementById('createJournalModal').showModal()"
                class="btn btn-primary"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau journal
            </button>
        </div>

        <!-- Journals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($journals as $journal)
                <div class="card card-hover overflow-hidden">
                    <div class="h-2 bg-{{ $journal->color ?? 'primary' }}-500"></div>
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-{{ $journal->color ?? 'primary' }}-100 dark:bg-{{ $journal->color ?? 'primary' }}-900/30 rounded-xl flex items-center justify-center">
                                    <span class="text-lg font-bold text-{{ $journal->color ?? 'primary' }}-600">
                                        {{ strtoupper(substr($journal->code, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-secondary-900 dark:text-white">{{ $journal->name }}</h3>
                                    <span class="font-mono text-sm text-secondary-500">{{ $journal->code }}</span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button type="button" class="btn-ghost btn-icon btn-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p class="text-sm text-secondary-500 mb-4 min-h-[2.5rem]">
                            {{ $journal->description ?? 'Aucune description' }}
                        </p>

                        <div class="grid grid-cols-2 gap-4 py-4 border-t border-b border-secondary-100 dark:border-secondary-800">
                            <div>
                                <div class="text-2xl font-bold text-secondary-900 dark:text-white">
                                    {{ number_format($journal->entries_count ?? 0) }}
                                </div>
                                <div class="text-xs text-secondary-500">Écritures</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-{{ $journal->color ?? 'primary' }}-600">
                                    @currency($journal->entries_sum_total_amount ?? 0)
                                </div>
                                <div class="text-xs text-secondary-500">Total</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-4">
                            <a
                                href="{{ route('accounting.entries', ['journal' => $journal->id]) }}"
                                class="btn btn-secondary btn-sm flex-1"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Voir écritures
                            </a>
                            <a
                                href="{{ route('accounting.entries.create', ['journal' => $journal->id]) }}"
                                class="btn btn-primary btn-sm flex-1"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nouvelle
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="card">
                        <div class="card-body text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-2">Aucun journal</h3>
                            <p class="text-secondary-500 mb-4">Créez votre premier journal comptable pour commencer</p>
                            <button
                                type="button"
                                onclick="document.getElementById('createJournalModal').showModal()"
                                class="btn btn-primary"
                            >
                                Créer un journal
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Default Journals Info -->
        <div class="card bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800">
            <div class="card-body">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-primary-900 dark:text-primary-100 mb-1">Journaux standards recommandés</h3>
                        <p class="text-sm text-primary-700 dark:text-primary-300 mb-3">
                            En comptabilité belge, les journaux suivants sont généralement utilisés :
                        </p>
                        <ul class="text-sm text-primary-700 dark:text-primary-300 grid grid-cols-2 md:grid-cols-4 gap-2">
                            <li class="flex items-center gap-2">
                                <span class="w-8 h-8 bg-primary-200 dark:bg-primary-800 rounded-lg flex items-center justify-center font-bold text-xs">AC</span>
                                Achats
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-8 h-8 bg-success-200 dark:bg-success-800 rounded-lg flex items-center justify-center font-bold text-xs">VE</span>
                                Ventes
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-8 h-8 bg-info-200 dark:bg-info-800 rounded-lg flex items-center justify-center font-bold text-xs">BQ</span>
                                Banque
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-8 h-8 bg-warning-200 dark:bg-warning-800 rounded-lg flex items-center justify-center font-bold text-xs">CA</span>
                                Caisse
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-8 h-8 bg-danger-200 dark:bg-danger-800 rounded-lg flex items-center justify-center font-bold text-xs">OD</span>
                                Opér. diverses
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-8 h-8 bg-secondary-200 dark:bg-secondary-700 rounded-lg flex items-center justify-center font-bold text-xs">AN</span>
                                À-nouveaux
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Journal Modal -->
    <dialog id="createJournalModal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-4">Nouveau journal</h3>

            <form action="{{ route('accounting.journals.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="form-label">Code *</label>
                        <input
                            type="text"
                            name="code"
                            id="code"
                            required
                            maxlength="10"
                            class="form-input uppercase"
                            placeholder="AC, VE, BQ..."
                        >
                    </div>
                    <div>
                        <label for="color" class="form-label">Couleur</label>
                        <select name="color" id="color" class="form-select">
                            <option value="primary">Bleu</option>
                            <option value="success">Vert</option>
                            <option value="warning">Orange</option>
                            <option value="danger">Rouge</option>
                            <option value="info">Cyan</option>
                            <option value="secondary">Gris</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="name" class="form-label">Nom *</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        required
                        class="form-input"
                        placeholder="Journal des achats"
                    >
                </div>

                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="2"
                        class="form-input"
                        placeholder="Description optionnelle..."
                    ></textarea>
                </div>

                <div>
                    <label for="default_account_id" class="form-label">Compte de contrepartie par défaut</label>
                    <select name="default_account_id" id="default_account_id" class="form-select">
                        <option value="">Aucun</option>
                        @php
                            $accounts = \App\Models\ChartOfAccount::where('is_group', false)->orderBy('code')->get();
                        @endphp
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Compte utilisé automatiquement comme contrepartie (ex: 550 pour Banque)</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="form-checkbox">
                    <label for="is_active" class="text-sm text-secondary-700 dark:text-secondary-300">Journal actif</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-secondary-200 dark:border-secondary-700">
                    <button type="button" onclick="document.getElementById('createJournalModal').close()" class="btn btn-secondary">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Créer le journal
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</x-app-layout>
