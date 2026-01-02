@extends('layouts.app')

@section('title', 'Scanner OCR')

@section('content')
<div x-data="scannerApp()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Scanner OCR Intelligent
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Déposez vos factures pour extraction automatique
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-badge color="blue" class="px-3 py-1">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    IA Active
                </span>
            </x-badge>
        </div>
    </div>

    <!-- Zone de Drop -->
    <div
        x-on:dragover.prevent="dragOver = true"
        x-on:dragleave.prevent="dragOver = false"
        x-on:drop.prevent="handleDrop($event)"
        :class="dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-secondary-300 dark:border-secondary-600'"
        class="relative border-2 border-dashed rounded-2xl p-12 transition-all duration-300"
    >
        <input
            type="file"
            x-ref="fileInput"
            @change="handleFiles($event.target.files)"
            multiple
            accept=".pdf,.jpg,.jpeg,.png"
            class="hidden"
        >

        <div class="text-center">
            <!-- Animation d'upload -->
            <div class="relative mx-auto w-24 h-24 mb-6">
                <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900/30 rounded-full animate-ping opacity-20"></div>
                <div class="relative bg-gradient-to-br from-primary-500 to-primary-600 rounded-full w-24 h-24 flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-semibold text-secondary-900 dark:text-white mb-2">
                Glissez-déposez vos documents ici
            </h3>
            <p class="text-secondary-600 dark:text-secondary-400 mb-4">
                ou <button @click="$refs.fileInput.click()" class="text-primary-600 hover:text-primary-700 font-medium">parcourez vos fichiers</button>
            </p>

            <div class="flex items-center justify-center gap-6 text-sm text-secondary-500">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    PDF
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    JPG, PNG
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    Max 10 MB
                </span>
            </div>
        </div>

        <!-- Type de document -->
        <div class="absolute bottom-4 left-4 right-4">
            <div class="flex items-center justify-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="documentType" value="invoice" class="text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-secondary-700 dark:text-secondary-300">Facture</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="documentType" value="receipt" class="text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-secondary-700 dark:text-secondary-300">Ticket de caisse</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="documentType" value="credit_note" class="text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-secondary-700 dark:text-secondary-300">Note de crédit</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="documentType" value="expense" class="text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-secondary-700 dark:text-secondary-300">Dépense</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Files en cours de traitement -->
    <template x-if="processingFiles.length > 0">
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Traitement en cours
                </h3>
            </x-slot:header>

            <div class="space-y-4">
                <template x-for="(file, index) in processingFiles" :key="index">
                    <div class="flex items-center gap-4 p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                        <div class="flex-shrink-0">
                            <div x-show="file.status === 'processing'" class="w-10 h-10 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                            <div x-show="file.status === 'success'" class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div x-show="file.status === 'error'" class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-secondary-900 dark:text-white truncate" x-text="file.name"></p>
                            <p class="text-sm text-secondary-500" x-text="file.message"></p>
                        </div>
                        <div x-show="file.status === 'success' && file.confidence" class="text-right">
                            <div class="text-sm font-medium text-secondary-900 dark:text-white">
                                Confiance: <span x-text="Math.round(file.confidence * 100) + '%'"></span>
                            </div>
                            <template x-if="file.auto_created">
                                <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Facture créée auto
                                </span>
                            </template>
                        </div>
                        <template x-if="file.scan_id">
                            <a :href="`/ai/scan/${file.scan_id}`" class="text-primary-600 hover:text-primary-700">
                                Voir &rarr;
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </x-card>
    </template>

    <!-- Scans récents -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- En attente de validation -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">
                        À valider ({{ $pendingScans->count() }})
                    </h3>
                </div>
            </x-slot:header>

            @if($pendingScans->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto w-12 h-12 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-secondary-500">Aucun document en attente</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pendingScans as $scan)
                        <a href="{{ route('ai.scan.show', $scan) }}"
                           class="flex items-center gap-4 p-3 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors">
                            <div class="flex-shrink-0 w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-secondary-900 dark:text-white truncate">
                                    {{ $scan->original_filename }}
                                </p>
                                <p class="text-sm text-secondary-500">
                                    {{ $scan->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Historique -->
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Historique récent
                </h3>
            </x-slot:header>

            @if($recentScans->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto w-12 h-12 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-secondary-500">Aucun scan récent</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentScans as $scan)
                        <a href="{{ route('ai.scan.show', $scan) }}"
                           class="flex items-center gap-4 p-3 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center
                                {{ $scan->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30' : ($scan->status === 'failed' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-orange-100 dark:bg-orange-900/30') }}">
                                @if($scan->status === 'completed')
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($scan->status === 'failed')
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-secondary-900 dark:text-white truncate">
                                    {{ $scan->original_filename }}
                                </p>
                                <div class="flex items-center gap-2 text-sm text-secondary-500">
                                    <span>{{ $scan->created_at->diffForHumans() }}</span>
                                    @if($scan->confidence_score)
                                        <span>&bull;</span>
                                        <span>{{ number_format($scan->confidence_score * 100, 0) }}% confiance</span>
                                    @endif
                                </div>
                            </div>
                            @if($scan->auto_created)
                                <x-badge color="green" class="flex-shrink-0">Auto</x-badge>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    <!-- Conseils -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6">
        <h3 class="font-semibold text-secondary-900 dark:text-white mb-4">
            Conseils pour de meilleurs résultats
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex gap-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-sm">1</span>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Document net</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Assurez-vous que le document est bien lisible et non froissé</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-sm">2</span>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Bonne résolution</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Minimum 300 DPI pour les scans, photos bien éclairées</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-sm">3</span>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Format standard</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Les factures au format standard belge sont mieux reconnues</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function scannerApp() {
    return {
        dragOver: false,
        documentType: 'invoice',
        processingFiles: [],

        handleDrop(event) {
            this.dragOver = false;
            const files = event.dataTransfer.files;
            this.handleFiles(files);
        },

        async handleFiles(files) {
            for (let file of files) {
                const fileData = {
                    name: file.name,
                    status: 'processing',
                    message: 'Analyse en cours...'
                };
                this.processingFiles.push(fileData);

                try {
                    const formData = new FormData();
                    formData.append('document', file);
                    formData.append('type', this.documentType);

                    const response = await fetch('{{ route('ai.scan') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        fileData.status = 'success';
                        fileData.message = result.message;
                        fileData.confidence = result.scan.confidence_score;
                        fileData.auto_created = result.scan.auto_created;
                        fileData.scan_id = result.scan.id;
                    } else {
                        fileData.status = 'error';
                        fileData.message = result.message || 'Erreur de traitement';
                    }
                } catch (error) {
                    fileData.status = 'error';
                    fileData.message = 'Erreur de connexion';
                }
            }
        }
    }
}
</script>
@endpush
@endsection
