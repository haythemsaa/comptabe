<x-admin-layout>
    <x-slot name="title">Entreprises sans abonnement</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.subscriptions.index') }}" class="text-secondary-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span>Entreprises sans abonnement</span>
        </div>
    </x-slot>

    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        @if($companies->count() > 0)
            <div class="p-4 bg-warning-500/10 border-b border-secondary-700">
                <div class="flex items-center gap-2 text-warning-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ $companies->total() }} entreprise(s) sans abonnement actif</span>
                </div>
            </div>
        @endif

        <table class="w-full">
            <thead class="bg-secondary-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Entreprise</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Utilisateurs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Créée le</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Ancien abonnement</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($companies as $company)
                    <tr class="hover:bg-secondary-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.companies.show', $company) }}" class="text-white hover:text-primary-400 font-medium">
                                {{ $company->name }}
                            </a>
                            @if($company->vat_number)
                                <div class="text-xs text-secondary-400">{{ $company->vat_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-secondary-400">
                            {{ $company->users->count() }} utilisateur(s)
                        </td>
                        <td class="px-6 py-4 text-secondary-400">
                            {{ $company->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($company->subscription)
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $company->subscription->status_color }}-500/20 text-{{ $company->subscription->status_color }}-400">
                                    {{ $company->subscription->status_label }}
                                </span>
                                <span class="text-secondary-400 text-xs ml-1">
                                    ({{ $company->subscription->plan->name }})
                                </span>
                            @else
                                <span class="text-secondary-500 text-sm">Jamais abonné</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2" x-data="{ showForm: false, selectedPlan: '' }">
                                <button @click="showForm = !showForm" class="px-3 py-1 bg-primary-500/20 text-primary-400 hover:bg-primary-500/30 rounded text-sm transition-colors">
                                    Assigner un plan
                                </button>

                                <div x-show="showForm" x-cloak class="absolute right-0 mt-32 w-64 bg-secondary-800 border border-secondary-700 rounded-lg shadow-xl p-4 z-10">
                                    <form action="{{ route('admin.subscriptions.store', $company) }}" method="POST">
                                        @csrf
                                        <select name="plan_id" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm mb-2">
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="billing_cycle" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm mb-2">
                                            <option value="monthly">Mensuel</option>
                                            <option value="yearly">Annuel</option>
                                        </select>
                                        <label class="flex items-center gap-2 text-sm text-secondary-300 mb-3">
                                            <input type="checkbox" name="start_trial" value="1" checked class="rounded text-primary-500">
                                            Période d'essai
                                        </label>
                                        <div class="flex gap-2">
                                            <button type="button" @click="showForm = false" class="flex-1 px-3 py-1 bg-secondary-700 hover:bg-secondary-600 rounded text-sm">
                                                Annuler
                                            </button>
                                            <button type="submit" class="flex-1 px-3 py-1 bg-primary-500 hover:bg-primary-600 rounded text-sm">
                                                Créer
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <a href="{{ route('admin.companies.show', $company) }}" class="text-secondary-400 hover:text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-secondary-400">
                            <svg class="w-12 h-12 mx-auto text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Toutes les entreprises ont un abonnement actif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($companies->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
