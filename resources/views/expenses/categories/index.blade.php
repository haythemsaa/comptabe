@extends('layouts.app')

@section('title', 'Catégories de dépenses')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span>
                Catégories
            </h4>
            <p class="text-muted mb-0">Gérez les catégories de dépenses</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="ti ti-plus me-1"></i> Nouvelle catégorie
        </button>
    </div>

    <!-- Categories List -->
    <div class="row g-4">
        @forelse($categories as $category)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-{{ $category->color ?? 'primary' }}">
                                <i class="ti ti-{{ $category->icon ?? 'category' }} ti-md"></i>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical ti-md"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <button type="button" class="dropdown-item"
                                        onclick="editCategory({{ json_encode($category) }})">
                                    <i class="ti ti-edit me-2"></i> Modifier
                                </button>
                                @if($category->expenses_count == 0)
                                <form action="{{ route('expenses.categories.destroy', $category) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette catégorie ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="ti ti-trash me-2"></i> Supprimer
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    <h5 class="mb-1">{{ $category->name }}</h5>
                    @if($category->description)
                        <p class="text-muted small mb-2">{{ Str::limit($category->description, 60) }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            @if($category->is_mileage)
                                <span class="badge bg-label-info">Kilométrique</span>
                            @endif
                            @if(!$category->is_active)
                                <span class="badge bg-label-secondary">Inactive</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $category->expenses_count ?? 0 }} dépenses</small>
                    </div>
                    @if($category->default_vat_rate !== null)
                        <div class="mt-2">
                            <small class="text-muted">TVA par défaut: {{ $category->default_vat_rate }}%</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ti ti-category-off ti-xl text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-3">Aucune catégorie de dépenses</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                        <i class="ti ti-plus me-1"></i> Créer une catégorie
                    </button>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('expenses.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="name">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               placeholder="Ex: Restauration, Transport...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                                  placeholder="Description de la catégorie..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="default_vat_rate">TVA par défaut</label>
                            <select class="form-select" id="default_vat_rate" name="default_vat_rate">
                                <option value="">-- Aucune --</option>
                                <option value="0">0%</option>
                                <option value="6">6%</option>
                                <option value="12">12%</option>
                                <option value="21" selected>21%</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="icon">Icône</label>
                            <select class="form-select" id="icon" name="icon">
                                <option value="category">Défaut</option>
                                <option value="car">Voiture</option>
                                <option value="plane">Avion</option>
                                <option value="train">Train</option>
                                <option value="hotel">Hôtel</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="phone">Téléphone</option>
                                <option value="printer">Fournitures</option>
                                <option value="tools">Équipement</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_mileage" name="is_mileage" value="1">
                            <label class="form-check-label" for="is_mileage">Catégorie kilométrique</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Modifier la catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="edit_name">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_default_vat_rate">TVA par défaut</label>
                            <select class="form-select" id="edit_default_vat_rate" name="default_vat_rate">
                                <option value="">-- Aucune --</option>
                                <option value="0">0%</option>
                                <option value="6">6%</option>
                                <option value="12">12%</option>
                                <option value="21">21%</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_icon">Icône</label>
                            <select class="form-select" id="edit_icon" name="icon">
                                <option value="category">Défaut</option>
                                <option value="car">Voiture</option>
                                <option value="plane">Avion</option>
                                <option value="train">Train</option>
                                <option value="hotel">Hôtel</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="phone">Téléphone</option>
                                <option value="printer">Fournitures</option>
                                <option value="tools">Équipement</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Catégorie active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editCategory(category) {
    document.getElementById('editCategoryForm').action = `/expenses/categories/${category.id}`;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_default_vat_rate').value = category.default_vat_rate || '';
    document.getElementById('edit_icon').value = category.icon || 'category';
    document.getElementById('edit_is_active').checked = category.is_active;

    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>
@endpush
