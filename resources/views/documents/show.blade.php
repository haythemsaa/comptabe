<x-app-layout>
    <x-slot name="title">{{ $document->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('documents.index') }}" class="text-secondary-500 hover:text-secondary-700">Archive de Documents</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium truncate max-w-xs">{{ $document->name }}</span>
    @endsection

    <div class="flex gap-6 h-[calc(100vh-12rem)]">
        <!-- Preview Panel -->
        <div class="flex-1 bg-secondary-900 rounded-xl overflow-hidden relative">
            @if($document->hasPreview())
                @if($document->isPdf())
                    <iframe src="{{ route('documents.preview', $document) }}" class="w-full h-full"></iframe>
                @elseif($document->isImage())
                    <div class="w-full h-full flex items-center justify-center p-4">
                        <img src="{{ route('documents.preview', $document) }}" alt="{{ $document->name }}" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
                    </div>
                @endif
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-white">
                    <svg class="w-24 h-24 text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg font-medium">Apercu non disponible</p>
                    <p class="text-secondary-400 mt-1">{{ strtoupper($document->extension) }} - {{ $document->formatted_size }}</p>
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-primary mt-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Telecharger le fichier
                    </a>
                </div>
            @endif

            <!-- Floating Actions -->
            <div class="absolute top-4 right-4 flex gap-2">
                <a href="{{ route('documents.download', $document) }}" class="p-2 bg-white/90 dark:bg-secondary-800/90 rounded-lg shadow-lg hover:bg-white dark:hover:bg-secondary-800 transition-colors" title="Telecharger">
                    <svg class="w-5 h-5 text-secondary-700 dark:text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </a>
                @if($document->hasPreview())
                    <a href="{{ route('documents.preview', $document) }}" target="_blank" class="p-2 bg-white/90 dark:bg-secondary-800/90 rounded-lg shadow-lg hover:bg-white dark:hover:bg-secondary-800 transition-colors" title="Ouvrir dans un nouvel onglet">
                        <svg class="w-5 h-5 text-secondary-700 dark:text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        <!-- Details Panel -->
        <div class="w-96 flex-shrink-0 bg-white dark:bg-secondary-900 rounded-xl border border-secondary-200 dark:border-secondary-700 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="p-4 border-b border-secondary-200 dark:border-secondary-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg font-bold text-secondary-900 dark:text-white truncate">{{ $document->name }}</h1>
                        <p class="text-sm text-secondary-500 truncate">{{ $document->original_filename }}</p>
                    </div>
                    <button type="button" onclick="toggleStar('{{ $document->id }}')" class="p-2 rounded-lg hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors">
                        <svg class="w-5 h-5 {{ $document->is_starred ? 'text-warning-500 fill-current' : 'text-secondary-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4 space-y-6">
                <!-- File Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Type</span>
                        <div class="mt-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-secondary-100 dark:bg-secondary-800 text-sm font-medium text-secondary-700 dark:text-secondary-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $document->type_label }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Taille</span>
                        <p class="mt-1 text-sm font-medium text-secondary-900 dark:text-white">{{ $document->formatted_size }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Format</span>
                        <p class="mt-1 text-sm font-mono font-medium text-secondary-900 dark:text-white uppercase">{{ $document->extension }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Annee fiscale</span>
                        <p class="mt-1 text-sm font-medium text-secondary-900 dark:text-white">{{ $document->fiscal_year ?? '-' }}</p>
                    </div>
                </div>

                <!-- Folder -->
                @if($document->folder)
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Dossier</span>
                        <a href="{{ route('documents.index', ['folder' => $document->folder_id]) }}" class="mt-1 flex items-center gap-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            <svg class="w-4 h-4 text-{{ $document->folder->color ?? 'secondary' }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            {{ $document->folder->full_path }}
                        </a>
                    </div>
                @endif

                <!-- Tags -->
                @if($document->tags->isNotEmpty())
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Etiquettes</span>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($document->tags as $tag)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $tag->color }}-100 text-{{ $tag->color }}-800 dark:bg-{{ $tag->color }}-900/30 dark:text-{{ $tag->color }}-400">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Description -->
                @if($document->description)
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Description</span>
                        <p class="mt-1 text-sm text-secondary-700 dark:text-secondary-300">{{ $document->description }}</p>
                    </div>
                @endif

                <!-- Partner -->
                @if($document->partner)
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Partenaire</span>
                        <a href="{{ route('partners.show', $document->partner) }}" class="mt-1 flex items-center gap-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $document->partner->name }}
                        </a>
                    </div>
                @endif

                <!-- Invoice Link -->
                @if($document->invoice)
                    <div>
                        <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">Facture liee</span>
                        <a href="{{ route('invoices.show', $document->invoice) }}" class="mt-1 flex items-center gap-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ $document->invoice->invoice_number }}
                        </a>
                    </div>
                @endif

                <!-- Dates -->
                <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700 space-y-3">
                    @if($document->document_date)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-secondary-500">Date du document</span>
                            <span class="text-secondary-900 dark:text-white">{{ $document->document_date->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-secondary-500">Telecharge le</span>
                        <span class="text-secondary-900 dark:text-white">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($document->uploader)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-secondary-500">Par</span>
                            <span class="text-secondary-900 dark:text-white">{{ $document->uploader->full_name }}</span>
                        </div>
                    @endif
                </div>

                <!-- Shared with Accountant -->
                <div class="flex items-center justify-between p-3 rounded-lg bg-secondary-50 dark:bg-secondary-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $document->shared_with_accountant ? 'text-success-500' : 'text-secondary-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <span class="text-sm text-secondary-700 dark:text-secondary-300">Partage avec comptable</span>
                    </div>
                    <span class="text-sm font-medium {{ $document->shared_with_accountant ? 'text-success-600 dark:text-success-400' : 'text-secondary-500' }}">
                        {{ $document->shared_with_accountant ? 'Oui' : 'Non' }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-4 border-t border-secondary-200 dark:border-secondary-700 space-y-2">
                <div class="flex gap-2">
                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-secondary flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-primary flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Telecharger
                    </a>
                </div>
                <div class="flex gap-2">
                    @if($document->is_archived)
                        <form action="{{ route('documents.unarchive', $document) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Restaurer
                            </button>
                        </form>
                    @else
                        <form action="{{ route('documents.archive', $document) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Archiver
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('documents.destroy', $document) }}" method="POST" class="flex-1" onsubmit="return confirm('Supprimer definitivement ce document?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        async function toggleStar(documentId) {
            try {
                const response = await fetch(`/documents/${documentId}/star`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error toggling star:', error);
            }
        }
    </script>
    @endpush
</x-app-layout>
