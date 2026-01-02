<x-app-layout>
    <x-slot name="title">Documents Archives</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('documents.index') }}" class="text-secondary-500 hover:text-secondary-700">Archive de Documents</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Documents Archives</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Documents Archives</h1>
                <p class="text-secondary-600 dark:text-secondary-400">{{ $documents->total() }} document(s) archive(s)</p>
            </div>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour a l'archive
            </a>
        </div>

        <!-- Info Box -->
        <div class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-xl p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-info-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-info-800 dark:text-info-200">Les documents archives sont conserves mais n'apparaissent plus dans la vue principale.</p>
                    <p class="text-sm text-info-600 dark:text-info-400 mt-1">Vous pouvez les restaurer a tout moment ou les supprimer definitivement.</p>
                </div>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="card overflow-hidden">
            @if($documents->isEmpty())
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 text-secondary-300 dark:text-secondary-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <p class="text-secondary-500 dark:text-secondary-400">Aucun document archive</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Document</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Dossier</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">Taille</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">Archive le</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-secondary-900 divide-y divide-secondary-200 dark:divide-secondary-700">
                        @foreach($documents as $document)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-secondary-100 dark:bg-secondary-800 flex items-center justify-center">
                                            @if($document->isPdf())
                                                <svg class="w-5 h-5 text-danger-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.05-7.44 7-7.93v15.86zm2-15.86c1.03.13 2 .45 2.87.93H13v-.93zM13 7h5.24c.25.31.48.65.68 1H13V7zm0 3h6.74c.08.33.15.66.19 1H13v-1zm0 9.93V19h2.87c-.87.48-1.84.8-2.87.93zM18.24 17H13v-1h5.92c-.2.35-.43.69-.68 1zm1.5-3H13v-1h6.93c-.04.34-.11.67-.19 1z"/>
                                                </svg>
                                            @elseif($document->isImage())
                                                <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-secondary-900 dark:text-white">{{ $document->name }}</p>
                                            <p class="text-sm text-secondary-500 font-mono">{{ strtoupper($document->extension) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-700 dark:text-secondary-300">
                                        {{ $document->type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-secondary-600 dark:text-secondary-400">
                                    {{ $document->folder?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-secondary-600 dark:text-secondary-400">
                                    {{ $document->formatted_size }}
                                </td>
                                <td class="px-6 py-4 text-center text-secondary-600 dark:text-secondary-400">
                                    {{ $document->archived_at?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('documents.download', $document) }}" class="p-2 text-secondary-400 hover:text-primary-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors" title="Telecharger">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('documents.unarchive', $document) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-secondary-400 hover:text-success-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors" title="Restaurer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer definitivement ce document?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-secondary-400 hover:text-danger-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors" title="Supprimer definitivement">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($documents->hasPages())
                    <div class="px-6 py-4 border-t border-secondary-200 dark:border-secondary-700">
                        {{ $documents->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
