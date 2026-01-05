@extends('layouts.app')

@section('title', 'Tâche: ' . $task->title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('firm.tasks.index') }}" class="text-muted">Tâches</a>
                <span class="text-muted">/</span> {{ Str::limit($task->title, 30) }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            @if($task->status !== 'completed')
            <a href="{{ route('firm.tasks.edit', $task) }}" class="btn btn-outline-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
            @endif
            <a href="{{ route('firm.tasks.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">{{ $task->title }}</h5>
                    @php
                        $statusColors = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'secondary'];
                        $statusLabels = ['pending' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Terminé', 'cancelled' => 'Annulé'];
                    @endphp
                    <span class="badge bg-label-{{ $statusColors[$task->status] ?? 'secondary' }}">
                        {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                    </span>
                </div>
                <div class="card-body">
                    @if($task->description)
                    <div class="mb-4">
                        <h6 class="text-muted">Description</h6>
                        <p class="mb-0">{{ $task->description }}</p>
                    </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Mandat</small>
                            @if($task->mandate)
                                <a href="{{ route('firm.mandates.show', $task->mandate) }}">{{ $task->mandate->company->name }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Assigné à</small>
                            <span>{{ $task->assignee?->name ?? 'Non assigné' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Date d'échéance</small>
                            @if($task->due_date)
                                <span class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-danger' : '' }}">
                                    {{ $task->due_date->format('d/m/Y') }}
                                    @if($task->due_date->isPast() && $task->status !== 'completed')
                                        <i class="ti ti-alert-triangle text-danger"></i>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Priorité</small>
                            @php $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger']; @endphp
                            <span class="badge bg-label-{{ $priorityColors[$task->priority] ?? 'secondary' }}">
                                {{ ucfirst($task->priority ?? 'medium') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if($task->comments && $task->comments->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Commentaires</h5>
                </div>
                <div class="card-body">
                    @foreach($task->comments as $comment)
                    <div class="d-flex mb-3">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ substr($comment->user->name, 0, 1) }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0">{{ $comment->content }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Créée le</span>
                        <span>{{ $task->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Créée par</span>
                        <span>{{ $task->creator?->name ?? '-' }}</span>
                    </div>
                    @if($task->completed_at)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Terminée le</span>
                        <span>{{ $task->completed_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($task->status !== 'completed' && $task->status !== 'cancelled')
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($task->status === 'pending')
                        <form action="{{ route('firm.tasks.start', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info w-100">
                                <i class="ti ti-player-play me-1"></i> Démarrer
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('firm.tasks.complete', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ti ti-check me-1"></i> Marquer terminé
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
