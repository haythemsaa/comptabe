<x-app-layout>
    <x-slot name="title">Paramètres E-Reporting</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('ereporting.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">E-Reporting</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Paramètres</span>
    @endsection

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Paramètres E-Reporting</h1>
            <p class="text-secondary-500 dark:text-secondary-400 mt-1">Configurez la connexion au système e-Reporting belge (SPF Finances)</p>
        </div>

        <!-- Information Banner -->
        <div class="card p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">Module en préparation</h4>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                        Le système e-Reporting belge sera disponible à partir de 2028. Ce module vous permet de préparer votre infrastructure
                        et de tester en mode sandbox dès maintenant.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('ereporting.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-800 dark:text-white">Configuration</h3>
                </div>
                <div class="card-body space-y-6">
                    <!-- Enable E-Reporting -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-secondary-700 dark:text-white">Activer l'e-Reporting</label>
                            <p class="text-sm text-secondary-500 dark:text-secondary-400">Active la soumission automatique au SPF Finances (5ème coin)</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="ereporting_enabled" value="0">
                            <input type="checkbox" name="ereporting_enabled" value="1" class="sr-only peer" {{ $company->ereporting_enabled ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <!-- Test Mode -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-secondary-700 dark:text-white">Mode test (sandbox)</label>
                            <p class="text-sm text-secondary-500 dark:text-secondary-400">Utilisez l'environnement de test au lieu de la production</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="ereporting_test_mode" value="0">
                            <input type="checkbox" name="ereporting_test_mode" value="1" class="sr-only peer" {{ $company->ereporting_test_mode ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <hr class="border-secondary-200 dark:border-secondary-700">

                    <!-- API Key -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-white mb-2">Clé API SPF Finances</label>
                        <input type="password" name="ereporting_api_key" value="{{ $company->ereporting_api_key }}"
                               class="form-input w-full"
                               placeholder="Votre clé API e-Reporting">
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mt-1">
                            La clé API sera fournie par le SPF Finances lors de l'inscription au programme e-Reporting.
                        </p>
                    </div>

                    <!-- Certificate ID -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 dark:text-white mb-2">ID Certificat</label>
                        <input type="text" name="ereporting_certificate_id" value="{{ $company->ereporting_certificate_id }}"
                               class="form-input w-full"
                               placeholder="Identifiant du certificat numérique">
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mt-1">
                            L'identifiant de votre certificat numérique d'entreprise pour l'authentification.
                        </p>
                    </div>
                </div>
                <div class="card-footer flex justify-end gap-3">
                    <a href="{{ route('ereporting.index') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </form>

        <!-- 5-Corner Model Explanation -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white">Le modèle 5 coins</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-center">
                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-blue-100 dark:bg-blue-800 flex items-center justify-center">
                            <span class="text-xl font-bold text-blue-600 dark:text-blue-400">1</span>
                        </div>
                        <p class="text-sm font-medium text-secondary-800 dark:text-white">Vendeur</p>
                        <p class="text-xs text-secondary-500">Votre entreprise</p>
                    </div>
                    <div class="p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-purple-100 dark:bg-purple-800 flex items-center justify-center">
                            <span class="text-xl font-bold text-purple-600 dark:text-purple-400">2</span>
                        </div>
                        <p class="text-sm font-medium text-secondary-800 dark:text-white">Access Point</p>
                        <p class="text-xs text-secondary-500">Fournisseur Peppol</p>
                    </div>
                    <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                            <span class="text-xl font-bold text-green-600 dark:text-green-400">5</span>
                        </div>
                        <p class="text-sm font-medium text-secondary-800 dark:text-white">Gouvernement</p>
                        <p class="text-xs text-secondary-500">SPF Finances</p>
                    </div>
                    <div class="p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-purple-100 dark:bg-purple-800 flex items-center justify-center">
                            <span class="text-xl font-bold text-purple-600 dark:text-purple-400">3</span>
                        </div>
                        <p class="text-sm font-medium text-secondary-800 dark:text-white">Access Point</p>
                        <p class="text-xs text-secondary-500">Destinataire Peppol</p>
                    </div>
                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-blue-100 dark:bg-blue-800 flex items-center justify-center">
                            <span class="text-xl font-bold text-blue-600 dark:text-blue-400">4</span>
                        </div>
                        <p class="text-sm font-medium text-secondary-800 dark:text-white">Acheteur</p>
                        <p class="text-xs text-secondary-500">Client B2B</p>
                    </div>
                </div>
                <p class="text-sm text-secondary-500 dark:text-secondary-400 mt-4 text-center">
                    Dans le modèle 5 coins, chaque facture B2B est simultanément envoyée via Peppol (coins 1-4) et déclarée au gouvernement (coin 5).
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
