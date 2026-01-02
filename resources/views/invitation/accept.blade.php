<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-white mb-2">Accepter l'invitation</h1>
                    <p class="text-secondary-400">
                        Vous avez été invité par <strong>{{ $invitation->invitedBy?->name ?? 'ComptaBE' }}</strong>
                    </p>
                </div>

                <!-- Invitation Details -->
                <div class="bg-secondary-900/50 rounded-lg p-4 mb-6">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Email</span>
                            <span class="text-white">{{ $invitation->email }}</span>
                        </div>
                        @if($invitation->company)
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Entreprise</span>
                            <span class="text-white">{{ $invitation->company->name }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Rôle</span>
                            <span class="text-white capitalize">{{ $invitation->role }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Expire le</span>
                            <span class="text-warning-400">{{ $invitation->expires_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('invitation.accept', $invitation->token) }}" method="POST">
                    @csrf

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-secondary-300 mb-2">
                            Nom complet *
                        </label>
                        <input type="text" name="name" id="name" required
                               class="input w-full bg-secondary-900 border-secondary-600 text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500"
                               value="{{ old('name', $invitation->user->name) }}"
                               placeholder="Votre nom complet">
                        @error('name')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-secondary-300 mb-2">
                            Mot de passe *
                        </label>
                        <input type="password" name="password" id="password" required
                               class="input w-full bg-secondary-900 border-secondary-600 text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500"
                               placeholder="Minimum 8 caractères">
                        @error('password')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-secondary-300 mb-2">
                            Confirmer le mot de passe *
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="input w-full bg-secondary-900 border-secondary-600 text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500"
                               placeholder="Confirmez votre mot de passe">
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-full">
                        Accepter et créer mon compte
                    </button>
                </form>

                <!-- Info -->
                <p class="text-center text-secondary-500 text-sm mt-6">
                    En acceptant, vous acceptez nos
                    <a href="#" class="text-primary-400 hover:text-primary-300">conditions d'utilisation</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
