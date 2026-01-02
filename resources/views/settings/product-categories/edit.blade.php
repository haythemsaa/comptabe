<x-app-layout>
    <x-slot name="title">Modifier {{ $productCategory->name }}</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('settings.product-categories.index') }}" class="btn btn-secondary btn-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $productCategory->name }}</h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    {{ $productCategory->products_count }} produits dans cette catégorie
                </p>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('settings.product-categories.update', $productCategory) }}" method="POST" class="card">
            @csrf
            @method('PUT')

            <div class="card-body space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Nom <span class="text-danger-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $productCategory->name) }}"
                            required
                            class="form-input w-full"
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
                            value="{{ old('slug', $productCategory->slug) }}"
                            class="form-input w-full"
                        >
                        @error('slug')
                            <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="parent_id" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                        Catégorie parente
                    </label>
                    <select id="parent_id" name="parent_id" class="form-select w-full">
                        <option value="">Aucune (catégorie racine)</option>
                        @foreach($parentCategories as $id => $name)
                            <option value="{{ $id }}" {{ old('parent_id', $productCategory->parent_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
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
                    >{{ old('description', $productCategory->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

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
                                value="{{ old('color', $productCategory->color ?? '#3B82F6') }}"
                                class="h-10 w-16 rounded border border-secondary-300 dark:border-secondary-600"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="icon" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Icône
                        </label>
                        <input
                            type="text"
                            id="icon"
                            name="icon"
                            value="{{ old('icon', $productCategory->icon) }}"
                            class="form-input w-full"
                        >
                    </div>
                </div>

                <label class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $productCategory->is_active) ? 'checked' : '' }}
                        class="form-checkbox"
                    >
                    <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Catégorie active</span>
                </label>
            </div>

            <div class="card-footer flex justify-between">
                <div>
                    @if($productCategory->products_count === 0 && !$productCategory->children()->exists())
                        <form action="{{ route('settings.product-categories.destroy', $productCategory) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="btn btn-danger"
                                onclick="return confirm('Supprimer cette catégorie ?')"
                            >
                                Supprimer
                            </button>
                        </form>
                    @endif
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('settings.product-categories.index') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
