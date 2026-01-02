<x-app-layout>
    <x-slot name="title">Modifier {{ $productType->name }}</x-slot>

    <div class="space-y-6" x-data="productTypeEditor()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('settings.product-types.index') }}" class="btn btn-secondary btn-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $productType->name }}</h1>
                    <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                        {{ $productType->products_count }} produits utilisent ce type
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($productType->products_count === 0)
                    <form action="{{ route('settings.product-types.destroy', $productType) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="btn btn-danger"
                            onclick="return confirm('Voulez-vous vraiment supprimer ce type ?')"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Type Settings -->
            <form action="{{ route('settings.product-types.update', $productType) }}" method="POST" class="card">
                @csrf
                @method('PUT')

                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Paramètres du type</h2>
                </div>

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
                                value="{{ old('name', $productType->name) }}"
                                required
                                class="form-input w-full"
                            >
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Identifiant
                            </label>
                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                value="{{ old('slug', $productType->slug) }}"
                                class="form-input w-full"
                            >
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
                        >{{ old('description', $productType->description) }}</textarea>
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
                                    value="{{ old('color', $productType->color ?? '#3B82F6') }}"
                                    class="h-10 w-16 rounded border border-secondary-300 dark:border-secondary-600"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="icon" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Icône
                            </label>
                            <select id="icon" name="icon" class="form-select w-full">
                                <option value="">Aucune</option>
                                <option value="box" {{ old('icon', $productType->icon) === 'box' ? 'selected' : '' }}>Boîte</option>
                                <option value="briefcase" {{ old('icon', $productType->icon) === 'briefcase' ? 'selected' : '' }}>Mallette</option>
                                <option value="download" {{ old('icon', $productType->icon) === 'download' ? 'selected' : '' }}>Téléchargement</option>
                                <option value="refresh" {{ old('icon', $productType->icon) === 'refresh' ? 'selected' : '' }}>Refresh</option>
                                <option value="clock" {{ old('icon', $productType->icon) === 'clock' ? 'selected' : '' }}>Horloge</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_service" value="1" {{ old('is_service', $productType->is_service) ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-sm text-secondary-700 dark:text-secondary-300">C'est un service</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="track_inventory" value="1" {{ old('track_inventory', $productType->track_inventory) ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-sm text-secondary-700 dark:text-secondary-300">Gestion de stock</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="has_variants" value="1" {{ old('has_variants', $productType->has_variants) ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-sm text-secondary-700 dark:text-secondary-300">Supporte les variantes</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $productType->is_active) ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-sm text-secondary-700 dark:text-secondary-300">Type actif</span>
                        </label>
                    </div>
                </div>

                <div class="card-footer flex justify-end">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

            <!-- Custom Fields -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Champs personnalisés</h2>
                    <button
                        type="button"
                        @click="showAddFieldModal = true"
                        class="btn btn-sm btn-primary"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter un champ
                    </button>
                </div>

                <div class="card-body">
                    @if($productType->customFields->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-12 h-12 mx-auto bg-secondary-100 dark:bg-secondary-800 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                            </div>
                            <p class="text-secondary-500 dark:text-secondary-400 text-sm">
                                Aucun champ personnalisé défini.
                            </p>
                            <p class="text-secondary-400 dark:text-secondary-500 text-xs mt-1">
                                Ajoutez des champs pour collecter des informations spécifiques.
                            </p>
                        </div>
                    @else
                        <div class="space-y-2" x-data="{ draggedField: null }">
                            @foreach($productType->customFields as $field)
                                <div
                                    class="flex items-center gap-3 p-3 bg-secondary-50 dark:bg-dark-500 rounded-lg group"
                                    draggable="true"
                                >
                                    <div class="cursor-grab text-secondary-400 hover:text-secondary-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-secondary-900 dark:text-white">{{ $field->label }}</span>
                                            <span class="text-xs px-1.5 py-0.5 bg-secondary-200 dark:bg-secondary-700 text-secondary-600 dark:text-secondary-400 rounded">
                                                {{ $fieldTypes[$field->type]['label'] ?? $field->type }}
                                            </span>
                                            @if($field->is_required)
                                                <span class="text-danger-500 text-xs">*</span>
                                            @endif
                                            @if(!$field->is_active)
                                                <span class="text-xs text-secondary-400">(inactif)</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-secondary-500 dark:text-secondary-400">
                                            {{ $field->slug }}
                                            @if($field->show_in_list)
                                                &bull; Liste
                                            @endif
                                            @if($field->show_in_invoice)
                                                &bull; Facture
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            type="button"
                                            @click="editField({{ $field->toJson() }})"
                                            class="p-1.5 text-secondary-400 hover:text-secondary-600 hover:bg-secondary-200 dark:hover:bg-secondary-700 rounded"
                                            title="Modifier"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('settings.product-types.fields.destroy', [$productType, $field]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-secondary-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/30 rounded"
                                                title="Supprimer"
                                                onclick="return confirm('Supprimer ce champ ?')"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add Field Modal -->
        <div
            x-show="showAddFieldModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showAddFieldModal = false"
            @keydown.escape.window="showAddFieldModal = false"
        >
            <div
                x-show="showAddFieldModal"
                x-transition
                class="relative w-full max-w-lg mx-4 bg-white dark:bg-dark-400 rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <form action="{{ route('settings.product-types.fields.store', $productType) }}" method="POST">
                    @csrf

                    <div class="sticky top-0 bg-white dark:bg-dark-400 px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Ajouter un champ</h3>
                            <button type="button" @click="showAddFieldModal = false" class="text-secondary-400 hover:text-secondary-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                    Nom du champ <span class="text-danger-500">*</span>
                                </label>
                                <input type="text" name="name" required class="form-input w-full" placeholder="ex: Référence fabricant">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                    Label (affichage)
                                </label>
                                <input type="text" name="label" class="form-input w-full" placeholder="ex: Réf. fabricant">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Type de champ <span class="text-danger-500">*</span>
                            </label>
                            <select name="type" required class="form-select w-full" x-model="newFieldType">
                                @foreach($fieldTypes as $type => $info)
                                    <option value="{{ $type }}">{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Description
                            </label>
                            <input type="text" name="description" class="form-input w-full" placeholder="Aide pour l'utilisateur">
                        </div>

                        <!-- Options for select/multiselect -->
                        <div x-show="['select', 'multiselect', 'radio'].includes(newFieldType)">
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Options (une par ligne, format: valeur|Label)
                            </label>
                            <textarea
                                name="options_text"
                                rows="3"
                                class="form-input w-full font-mono text-sm"
                                placeholder="small|Petit&#10;medium|Moyen&#10;large|Grand"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Valeur par défaut
                            </label>
                            <input type="text" name="default_value" class="form-input w-full">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Groupe
                            </label>
                            <input type="text" name="group" class="form-input w-full" placeholder="ex: Caractéristiques">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_required" value="1" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Requis</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="show_in_list" value="1" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Afficher en liste</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="show_in_invoice" value="1" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Afficher sur facture</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_searchable" value="1" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Recherchable</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_filterable" value="1" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Filtrable</span>
                            </label>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white dark:bg-dark-400 px-6 py-4 border-t border-secondary-200 dark:border-secondary-700 flex justify-end gap-3">
                        <button type="button" @click="showAddFieldModal = false" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter le champ</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Field Modal -->
        <div
            x-show="showEditFieldModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showEditFieldModal = false"
            @keydown.escape.window="showEditFieldModal = false"
        >
            <div
                x-show="showEditFieldModal"
                x-transition
                class="relative w-full max-w-lg mx-4 bg-white dark:bg-dark-400 rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <form :action="editFieldUrl" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="sticky top-0 bg-white dark:bg-dark-400 px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Modifier le champ</h3>
                            <button type="button" @click="showEditFieldModal = false" class="text-secondary-400 hover:text-secondary-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Nom</label>
                                <input type="text" name="name" x-model="editingField.name" required class="form-input w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Label</label>
                                <input type="text" name="label" x-model="editingField.label" class="form-input w-full">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Type</label>
                            <select name="type" x-model="editingField.type" required class="form-select w-full">
                                @foreach($fieldTypes as $type => $info)
                                    <option value="{{ $type }}">{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Description</label>
                            <input type="text" name="description" x-model="editingField.description" class="form-input w-full">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Groupe</label>
                            <input type="text" name="group" x-model="editingField.group" class="form-input w-full">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_required" value="1" x-model="editingField.is_required" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Requis</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="show_in_list" value="1" x-model="editingField.show_in_list" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Afficher en liste</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="show_in_invoice" value="1" x-model="editingField.show_in_invoice" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Sur facture</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" x-model="editingField.is_active" class="form-checkbox">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">Actif</span>
                            </label>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white dark:bg-dark-400 px-6 py-4 border-t border-secondary-200 dark:border-secondary-700 flex justify-end gap-3">
                        <button type="button" @click="showEditFieldModal = false" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function productTypeEditor() {
            return {
                showAddFieldModal: false,
                showEditFieldModal: false,
                newFieldType: 'text',
                editingField: {},
                editFieldUrl: '',

                editField(field) {
                    this.editingField = {
                        ...field,
                        is_required: Boolean(field.is_required),
                        is_active: Boolean(field.is_active),
                        show_in_list: Boolean(field.show_in_list),
                        show_in_invoice: Boolean(field.show_in_invoice),
                    };
                    this.editFieldUrl = '{{ route("settings.product-types.fields.update", [$productType, ""]) }}/' + field.id;
                    this.showEditFieldModal = true;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
