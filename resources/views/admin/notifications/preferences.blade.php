<x-admin-layout>
    <x-slot name="title">Préférences Notifications</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Préférences de Notifications</span>
            <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                ← Retour aux notifications
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <form action="{{ route('admin.notifications.update-preferences') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Types de Notifications</h3>
                <p class="text-sm text-secondary-400 mb-6">Choisissez les types d'événements pour lesquels vous souhaitez recevoir des notifications.</p>

                <div class="space-y-4">
                    <!-- Critical Errors -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-danger-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Erreurs Critiques</p>
                                <p class="text-sm text-secondary-500">Recevoir des notifications pour les erreurs système critiques</p>
                            </div>
                        </div>
                        <input type="checkbox" name="critical_errors" value="1" {{ $preferences['critical_errors'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>

                    <!-- New Company -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Nouvelle Entreprise</p>
                                <p class="text-sm text-secondary-500">Notifier lors de la création d'une nouvelle entreprise</p>
                            </div>
                        </div>
                        <input type="checkbox" name="new_company" value="1" {{ $preferences['new_company'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>

                    <!-- New User -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-success-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Nouvel Utilisateur</p>
                                <p class="text-sm text-secondary-500">Notifier lors de la création d'un nouvel utilisateur</p>
                            </div>
                        </div>
                        <input type="checkbox" name="new_user" value="1" {{ $preferences['new_user'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>

                    <!-- System Alerts -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-warning-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Alertes Système</p>
                                <p class="text-sm text-secondary-500">Recevoir les alertes de performance et de santé système</p>
                            </div>
                        </div>
                        <input type="checkbox" name="system_alerts" value="1" {{ $preferences['system_alerts'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>

                    <!-- Failed Jobs -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-danger-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Jobs Échoués</p>
                                <p class="text-sm text-secondary-500">Notifier des jobs en file d'attente qui échouent</p>
                            </div>
                        </div>
                        <input type="checkbox" name="failed_jobs" value="1" {{ $preferences['failed_jobs'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>

                    <!-- Revenue Milestones -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-success-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Jalons de Revenus</p>
                                <p class="text-sm text-secondary-500">Notifier lorsque des seuils de revenus sont atteints</p>
                            </div>
                        </div>
                        <input type="checkbox" name="revenue_milestones" value="1" {{ $preferences['revenue_milestones'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Canaux de Notification</h3>
                <p class="text-sm text-secondary-400 mb-6">Choisissez comment vous souhaitez recevoir vos notifications.</p>

                <div class="space-y-4">
                    <!-- Email Notifications -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Notifications Email</p>
                                <p class="text-sm text-secondary-500">Recevoir également les notifications par email</p>
                            </div>
                        </div>
                        <input type="checkbox" name="email_notifications" value="1" {{ $preferences['email_notifications'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>

                    <!-- Browser Notifications -->
                    <label class="flex items-center justify-between p-4 bg-secondary-900 rounded-lg cursor-pointer hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Notifications Navigateur</p>
                                <p class="text-sm text-secondary-500">Afficher les notifications dans le navigateur</p>
                            </div>
                        </div>
                        <input type="checkbox" name="browser_notifications" value="1" {{ $preferences['browser_notifications'] ? 'checked' : '' }} class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </label>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                    Enregistrer les Préférences
                </button>
                <a href="{{ route('admin.notifications.index') }}" class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors">
                    Annuler
                </a>
            </div>
        </form>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">Test de Notification</h3>
            <p class="text-sm text-secondary-400 mb-4">Envoyez-vous une notification de test pour vérifier que tout fonctionne correctement.</p>
            <form action="{{ route('admin.notifications.send-test') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Envoyer Notification Test
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
