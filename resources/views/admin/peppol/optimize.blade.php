<x-app-layout>
    <x-slot name="title">Peppol - Optimisation</x-slot>

    <div class="space-y-6">
        <h1 class="text-2xl font-bold">Optimisation du Plan Provider</h1>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-4">Analyse</h3>
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div>
                    <div class="text-sm text-secondary-600">Volume Actuel</div>
                    <div class="text-2xl font-bold">{{ $currentVolume }}</div>
                </div>
                <div>
                    <div class="text-sm text-secondary-600">Projection Mois Prochain</div>
                    <div class="text-2xl font-bold">{{ $projectedVolume }}</div>
                </div>
                <div>
                    <div class="text-sm text-secondary-600">Coût Optimal</div>
                    <div class="text-2xl font-bold text-success-600">€{{ number_format($optimal['total_cost'], 2) }}</div>
                </div>
            </div>

            <div class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-4">
                <h4 class="font-semibold mb-2">Plan Optimal Recommandé</h4>
                <p class="text-lg">{{ $optimal['provider_name'] }} - {{ $optimal['plan_name'] }}</p>
                <p class="text-sm text-secondary-600 mt-1">
                    €{{ $optimal['monthly_cost'] }}/mois + overage ({{ $optimal['included_documents'] }} documents inclus)
                </p>
            </div>

            <form action="{{ route('admin.peppol.optimize.apply') }}" method="POST" class="mt-6">
                @csrf
                <button type="submit" class="btn btn-primary">Appliquer ce Plan</button>
            </form>
        </div>
    </div>
</x-app-layout>
