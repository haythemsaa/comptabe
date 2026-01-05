<x-app-layout>
    <x-slot name="title">Modifier {{ $asset->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('assets.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Immobilisations</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Modifier</span>
    @endsection

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Modifier l'immobilisation</h1>
            <p class="text-secondary-500 dark:text-secondary-400 mt-1">{{ $asset->name }}</p>
        </div>

        <form action="{{ route('assets.update', $asset) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Informations generales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" value="{{ old('reference', $asset->reference) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Categorie</label>
                        <select name="category_id" class="form-select">
                            <option value="">Sans categorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Designation <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $asset->name) }}" class="form-input" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="2" class="form-input">{{ old('description', $asset->description) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Numero de serie</label>
                        <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Emplacement</label>
                        <input type="text" name="location" value="{{ old('location', $asset->location) }}" class="form-input">
                    </div>
                </div>
            </div>

            @if($asset->status == 'draft')
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Valeurs (modifiable uniquement en brouillon)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Cout d'acquisition HT</label>
                        <input type="number" step="0.01" name="acquisition_cost" value="{{ old('acquisition_cost', $asset->acquisition_cost) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Valeur residuelle</label>
                        <input type="number" step="0.01" name="residual_value" value="{{ old('residual_value', $asset->residual_value) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Duree d'utilisation (annees)</label>
                        <input type="number" step="0.5" name="useful_life" value="{{ old('useful_life', $asset->useful_life) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Methode d'amortissement</label>
                        <select name="depreciation_method" class="form-select">
                            <option value="linear" {{ old('depreciation_method', $asset->depreciation_method) == 'linear' ? 'selected' : '' }}>Lineaire</option>
                            <option value="degressive" {{ old('depreciation_method', $asset->depreciation_method) == 'degressive' ? 'selected' : '' }}>Degressif</option>
                            <option value="units_of_production" {{ old('depreciation_method', $asset->depreciation_method) == 'units_of_production' ? 'selected' : '' }}>Unites de production</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between">
                <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette immobilisation ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Supprimer</button>
                </form>
                <div class="flex items-center gap-3">
                    <a href="{{ route('assets.show', $asset) }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
