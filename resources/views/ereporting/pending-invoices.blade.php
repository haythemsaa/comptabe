@extends('layouts.app')

@section('title', 'Factures en attente E-Reporting')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('ereporting.index') }}" class="text-muted">E-Reporting</a>
                <span class="text-muted">/</span> Factures en attente
            </h4>
            <p class="text-muted mb-0">Factures à soumettre au système e-reporting</p>
        </div>
        <div class="d-flex gap-2">
            @if($invoices->count() > 0)
            <form action="{{ route('ereporting.batch-submit') }}" method="POST" id="batchForm">
                @csrf
                <input type="hidden" name="invoice_ids" id="selectedIds">
                <button type="submit" class="btn btn-primary" id="batchSubmitBtn" disabled>
                    <i class="ti ti-send me-1"></i> Soumettre la sélection (<span id="selectedCount">0</span>)
                </button>
            </form>
            @endif
            <a href="{{ route('ereporting.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAll()">
                        </th>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th class="text-end">Montant TTC</th>
                        <th class="text-center">TVA</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input invoice-check"
                                   value="{{ $invoice->id }}" onchange="updateSelection()">
                        </td>
                        <td>
                            <a href="{{ route('invoices.show', $invoice) }}" class="fw-medium">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $invoice->partner?->name ?? '-' }}</td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td class="text-end fw-medium">{{ number_format($invoice->total_incl_vat, 2, ',', ' ') }} €</td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ number_format($invoice->total_vat, 2, ',', ' ') }} €</span>
                        </td>
                        <td>
                            <form action="{{ route('ereporting.submit-invoice', $invoice) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-send"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="ti ti-check-circle ti-xl mb-2 d-block text-success"></i>
                                <p class="mb-0">Toutes les factures ont été soumises</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleAll() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.invoice-check').forEach(cb => cb.checked = checked);
    updateSelection();
}

function updateSelection() {
    const checked = document.querySelectorAll('.invoice-check:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    document.getElementById('selectedIds').value = ids.join(',');
    document.getElementById('selectedCount').textContent = ids.length;
    document.getElementById('batchSubmitBtn').disabled = ids.length === 0;
}
</script>
@endpush
@endsection
