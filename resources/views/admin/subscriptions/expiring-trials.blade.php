<x-admin-layout>
    <x-slot name="title">Essais expirants</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.subscriptions.index') }}" class="text-secondary-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span>Essais expirants (7 prochains jours)</span>
        </div>
    </x-slot>

    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        @if($subscriptions->count() > 0)
            <div class="p-4 bg-info-500/10 border-b border-secondary-700">
                <div class="flex items-center gap-2 text-info-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $subscriptions->total() }} essai(s) expirant dans les 7 prochains jours</span>
                </div>
            </div>
        @endif

        <table class="w-full">
            <thead class="bg-secondary-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Entreprise</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Expire le</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Jours restants</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($subscriptions as $subscription)
                    <tr class="hover:bg-secondary-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.companies.show', $subscription->company) }}" class="text-white hover:text-primary-400 font-medium">
                                {{ $subscription->company->name }}
                            </a>
                            @if($subscription->company->email)
                                <div class="text-xs text-secondary-400">{{ $subscription->company->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-primary-500/20 text-primary-400 text-sm rounded">
                                {{ $subscription->plan->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-secondary-400">
                            {{ $subscription->trial_ends_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $daysLeft = $subscription->trial_days_remaining;
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $daysLeft <= 2 ? 'bg-danger-500/20 text-danger-400' : ($daysLeft <= 5 ? 'bg-warning-500/20 text-warning-400' : 'bg-info-500/20 text-info-400') }}">
                                {{ $daysLeft }} jour(s)
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('admin.subscriptions.extend-trial', $subscription) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="days" value="7">
                                    <button type="submit" class="px-3 py-1 bg-info-500/20 text-info-400 hover:bg-info-500/30 rounded text-sm transition-colors">
                                        +7 jours
                                    </button>
                                </form>
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="text-secondary-400 hover:text-white">
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
                            Aucun essai n'expire dans les 7 prochains jours
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($subscriptions->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
