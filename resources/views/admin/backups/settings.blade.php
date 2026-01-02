<x-admin-layout>
    <x-slot name="title">Paramètres Backup</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.backups.index') }}" class="p-2 hover:bg-secondary-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span>Paramètres des Backups Automatiques</span>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Current Settings Info -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-bold text-white mb-4">Configuration Actuelle</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-secondary-400">Backups Automatiques</dt>
                    <dd class="text-white font-medium">
                        @if($settings['auto_backup_enabled'])
                            <span class="px-2 py-1 text-xs rounded-full bg-success-500/20 text-success-400">Activés</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-danger-500/20 text-danger-400">Désactivés</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-secondary-400">Type de Backup</dt>
                    <dd class="text-white font-medium">{{ ucfirst($settings['auto_backup_type']) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-secondary-400">Fréquence</dt>
                    <dd class="text-white font-medium">
                        @if($settings['auto_backup_frequency'] === 'hourly')
                            Toutes les heures
                        @elseif($settings['auto_backup_frequency'] === 'daily')
                            Quotidien
                        @else
                            Hebdomadaire
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-secondary-400">Heure d'exécution</dt>
                    <dd class="text-white font-medium">{{ $settings['auto_backup_time'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-secondary-400">Rétention</dt>
                    <dd class="text-white font-medium">{{ $settings['auto_backup_retention_days'] }} jours</dd>
                </div>
            </dl>
        </div>

        <!-- Settings Form -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-bold text-white mb-6">Modifier les Paramètres</h3>

            <form action="{{ route('admin.backups.update-settings') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Enable/Disable Auto Backup -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_backup_enabled" value="1" {{ $settings['auto_backup_enabled'] ? 'checked' : '' }} class="rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                        <div>
                            <p class="text-white font-medium">Activer les backups automatiques</p>
                            <p class="text-sm text-secondary-400">Les backups seront créés automatiquement selon la fréquence définie</p>
                        </div>
                    </label>
                </div>

                <div class="border-t border-secondary-700 pt-6">
                    <!-- Backup Type -->
                    <div class="mb-6">
                        <label for="auto_backup_type" class="block text-sm font-medium text-secondary-300 mb-2">Type de Backup</label>
                        <select id="auto_backup_type" name="auto_backup_type" required class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                            <option value="database" {{ $settings['auto_backup_type'] === 'database' ? 'selected' : '' }}>Base de données uniquement</option>
                            <option value="files" {{ $settings['auto_backup_type'] === 'files' ? 'selected' : '' }}>Fichiers uniquement</option>
                            <option value="full" {{ $settings['auto_backup_type'] === 'full' ? 'selected' : '' }}>Complet (Database + Fichiers)</option>
                        </select>
                        <p class="mt-1 text-sm text-secondary-400">
                            <strong>Database:</strong> Rapide, idéal pour sauvegardes fréquentes<br>
                            <strong>Files:</strong> Sauvegarde les documents et fichiers uploadés<br>
                            <strong>Complet:</strong> Recommandé pour backups de sécurité (plus lent)
                        </p>
                    </div>

                    <!-- Frequency -->
                    <div class="mb-6">
                        <label for="auto_backup_frequency" class="block text-sm font-medium text-secondary-300 mb-2">Fréquence</label>
                        <select id="auto_backup_frequency" name="auto_backup_frequency" required class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                            <option value="hourly" {{ $settings['auto_backup_frequency'] === 'hourly' ? 'selected' : '' }}>Toutes les heures</option>
                            <option value="daily" {{ $settings['auto_backup_frequency'] === 'daily' ? 'selected' : '' }}>Quotidien</option>
                            <option value="weekly" {{ $settings['auto_backup_frequency'] === 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                        </select>
                        <p class="mt-1 text-sm text-secondary-400">
                            <strong>Hourly:</strong> Uniquement pour database (recommandé pour données critiques)<br>
                            <strong>Daily:</strong> Recommandé pour la plupart des cas<br>
                            <strong>Weekly:</strong> Pour backups complets si espace limité
                        </p>
                    </div>

                    <!-- Backup Time -->
                    <div class="mb-6">
                        <label for="auto_backup_time" class="block text-sm font-medium text-secondary-300 mb-2">Heure d'exécution (pour backups quotidiens/hebdomadaires)</label>
                        <input type="time" id="auto_backup_time" name="auto_backup_time" value="{{ $settings['auto_backup_time'] }}" required class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <p class="mt-1 text-sm text-secondary-400">
                            Choisissez une heure de faible activité (recommandé: 02:00 - 04:00)
                        </p>
                    </div>

                    <!-- Retention Days -->
                    <div class="mb-6">
                        <label for="auto_backup_retention_days" class="block text-sm font-medium text-secondary-300 mb-2">Durée de rétention (jours)</label>
                        <input type="number" id="auto_backup_retention_days" name="auto_backup_retention_days" value="{{ $settings['auto_backup_retention_days'] }}" min="1" max="365" required class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <p class="mt-1 text-sm text-secondary-400">
                            Les backups plus anciens seront automatiquement supprimés (1-365 jours)<br>
                            <strong>Recommandé:</strong> 7 jours (hourly), 30 jours (daily), 90 jours (weekly)
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-secondary-700">
                    <a href="{{ route('admin.backups.index') }}" class="px-6 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        Annuler
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                        Enregistrer les Paramètres
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mt-6">
            <h3 class="text-lg font-bold text-white mb-4">Configuration du Scheduler</h3>
            <div class="prose prose-invert max-w-none text-sm">
                <p class="text-secondary-300 mb-4">
                    Pour que les backups automatiques fonctionnent, vous devez configurer le scheduler Laravel sur votre serveur:
                </p>

                <div class="bg-secondary-900 rounded-lg p-4 mb-4">
                    <p class="text-secondary-400 text-xs mb-2">Ajoutez cette ligne au crontab (Linux/Mac):</p>
                    <code class="text-primary-400 font-mono">* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1</code>
                </div>

                <div class="bg-secondary-900 rounded-lg p-4 mb-4">
                    <p class="text-secondary-400 text-xs mb-2">Windows Task Scheduler:</p>
                    <code class="text-primary-400 font-mono">php C:\laragon\www\compta\artisan schedule:run</code>
                    <p class="text-secondary-400 text-xs mt-2">À exécuter toutes les minutes</p>
                </div>

                <p class="text-secondary-300 mt-4">
                    <strong>Note:</strong> Les backups automatiques sont créés en arrière-plan via Laravel Queue.
                    Assurez-vous que le queue worker est actif: <code class="text-primary-400">php artisan queue:work</code>
                </p>
            </div>
        </div>
    </div>
</x-admin-layout>
