@extends('client-portal.layouts.portal')

@section('title', 'Uploader un Document')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('client-portal.documents.index', $company) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-2 inline-block">
            ← Retour aux documents
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Uploader un Document
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Téléchargez vos justificatifs comptables (factures, reçus, relevés...)
        </p>
    </div>

    <!-- Upload Form -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg" x-data="documentUpload()">
        <form action="{{ route('client-portal.documents.store', $company) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="px-6 py-5 space-y-6">
                <!-- File Upload Area -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fichier <span class="text-red-500">*</span>
                    </label>

                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:border-primary-400 dark:hover:border-primary-500 transition"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': isDragging }">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Choisir un fichier</span>
                                    <input id="file-upload" name="file" type="file" class="sr-only" required
                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                           @change="handleFileSelect($event)">
                                </label>
                                <p class="pl-1">ou glisser-déposer</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                PDF, JPG, PNG, DOC, XLS jusqu'à 10MB
                            </p>
                        </div>
                    </div>

                    <!-- Selected File Preview -->
                    <div x-show="selectedFile" class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedFile?.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedFile ? formatFileSize(selectedFile.size) : ''"></p>
                                </div>
                            </div>
                            <button type="button" @click="clearFile()" class="text-red-600 hover:text-red-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @error('file')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Type de document <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Sélectionner un type</option>
                        @foreach(\App\Models\ClientDocument::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category (optional) -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Catégorie (optionnel)
                    </label>
                    <input type="text" name="category" id="category" value="{{ old('category') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                           placeholder="Ex: Frais de déplacement, Bureau, etc.">
                    @error('category')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Date -->
                <div>
                    <label for="document_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date du document
                    </label>
                    <input type="date" name="document_date" id="document_date" value="{{ old('document_date', date('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('document_date')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description (optionnel)
                    </label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="Ajoutez des détails sur ce document...">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end space-x-3 rounded-b-lg">
                <a href="{{ route('client-portal.documents.index', $company) }}"
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Uploader le document
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Conseils pour un traitement rapide
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Uploadez des documents lisibles et complets</li>
                        <li>Privilégiez le format PDF pour les factures</li>
                        <li>Ajoutez une description précise pour faciliter le traitement</li>
                        <li>Indiquez la bonne date du document pour une comptabilité précise</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function documentUpload() {
        return {
            isDragging: false,
            selectedFile: null,

            handleFileSelect(event) {
                this.selectedFile = event.target.files[0];
            },

            handleDrop(event) {
                this.isDragging = false;
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    this.selectedFile = files[0];
                    document.getElementById('file-upload').files = files;
                }
            },

            clearFile() {
                this.selectedFile = null;
                document.getElementById('file-upload').value = '';
            },

            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
        }
    }
</script>
@endpush
@endsection
