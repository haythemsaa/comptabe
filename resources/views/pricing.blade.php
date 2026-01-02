<x-layouts.public>
    <x-slot name="title">Tarifs</x-slot>
    <x-slot name="description">Decouvrez nos offres de comptabilite belge. Essai gratuit de 14 jours, sans engagement.</x-slot>

    <!-- Hero Section -->
    <section class="py-20 bg-gradient-to-b from-primary-50 to-white dark:from-secondary-900 dark:to-secondary-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Des tarifs simples et transparents
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-8">
                Choisissez le plan adapte a votre entreprise. Tous les plans incluent un essai gratuit de 14 jours.
            </p>

            <!-- Billing Toggle -->
            <div class="flex items-center justify-center gap-4 mb-12">
                <span class="text-gray-600 dark:text-gray-400 font-medium" id="monthly-label">Mensuel</span>
                <button type="button" id="billing-toggle"
                    class="relative inline-flex h-7 w-14 items-center rounded-full bg-gray-200 dark:bg-secondary-700 transition-colors"
                    role="switch" aria-checked="false">
                    <span class="sr-only">Facturation annuelle</span>
                    <span id="toggle-dot" class="inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition-transform translate-x-1"></span>
                </button>
                <span class="text-gray-600 dark:text-gray-400 font-medium" id="yearly-label">
                    Annuel
                    <span class="ml-2 px-2 py-0.5 bg-success-100 dark:bg-success-500/20 text-success-700 dark:text-success-400 text-xs font-semibold rounded-full">-20%</span>
                </span>
            </div>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="py-12 -mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($plans), 4) }} gap-8">
                @foreach($plans as $plan)
                    <div class="relative bg-white dark:bg-secondary-800 rounded-2xl shadow-xl border-2 {{ $plan->is_popular ? 'border-primary-500' : 'border-gray-100 dark:border-secondary-700' }} overflow-hidden transition-all hover:shadow-2xl hover:-translate-y-1">
                        @if($plan->is_popular)
                            <div class="absolute top-0 left-0 right-0 bg-primary-500 text-white text-center text-sm font-semibold py-2">
                                Le plus populaire
                            </div>
                        @endif

                        <div class="{{ $plan->is_popular ? 'pt-12' : 'pt-8' }} px-8 pb-8">
                            <!-- Plan Header -->
                            <div class="text-center mb-8">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $plan->name }}</h3>
                                <p class="text-gray-600 dark:text-gray-400">{{ $plan->description }}</p>

                                <div class="mt-6">
                                    <!-- Monthly Price -->
                                    <div class="price-monthly {{ $plan->price_monthly == 0 ? '' : '' }}">
                                        @if($plan->price_monthly == 0)
                                            <span class="text-5xl font-bold text-gray-900 dark:text-white">Gratuit</span>
                                        @else
                                            <span class="text-5xl font-bold text-gray-900 dark:text-white">{{ number_format($plan->price_monthly, 0) }}</span>
                                            <span class="text-xl text-gray-500 dark:text-gray-400">EUR/mois</span>
                                        @endif
                                    </div>
                                    <!-- Yearly Price (hidden by default) -->
                                    <div class="price-yearly hidden">
                                        @if($plan->price_yearly == 0)
                                            <span class="text-5xl font-bold text-gray-900 dark:text-white">Gratuit</span>
                                        @else
                                            <span class="text-5xl font-bold text-gray-900 dark:text-white">{{ number_format($plan->price_yearly / 12, 0) }}</span>
                                            <span class="text-xl text-gray-500 dark:text-gray-400">EUR/mois</span>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                facture {{ number_format($plan->price_yearly, 0) }} EUR/an
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <a href="{{ route('register') }}?plan={{ $plan->slug }}"
                                class="block w-full py-3 px-6 text-center font-semibold rounded-xl transition-colors {{ $plan->is_popular ? 'bg-primary-500 hover:bg-primary-600 text-white' : 'bg-gray-100 dark:bg-secondary-700 hover:bg-gray-200 dark:hover:bg-secondary-600 text-gray-900 dark:text-white' }}">
                                @if($plan->trial_days > 0)
                                    Essai gratuit {{ $plan->trial_days }} jours
                                @elseif($plan->price_monthly == 0)
                                    Commencer gratuitement
                                @else
                                    Choisir ce plan
                                @endif
                            </a>

                            <!-- Features List -->
                            <div class="mt-8 space-y-4">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Inclus :</div>

                                <!-- Limits -->
                                <ul class="space-y-3">
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $plan->max_invoices_per_month == -1 ? 'Factures illimitees' : $plan->max_invoices_per_month . ' factures/mois' }}</span>
                                    </li>
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $plan->max_clients == -1 ? 'Clients illimites' : $plan->max_clients . ' clients' }}</span>
                                    </li>
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $plan->max_users == -1 ? 'Utilisateurs illimites' : $plan->max_users . ' utilisateur(s)' }}</span>
                                    </li>
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                        <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $plan->max_products == -1 ? 'Produits illimites' : $plan->max_products . ' produits' }}</span>
                                    </li>
                                </ul>

                                <!-- Features -->
                                <div class="pt-4 border-t border-gray-100 dark:border-secondary-700 space-y-3">
                                    @if($plan->feature_peppol)
                                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Facturation Peppol</span>
                                        </li>
                                    @else
                                        <li class="flex items-center gap-3 text-gray-400 dark:text-gray-500">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Facturation Peppol</span>
                                        </li>
                                    @endif

                                    @if($plan->feature_recurring_invoices)
                                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Factures recurrentes</span>
                                        </li>
                                    @else
                                        <li class="flex items-center gap-3 text-gray-400 dark:text-gray-500">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Factures recurrentes</span>
                                        </li>
                                    @endif

                                    @if($plan->feature_quotes)
                                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Devis et bons de commande</span>
                                        </li>
                                    @else
                                        <li class="flex items-center gap-3 text-gray-400 dark:text-gray-500">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Devis et bons de commande</span>
                                        </li>
                                    @endif

                                    @if($plan->feature_api_access)
                                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Acces API</span>
                                        </li>
                                    @else
                                        <li class="flex items-center gap-3 text-gray-400 dark:text-gray-500">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Acces API</span>
                                        </li>
                                    @endif

                                    @if($plan->feature_priority_support)
                                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Support prioritaire</span>
                                        </li>
                                    @else
                                        <li class="flex items-center gap-3 text-gray-400 dark:text-gray-500">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Support prioritaire</span>
                                        </li>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="py-20 bg-gray-50 dark:bg-secondary-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Tout ce dont vous avez besoin
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    ComptaBE integre toutes les fonctionnalites pour gerer votre comptabilite belge en toute conformite.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Facturation Peppol</h3>
                    <p class="text-gray-600 dark:text-gray-400">Envoyez et recevez des factures electroniques via le reseau Peppol, conforme a la legislation belge 2026.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Import CODA</h3>
                    <p class="text-gray-600 dark:text-gray-400">Importez automatiquement vos releves bancaires CODA et reconciliez vos paiements en quelques clics.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-warning-100 dark:bg-warning-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Declarations TVA</h3>
                    <p class="text-gray-600 dark:text-gray-400">Generez vos declarations TVA periodiques et soumettez-les directement a Intervat.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Multi-utilisateurs</h3>
                    <p class="text-gray-600 dark:text-gray-400">Collaborez avec votre equipe et votre comptable en temps reel avec des droits d'acces granulaires.</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-rose-100 dark:bg-rose-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Tableaux de bord</h3>
                    <p class="text-gray-600 dark:text-gray-400">Visualisez vos performances financieres avec des tableaux de bord clairs et personnalisables.</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg">
                    <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Securite maximale</h3>
                    <p class="text-gray-600 dark:text-gray-400">Vos donnees sont chiffrees et sauvegardees quotidiennement sur des serveurs europeens.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Questions frequentes
                </h2>
            </div>

            <div class="space-y-4">
                <details class="bg-white dark:bg-secondary-800 rounded-xl shadow-lg overflow-hidden group">
                    <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        Puis-je changer de plan a tout moment ?
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                        Oui, vous pouvez passer a un plan superieur a tout moment. Le changement prend effet immediatement et le montant est calcule au prorata.
                    </div>
                </details>

                <details class="bg-white dark:bg-secondary-800 rounded-xl shadow-lg overflow-hidden group">
                    <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        Comment fonctionne l'essai gratuit ?
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                        L'essai gratuit vous donne acces a toutes les fonctionnalites du plan choisi pendant 14 jours, sans engagement et sans carte bancaire requise.
                    </div>
                </details>

                <details class="bg-white dark:bg-secondary-800 rounded-xl shadow-lg overflow-hidden group">
                    <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        Mes donnees sont-elles securisees ?
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                        Absolument. Vos donnees sont chiffrees en transit et au repos. Nous effectuons des sauvegardes quotidiennes sur des serveurs situes en Europe, conformement au RGPD.
                    </div>
                </details>

                <details class="bg-white dark:bg-secondary-800 rounded-xl shadow-lg overflow-hidden group">
                    <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        Qu'est-ce que Peppol ?
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                        Peppol est le reseau europeen de facturation electronique. A partir de 2026, toutes les entreprises belges devront utiliser Peppol pour leurs factures B2B. ComptaBE vous permet d'etre conforme des maintenant.
                    </div>
                </details>

                <details class="bg-white dark:bg-secondary-800 rounded-xl shadow-lg overflow-hidden group">
                    <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-900 dark:text-white flex items-center justify-between">
                        Puis-je annuler mon abonnement ?
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-4 text-gray-600 dark:text-gray-400">
                        Oui, vous pouvez annuler votre abonnement a tout moment depuis votre espace client. Vous conservez l'acces jusqu'a la fin de votre periode de facturation.
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Pret a simplifier votre comptabilite ?
            </h2>
            <p class="text-xl text-primary-100 mb-8">
                Rejoignez des centaines d'entreprises belges qui font confiance a ComptaBE.
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-primary-600 font-semibold rounded-xl hover:bg-primary-50 transition-colors">
                Commencer l'essai gratuit
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </section>

    <!-- JavaScript for billing toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('billing-toggle');
            const dot = document.getElementById('toggle-dot');
            const monthlyPrices = document.querySelectorAll('.price-monthly');
            const yearlyPrices = document.querySelectorAll('.price-yearly');
            let isYearly = false;

            toggle.addEventListener('click', function() {
                isYearly = !isYearly;

                if (isYearly) {
                    toggle.classList.add('bg-primary-500');
                    toggle.classList.remove('bg-gray-200', 'dark:bg-secondary-700');
                    dot.classList.add('translate-x-8');
                    dot.classList.remove('translate-x-1');
                    monthlyPrices.forEach(el => el.classList.add('hidden'));
                    yearlyPrices.forEach(el => el.classList.remove('hidden'));
                } else {
                    toggle.classList.remove('bg-primary-500');
                    toggle.classList.add('bg-gray-200', 'dark:bg-secondary-700');
                    dot.classList.remove('translate-x-8');
                    dot.classList.add('translate-x-1');
                    monthlyPrices.forEach(el => el.classList.remove('hidden'));
                    yearlyPrices.forEach(el => el.classList.add('hidden'));
                }
            });
        });
    </script>
</x-layouts.public>
