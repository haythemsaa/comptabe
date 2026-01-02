<x-app-layout>
    <x-slot name="title">Paramètres - Peppol</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Paramètres Peppol</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Paramètres</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Configurez votre entreprise et vos préférences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:w-64 flex-shrink-0">
                <nav class="space-y-1">
                    <a href="{{ route('settings.company') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Entreprise
                    </a>
                    <a href="{{ route('settings.peppol') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Peppol
                    </a>
                    <a href="{{ route('settings.invoices') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Facturation
                    </a>
                    <a href="{{ route('settings.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        Utilisateurs
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <!-- 2026 Alert -->
                <div class="card bg-gradient-to-r from-primary-500 to-primary-600 text-white">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">Obligation 2026 - Facturation B2B</h3>
                                <p class="mt-1 text-primary-100">
                                    À partir du 1er janvier 2026, toutes les entreprises belges assujetties à la TVA devront envoyer et recevoir leurs factures B2B via le réseau Peppol au format UBL 2.1.
                                </p>
                                <a href="https://finances.belgium.be/fr/entreprises/tva/declaration-tva/factures-electroniques-b2b" target="_blank" class="inline-flex items-center gap-1 mt-3 text-sm font-medium hover:underline">
                                    En savoir plus
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Statut Peppol</h2>
                    </div>
                    <div class="card-body">
                        @if($company->peppol_registered)
                            <div class="flex items-center gap-4 p-4 bg-success-50 dark:bg-success-900/20 rounded-xl">
                                <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center text-success-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-success-900 dark:text-success-100">Enregistré sur Peppol</h3>
                                    <p class="text-sm text-success-700 dark:text-success-300">
                                        Votre entreprise est prête à envoyer et recevoir des factures électroniques.
                                    </p>
                                    @if($company->peppol_registered_at)
                                        <p class="text-xs text-success-600 mt-1">
                                            Depuis le @dateFormat($company->peppol_registered_at)
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-4 p-4 bg-warning-50 dark:bg-warning-900/20 rounded-xl">
                                <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center text-warning-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-warning-900 dark:text-warning-100">Non enregistré</h3>
                                    <p class="text-sm text-warning-700 dark:text-warning-300">
                                        Configurez votre accès Peppol pour être conforme à l'obligation 2026.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Configuration Form -->
                <form action="{{ route('settings.peppol.update') }}" method="POST" x-data="peppolForm()">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Configuration Peppol</h2>
                        </div>
                        <div class="card-body space-y-6">
                            <!-- Peppol Identifier -->
                            <div>
                                <label for="peppol_id" class="form-label">Identifiant Peppol (Participant ID)</label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        id="peppol_id"
                                        name="peppol_id"
                                        value="{{ old('peppol_id', $company->peppol_id) }}"
                                        class="form-input font-mono @error('peppol_id') form-input-error @enderror"
                                        placeholder="0208:0123456789"
                                    >
                                    @if($company->vat_number && !$company->peppol_id)
                                        <button type="button" onclick="document.getElementById('peppol_id').value = '0208:{{ preg_replace('/[^0-9]/', '', $company->vat_number) }}'" class="btn btn-secondary btn-sm whitespace-nowrap">
                                            Générer
                                        </button>
                                    @endif
                                </div>
                                @error('peppol_id')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                                <p class="form-helper">
                                    Format: scheme:identifier. Pour la Belgique, le scheme est 0208 (numéro d'entreprise à 10 chiffres).
                                </p>
                            </div>

                            <!-- Access Point Provider -->
                            <div>
                                <label for="peppol_provider" class="form-label">Access Point (fournisseur)</label>
                                <select name="peppol_provider" id="peppol_provider" class="form-select" x-model="provider">
                                    <option value="">Sélectionner un fournisseur...</option>
                                    @foreach(\App\Services\PeppolService::getProviders() as $key => $providerInfo)
                                        <option value="{{ $key }}" {{ old('peppol_provider', $company->peppol_provider) === $key ? 'selected' : '' }}>
                                            {{ $providerInfo['name'] }} - {{ $providerInfo['description'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="form-helper">
                                    Votre Access Point est le fournisseur certifié qui vous connecte au réseau Peppol.
                                </p>
                            </div>

                            <hr class="border-secondary-200 dark:border-secondary-700">

                            <h3 class="font-medium text-secondary-900 dark:text-white">Identifiants API</h3>
                            <p class="text-sm text-secondary-500 mb-4">Ces informations sont fournies par votre Access Point lors de votre inscription.</p>

                            <!-- API Key -->
                            <div>
                                <label for="peppol_api_key" class="form-label">Clé API</label>
                                <div class="relative">
                                    <input
                                        :type="showApiKey ? 'text' : 'password'"
                                        id="peppol_api_key"
                                        name="peppol_api_key"
                                        value="{{ old('peppol_api_key', $company->peppol_api_key) }}"
                                        class="form-input font-mono pr-10"
                                        placeholder="sk_live_xxxxxxxx"
                                    >
                                    <button type="button" @click="showApiKey = !showApiKey" class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600">
                                        <svg x-show="!showApiKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showApiKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="form-helper">
                                    Clé API (Bearer token) fournie par l'Access Point pour l'authentification.
                                </p>
                            </div>

                            <!-- API Secret -->
                            <div>
                                <label for="peppol_api_secret" class="form-label">Secret API (optionnel)</label>
                                <input
                                    type="password"
                                    id="peppol_api_secret"
                                    name="peppol_api_secret"
                                    value="{{ old('peppol_api_secret', $company->peppol_api_secret) }}"
                                    class="form-input font-mono"
                                    placeholder="secret_xxxxxxxx"
                                >
                                <p class="form-helper">
                                    Certains Access Points demandent un secret en plus de la clé API.
                                </p>
                            </div>

                            <!-- Test Mode -->
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    id="peppol_test_mode"
                                    name="peppol_test_mode"
                                    value="1"
                                    {{ old('peppol_test_mode', $company->peppol_test_mode ?? true) ? 'checked' : '' }}
                                    class="form-checkbox"
                                >
                                <label for="peppol_test_mode" class="text-sm text-secondary-700 dark:text-secondary-300">
                                    Mode test (sandbox) - utiliser l'environnement de test de l'Access Point
                                </label>
                            </div>

                            <hr class="border-secondary-200 dark:border-secondary-700">

                            <!-- Webhook URL -->
                            <div>
                                <label class="form-label">URL Webhook (réception de factures)</label>
                                @if($company->peppol_webhook_secret)
                                    <div class="flex gap-2">
                                        <input
                                            type="text"
                                            id="webhookUrl"
                                            value="{{ url('/api/webhooks/peppol/' . $company->peppol_webhook_secret) }}"
                                            class="form-input font-mono text-sm bg-secondary-50 dark:bg-secondary-800"
                                            readonly
                                        >
                                        <button type="button" @click="copyWebhook()" class="btn btn-secondary btn-sm" title="Copier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <p class="text-sm text-secondary-500 italic">
                                        L'URL webhook sera générée après avoir sélectionné un fournisseur et enregistré.
                                    </p>
                                @endif
                                <p class="form-helper">
                                    Configurez cette URL dans le tableau de bord de votre Access Point pour recevoir les factures entrantes automatiquement.
                                </p>
                            </div>

                            <!-- Test Connection -->
                            <div class="pt-4 flex items-center gap-4">
                                <button type="button" @click="testConnection()" class="btn btn-outline-primary" :disabled="testing">
                                    <svg x-show="!testing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <svg x-show="testing" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span x-text="testing ? 'Test en cours...' : 'Tester la connexion'"></span>
                                </button>
                                <span x-show="testResult" :class="testSuccess ? 'text-success-600' : 'text-danger-600'" class="text-sm font-medium" x-text="testResult"></span>
                            </div>

                            @if($company->peppol_connected_at)
                                <p class="text-sm text-success-600">
                                    Dernière connexion réussie: {{ $company->peppol_connected_at->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>

                        <div class="card-footer flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>

                @push('scripts')
                <script>
                    function peppolForm() {
                        return {
                            provider: '{{ old('peppol_provider', $company->peppol_provider) }}',
                            showApiKey: false,
                            testing: false,
                            testResult: '',
                            testSuccess: false,

                            copyWebhook() {
                                const url = document.getElementById('webhookUrl')?.value;
                                if (url) {
                                    navigator.clipboard.writeText(url);
                                    this.testResult = 'URL copiée!';
                                    this.testSuccess = true;
                                    setTimeout(() => this.testResult = '', 2000);
                                }
                            },

                            async testConnection() {
                                this.testing = true;
                                this.testResult = '';

                                try {
                                    const response = await fetch('{{ route('settings.peppol.test') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json',
                                        },
                                    });

                                    const data = await response.json();
                                    this.testSuccess = data.success;
                                    this.testResult = data.message;
                                } catch (error) {
                                    this.testSuccess = false;
                                    this.testResult = 'Erreur de connexion';
                                } finally {
                                    this.testing = false;
                                }
                            }
                        };
                    }
                </script>
                @endpush

                <!-- Help Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Comment s'enregistrer sur Peppol ?</h2>
                    </div>
                    <div class="card-body">
                        <ol class="space-y-4">
                            <li class="flex gap-4">
                                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600 font-bold text-sm flex-shrink-0">1</div>
                                <div>
                                    <h4 class="font-medium text-secondary-900 dark:text-white">Choisir un Access Point</h4>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                        Sélectionnez un fournisseur certifié Peppol (Access Point) qui vous connectera au réseau.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600 font-bold text-sm flex-shrink-0">2</div>
                                <div>
                                    <h4 class="font-medium text-secondary-900 dark:text-white">S'enregistrer</h4>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                        Créez un compte chez l'Access Point choisi et fournissez vos informations d'entreprise.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600 font-bold text-sm flex-shrink-0">3</div>
                                <div>
                                    <h4 class="font-medium text-secondary-900 dark:text-white">Configurer</h4>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                        Entrez votre identifiant Peppol et les informations de connexion dans cette page.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="w-8 h-8 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center text-success-600 font-bold text-sm flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-secondary-900 dark:text-white">Prêt !</h4>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                        Vous pouvez maintenant envoyer et recevoir des factures via Peppol.
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
