@extends('layouts.app')

@section('title', 'Exécutions de rapports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('reports.index') }}" class="text-muted">Rapports</a>
                <span class="text-muted">/</span> Historique des exécutions
            </h4>
            <p class="text-muted mb-0">Consultez l'historique des rapports générés</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Rapport</th>
                        <th>Type</th>
                        <th>Généré le</th>
                        <th>Généré par</th>
                        <th class="text-center">Statut</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($executions ?? [] as $execution)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $execution->report?->name ?? $execution->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ ucfirst($execution->type ?? 'standard') }}</span>
                        </td>
                        <td>
                            {{ $execution->created_at->format('d/m/Y H:i') }}
                            <br><small class="text-muted">{{ $execution->created_at->diffForHumans() }}</small>
                        </td>
                        <td>{{ $execution->user?->name ?? 'Système' }}</td>
                        <td class="text-center">
                            @php
                                $statusColors = ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success', 'failed' => 'danger'];
                                $statusLabels = ['pending' => 'En attente', 'processing' => 'En cours', 'completed' => 'Terminé', 'failed' => 'Échec'];
                            @endphp
                            <span class="badge bg-label-{{ $statusColors[$execution->status] ?? 'secondary' }}">
                                {{ $statusLabels[$execution->status] ?? ucfirst($execution->status) }}
                            </span>
                        </td>
                        <td>
                            @if($execution->status === 'completed' && $execution->file_path)
                            <a href="{{ route('reports.download', $execution) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-download me-1"></i> Télécharger
                            </a>
                            @elseif($execution->status === 'failed')
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                    title="{{ $execution->error_message }}">
                                <i class="ti ti-info-circle"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="ti ti-file-off ti-xl mb-2 d-block"></i>
                            Aucune exécution de rapport
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($executions) && $executions->hasPages())
        <div class="card-footer">
            {{ $executions->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endpush
@endsection
