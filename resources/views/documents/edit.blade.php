<x-app-layout>
    <x-slot name="title">Modifier - {{ $document->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('documents.index') }}" class="text-secondary-500 hover:text-secondary-700">Archive de Documents</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('documents.show', $document) }}" class="text-secondary-500 hover:text-secondary-700 truncate max-w-xs">{{ $document->name }}</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Modifier</span>
    @endsection

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Modifier le Document</h1>
            <p class="text-secondary-600 dark:text-secondary-400">{{ $document->original_filename }}</p>
        </div>

        <form action="{{ route('documents.update', $document) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="card">
                <div class="p-6 space-y-6">
                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white">Informations du document</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Nom *</label>
                            <input type="text" name="name" value="{{ old('name', $document->name) }}" class="form-input" required>
                            @error('name')
                                <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Folder -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Dossier</label>
                            <select name="folder_id" class="form-select">
                                <option value="">-- Aucun dossier --</option>
                                @foreach($folders as $folder)
                                    <option value="{{ $folder->id }}" {{ old('folder_id', $document->folder_id) == $folder->id ? 'selected' : '' }}>
                                        {{ $folder->full_path }}
                                    </option>
                                    @foreach($folder->children as $child)
                                        <option value="{{ $child->id }}" {{ old('folder_id', $document->folder_id) == $child->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;└ {{ $child->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Type de document *</label>
                            <select name="type" class="form-select" required>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ old('type', $document->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Reference -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Reference</label>
                            <input type="text" name="reference" value="{{ old('reference', $document->reference) }}" class="form-input" placeholder="N° de facture, de contrat...">
                        </div>

                        <!-- Document Date -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Date du document</label>
                            <input type="date" name="document_date" value="{{ old('document_date', $document->document_date?->format('Y-m-d')) }}" class="form-input">
                        </div>

                        <!-- Fiscal Year -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Annee fiscale</label>
                            <select name="fiscal_year" class="form-select">
                                <option value="">-- Non specifiee --</option>
                                @for($year = now()->year; $year >= now()->year - 10; $year--)
                                    <option value="{{ $year }}" {{ old('fiscal_year', $document->fiscal_year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Partner -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Partenaire lie</label>
                            <select name="partner_id" class="form-select">
                                <option value="">-- Aucun --</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ old('partner_id', $document->partner_id) == $partner->id ? 'selected' : '' }}>
                                        {{ $partner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Description</label>
                        <textarea name="description" rows="3" class="form-textarea" placeholder="Description du document...">{{ old('description', $document->description) }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Notes internes</label>
                        <textarea name="notes" rows="3" class="form-textarea" placeholder="Notes internes (non partagees avec le comptable)...">{{ old('notes', $document->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-4">Etiquettes</h3>
                    <div class="flex flex-wrap gap-3">
                        @forelse($tags as $tag)
                            <label class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 {{ $document->tags->contains($tag->id) ? 'border-'.$tag->color.'-500 bg-'.$tag->color.'-50 dark:bg-'.$tag->color.'-900/20' : 'border-secondary-200 dark:border-secondary-700' }} hover:border-{{ $tag->color }}-300 dark:hover:border-{{ $tag->color }}-700 cursor-pointer transition-colors">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-checkbox w-4 h-4 text-{{ $tag->color }}-600" {{ $document->tags->contains($tag->id) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">{{ $tag->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-secondary-500">Aucune etiquette disponible. Creez-en dans les parametres.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sharing -->
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-4">Partage</h3>
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="shared_with_accountant" value="0">
                        <input type="checkbox" name="shared_with_accountant" value="1" class="form-checkbox w-5 h-5 text-primary-600" {{ old('shared_with_accountant', $document->shared_with_accountant) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-medium text-secondary-900 dark:text-white">Partager avec l'expert-comptable</span>
                            <p class="text-xs text-secondary-500">Le comptable pourra voir et telecharger ce document</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between">
                <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
