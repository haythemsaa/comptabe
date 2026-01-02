<x-app-layout>
    <x-slot name="title">Détails Email - {{ $emailInvoice->subject }}</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Détails de l'email</h1>
                <p class="text-secondary-600 dark:text-secondary-400">{{ $emailInvoice->subject }}</p>
            </div>
            <a href="{{ route('email-invoices.index') }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>

        <!-- Status Card -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-2">Statut</h3>
                        <span class="badge {{ $emailInvoice->getStatusBadgeClass() }} text-lg">
                            {{ $emailInvoice->getStatusLabel() }}
                        </span>
                        @if($emailInvoice->confidence_score)
                            <span class="ml-2 text-sm text-secondary-600">
                                Confiance: {{ round($emailInvoice->confidence_score * 100) }}%
                            </span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @if($emailInvoice->invoice_id)
                            <a href="{{ route('purchases.show', $emailInvoice->invoice_id) }}" class="btn btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Voir la facture
                            </a>
                        @elseif($emailInvoice->isPending())
                            <button onclick="processEmail('{{ $emailInvoice->id }}')" class="btn btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Traiter maintenant
                            </button>
                            <button onclick="rejectEmail('{{ $emailInvoice->id }}')" class="btn btn-danger">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Rejeter
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Email Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Email Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">Informations de l'email</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                Sujet
                            </label>
                            <p class="text-secondary-900 dark:text-white">{{ $emailInvoice->subject }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                    De
                                </label>
                                <p class="text-secondary-900 dark:text-white">{{ $emailInvoice->from_name ?? $emailInvoice->from_email }}</p>
                                <p class="text-sm text-secondary-500">{{ $emailInvoice->from_email }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                    Date de réception
                                </label>
                                <p class="text-secondary-900 dark:text-white">{{ $emailInvoice->email_date->format('d/m/Y H:i') }}</p>
                                <p class="text-sm text-secondary-500">{{ $emailInvoice->email_date->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if($emailInvoice->body_text)
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                    Corps de l'email
                                </label>
                                <div class="bg-secondary-50 dark:bg-secondary-800 p-4 rounded-lg">
                                    <p class="text-sm text-secondary-700 dark:text-secondary-300 whitespace-pre-wrap">{{ Str::limit($emailInvoice->body_text, 500) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Extracted Data -->
                @if($emailInvoice->extracted_data)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Données extraites</h2>
                        </div>
                        <div class="card-body">
                            @php
                                $data = $emailInvoice->extracted_data;
                            @endphp

                            <div class="grid grid-cols-2 gap-4">
                                @if(isset($data['invoice_number']))
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                            N° de facture
                                        </label>
                                        <p class="text-secondary-900 dark:text-white">{{ $data['invoice_number'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['invoice_date']))
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                            Date facture
                                        </label>
                                        <p class="text-secondary-900 dark:text-white">{{ $data['invoice_date'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['supplier_name']))
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                            Fournisseur
                                        </label>
                                        <p class="text-secondary-900 dark:text-white">{{ $data['supplier_name'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['supplier_vat']))
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                            N° TVA
                                        </label>
                                        <p class="text-secondary-900 dark:text-white">{{ $data['supplier_vat'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['total_amount']))
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                            Montant total
                                        </label>
                                        <p class="text-lg font-semibold text-primary-600">{{ number_format($data['total_amount'], 2, ',', ' ') }} €</p>
                                    </div>
                                @endif

                                @if(isset($data['vat_amount']))
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                                            Montant TVA
                                        </label>
                                        <p class="text-secondary-900 dark:text-white">{{ number_format($data['vat_amount'], 2, ',', ' ') }} €</p>
                                    </div>
                                @endif
                            </div>

                            @if(!$emailInvoice->invoice_id && $emailInvoice->extracted_data)
                                <div class="mt-6 pt-6 border-t border-secondary-200 dark:border-secondary-700">
                                    <button onclick="createInvoiceFromData()" class="btn btn-primary w-full">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Créer la facture avec ces données
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Processing Notes -->
                @if($emailInvoice->processing_notes)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Notes de traitement</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary-700 dark:text-secondary-300">{{ $emailInvoice->processing_notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Error Message -->
                @if($emailInvoice->error_message)
                    <div class="card bg-danger-50 dark:bg-danger-900/20 border-danger-200">
                        <div class="card-header">
                            <h2 class="font-semibold text-danger-900 dark:text-danger-100">Erreur</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-danger-800 dark:text-danger-200">{{ $emailInvoice->error_message }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Attachments -->
                @if($emailInvoice->hasAttachments())
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold">Pièces jointes ({{ $emailInvoice->getAttachmentCount() }})</h2>
                        </div>
                        <div class="divide-y divide-secondary-100 dark:divide-secondary-800">
                            @foreach($emailInvoice->attachments as $attachment)
                                <div class="p-4 hover:bg-secondary-50 dark:hover:bg-secondary-800">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-secondary-900 dark:text-white truncate">
                                                {{ $attachment['filename'] }}
                                            </p>
                                            <p class="text-sm text-secondary-500">
                                                {{ round($attachment['size'] / 1024, 2) }} KB
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Processing History -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold">Historique</h2>
                    </div>
                    <div class="card-body space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-primary-600 rounded-full"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-secondary-900 dark:text-white">Email reçu</p>
                                <p class="text-xs text-secondary-500">{{ $emailInvoice->email_date->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if($emailInvoice->processed_at)
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-2 h-2 bg-success-600 rounded-full"></div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-secondary-900 dark:text-white">Traité</p>
                                    <p class="text-xs text-secondary-500">{{ $emailInvoice->processed_at->format('d/m/Y H:i') }}</p>
                                    @if($emailInvoice->processedBy)
                                        <p class="text-xs text-secondary-500">par {{ $emailInvoice->processedBy->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        async function processEmail(id) {
            if (!confirm('Traiter cet email et extraire les données de la facture ?')) return;

            try {
                const response = await fetch(`/email-invoices/${id}/process`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ auto_create: true })
                });

                const data = await response.json();

                if (data.success) {
                    window.showToast(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    } else {
                        setTimeout(() => window.location.reload(), 1500);
                    }
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (error) {
                window.showToast('Erreur lors du traitement', 'error');
            }
        }

        async function rejectEmail(id) {
            const reason = prompt('Raison du rejet:');
            if (!reason) return;

            try {
                const response = await fetch(`/email-invoices/${id}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ reason })
                });

                const data = await response.json();

                if (data.success) {
                    window.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (error) {
                window.showToast('Erreur lors du rejet', 'error');
            }
        }

        async function createInvoiceFromData() {
            if (!confirm('Créer une facture avec les données extraites ?')) return;

            try {
                const response = await fetch(`/email-invoices/{{ $emailInvoice->id }}/create-invoice`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(@json($emailInvoice->extracted_data))
                });

                const data = await response.json();

                if (data.success) {
                    window.showToast(data.message, 'success');
                    setTimeout(() => window.location.href = data.invoice.url, 1500);
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (error) {
                window.showToast('Erreur lors de la création', 'error');
            }
        }
    </script>
    @endpush
</x-app-layout>
