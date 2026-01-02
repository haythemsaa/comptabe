@extends('layouts.app')

@section('title', 'Déclaration TVA ' . $declaration->period)

@section('content')
<div x-data="vatDeclarationShow()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('vat.declarations.index') }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <div>
                <h1 class="text-3xl font-bold text-secondary-900 dark:text-white">Déclaration TVA {{ $declaration->period }}</h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    {{ \Carbon\Carbon::parse($declaration->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($declaration->end_date)->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <div class="flex gap-3">
            @if($declaration->xml_content)
                <a href="{{ route('vat.declarations.download-xml', $declaration) }}" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Télécharger XML
                </a>
            @endif

            @if($declaration->status === 'draft' || $declaration->status === 'validated')
                <button @click="submitDeclaration()" class="btn btn-primary" :disabled="submitting">
                    <template x-if="!submitting">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Soumettre à Intervat
                        </span>
                    </template>
                    <template x-if="submitting">
                        <span class="flex items-center">
                            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Soumission...
                        </span>
                    </template>
                </button>
            @endif
        </div>
    </div>

    {{-- Status Badge --}}
    <div class="flex items-center gap-4">
        <span class="badge {{ $declaration->status === 'submitted' || $declaration->status === 'accepted' ? 'badge-success' : ($declaration->status === 'rejected' ? 'badge-danger' : 'badge-secondary') }}">
            Statut:
            @switch($declaration->status)
                @case('draft') Brouillon @break
                @case('validated') Validée @break
                @case('submitted') Soumise @break
                @case('rejected') Rejetée @break
                @case('accepted') Acceptée @break
                @default {{ $declaration->status }}
            @endswitch
        </span>

        @if($declaration->submitted_at)
            <span class="text-sm text-secondary-600 dark:text-secondary-400">
                Soumise le {{ \Carbon\Carbon::parse($declaration->submitted_at)->format('d/m/Y à H:i') }}
            </span>
        @endif

        @if($declaration->submission_reference)
            <span class="text-sm text-secondary-600 dark:text-secondary-400">
                Référence: <code class="px-2 py-1 bg-secondary-100 dark:bg-secondary-800 rounded">{{ $declaration->submission_reference }}</code>
            </span>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">TVA collectée</p>
                    <p class="text-2xl font-semibold text-success-600 dark:text-success-400">
                        {{ number_format($declaration->total_vat_collected, 2, ',', ' ') }} €
                    </p>
                </div>
                <div class="p-3 bg-success-100 dark:bg-success-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-secondary-500 dark:text-secondary-400">
                Sur {{ $declaration->invoice_count_sales }} factures de vente
            </p>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">TVA déductible</p>
                    <p class="text-2xl font-semibold text-info-600 dark:text-info-400">
                        {{ number_format($declaration->total_vat_deductible, 2, ',', ' ') }} €
                    </p>
                </div>
                <div class="p-3 bg-info-100 dark:bg-info-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-secondary-500 dark:text-secondary-400">
                Sur {{ $declaration->invoice_count_purchases }} factures d'achat
            </p>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Solde TVA</p>
                    <p class="text-2xl font-semibold {{ $declaration->grid_71 >= 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                        {{ number_format($declaration->grid_71, 2, ',', ' ') }} €
                    </p>
                </div>
                <div class="p-3 {{ $declaration->grid_71 >= 0 ? 'bg-danger-100 dark:bg-danger-900/30' : 'bg-success-100 dark:bg-success-900/30' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $declaration->grid_71 >= 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-secondary-500 dark:text-secondary-400">
                {{ $declaration->grid_71 >= 0 ? 'À payer' : 'À récupérer' }}
            </p>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Chiffre d'affaires</p>
                    <p class="text-2xl font-semibold text-primary-600 dark:text-primary-400">
                        {{ number_format($declaration->grid_00, 2, ',', ' ') }} €
                    </p>
                </div>
                <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-secondary-500 dark:text-secondary-400">
                Total HT période
            </p>
        </div>
    </div>

    {{-- Grilles Intervat --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Opérations Sortantes --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Opérations Sortantes (Ventes)</h2>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 00 - Chiffre d'affaires total</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_00, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 01 - Base 6%</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_01, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 02 - Base 12%</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_02, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 03 - Base 21%</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_03, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 45 - Exportations hors UE</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_45, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 46 - Opérations exemptées</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_46, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>

        {{-- TVA Collectée --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">TVA Collectée</h2>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 54 - TVA 21%</span>
                    <span class="font-semibold text-success-600 dark:text-success-400">{{ number_format($declaration->grid_54, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 55 - TVA 12%</span>
                    <span class="font-semibold text-success-600 dark:text-success-400">{{ number_format($declaration->grid_55, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 56 - TVA 6%</span>
                    <span class="font-semibold text-success-600 dark:text-success-400">{{ number_format($declaration->grid_56, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 61 - TVA diverses opérations</span>
                    <span class="font-semibold text-success-600 dark:text-success-400">{{ number_format($declaration->grid_61, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 pt-4 border-t-2 border-secondary-200 dark:border-secondary-700">
                    <span class="text-sm font-semibold text-secondary-900 dark:text-white">Grille 63 - Total TVA due</span>
                    <span class="text-lg font-bold text-success-600 dark:text-success-400">{{ number_format($declaration->grid_63, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>

        {{-- Opérations Entrantes --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Opérations Entrantes (Achats)</h2>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 81 - Marchandises/matières</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_81, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 82 - Services et biens divers</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_82, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 83 - Biens d'investissement</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_83, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 84 - Notes de crédit reçues</span>
                    <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_84, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>

        {{-- TVA Déductible --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">TVA Déductible</h2>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 59 - TVA déductible</span>
                    <span class="font-semibold text-info-600 dark:text-info-400">{{ number_format($declaration->grid_59, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                    <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 62 - TVA diverses</span>
                    <span class="font-semibold text-info-600 dark:text-info-400">{{ number_format($declaration->grid_62, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between items-center py-2 pt-4 border-t-2 border-secondary-200 dark:border-secondary-700">
                    <span class="text-sm font-semibold text-secondary-900 dark:text-white">Grille 71 - Solde (à payer/récupérer)</span>
                    <span class="text-lg font-bold {{ $declaration->grid_71 >= 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                        {{ number_format($declaration->grid_71, 2, ',', ' ') }} €
                    </span>
                </div>
            </div>
        </div>

        {{-- Opérations Intracommunautaires --}}
        <div class="card lg:col-span-2">
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Opérations Intracommunautaires</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-secondary-700 dark:text-secondary-300">Acquisitions</h3>
                    <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                        <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 86 - Biens</span>
                        <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_86, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 87 - Services</span>
                        <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_87, 2, ',', ' ') }} €</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-secondary-700 dark:text-secondary-300">Livraisons</h3>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-secondary-600 dark:text-secondary-400">Grille 88</span>
                        <span class="font-semibold text-secondary-900 dark:text-white">{{ number_format($declaration->grid_88, 2, ',', ' ') }} €</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-secondary-700 dark:text-secondary-300">TVA Autoliquidée</h3>
                    <div class="flex justify-between items-center py-2 border-b border-secondary-100 dark:border-secondary-800">
                        <span class="text-sm text-secondary-600 dark:text-secondary-400">Biens (55)</span>
                        <span class="font-semibold text-warning-600 dark:text-warning-400">{{ number_format($declaration->grid_55_intra, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-secondary-600 dark:text-secondary-400">Services (56)</span>
                        <span class="font-semibold text-warning-600 dark:text-warning-400">{{ number_format($declaration->grid_56_intra, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Submission Response --}}
    @if($declaration->submission_response)
        <div class="card">
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Réponse Intervat</h2>
            </div>
            <div class="p-6">
                <pre class="p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg text-xs overflow-x-auto"><code>{{ $declaration->submission_response }}</code></pre>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function vatDeclarationShow() {
    return {
        submitting: false,

        async submitDeclaration() {
            if (!confirm('Êtes-vous sûr de vouloir soumettre cette déclaration à Intervat ?')) {
                return;
            }

            this.submitting = true;

            try {
                const response = await axios.post('/vat/declarations/{{ $declaration->id }}/submit');

                if (response.data.success) {
                    window.showToast?.('Déclaration soumise avec succès', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.showToast?.(response.data.message || 'Erreur lors de la soumission', 'error');
                }
            } catch (error) {
                console.error('Error submitting declaration:', error);
                const message = error.response?.data?.message || 'Erreur lors de la soumission à Intervat';
                window.showToast?.(message, 'error');
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
