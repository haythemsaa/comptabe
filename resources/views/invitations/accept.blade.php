<x-guest-layout>
    <x-slot name="title">Invitation - {{ $invitation->company->name }}</x-slot>

    <div class="animate-fade-in-up">
        <!-- Mobile Logo -->
        <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
            <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-secondary-900">ComptaBE</h1>
            </div>
        </div>

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">Invitation</h2>
            <p class="mt-2 text-secondary-600 dark:text-secondary-400">
                {{ $invitation->inviter->full_name }} vous invite a rejoindre
            </p>
            <p class="mt-1 text-lg font-semibold text-primary-600">{{ $invitation->company->name }}</p>
            <p class="mt-1 text-sm text-secondary-500">en tant que {{ $invitation->role_label }}</p>
        </div>

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('invitations.accept.store', $invitation->token) }}" class="space-y-6">
            @csrf

            @if($user)
                {{-- User is already logged in --}}
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <p class="text-green-800">
                        Vous etes connecte en tant que <strong>{{ $user->email }}</strong>
                    </p>
                    <p class="text-sm text-green-600 mt-1">
                        Cliquez sur "Accepter" pour rejoindre l'entreprise.
                    </p>
                </div>

            @elseif($existingUser)
                {{-- User exists but not logged in - need password --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center mb-4">
                    <p class="text-blue-800">
                        Un compte existe deja pour <strong>{{ $invitation->email }}</strong>
                    </p>
                    <p class="text-sm text-blue-600 mt-1">
                        Entrez votre mot de passe pour vous connecter et rejoindre l'entreprise.
                    </p>
                </div>

                <div>
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="relative" x-data="{ show: false }">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password"
                            name="password"
                            required
                            class="form-input @error('password') form-input-error @enderror pr-12"
                            placeholder="Votre mot de passe"
                        >
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-secondary-400 hover:text-secondary-600"
                        >
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

            @else
                {{-- New user - create account --}}
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center mb-4">
                    <p class="text-gray-800">
                        Creez votre compte pour rejoindre l'entreprise
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        Email: <strong>{{ $invitation->email }}</strong>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="form-label">Prenom</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            required
                            class="form-input @error('first_name') form-input-error @enderror"
                            placeholder="Votre prenom"
                        >
                        @error('first_name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="form-label">Nom</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            required
                            class="form-input @error('last_name') form-input-error @enderror"
                            placeholder="Votre nom"
                        >
                        @error('last_name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="relative" x-data="{ show: false }">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password"
                            name="password"
                            required
                            class="form-input @error('password') form-input-error @enderror pr-12"
                            placeholder="Minimum 8 caracteres"
                        >
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-secondary-400 hover:text-secondary-600"
                        >
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="form-input"
                        placeholder="Repetez votre mot de passe"
                    >
                </div>
            @endif

            <div>
                <button type="submit" class="btn btn-primary w-full justify-center text-base py-3">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Accepter l'invitation
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-secondary-500">
                Cette invitation expire le {{ $invitation->expires_at->format('d/m/Y a H:i') }}
            </p>
        </div>

        @if($user)
            <div class="mt-4 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-secondary-600 hover:text-secondary-900 underline">
                        Se connecter avec un autre compte
                    </button>
                </form>
            </div>
        @else
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-primary-600 hover:text-primary-700">
                    Deja un compte? Connectez-vous d'abord
                </a>
            </div>
        @endif
    </div>
</x-guest-layout>
