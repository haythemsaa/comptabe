@extends('layouts.admin')

@section('title', 'Modifier: ' . $emailTemplate->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('admin.email-templates.index') }}" class="text-muted">Modèles email</a>
                <span class="text-muted">/</span> Modifier
            </h4>
        </div>
    </div>

    <form action="{{ route('admin.email-templates.update', $emailTemplate) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations générales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name', $emailTemplate->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       name="slug" value="{{ old('slug', $emailTemplate->slug) }}" required>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catégorie</label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror"
                                       name="category" value="{{ old('category', $emailTemplate->category) }}" list="categories">
                                <datalist id="categories">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Sujet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                       name="subject" value="{{ old('subject', $emailTemplate->subject) }}" required>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contenu</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Corps du message <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  name="body" rows="15" required>{{ old('body', $emailTemplate->body) }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Actif</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Variables disponibles</h5>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <code>{company_name}</code> - Nom société<br>
                            <code>{client_name}</code> - Nom client<br>
                            <code>{invoice_number}</code> - N° facture<br>
                            <code>{amount}</code> - Montant<br>
                            <code>{due_date}</code> - Date échéance
                        </small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('admin.email-templates.show', $emailTemplate) }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
