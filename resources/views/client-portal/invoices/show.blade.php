@extends('client-portal.layouts.portal')

@section('title', 'Facture ' . $invoice->invoice_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ route('client-portal.invoices.index', $company) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-2 inline-block">
                ← Retour aux factures
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Facture {{ $invoice->invoice_number }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Émise le {{ $invoice->invoice_date->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex space-x-3">
            @if($access && $access->hasPermission('download_invoices'))
            <a href="{{ route('client-portal.invoices.download', [$company, $invoice]) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Télécharger PDF
            </a>
            @endif
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Company Info -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Émetteur</h3>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $company->name }}</p>
                    @if($company->address)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $company->address }}</p>
                    @endif
                    @if($company->vat_number)
                    <p class="text-sm text-gray-600 dark:text-gray-400">TVA: {{ $company->vat_number }}</p>
                    @endif
                </div>

                <!-- Client Info -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Client</h3>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $invoice->partner->name }}</p>
                    @if($invoice->partner->address)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->partner->address }}</p>
                    @endif
                </div>

                <!-- Invoice Info -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Informations</h3>
                    <dl class="text-sm space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Numéro:</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Date:</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_date->format('d/m/Y') }}</dd>
                        </div>
                        @if($invoice->due_date)
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Échéance:</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $invoice->due_date->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Status -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Statut</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($invoice->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                        @elseif($invoice->status === 'sent') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                        @endif">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Invoice Lines -->
        <div class="px-6 py-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Détails de la facture</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-2">Description</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-2">Quantité</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-2">Prix Unit.</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-2">TVA</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-2">Total HT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($invoice->lines as $line)
                    <tr>
                        <td class="py-3 text-sm text-gray-900 dark:text-white">
                            {{ $line->description }}
                        </td>
                        <td class="py-3 text-sm text-right text-gray-900 dark:text-white">
                            {{ number_format($line->quantity, 2) }}
                        </td>
                        <td class="py-3 text-sm text-right text-gray-900 dark:text-white">
                            {{ number_format($line->unit_price, 2, ',', ' ') }} €
                        </td>
                        <td class="py-3 text-sm text-right text-gray-900 dark:text-white">
                            {{ $line->vat_rate }}%
                        </td>
                        <td class="py-3 text-sm text-right font-medium text-gray-900 dark:text-white">
                            {{ number_format($line->line_amount, 2, ',', ' ') }} €
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex justify-end">
                    <div class="w-64 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Total HT:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($invoice->total_excl_vat, 2, ',', ' ') }} €</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">TVA:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($invoice->total_vat, 2, ',', ' ') }} €</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 dark:border-gray-700 pt-2">
                            <span class="text-gray-900 dark:text-white">Total TTC:</span>
                            <span class="text-primary-600 dark:text-primary-400">{{ number_format($invoice->total_incl_vat, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments -->
        @if($invoice->payments->isNotEmpty())
        <div class="px-6 py-5 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Paiements</h3>
            <div class="space-y-2">
                @foreach($invoice->payments as $payment)
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $payment->payment_date->format('d/m/Y') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $payment->payment_method_label }}
                        </p>
                    </div>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                        {{ number_format($payment->amount, 2, ',', ' ') }} €
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Comments Section -->
    @if($access && $access->hasPermission('comment'))
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden" x-data="commentsSection()">
        <div class="px-6 py-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Commentaires ({{ $comments->count() }})
            </h3>

            <!-- Comment Form -->
            <form action="{{ route('client-portal.comments.store', $company) }}" method="POST" class="mb-6">
                @csrf
                <input type="hidden" name="commentable_type" value="App\Models\Invoice">
                <input type="hidden" name="commentable_id" value="{{ $invoice->id }}">

                <div>
                    <label for="comment" class="sr-only">Votre commentaire</label>
                    <textarea name="content" id="comment" rows="3" required
                              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                              placeholder="Posez une question ou ajoutez un commentaire..."></textarea>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Envoyer
                    </button>
                </div>
            </form>

            <!-- Comments List -->
            <div class="space-y-6">
                @forelse($comments as $comment)
                <div class="border-l-2 border-gray-200 dark:border-gray-700 pl-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                @if($comment->is_resolved)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Résolu
                                </span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                {{ $comment->content }}
                            </p>

                            <!-- Replies -->
                            @if($comment->replies->isNotEmpty())
                            <div class="mt-3 space-y-3">
                                @foreach($comment->replies as $reply)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                {{ substr($reply->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-700 dark:text-gray-300">
                                            {{ $reply->content }}
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                    Aucun commentaire pour le moment
                </p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function commentsSection() {
            return {
                // Future: Add reply functionality, etc.
            }
        }
    </script>
    @endpush
    @endif
</div>
@endsection
