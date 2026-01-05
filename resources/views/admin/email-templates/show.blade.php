@extends('layouts.admin')

@section('title', 'Modèle: ' . $emailTemplate->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('admin.email-templates.index') }}" class="text-muted">Modèles email</a>
                <span class="text-muted">/</span> {{ $emailTemplate->name }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}" class="btn btn-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Aperçu</h5>
                    <span class="badge bg-label-{{ $emailTemplate->is_active ? 'success' : 'secondary' }}">
                        {{ $emailTemplate->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Sujet</label>
                        <div class="p-3 bg-light rounded">{{ $preview['subject'] ?? $emailTemplate->subject }}</div>
                    </div>
                    <div>
                        <label class="form-label fw-medium">Corps du message</label>
                        <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">{{ $preview['body'] ?? $emailTemplate->body }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Slug</span>
                        <code>{{ $emailTemplate->slug }}</code>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Catégorie</span>
                        <span>{{ $emailTemplate->category ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Créé le</span>
                        <span>{{ $emailTemplate->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Modifié le</span>
                        <span>{{ $emailTemplate->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.email-templates.destroy', $emailTemplate) }}" method="POST"
                          onsubmit="return confirm('Supprimer ce modèle ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="ti ti-trash me-1"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
