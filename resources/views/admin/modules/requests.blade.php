<x-admin-layout>
    <x-slot name="title">Demandes de Modules</x-slot>
    <x-slot name="header">Demandes de Modules des Tenants</x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">En Attente</p>
                    <p class="text-3xl font-bold text-warning-400">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-12 h-12 bg-warning-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Approuvées</p>
                    <p class="text-3xl font-bold text-success-400">{{ $stats['approved'] }}</p>
                </div>
                <div class="w-12 h-12 bg-success-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Refusées</p>
                    <p class="text-3xl font-bold text-danger-400">{{ $stats['rejected'] }}</p>
                </div>
                <div class="w-12 h-12 bg-danger-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests List -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700">
        <div class="px-6 py-4 border-b border-secondary-700">
            <h3 class="text-lg font-semibold text-white">Toutes les Demandes</h3>
            <p class="text-secondary-400 text-sm">Gérer les demandes d'activation de modules</p>
        </div>

        @if($requests->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Entreprise</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Module</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Demandeur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-700">
                    @foreach($requests as $request)
                    <tr class="hover:bg-secondary-900/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-white font-medium">{{ $request->company->name }}</div>
                                <div class="text-secondary-400 text-xs">{{ $request->company->email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-white font-medium">{{ $request->module->name }}</span>
                                @if($request->module->is_premium)
                                    <span class="px-2 py-1 bg-warning-500/20 text-warning-400 text-xs rounded">Premium</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-white text-sm">{{ $request->requestedBy->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-secondary-300 text-sm max-w-xs">
                                {{ $request->message ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-secondary-300 text-sm">
                            {{ $request->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($request->status === 'pending')
                                <span class="px-2 py-1 bg-warning-500/20 text-warning-400 text-xs rounded">En attente</span>
                            @elseif($request->status === 'approved')
                                <span class="px-2 py-1 bg-success-500/20 text-success-400 text-xs rounded">Approuvée</span>
                            @elseif($request->status === 'rejected')
                                <span class="px-2 py-1 bg-danger-500/20 text-danger-400 text-xs rounded">Refusée</span>
                            @else
                                <span class="px-2 py-1 bg-secondary-500/20 text-secondary-400 text-xs rounded">Annulée</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($request->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        onclick="showApproveModal({{ $request->id }}, '{{ $request->module->name }}')"
                                        class="text-sm text-success-400 hover:text-success-300">
                                        Approuver
                                    </button>
                                    <button
                                        onclick="showRejectModal({{ $request->id }}, '{{ $request->module->name }}')"
                                        class="text-sm text-danger-400 hover:text-danger-300">
                                        Refuser
                                    </button>
                                </div>
                            @else
                                <div class="text-xs text-secondary-400">
                                    Par {{ $request->reviewedBy->name ?? 'N/A' }}<br>
                                    {{ $request->reviewed_at?->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-secondary-700">
            {{ $requests->links() }}
        </div>
        @else
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-secondary-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-secondary-400">Aucune demande de module</p>
        </div>
        @endif
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-white mb-4">Approuver la Demande</h3>
            <p class="text-secondary-300 mb-4">Module: <span id="approveModuleName" class="text-white font-semibold"></span></p>

            <form id="approveForm" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Période d'essai (jours)</label>
                        <input type="number" name="trial_days" value="30" min="1" max="365" required
                               class="w-full bg-secondary-900 border border-secondary-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Message (optionnel)</label>
                        <textarea name="response" rows="3"
                                  class="w-full bg-secondary-900 border border-secondary-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-500"
                                  placeholder="Message de confirmation..."></textarea>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="hideApproveModal()" class="flex-1 btn-secondary">Annuler</button>
                    <button type="submit" class="flex-1 btn-primary">Approuver</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-white mb-4">Refuser la Demande</h3>
            <p class="text-secondary-300 mb-4">Module: <span id="rejectModuleName" class="text-white font-semibold"></span></p>

            <form id="rejectForm" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Raison du refus *</label>
                        <textarea name="reason" rows="4" required
                                  class="w-full bg-secondary-900 border border-secondary-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-500"
                                  placeholder="Expliquez pourquoi cette demande est refusée..."></textarea>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="hideRejectModal()" class="flex-1 btn-secondary">Annuler</button>
                    <button type="submit" class="flex-1 bg-danger-600 hover:bg-danger-700 text-white px-4 py-2 rounded-lg transition">Refuser</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showApproveModal(requestId, moduleName) {
        document.getElementById('approveModuleName').textContent = moduleName;
        document.getElementById('approveForm').action = `/admin/modules/requests/${requestId}/approve`;
        document.getElementById('approveModal').classList.remove('hidden');
    }

    function hideApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    function showRejectModal(requestId, moduleName) {
        document.getElementById('rejectModuleName').textContent = moduleName;
        document.getElementById('rejectForm').action = `/admin/modules/requests/${requestId}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function hideRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Close modals on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideApproveModal();
            hideRejectModal();
        }
    });
    </script>
</x-admin-layout>
