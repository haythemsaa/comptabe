@extends('layouts.app')

@section('title', 'Modifier la tâche')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('firm.tasks.index') }}" class="text-muted">Tâches</a>
                <span class="text-muted">/</span> Modifier
            </h4>
        </div>
    </div>

    <form action="{{ route('firm.tasks.update', $task) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations de la tâche</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Titre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   name="title" value="{{ old('title', $task->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      name="description" rows="4">{{ old('description', $task->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Date d'échéance</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                       name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priorité</label>
                                <select class="form-select @error('priority') is-invalid @enderror" name="priority">
                                    <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Basse</option>
                                    <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Moyenne</option>
                                    <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>Haute</option>
                                </select>
                                @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Assignation</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Assigné à</label>
                            <select class="form-select @error('assignee_id') is-invalid @enderror" name="assignee_id">
                                <option value="">-- Non assigné --</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" {{ old('assignee_id', $task->assignee_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assignee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status">
                                <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Terminé</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('firm.tasks.show', $task) }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
