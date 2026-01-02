<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-500 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="card">
                <div class="card-body space-y-6">
                    <!-- Header -->
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">
                            Authentification à deux facteurs
                        </h2>
                        <p class="mt-2 text-secondary-600 dark:text-secondary-400">
                            Scannez le QR code avec votre application d'authentification
                        </p>
                    </div>

                    <!-- QR Code -->
                    <div class="flex justify-center p-4 bg-white rounded-lg border border-secondary-200">
                        <div id="qrcode" class="w-48 h-48 flex items-center justify-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}"
                                 alt="QR Code 2FA"
                                 class="w-48 h-48">
                        </div>
                    </div>

                    <!-- Manual Entry -->
                    <div class="text-center">
                        <p class="text-sm text-secondary-500 mb-2">Ou entrez ce code manuellement :</p>
                        <div class="flex items-center justify-center gap-2">
                            <code class="px-4 py-2 bg-secondary-100 dark:bg-secondary-800 rounded-lg font-mono text-lg tracking-wider">
                                {{ $secret }}
                            </code>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $secret }}')"
                                    class="p-2 text-secondary-400 hover:text-primary-500 transition-colors"
                                    title="Copier">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Verification Form -->
                    <form method="POST" action="{{ route('2fa.enable') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="code" class="form-label">Code de vérification</label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   class="form-input text-center text-2xl tracking-widest font-mono @error('code') form-input-error @enderror"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   placeholder="000000"
                                   required
                                   autofocus>
                            @error('code')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <a href="{{ route('settings.security') }}" class="btn btn-secondary flex-1">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary flex-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Activer
                            </button>
                        </div>
                    </form>

                    <!-- Info -->
                    <div class="p-4 bg-info-50 dark:bg-info-900/20 rounded-lg">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-info-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-info-700 dark:text-info-300">
                                <p class="font-medium">Applications recommandées :</p>
                                <ul class="mt-1 list-disc list-inside">
                                    <li>Google Authenticator</li>
                                    <li>Microsoft Authenticator</li>
                                    <li>Authy</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
