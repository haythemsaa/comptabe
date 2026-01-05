@extends('layouts.app')

@section('title', 'Soumission E-Reporting')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('ereporting.index') }}" class="text-muted">E-Reporting</a>
                <span class="text-muted">/</span> Soumission #{{ $submission->id }}
            </h4>
        </div>
        <a href="{{ route('ereporting.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Détails de la soumission</h5>
                    @php
                        $statusColors = ['pending' => 'warning', 'submitted' => 'info', 'accepted' => 'success', 'rejected' => 'danger'];
                        $statusLabels = ['pending' => 'En attente', 'submitted' => 'Soumis', 'accepted' => 'Accepté', 'rejected' => 'Rejeté'];
                    @endphp
                    <span class="badge bg-label-{{ $statusColors[$submission->status] ?? 'secondary' }}">
                        {{ $statusLabels[$submission->status] ?? ucfirst($submission->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Facture</small>
                            @if($submission->invoice)
                                <a href="{{ route('invoices.show', $submission->invoice) }}" class="fw-medium">
                                    {{ $submission->invoice->invoice_number }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Client/Fournisseur</small>
                            <span class="fw-medium">{{ $submission->invoice?->partner?->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Montant</small>
                            <span class="fw-medium">{{ number_format($submission->invoice?->total_incl_vat ?? 0, 2, ',', ' ') }} €</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Type</small>
                            <span class="badge bg-label-primary">{{ ucfirst($submission->type ?? 'invoice') }}</span>
                        </div>
                    </div>

                    @if($submission->submission_reference)
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Référence soumission</small>
                            <code>{{ $submission->submission_reference }}</code>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Soumis le</small>
                            <span>{{ $submission->submitted_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    @endif

                    @if($submission->response_message)
                    <hr>
                    <div>
                        <small class="text-muted d-block mb-1">Message de réponse</small>
                        <div class="p-3 bg-{{ $submission->status === 'rejected' ? 'danger' : 'light' }} {{ $submission->status === 'rejected' ? 'bg-opacity-10' : '' }} rounded">
                            {{ $submission->response_message }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($submission->xml_content)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Contenu XML</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow: auto;"><code>{{ $submission->xml_content }}</code></pre>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Historique</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline mb-0">
                        <li class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-primary">
                                <i class="ti ti-plus"></i>
                            </span>
                            <div class="timeline-event">
                                <p class="mb-0">Créé</p>
                                <small class="text-muted">{{ $submission->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </li>
                        @if($submission->submitted_at)
                        <li class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-info">
                                <i class="ti ti-send"></i>
                            </span>
                            <div class="timeline-event">
                                <p class="mb-0">Soumis</p>
                                <small class="text-muted">{{ $submission->submitted_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </li>
                        @endif
                        @if($submission->status === 'accepted')
                        <li class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-success">
                                <i class="ti ti-check"></i>
                            </span>
                            <div class="timeline-event">
                                <p class="mb-0">Accepté</p>
                                <small class="text-muted">{{ $submission->updated_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </li>
                        @elseif($submission->status === 'rejected')
                        <li class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-danger">
                                <i class="ti ti-x"></i>
                            </span>
                            <div class="timeline-event">
                                <p class="mb-0">Rejeté</p>
                                <small class="text-muted">{{ $submission->updated_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            @if($submission->status === 'pending')
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('ereporting.submit', $submission) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-send me-1"></i> Soumettre maintenant
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.timeline { position: relative; padding-left: 1.5rem; list-style: none; }
.timeline-item { position: relative; padding-bottom: 1rem; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-item::before { content: ''; position: absolute; left: -1.5rem; top: 1.5rem; bottom: 0; width: 2px; background: #e0e0e0; }
.timeline-item:last-child::before { display: none; }
.timeline-indicator { position: absolute; left: -2rem; width: 1rem; height: 1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.625rem; color: #fff; }
.timeline-indicator-primary { background: var(--bs-primary); }
.timeline-indicator-info { background: var(--bs-info); }
.timeline-indicator-success { background: var(--bs-success); }
.timeline-indicator-danger { background: var(--bs-danger); }
</style>
@endsection
