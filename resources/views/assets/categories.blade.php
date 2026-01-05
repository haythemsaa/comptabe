<x-app-layout>
    <x-slot name="title">Categories d'immobilisations</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('assets.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Immobilisations</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Categories</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Categories d'immobilisations</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Definissez les methodes d'amortissement par categorie</p>
            </div>
            <button type="button" onclick="document.getElementById('create-modal').classList.remove('hidden')" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle categorie
            </button>
        </div>

        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Nom</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Methode</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Duree</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Comptes</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Actifs</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($categories as $category)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-secondary-800 dark:text-white">{{ $category->name }}</div>
                                    @if($category->code)
                                        <div class="text-xs text-secondary-500">{{ $category->code }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    @switch($category->depreciation_method)
                                        @case('linear') Lineaire @break
                                        @case('degressive') Degressif ({{ $category->degressive_rate }}) @break
                                        @case('units_of_production') Unites de production @break
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $category->default_useful_life }} ans</td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    @if($category->accounting_asset_account)
                                        <span class="text-xs">{{ $category->accounting_asset_account }} / {{ $category->accounting_depreciation_account }} / {{ $category->accounting_expense_account }}</span>
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge badge-secondary">{{ $category->assets_count ?? 0 }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->code }}', '{{ $category->depreciation_method }}', {{ $category->default_useful_life }}, {{ $category->degressive_rate ?? 'null' }}, '{{ $category->accounting_asset_account }}', '{{ $category->accounting_depreciation_account }}', '{{ $category->accounting_expense_account }}')" class="text-secondary-500 hover:text-warning-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        @if(($category->assets_count ?? 0) == 0)
                                            <form action="{{ route('assets.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette categorie ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-secondary-500 hover:text-danger-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-secondary-500">Aucune categorie</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Creation -->
    <div id="create-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('create-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Nouvelle categorie</h3>
                <form action="{{ route('assets.categories.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="form-label">Nom <span class="text-danger-500">*</span></label>
                                <input type="text" name="name" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-input" placeholder="Ex: MAT-INFO">
                            </div>
                            <div>
                                <label class="form-label">Duree (annees)</label>
                                <input type="number" step="0.5" name="default_useful_life" value="5" class="form-input">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Methode d'amortissement</label>
                            <select name="depreciation_method" id="create_method" class="form-select" onchange="toggleDegressiveRate('create')">
                                <option value="linear">Lineaire</option>
                                <option value="degressive">Degressif</option>
                                <option value="units_of_production">Unites de production</option>
                            </select>
                        </div>
                        <div id="create_degressive_group" class="hidden">
                            <label class="form-label">Coefficient degressif</label>
                            <input type="number" step="0.01" name="degressive_rate" value="1.75" class="form-input">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Compte actif</label>
                                <input type="text" name="accounting_asset_account" class="form-input" placeholder="2100">
                            </div>
                            <div>
                                <label class="form-label">Compte amort.</label>
                                <input type="text" name="accounting_depreciation_account" class="form-input" placeholder="2109">
                            </div>
                            <div>
                                <label class="form-label">Compte charge</label>
                                <input type="text" name="accounting_expense_account" class="form-input" placeholder="6302">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Creer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edition -->
    <div id="edit-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('edit-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Modifier la categorie</h3>
                <form id="edit-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="form-label">Nom <span class="text-danger-500">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Code</label>
                                <input type="text" name="code" id="edit_code" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Duree (annees)</label>
                                <input type="number" step="0.5" name="default_useful_life" id="edit_useful_life" class="form-input">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Methode d'amortissement</label>
                            <select name="depreciation_method" id="edit_method" class="form-select" onchange="toggleDegressiveRate('edit')">
                                <option value="linear">Lineaire</option>
                                <option value="degressive">Degressif</option>
                                <option value="units_of_production">Unites de production</option>
                            </select>
                        </div>
                        <div id="edit_degressive_group" class="hidden">
                            <label class="form-label">Coefficient degressif</label>
                            <input type="number" step="0.01" name="degressive_rate" id="edit_degressive_rate" class="form-input">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Compte actif</label>
                                <input type="text" name="accounting_asset_account" id="edit_asset_account" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Compte amort.</label>
                                <input type="text" name="accounting_depreciation_account" id="edit_depreciation_account" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Compte charge</label>
                                <input type="text" name="accounting_expense_account" id="edit_expense_account" class="form-input">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleDegressiveRate(prefix) {
            const method = document.getElementById(prefix + '_method').value;
            const group = document.getElementById(prefix + '_degressive_group');
            group.classList.toggle('hidden', method !== 'degressive');
        }

        function editCategory(id, name, code, method, usefulLife, degressiveRate, assetAccount, depreciationAccount, expenseAccount) {
            document.getElementById('edit-form').action = '/assets/categories/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_code').value = code || '';
            document.getElementById('edit_method').value = method;
            document.getElementById('edit_useful_life').value = usefulLife;
            document.getElementById('edit_degressive_rate').value = degressiveRate || 1.75;
            document.getElementById('edit_asset_account').value = assetAccount || '';
            document.getElementById('edit_depreciation_account').value = depreciationAccount || '';
            document.getElementById('edit_expense_account').value = expenseAccount || '';
            toggleDegressiveRate('edit');
            document.getElementById('edit-modal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
