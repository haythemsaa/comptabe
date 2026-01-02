<x-app-layout>
    <x-slot name="title">Factures par Email</x-slot>

    <div class="space-y-6">
        <!-- Header & Stats -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Factures par Email</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Gérez les factures reçues automatiquement par email</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-500">En attente</p>
                            <p class="text-2xl font-bold text-warning-600">{{ $stats['pending'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-warning-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-500">Traités</p>
                            <p class="text-2xl font-bold text-success-600">{{ $stats['processed'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-success-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-500">Échecs</p>
                            <p class="text-2xl font-bold text-danger-600">{{ $stats['failed'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-danger-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-500">Rejetés</p>
                            <p class="text-2xl font-bold text-secondary-600">{{ $stats['rejected'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-secondary-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="font-semibold">Emails reçus</h2>
                <div class="flex gap-2">
                    <select class="form-select form-select-sm" onchange="window.location.href='?status='+this.value">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tous</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Traités</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échecs</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetés</option>
                    </select>
                </div>
            </div>
            <div class="divide-y divide-secondary-100 dark:divide-secondary-800">
                @forelse($emailInvoices as $emailInvoice)
                    <div class="flex items-center gap-4 p-4 hover:bg-secondary-50 dark:hover:bg-secondary-800">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-secondary-900 dark:text-white truncate">{{ $emailInvoice->subject }}</p>
                                <span class="badge {{ $emailInvoice->getStatusBadgeClass() }}">{{ $emailInvoice->getStatusLabel() }}</span>
                                @if($emailInvoice->confidence_score)
                                    <span class="text-xs text-secondary-500">{{ round($emailInvoice->confidence_score * 100) }}% confiance</span>
                                @endif
                            </div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400 truncate">
                                De: {{ $emailInvoice->from_name ?? $emailInvoice->from_email }}
                                • {{ $emailInvoice->email_date->diffForHumans() }}
                                @if($emailInvoice->hasAttachments())
                                    • {{ $emailInvoice->getAttachmentCount() }} pièce(s) jointe(s)
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($emailInvoice->invoice_id)
                                <a href="{{ route('purchases.show', $emailInvoice->invoice_id) }}" class="btn btn-sm btn-secondary">
                                    Voir facture
                                </a>
                            @elseif($emailInvoice->isPending())
                                <button onclick="processEmail('{{ $emailInvoice->id }}')" class="btn btn-sm btn-primary">
                                    Traiter
                                </button>
                            @endif
                            <a href="{{ route('email-invoices.show', $emailInvoice) }}" class="btn btn-sm btn-secondary">
                                Détails
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-secondary-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p>Aucun email de facture reçu</p>
                    </div>
                @endforelse
            </div>

            @if($emailInvoices->hasPages())
                <div class="card-footer">
                    {{ $emailInvoices->links() }}
                </div>
            @endif
        </div>

        <!-- Info Box -->
        <div class="card bg-info-50 dark:bg-info-900/20 border-info-200">
            <div class="card-body">
                <h3 class="font-semibold text-info-900 dark:text-info-100 mb-2">
                    <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Comment configurer l'import automatique ?
                </h3>
                <p class="text-sm text-info-800 dark:text-info-200">
                    Pour recevoir automatiquement vos factures par email :
                </p>
                <ol class="list-decimal list-inside text-sm text-info-700 dark:text-info-300 mt-2 space-y-1">
                    <li>Configurez une adresse email dédiée (ex: factures@votre-entreprise.be)</li>
                    <li>Utilisez un webhook Mailgun/SendGrid ou configurez IMAP</li>
                    <li>Lancez la commande: <code class="bg-info-100 px-2 py-1 rounded">php artisan invoices:process-emails --auto-create</code></li>
                    <li>Ajoutez cette commande au cron pour traitement automatique</li>
                </ol>
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
    </script>
    @endpush
</x-app-layout>
