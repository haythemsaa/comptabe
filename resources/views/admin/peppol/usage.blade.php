<x-app-layout>
    <x-slot name="title">Peppol - Historique d'Usage</x-slot>

    <div class="space-y-6">
        <h1 class="text-2xl font-bold">Historique d'Usage Peppol</h1>

        <!-- Stats Summary -->
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-4">
                <div class="text-sm text-secondary-600">Total</div>
                <div class="text-2xl font-bold">{{ $summary['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-4">
                <div class="text-sm text-success-600">Réussis</div>
                <div class="text-2xl font-bold text-success-600">{{ $summary['successful'] }}</div>
            </div>
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-4">
                <div class="text-sm text-danger-600">Échecs</div>
                <div class="text-2xl font-bold text-danger-600">{{ $summary['failed'] }}</div>
            </div>
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-4">
                <div class="text-sm text-warning-600">Coût Total</div>
                <div class="text-2xl font-bold text-warning-600">€{{ number_format($summary['total_cost'], 2) }}</div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="bg-white dark:bg-secondary-800 rounded-xl p-4 flex gap-4">
            <input type="date" name="from" value="{{ $dateFrom }}" class="input">
            <input type="date" name="to" value="{{ $dateTo }}" class="input">
            <select name="status" class="input">
                <option value="">Tous statuts</option>
                <option value="success" {{ $status === 'success' ? 'selected' : '' }}>Succès</option>
                <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Échec</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>

        <!-- Usage Table -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-secondary-200">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Entreprise</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Coût</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-200">
                    @forelse($usage as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $item->company->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $item->action === 'send' ? 'bg-primary-100 text-primary-800' : 'bg-success-100 text-success-800' }}">
                                {{ ucfirst($item->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $item->status === 'success' ? 'bg-success-100 text-success-800' : 'bg-danger-100 text-danger-800' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">€{{ number_format($item->cost, 4) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-secondary-500">Aucun usage trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $usage->links() }}
    </div>
</x-app-layout>
