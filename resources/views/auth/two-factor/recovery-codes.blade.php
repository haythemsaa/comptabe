<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-500 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="card">
                <div class="card-body space-y-6">
                    <!-- Header -->
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">
                            @if(isset($regenerated) && $regenerated)
                                Nouveaux codes de récupération
                            @else
                                2FA activée avec succès !
                            @endif
                        </h2>
                        <p class="mt-2 text-secondary-600 dark:text-secondary-400">
                            Conservez ces codes de récupération en lieu sûr
                        </p>
                    </div>

                    <!-- Warning -->
                    <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="text-sm text-warning-700 dark:text-warning-300">
                                <p class="font-medium">Important !</p>
                                <p class="mt-1">Ces codes ne seront plus affichés. Gardez-les dans un endroit sûr comme un gestionnaire de mots de passe.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recovery Codes -->
                    <div class="bg-secondary-50 dark:bg-secondary-800 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($recoveryCodes as $code)
                                <code class="px-3 py-2 bg-white dark:bg-secondary-900 rounded text-center font-mono text-sm">
                                    {{ $code }}
                                </code>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button type="button"
                                onclick="copyRecoveryCodes()"
                                class="btn btn-secondary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copier
                        </button>
                        <button type="button"
                                onclick="downloadRecoveryCodes()"
                                class="btn btn-secondary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Télécharger
                        </button>
                    </div>

                    <!-- Continue -->
                    <a href="{{ isset($showBackLink) && $showBackLink ? route('settings.security') : route('dashboard') }}"
                       class="btn btn-primary w-full">
                        @if(isset($showBackLink) && $showBackLink)
                            Retour aux paramètres
                        @else
                            Continuer vers le tableau de bord
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const recoveryCodes = @json($recoveryCodes);

        function copyRecoveryCodes() {
            const text = recoveryCodes.join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('Codes copiés dans le presse-papiers !');
            });
        }

        function downloadRecoveryCodes() {
            const text = 'ComptaBE - Codes de récupération 2FA\n' +
                         '=====================================\n\n' +
                         recoveryCodes.join('\n') +
                         '\n\n⚠️ Gardez ces codes en lieu sûr !';

            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'comptabe-recovery-codes.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
</x-guest-layout>
