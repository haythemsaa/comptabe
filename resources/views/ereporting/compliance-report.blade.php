@extends('layouts.app')

@section('title', 'Rapport de conformité E-Reporting')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('ereporting.index') }}" class="text-muted">E-Reporting</a>
                <span class="text-muted">/</span> Rapport de conformité
            </h4>
            <p class="text-muted mb-0">Analyse de conformité de vos soumissions e-reporting</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="ti ti-printer me-1"></i> Imprimer
            </button>
            <a href="{{ route('ereporting.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-2 bg-label-primary">
                        <span class="avatar-initial rounded"><i class="ti ti-file-invoice ti-lg"></i></span>
                    </div>
                    <h3 class="mb-0">{{ $report['total_invoices'] ?? 0 }}</h3>
                    <small class="text-muted">Factures analysées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-2 bg-label-success">
                        <span class="avatar-initial rounded"><i class="ti ti-check ti-lg"></i></span>
                    </div>
                    <h3 class="mb-0">{{ $report['compliant'] ?? 0 }}</h3>
                    <small class="text-muted">Conformes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-2 bg-label-warning">
                        <span class="avatar-initial rounded"><i class="ti ti-alert-triangle ti-lg"></i></span>
                    </div>
                    <h3 class="mb-0">{{ $report['warnings'] ?? 0 }}</h3>
                    <small class="text-muted">Avertissements</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-2 bg-label-danger">
                        <span class="avatar-initial rounded"><i class="ti ti-x ti-lg"></i></span>
                    </div>
                    <h3 class="mb-0">{{ $report['errors'] ?? 0 }}</h3>
                    <small class="text-muted">Erreurs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Rate -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Taux de conformité</h5>
                <span class="fs-4 fw-bold text-{{ ($report['compliance_rate'] ?? 0) >= 90 ? 'success' : (($report['compliance_rate'] ?? 0) >= 70 ? 'warning' : 'danger') }}">
                    {{ number_format($report['compliance_rate'] ?? 0, 1) }}%
                </span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" style="width: {{ $report['compliance_rate'] ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Issues List -->
    @if(!empty($report['issues']))
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Problèmes détectés</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Facture</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Sévérité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['issues'] as $issue)
                    <tr>
                        <td>
                            <a href="{{ route('invoices.show', $issue['invoice_id'] ?? 0) }}">
                                {{ $issue['invoice_number'] ?? '-' }}
                            </a>
                        </td>
                        <td>{{ $issue['type'] ?? '-' }}</td>
                        <td>{{ $issue['message'] ?? '-' }}</td>
                        <td>
                            @php $severity = $issue['severity'] ?? 'info'; @endphp
                            <span class="badge bg-label-{{ $severity === 'error' ? 'danger' : ($severity === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($severity) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recommendations -->
    @if(!empty($report['recommendations']))
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recommandations</h5>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                @foreach($report['recommendations'] as $rec)
                <li class="mb-2">{{ $rec }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
@media print {
    .btn, nav, .sidebar, .navbar { display: none !important; }
    .card { border: 1px solid #ddd !important; }
}
</style>
@endpush
@endsection
