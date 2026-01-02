<x-app-layout>
    <x-slot name="title">Peppol - Configuration</x-slot>

    @section('breadcrumb')
        <a href="{{ route('admin.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Admin</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('admin.peppol.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Peppol</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Configuration</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Configuration Peppol Globale</h1>
                <p class="text-secondary-600 dark:text-secondary-400">API centralisée pour tous vos clients</p>
            </div>
            <a href="{{ route('admin.peppol.dashboard') }}" class="btn btn-secondary">
                ← Retour au dashboard
            </a>
        </div>

        <!-- Info Alert -->
        <div class="bg-primary-50 dark:bg-primary-900/20 border-l-4 border-primary-500 p-4 rounded-r-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-primary-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm text-primary-700 dark:text-primary-300">
                        <strong>Architecture SaaS:</strong> Vous configurez UNE SEULE API key ici, et tous vos clients l'utilisent automatiquement.
                        Vos clients ne voient jamais les credentials - ils voient uniquement leur quota et usage.
                    </p>
                </div>
            </div>
        </div>

        <!-- Configuration Form -->
        <form action="{{ route('admin.peppol.settings.update') }}" method="POST" class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm">
            @csrf

            <div class="p-6 space-y-6">
                <!-- Provider Selection -->
                <div>
                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                        Provider Peppol
                    </label>
                    <select name="provider" id="provider" class="input" required>
                        @foreach($providers as $key => $provider)
                            <option value="{{ $key }}" {{ $settings['provider'] === $key ? 'selected' : '' }}>
                                {{ $provider['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-secondary-500 mt-1">
                        Recommandé: Recommand.eu (open source, pricing volume-based)
                    </p>
                </div>

                <!-- Plan Selection -->
                <div>
                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                        Plan Provider
                    </label>
                    <select name="plan" id="plan" class="input" required>
                        @foreach($availablePlans as $key => $plan)
                            <option value="{{ $key }}" {{ $settings['plan'] === $key ? 'selected' : '' }}>
                                {{ $plan['name'] }} - €{{ $plan['monthly_cost'] }}/mois ({{ $plan['included_documents'] }} docs inclus)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-secondary-500 mt-1">
                        Démarrez avec FREE (€0/mois, 25 docs gratuits)
                    </p>
                </div>

                <!-- API Key -->
                <div>
                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                        API Key Globale
                    </label>
                    <input
                        type="text"
                        name="api_key"
                        value="{{ $settings['api_key'] }}"
                        class="input font-mono text-sm"
                        placeholder="sk_live_..."
                    >
                    <p class="text-xs text-secondary-500 mt-1">
                        Obtenez votre API key sur le dashboard de votre provider
                    </p>
                </div>

                <!-- API Secret -->
                <div>
                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                        API Secret
                    </label>
                    <input
                        type="password"
                        name="api_secret"
                        value="{{ $settings['api_secret'] }}"
                        class="input font-mono text-sm"
                        placeholder="••••••••"
                    >
                    <p class="text-xs text-secondary-500 mt-1">
                        Stocké de manière sécurisée (encrypted)
                    </p>
                </div>

                <!-- Test Mode -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="test_mode"
                        id="test_mode"
                        value="1"
                        {{ $settings['test_mode'] ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 border-secondary-300 rounded focus:ring-primary-500"
                    >
                    <label for="test_mode" class="ml-2 block text-sm text-secondary-700 dark:text-secondary-300">
                        Mode Test (Sandbox)
                    </label>
                </div>

                <!-- Enabled -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="enabled"
                        id="enabled"
                        value="1"
                        {{ $settings['enabled'] ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 border-secondary-300 rounded focus:ring-primary-500"
                    >
                    <label for="enabled" class="ml-2 block text-sm text-secondary-700 dark:text-secondary-300">
                        Peppol activé
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-700/50 border-t border-secondary-200 dark:border-secondary-700 rounded-b-xl flex justify-between">
                <form action="{{ route('admin.peppol.test') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tester la Connexion
                    </button>
                </form>

                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Enregistrer la Configuration
                </button>
            </div>
        </form>

        <!-- Guide de Démarrage -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">
                🚀 Guide de Démarrage Rapide
            </h3>
            <div class="space-y-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600 font-semibold">
                        1
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Démarrez GRATUIT</div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">
                            Sélectionnez "Recommand.eu" et plan "Free". Pas besoin d'API key pour commencer ! (0-25 factures gratuites)
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600 font-semibold">
                        2
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Obtenez l'API key (quand nécessaire)</div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">
                            Allez sur <a href="https://recommand.eu" target="_blank" class="text-primary-600 hover:underline">recommand.eu</a>, créez un compte, et copiez votre API key ici.
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600 font-semibold">
                        3
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Testez la connexion</div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">
                            Cliquez sur "Tester la Connexion" pour vérifier que tout fonctionne.
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center text-success-600 font-semibold">
                        ✓
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">C'est tout !</div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">
                            Vos clients peuvent maintenant envoyer des factures Peppol. Vous serez alerté quand upgrader le plan.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Info -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">
                💰 Pricing Recommand.eu
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="border border-secondary-200 dark:border-secondary-700 rounded-lg p-4">
                    <div class="text-sm text-secondary-600 dark:text-secondary-400">Free</div>
                    <div class="text-2xl font-bold text-secondary-900 dark:text-white my-2">€0</div>
                    <div class="text-xs text-secondary-600">25 docs/mois</div>
                    <div class="text-xs text-secondary-500 mt-1">puis €0.30/doc</div>
                </div>
                <div class="border border-secondary-200 dark:border-secondary-700 rounded-lg p-4">
                    <div class="text-sm text-secondary-600 dark:text-secondary-400">Starter</div>
                    <div class="text-2xl font-bold text-secondary-900 dark:text-white my-2">€29</div>
                    <div class="text-xs text-secondary-600">200 docs/mois</div>
                    <div class="text-xs text-secondary-500 mt-1">puis €0.20/doc</div>
                </div>
                <div class="border border-primary-200 dark:border-primary-700 rounded-lg p-4 bg-primary-50 dark:bg-primary-900/20">
                    <div class="text-sm text-primary-600">Professional ⭐</div>
                    <div class="text-2xl font-bold text-primary-600 my-2">€99</div>
                    <div class="text-xs text-primary-600">1000 docs/mois</div>
                    <div class="text-xs text-primary-500 mt-1">puis €0.10/doc</div>
                </div>
                <div class="border border-secondary-200 dark:border-secondary-700 rounded-lg p-4">
                    <div class="text-sm text-secondary-600 dark:text-secondary-400">Enterprise</div>
                    <div class="text-2xl font-bold text-secondary-900 dark:text-white my-2">Sur mesure</div>
                    <div class="text-xs text-secondary-600">10000+ docs/mois</div>
                    <div class="text-xs text-secondary-500 mt-1">Contactez-les</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
