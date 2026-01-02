@extends('layouts.app')

@section('title', 'Connecter une banque')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('openbanking.index') }}" class="p-2 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Connecter une banque belge
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Sélectionnez votre banque pour établir une connexion sécurisée
            </p>
        </div>
    </div>

    <!-- Banques disponibles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($supportedBanks as $bank)
            <a href="{{ route('openbanking.connect', $bank['id']) }}"
               class="group block bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg border-2 border-transparent hover:border-primary-500 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-secondary-100 dark:bg-secondary-700 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <img src="{{ $bank['logo'] }}"
                             alt="{{ $bank['name'] }}"
                             class="w-10 h-10"
                             onerror="this.onerror=null;this.parentElement.innerHTML='<svg class=\'w-8 h-8 text-secondary-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/></svg>';">
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white group-hover:text-primary-600 transition-colors">
                            {{ $bank['name'] }}
                        </h3>
                        <p class="text-sm text-secondary-500">BIC: {{ $bank['bic'] }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-secondary-500">Cliquez pour connecter</span>
                    <svg class="w-5 h-5 text-secondary-400 group-hover:text-primary-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Process -->
    <x-card>
        <x-slot:header>
            <h3 class="font-semibold text-secondary-900 dark:text-white">
                Comment ça fonctionne?
            </h3>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 mx-auto bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-3">
                    <span class="text-primary-600 font-bold">1</span>
                </div>
                <h4 class="font-medium text-secondary-900 dark:text-white mb-1">Sélectionnez</h4>
                <p class="text-sm text-secondary-500">Choisissez votre banque dans la liste ci-dessus</p>
            </div>

            <div class="text-center">
                <div class="w-12 h-12 mx-auto bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-3">
                    <span class="text-primary-600 font-bold">2</span>
                </div>
                <h4 class="font-medium text-secondary-900 dark:text-white mb-1">Authentifiez</h4>
                <p class="text-sm text-secondary-500">Connectez-vous à votre espace bancaire personnel</p>
            </div>

            <div class="text-center">
                <div class="w-12 h-12 mx-auto bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-3">
                    <span class="text-primary-600 font-bold">3</span>
                </div>
                <h4 class="font-medium text-secondary-900 dark:text-white mb-1">Autorisez</h4>
                <p class="text-sm text-secondary-500">Donnez votre consentement pour accéder à vos comptes</p>
            </div>

            <div class="text-center">
                <div class="w-12 h-12 mx-auto bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-3">
                    <span class="text-primary-600 font-bold">4</span>
                </div>
                <h4 class="font-medium text-secondary-900 dark:text-white mb-1">Synchronisez</h4>
                <p class="text-sm text-secondary-500">Vos transactions sont automatiquement importées</p>
            </div>
        </div>
    </x-card>

    <!-- FAQ -->
    <x-card>
        <x-slot:header>
            <h3 class="font-semibold text-secondary-900 dark:text-white">
                Questions fréquentes
            </h3>
        </x-slot:header>

        <div x-data="{ open: null }" class="divide-y divide-secondary-200 dark:divide-secondary-700">
            <div class="py-4">
                <button @click="open = open === 1 ? null : 1"
                        class="flex items-center justify-between w-full text-left">
                    <span class="font-medium text-secondary-900 dark:text-white">
                        Mes données sont-elles sécurisées?
                    </span>
                    <svg class="w-5 h-5 text-secondary-500 transition-transform" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse class="mt-2 text-secondary-600 dark:text-secondary-400">
                    Absolument. Nous utilisons le protocole PSD2 réglementé par l'Union Européenne. Vos identifiants bancaires ne transitent jamais par nos serveurs - vous vous authentifiez directement auprès de votre banque. Les données sont chiffrées de bout en bout.
                </div>
            </div>

            <div class="py-4">
                <button @click="open = open === 2 ? null : 2"
                        class="flex items-center justify-between w-full text-left">
                    <span class="font-medium text-secondary-900 dark:text-white">
                        Puis-je révoquer l'accès?
                    </span>
                    <svg class="w-5 h-5 text-secondary-500 transition-transform" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse class="mt-2 text-secondary-600 dark:text-secondary-400">
                    Oui, vous pouvez révoquer l'accès à tout moment depuis cette application ou directement depuis votre espace bancaire en ligne. Le consentement expire automatiquement après 90 jours (conformément à PSD2).
                </div>
            </div>

            <div class="py-4">
                <button @click="open = open === 3 ? null : 3"
                        class="flex items-center justify-between w-full text-left">
                    <span class="font-medium text-secondary-900 dark:text-white">
                        Quelles données sont accessibles?
                    </span>
                    <svg class="w-5 h-5 text-secondary-500 transition-transform" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse class="mt-2 text-secondary-600 dark:text-secondary-400">
                    Uniquement les informations nécessaires: liste des comptes, soldes et historique des transactions. Nous n'avons pas accès à vos identifiants, mots de passe ou à la possibilité de faire des virements sans votre autorisation explicite.
                </div>
            </div>

            <div class="py-4">
                <button @click="open = open === 4 ? null : 4"
                        class="flex items-center justify-between w-full text-left">
                    <span class="font-medium text-secondary-900 dark:text-white">
                        Pourquoi dois-je me reconnecter tous les 90 jours?
                    </span>
                    <svg class="w-5 h-5 text-secondary-500 transition-transform" :class="open === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 4" x-collapse class="mt-2 text-secondary-600 dark:text-secondary-400">
                    C'est une mesure de sécurité imposée par la directive PSD2. Le consentement d'accès aux données bancaires expire automatiquement après 90 jours pour garantir que vous gardez le contrôle sur qui accède à vos données.
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection
