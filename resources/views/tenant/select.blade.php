<x-guest-layout>
    <x-slot name="title">Sélectionner une entreprise</x-slot>

    <div class="animate-fade-in-up">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">Sélectionner une entreprise</h2>
            <p class="mt-2 text-secondary-600 dark:text-secondary-400">Choisissez l'entreprise avec laquelle vous souhaitez travailler</p>
        </div>

        <div class="space-y-3">
            @foreach($companies as $company)
                <form method="POST" action="{{ route('tenant.switch') }}">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <button
                        type="submit"
                        class="w-full p-4 card card-hover text-left flex items-center gap-4 transition-all duration-200"
                    >
                        <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                {{ strtoupper(substr($company->name, 0, 2)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-secondary-900 dark:text-white truncate">
                                {{ $company->name }}
                            </div>
                            <div class="text-sm text-secondary-500 truncate">
                                {{ $company->formatted_vat_number }}
                            </div>
                            <div class="mt-1 flex items-center gap-2">
                                @if($company->peppol_registered)
                                    <span class="inline-flex items-center gap-1 text-xs text-success-600">
                                        <span class="w-1.5 h-1.5 bg-success-500 rounded-full"></span>
                                        Peppol actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-warning-600">
                                        <span class="w-1.5 h-1.5 bg-warning-500 rounded-full"></span>
                                        Peppol non configuré
                                    </span>
                                @endif
                                <span class="badge badge-{{ $company->pivot->role === 'owner' ? 'primary' : 'secondary' }} text-xs">
                                    {{ ucfirst($company->pivot->role) }}
                                </span>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            @endforeach
        </div>

        <!-- Add New Company -->
        <div class="mt-6 pt-6 border-t border-secondary-200 dark:border-secondary-700">
            <a
                href="{{ route('companies.create') }}"
                class="w-full py-4 border-2 border-dashed border-secondary-300 dark:border-secondary-600 rounded-xl text-secondary-500 hover:text-primary-600 hover:border-primary-300 dark:hover:border-primary-600 transition-colors flex items-center justify-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter une nouvelle entreprise
            </a>
        </div>

        <!-- Logout -->
        <div class="mt-8 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-secondary-500 hover:text-secondary-700 dark:hover:text-secondary-300">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
