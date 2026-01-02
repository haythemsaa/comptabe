<x-app-layout>
    <x-slot name="title">Écritures comptables</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.index') }}" class="text-secondary-500 hover:text-secondary-700">Comptabilité</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Écritures</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Écritures comptables</h1>
                <p class="text-secondary-600 dark:text-secondary-400">{{ $entries->total() }} écriture(s)</p>
            </div>
            <a href="{{ route('accounting.entries.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle écriture
            </a>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('accounting.entries') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="form-label">Recherche</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="text"
                                    name="search"
                                    id="search"
                                    value="{{ request('search') }}"
                                    placeholder="N° pièce, description, référence..."
                                    class="form-input pl-10"
                                >
                            </div>
                        </div>
                        <div>
                            <label for="journal" class="form-label">Journal</label>
                            <select name="journal" id="journal" class="form-select">
                                <option value="">Tous les journaux</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal') == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->code }} - {{ $journal->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="form-label">Du</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-input">
                        </div>
                        <div>
                            <label for="date_to" class="form-label">Au</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-input">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filtrer
                        </button>
                        @if(request()->hasAny(['search', 'journal', 'date_from', 'date_to']))
                            <a href="{{ route('accounting.entries') }}" class="btn btn-secondary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Réinitialiser
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Entries Table -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>N° Pièce</th>
                            <th>Journal</th>
                            <th>Description</th>
                            <th>Référence</th>
                            <th class="text-right">Montant</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr class="group hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('accounting.entries.show', $entry) }}" class="font-mono text-primary-600 hover:text-primary-700 font-medium">
                                        {{ $entry->entry_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $entry->journal->color ?? 'secondary' }} text-xs">
                                        {{ $entry->journal->code }}
                                    </span>
                                </td>
                                <td>
                                    <div class="max-w-xs truncate" title="{{ $entry->description }}">
                                        {{ $entry->description }}
                                    </div>
                                </td>
                                <td class="text-secondary-500">
                                    {{ $entry->reference ?: '-' }}
                                </td>
                                <td class="text-right font-mono font-medium">
                                    @currency($entry->total_amount)
                                </td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'draft' => ['label' => 'Brouillon', 'class' => 'warning'],
                                            'posted' => ['label' => 'Validée', 'class' => 'success'],
                                            'cancelled' => ['label' => 'Annulée', 'class' => 'danger'],
                                        ];
                                        $status = $statusConfig[$entry->status] ?? ['label' => $entry->status, 'class' => 'secondary'];
                                    @endphp
                                    <span class="badge badge-{{ $status['class'] }} text-xs">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a
                                            href="{{ route('accounting.entries.show', $entry) }}"
                                            class="btn-ghost btn-icon btn-sm"
                                            title="Voir"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($entry->status === 'draft')
                                            <a
                                                href="{{ route('accounting.entries.edit', $entry) }}"
                                                class="btn-ghost btn-icon btn-sm"
                                                title="Modifier"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('accounting.entries.post', $entry) }}" method="POST" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="btn-ghost btn-icon btn-sm text-success-600"
                                                    title="Valider"
                                                    onclick="return confirm('Valider cette écriture ? Cette action est irréversible.')"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Expandable Lines Preview -->
                            <tr class="hidden" x-data="{ open: false }">
                                <td colspan="8" class="p-0 border-0">
                                    <div class="bg-secondary-50 dark:bg-secondary-800/30 px-6 py-3">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="text-secondary-500">
                                                    <th class="text-left py-1">Compte</th>
                                                    <th class="text-left py-1">Libellé</th>
                                                    <th class="text-right py-1">Débit</th>
                                                    <th class="text-right py-1">Crédit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($entry->lines as $line)
                                                    <tr>
                                                        <td class="py-1">
                                                            <span class="font-mono">{{ $line->account->code }}</span>
                                                            <span class="text-secondary-500 ml-2">{{ $line->account->name }}</span>
                                                        </td>
                                                        <td class="py-1 text-secondary-500">{{ $line->description }}</td>
                                                        <td class="py-1 text-right font-mono">{{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') . ' €' : '' }}</td>
                                                        <td class="py-1 text-right font-mono">{{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') . ' €' : '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-secondary-500 mb-4">Aucune écriture trouvée</p>
                                    <a href="{{ route('accounting.entries.create') }}" class="btn btn-primary">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Créer une écriture
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($entries->hasPages())
                <div class="card-footer">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>

        <!-- Export Options -->
        @if($entries->count() > 0)
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('accounting.entries.export', ['format' => 'csv'] + request()->all()) }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter CSV
                </a>
                <a href="{{ route('accounting.entries.export', ['format' => 'pdf'] + request()->all()) }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Exporter PDF
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
