<x-app-layout>
    <x-slot name="title">Telecharger des Documents</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('documents.index') }}" class="text-secondary-500 hover:text-secondary-700">Archive de Documents</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Telecharger</span>
    @endsection

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Telecharger des Documents</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Ajoutez des factures, tickets, contrats et autres documents a votre archive</p>
        </div>

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Dropzone -->
            <div class="card">
                <div class="p-6">
                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Fichiers *</label>
                    <div
                        x-data="{
                            files: [],
                            dragover: false,
                            handleFiles(fileList) {
                                this.files = [...this.files, ...Array.from(fileList)];
                            },
                            removeFile(index) {
                                this.files.splice(index, 1);
                            },
                            formatSize(bytes) {
                                if (bytes === 0) return '0 B';
                                const k = 1024;
                                const sizes = ['B', 'KB', 'MB', 'GB'];
                                const i = Math.floor(Math.log(bytes) / Math.log(k));
                                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                            }
                        }"
                        class="space-y-4"
                    >
                        <!-- Drop Zone -->
                        <div
                            @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false"
                            @drop.prevent="dragover = false; handleFiles($event.dataTransfer.files)"
                            :class="dragover ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-secondary-300 dark:border-secondary-600'"
                            class="border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer hover:border-primary-400 dark:hover:border-primary-500"
                            @click="$refs.fileInput.click()"
                        >
                            <input
                                type="file"
                                name="files[]"
                                multiple
                                x-ref="fileInput"
                                @change="handleFiles($event.target.files)"
                                accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv,.txt"
                                class="hidden"
                            >
                            <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="mt-2 text-sm text-secondary-600 dark:text-secondary-400">
                                <span class="font-semibold text-primary-600 dark:text-primary-400">Cliquez pour choisir</span>
                                ou glissez-deposez vos fichiers ici
                            </p>
                            <p class="mt-1 text-xs text-secondary-500">PDF, Images, Documents Office - Max 20MB par fichier</p>
                        </div>

                        <!-- File List -->
                        <div x-show="files.length > 0" class="space-y-2">
                            <template x-for="(file, index) in files" :key="index">
                                <div class="flex items-center gap-3 p-3 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                                    <div class="w-10 h-10 rounded-lg bg-secondary-200 dark:bg-secondary-700 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-secondary-900 dark:text-white truncate" x-text="file.name"></p>
                                        <p class="text-xs text-secondary-500" x-text="formatSize(file.size)"></p>
                                    </div>
                                    <button type="button" @click="removeFile(index)" class="p-1.5 text-secondary-400 hover:text-danger-600 hover:bg-danger-100 dark:hover:bg-danger-900/30 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('files')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                    @error('files.*')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Metadata -->
            <div class="card">
                <div class="p-6 space-y-6">
                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white">Informations du document</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Folder -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Dossier</label>
                            <select name="folder_id" class="form-select">
                                <option value="">-- Aucun dossier --</option>
                                @foreach($folders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->full_path }}</option>
                                    @foreach($folder->children as $child)
                                        <option value="{{ $child->id }}">&nbsp;&nbsp;└ {{ $child->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Type de document</label>
                            <select name="type" class="form-select">
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ $key === 'other' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Document Date -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Date du document</label>
                            <input type="date" name="document_date" value="{{ old('document_date', now()->format('Y-m-d')) }}" class="form-input">
                        </div>

                        <!-- Fiscal Year -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Annee fiscale</label>
                            <select name="fiscal_year" class="form-select">
                                @for($year = now()->year; $year >= now()->year - 5; $year--)
                                    <option value="{{ $year }}" {{ $year === now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Partner -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Partenaire lie</label>
                            <select name="partner_id" class="form-select">
                                <option value="">-- Aucun --</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Etiquettes</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($tags as $tag)
                                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-secondary-200 dark:border-secondary-700 hover:border-{{ $tag->color }}-300 dark:hover:border-{{ $tag->color }}-700 cursor-pointer transition-colors">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-checkbox w-4 h-4 text-{{ $tag->color }}-600">
                                        <span class="text-sm text-secondary-700 dark:text-secondary-300">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                                @if($tags->isEmpty())
                                    <span class="text-sm text-secondary-500">Aucune etiquette disponible</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Description</label>
                        <textarea name="description" rows="3" class="form-textarea" placeholder="Description optionnelle du document...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Telecharger
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
