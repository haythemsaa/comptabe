<x-app-layout>
    <x-slot name="title">Peppol - Gestion des Quotas</x-slot>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Gestion des Quotas Peppol</h1>
            <a href="{{ route('admin.peppol.dashboard') }}" class="btn btn-secondary">← Dashboard</a>
        </div>

        <!-- Search & Filter -->
        <form method="GET" class="bg-white dark:bg-secondary-800 rounded-xl p-4 flex gap-4">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher..." class="input flex-1">
            <select name="plan" class="input w-48">
                <option value="">Tous les plans</option>
                @foreach($tenantPlans as $key => $plan)
                    <option value="{{ $key }}" {{ ($planFilter ?? '') === $key ? 'selected' : '' }}>{{ $plan['name'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>

        <!-- Companies List -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                <thead class="bg-secondary-50 dark:bg-secondary-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Entreprise</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Quota</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase">%</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                    @forelse($companies as $company)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ $company->name }}</div>
                            <div class="text-sm text-secondary-500">{{ $company->vat_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-primary-100 text-primary-800">
                                {{ ucfirst($company->peppol_plan ?? 'free') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-semibold">{{ $company->peppol_usage_current_month }}</td>
                        <td class="px-6 py-4">{{ $company->peppol_quota_monthly }}</td>
                        <td class="px-6 py-4">
                            @php
                                $percentage = $company->peppol_quota_monthly > 0
                                    ? ($company->peppol_usage_current_month / $company->peppol_quota_monthly) * 100
                                    : 0;
                            @endphp
                            <span class="text-sm {{ $percentage > 80 ? 'text-warning-600' : 'text-success-600' }}">
                                {{ number_format($percentage, 0) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="editQuota({{ $company->id }}, '{{ $company->peppol_plan }}', {{ $company->peppol_quota_monthly }})" class="text-primary-600 hover:text-primary-900">
                                Modifier
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-secondary-500">Aucune entreprise trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $companies->links() }}
    </div>

    <script>
    function editQuota(id, plan, quota) {
        // Simple modal - you can enhance this
        const newPlan = prompt('Nouveau plan (free/starter/pro/business/enterprise):', plan);
        const newQuota = prompt('Nouveau quota mensuel:', quota);

        if (newPlan && newQuota) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/peppol/quotas/${id}`;
            form.innerHTML = `
                @csrf
                <input name="peppol_plan" value="${newPlan}">
                <input name="peppol_quota_monthly" value="${newQuota}">
                <input name="peppol_overage_allowed" value="0">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</x-app-layout>
