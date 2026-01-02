<x-app-layout>
    <x-slot name="title">Nouveau type de produit</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('settings.product-types.index') }}" class="btn btn-secondary btn-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouveau type de produit</h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    Définissez un nouveau type pour organiser vos produits et services.
                </p>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('settings.product-types.store') }}" method="POST" class="card">
            @csrf

            <div class="card-body space-y-6">
                <!-- Basic Info -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Nom <span class="text-danger-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="form-input w-full"
                            placeholder="ex: Produit physique"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Identifiant (slug)
                        </label>
                        <input
                            type="text"
                            id="slug"
                            name="slug"
                            value="{{ old('slug') }}"
                            class="form-input w-full"
                            placeholder="Généré automatiquement"
                        >
                        <p class="mt-1 text-xs text-secondary-500">Laissez vide pour générer automatiquement</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="2"
                        class="form-input w-full"
                        placeholder="Description optionnelle du type..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Appearance -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="color" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Couleur
                        </label>
                        <div class="flex items-center gap-3">
                            <input
                                type="color"
                                id="color"
                                name="color"
                                value="{{ old('color', '#3B82F6') }}"
                                class="h-10 w-16 rounded border border-secondary-300 dark:border-secondary-600"
                            >
                            <input
                                type="text"
                                id="color_text"
                                value="{{ old('color', '#3B82F6') }}"
                                class="form-input flex-1"
                                placeholder="#3B82F6"
                                oninput="document.getElementById('color').value = this.value"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="icon" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Icône
                        </label>
                        <select id="icon" name="icon" class="form-select w-full">
                            <option value="">Aucune</option>
                            <option value="box" {{ old('icon') === 'box' ? 'selected' : '' }}>Boîte (produit)</option>
                            <option value="briefcase" {{ old('icon') === 'briefcase' ? 'selected' : '' }}>Mallette (service)</option>
                            <option value="download" {{ old('icon') === 'download' ? 'selected' : '' }}>Téléchargement (digital)</option>
                            <option value="refresh" {{ old('icon') === 'refresh' ? 'selected' : '' }}>Refresh (abonnement)</option>
                            <option value="clock" {{ old('icon') === 'clock' ? 'selected' : '' }}>Horloge (location)</option>
                        </select>
                    </div>
                </div>

                <!-- Options -->
                <div class="border-t border-secondary-200 dark:border-secondary-700 pt-6">
                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-4">Options</h3>

                    <div class="space-y-4">
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="is_service"
                                value="1"
                                {{ old('is_service') ? 'checked' : '' }}
                                class="form-checkbox"
                            >
                            <div>
                                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">C'est un service</span>
                                <p class="text-xs text-secondary-500">Cochez si ce type représente des services plutôt que des produits physiques</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="track_inventory"
                                value="1"
                                {{ old('track_inventory') ? 'checked' : '' }}
                                class="form-checkbox"
                            >
                            <div>
                                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Gestion de stock</span>
                                <p class="text-xs text-secondary-500">Activer le suivi des quantités en stock</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="has_variants"
                                value="1"
                                {{ old('has_variants') ? 'checked' : '' }}
                                class="form-checkbox"
                            >
                            <div>
                                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Supporte les variantes</span>
                                <p class="text-xs text-secondary-500">Permettre de créer des variantes (taille, couleur, etc.)</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="form-checkbox"
                            >
                            <div>
                                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Actif</span>
                                <p class="text-xs text-secondary-500">Les types inactifs ne seront pas proposés lors de la création de produits</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-footer flex justify-end gap-3">
                <a href="{{ route('settings.product-types.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    Créer le type
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('color').addEventListener('input', function() {
            document.getElementById('color_text').value = this.value;
        });
    </script>
</x-app-layout>
