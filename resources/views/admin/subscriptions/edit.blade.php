<x-admin-layout>
    <x-slot name="title">Modifier l'abonnement - {{ $subscription->company->name }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="text-secondary-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span>Modifier l'abonnement</span>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('admin.subscriptions.update', $subscription) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h2 class="text-lg font-semibold text-white">{{ $subscription->company->name }}</h2>
                    <p class="text-secondary-400 text-sm">{{ $subscription->company->vat_number }}</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Plan -->
                    <div>
                        <label for="plan_id" class="block text-sm font-medium text-secondary-300 mb-2">Plan</label>
                        <select name="plan_id" id="plan_id" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ $subscription->plan_id === $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - {{ $plan->isFree() ? 'Gratuit' : number_format($plan->price_monthly, 2) . ' €/mois' }}
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-secondary-300 mb-2">Statut</label>
                        <select name="status" id="status" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            @foreach(\App\Models\Subscription::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ $subscription->status === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Cycle -->
                    <div>
                        <label for="billing_cycle" class="block text-sm font-medium text-secondary-300 mb-2">Cycle de facturation</label>
                        <select name="billing_cycle" id="billing_cycle" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            <option value="monthly" {{ $subscription->billing_cycle === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                            <option value="yearly" {{ $subscription->billing_cycle === 'yearly' ? 'selected' : '' }}>Annuel</option>
                        </select>
                        @error('billing_cycle')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Admin Notes -->
                    <div>
                        <label for="admin_notes" class="block text-sm font-medium text-secondary-300 mb-2">Notes admin</label>
                        <textarea
                            name="admin_notes"
                            id="admin_notes"
                            rows="3"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white"
                            placeholder="Notes internes..."
                        >{{ old('admin_notes', $subscription->admin_notes) }}</textarea>
                    </div>

                    <!-- Current Info -->
                    <div class="p-4 bg-secondary-700/50 rounded-lg">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-secondary-400">Montant actuel:</span>
                                <span class="text-white ml-2">{{ number_format($subscription->amount, 2) }} €</span>
                            </div>
                            <div>
                                <span class="text-secondary-400">Créé le:</span>
                                <span class="text-white ml-2">{{ $subscription->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($subscription->trial_ends_at)
                                <div>
                                    <span class="text-secondary-400">Fin essai:</span>
                                    <span class="text-white ml-2">{{ $subscription->trial_ends_at->format('d/m/Y') }}</span>
                                </div>
                            @endif
                            @if($subscription->current_period_end)
                                <div>
                                    <span class="text-secondary-400">Fin période:</span>
                                    <span class="text-white ml-2">{{ $subscription->current_period_end->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-secondary-700 flex items-center justify-between">
                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="text-secondary-400 hover:text-white">
                        Annuler
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
