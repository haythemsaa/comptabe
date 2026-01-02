@extends('layouts.app')

@section('title', 'Détail du scan')

@section('content')
<div x-data="scanDetail()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('ai.scanner') }}" class="p-2 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                    {{ $scan->original_filename }}
                </h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Scanné {{ $scan->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @switch($scan->status)
                @case('completed')
                    <x-badge color="green" class="px-3 py-1">Traité</x-badge>
                    @break
                @case('pending')
                    <x-badge color="yellow" class="px-3 py-1">En attente</x-badge>
                    @break
                @case('needs_review')
                    <x-badge color="orange" class="px-3 py-1">À vérifier</x-badge>
                    @break
                @case('validated')
                    <x-badge color="blue" class="px-3 py-1">Validé</x-badge>
                    @break
                @case('failed')
                    <x-badge color="red" class="px-3 py-1">Échec</x-badge>
                    @break
            @endswitch

            @if($scan->confidence_score)
                <div class="flex items-center gap-2 px-3 py-1 bg-secondary-100 dark:bg-secondary-800 rounded-full">
                    <div class="w-2 h-2 rounded-full {{ $scan->confidence_score >= 0.8 ? 'bg-green-500' : ($scan->confidence_score >= 0.5 ? 'bg-yellow-500' : 'bg-red-500') }}"></div>
                    <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">
                        {{ number_format($scan->confidence_score * 100, 0) }}% confiance
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Aperçu du document -->
        <x-card class="h-fit">
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Document original
                </h3>
            </x-slot:header>

            <div class="bg-secondary-100 dark:bg-secondary-900 rounded-lg p-4 flex items-center justify-center min-h-96">
                @if(Str::endsWith($scan->file_path, '.pdf'))
                    <iframe src="{{ Storage::url($scan->file_path) }}" class="w-full h-96 rounded"></iframe>
                @else
                    <img src="{{ Storage::url($scan->file_path) }}" alt="{{ $scan->original_filename }}" class="max-w-full max-h-96 rounded shadow-lg">
                @endif
            </div>

            <div class="mt-4 flex justify-center gap-3">
                <a href="{{ Storage::url($scan->file_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 text-secondary-600 hover:text-secondary-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Ouvrir
                </a>
                <a href="{{ Storage::url($scan->file_path) }}" download
                   class="inline-flex items-center gap-2 px-4 py-2 text-secondary-600 hover:text-secondary-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Télécharger
                </a>
            </div>
        </x-card>

        <!-- Données extraites -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">
                        Données extraites
                    </h3>
                    @if($scan->auto_created)
                        <x-badge color="purple">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Créé automatiquement
                            </span>
                        </x-badge>
                    @endif
                </div>
            </x-slot:header>

            <form @submit.prevent="validateScan()" class="space-y-6">
                <!-- Fournisseur -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Fournisseur
                        </label>
                        <input type="text"
                               x-model="data.supplier_name"
                               class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800">
                        @if(isset($scan->extracted_data['supplier_matched']))
                            <p class="mt-1 text-xs text-green-600">
                                Fournisseur reconnu dans la base
                            </p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            N° TVA
                        </label>
                        <input type="text"
                               x-model="data.vat_number"
                               class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800">
                        @if(isset($scan->extracted_data['vat_valid']) && $scan->extracted_data['vat_valid'])
                            <p class="mt-1 text-xs text-green-600 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                TVA valide (mod 97)
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Numéros -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            N° Facture
                        </label>
                        <input type="text"
                               x-model="data.invoice_number"
                               class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Date facture
                        </label>
                        <input type="date"
                               x-model="data.invoice_date"
                               class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800">
                    </div>
                </div>

                <!-- Montants -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Montant HT
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01"
                                   x-model="data.subtotal"
                                   class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            TVA
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01"
                                   x-model="data.vat_amount"
                                   class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Total TTC
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01"
                                   x-model="data.total_amount"
                                   class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 pr-8 font-bold">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                        </div>
                    </div>
                </div>

                <!-- IBAN / Communication -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            IBAN
                        </label>
                        <input type="text"
                               x-model="data.iban"
                               class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 font-mono text-sm">
                        @if(isset($scan->extracted_data['bank_name']))
                            <p class="mt-1 text-xs text-secondary-500">
                                {{ $scan->extracted_data['bank_name'] }}
                            </p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Communication structurée
                        </label>
                        <input type="text"
                               x-model="data.structured_communication"
                               placeholder="+++xxx/xxxx/xxxxx+++"
                               class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 font-mono text-sm">
                    </div>
                </div>

                <!-- Date échéance -->
                <div>
                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                        Date d'échéance
                    </label>
                    <input type="date"
                           x-model="data.due_date"
                           class="w-full rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800">
                </div>

                <!-- Lignes détectées -->
                @if(isset($scan->extracted_data['line_items']) && count($scan->extracted_data['line_items']) > 0)
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                            Lignes détectées ({{ count($scan->extracted_data['line_items']) }})
                        </label>
                        <div class="border border-secondary-200 dark:border-secondary-700 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                                <thead class="bg-secondary-50 dark:bg-secondary-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500">Description</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-secondary-500">Qté</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-secondary-500">P.U.</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-secondary-500">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                    @foreach($scan->extracted_data['line_items'] as $item)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-secondary-900 dark:text-white">{{ $item['description'] ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-right text-secondary-600">{{ $item['quantity'] ?? 1 }}</td>
                                            <td class="px-4 py-2 text-sm text-right text-secondary-600">{{ number_format($item['unit_price'] ?? 0, 2, ',', ' ') }} €</td>
                                            <td class="px-4 py-2 text-sm text-right font-medium text-secondary-900 dark:text-white">{{ number_format($item['total'] ?? 0, 2, ',', ' ') }} €</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="createInvoice" id="createInvoice"
                               class="rounded text-primary-600 focus:ring-primary-500"
                               {{ $scan->invoice_id ? 'disabled' : '' }}>
                        <label for="createInvoice" class="text-sm text-secondary-700 dark:text-secondary-300">
                            @if($scan->invoice_id)
                                Facture déjà créée
                            @else
                                Créer la facture après validation
                            @endif
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($scan->invoice_id)
                            <a href="{{ route('invoices.show', $scan->invoice_id) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 text-primary-600 hover:text-primary-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Voir la facture
                            </a>
                        @endif
                        <button type="submit"
                                :disabled="saving"
                                class="inline-flex items-center gap-2 px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors">
                            <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="saving ? 'Enregistrement...' : 'Valider'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>

    <!-- Informations techniques -->
    <x-card>
        <x-slot:header>
            <h3 class="font-semibold text-secondary-900 dark:text-white">
                Informations techniques
            </h3>
        </x-slot:header>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-secondary-500">Type de document</p>
                <p class="font-medium text-secondary-900 dark:text-white">{{ ucfirst($scan->document_type) }}</p>
            </div>
            <div>
                <p class="text-secondary-500">Provider OCR</p>
                <p class="font-medium text-secondary-900 dark:text-white">{{ $scan->ocr_provider ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-secondary-500">Temps de traitement</p>
                <p class="font-medium text-secondary-900 dark:text-white">{{ $scan->processing_time ?? 'N/A' }} ms</p>
            </div>
            <div>
                <p class="text-secondary-500">Taille du fichier</p>
                <p class="font-medium text-secondary-900 dark:text-white">{{ number_format($scan->file_size / 1024, 1) }} KB</p>
            </div>
        </div>
    </x-card>
</div>

@push('scripts')
<script>
function scanDetail() {
    return {
        data: @json($scan->extracted_data ?? []),
        createInvoice: false,
        saving: false,

        async validateScan() {
            this.saving = true;

            try {
                const response = await fetch('{{ route('ai.scan.validate', $scan) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        extracted_data: this.data,
                        create_invoice: this.createInvoice
                    })
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = '{{ route('ai.scanner') }}';
                } else {
                    alert('Erreur: ' + (result.message || 'Échec de la validation'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Erreur de connexion');
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endpush
@endsection
