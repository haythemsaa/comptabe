<x-app-layout>
    <x-slot name="title">Import CODA</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('bank.index') }}" class="text-secondary-500 hover:text-secondary-700">Banque</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Import CODA</span>
    @endsection

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Importer un fichier CODA</h1>
            <p class="mt-2 text-secondary-600 dark:text-secondary-400">Importez vos extraits bancaires au format CODA belge</p>
        </div>

        <!-- Upload Form -->
        <form
            action="{{ route('bank.import.process') }}"
            method="POST"
            enctype="multipart/form-data"
            x-data="codaUpload()"
            class="card"
        >
            @csrf

            <div class="card-body space-y-6">
                <!-- Drop Zone -->
                <div
                    class="relative border-2 border-dashed rounded-xl p-8 text-center transition-colors"
                    :class="dragover ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-secondary-300 dark:border-secondary-700'"
                    @dragover.prevent="dragover = true"
                    @dragleave.prevent="dragover = false"
                    @drop.prevent="handleDrop($event)"
                >
                    <input
                        type="file"
                        name="file"
                        id="file"
                        accept=".txt,.coda"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        @change="handleFileSelect($event)"
                        required
                    >

                    <div x-show="!file">
                        <svg class="w-12 h-12 mx-auto text-secondary-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-secondary-600 dark:text-secondary-400">
                            <span class="font-medium text-primary-600">Cliquez pour sélectionner</span> ou glissez-déposez votre fichier
                        </p>
                        <p class="text-sm text-secondary-500 mt-2">Fichiers .txt ou .coda (max 5 MB)</p>
                    </div>

                    <div x-show="file" class="flex items-center justify-center gap-4">
                        <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-medium text-secondary-900 dark:text-white" x-text="file?.name"></p>
                            <p class="text-sm text-secondary-500" x-text="formatFileSize(file?.size)"></p>
                        </div>
                        <button type="button" @click="clearFile()" class="btn-ghost btn-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                @error('file')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                <!-- Bank Account Selection -->
                <div>
                    <label for="bank_account_id" class="form-label">Compte bancaire (optionnel)</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select">
                        <option value="">Détection automatique depuis le fichier</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} - {{ $account->formatted_iban }}</option>
                        @endforeach
                    </select>
                    <p class="form-helper">Si non spécifié, le compte sera détecté automatiquement depuis le fichier CODA.</p>
                </div>

                <!-- Info -->
                <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h4 class="font-medium text-primary-900 dark:text-primary-100">Format CODA</h4>
                            <p class="text-sm text-primary-700 dark:text-primary-300 mt-1">
                                Le format CODA est le standard belge pour les extraits de compte bancaires électroniques.
                                Vous pouvez obtenir ces fichiers depuis votre espace bancaire en ligne (Isabel, KBC Online, BNP Fortis, etc.).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center gap-2 text-secondary-600 dark:text-secondary-400">
                        <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Réconciliation automatique
                    </div>
                    <div class="flex items-center gap-2 text-secondary-600 dark:text-secondary-400">
                        <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Détection des doublons
                    </div>
                    <div class="flex items-center gap-2 text-secondary-600 dark:text-secondary-400">
                        <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Correspondance factures
                    </div>
                    <div class="flex items-center gap-2 text-secondary-600 dark:text-secondary-400">
                        <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Communication structurée
                    </div>
                </div>
            </div>

            <div class="card-footer flex justify-between">
                <a href="{{ route('bank.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary" :disabled="!file">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Importer
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function codaUpload() {
            return {
                file: null,
                dragover: false,

                handleFileSelect(event) {
                    const files = event.target.files;
                    if (files.length > 0) {
                        this.file = files[0];
                    }
                },

                handleDrop(event) {
                    this.dragover = false;
                    const files = event.dataTransfer.files;
                    if (files.length > 0) {
                        this.file = files[0];
                        // Update the input
                        const input = document.getElementById('file');
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(files[0]);
                        input.files = dataTransfer.files;
                    }
                },

                clearFile() {
                    this.file = null;
                    document.getElementById('file').value = '';
                },

                formatFileSize(bytes) {
                    if (!bytes) return '';
                    const units = ['B', 'KB', 'MB'];
                    let i = 0;
                    while (bytes >= 1024 && i < units.length - 1) {
                        bytes /= 1024;
                        i++;
                    }
                    return Math.round(bytes * 100) / 100 + ' ' + units[i];
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
