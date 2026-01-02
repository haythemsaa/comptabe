<x-admin-layout>
    <x-slot name="title">Modifier {{ $user->full_name }}</x-slot>
    <x-slot name="header">Modifier l'Utilisateur</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 space-y-6">
                <h2 class="text-lg font-semibold border-b border-secondary-700 pb-4">Informations Personnelles</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-secondary-300 mb-2">Prénom</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" required>
                        @error('first_name')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-secondary-300 mb-2">Nom</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" required>
                        @error('last_name')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-secondary-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" required>
                    @error('email')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-secondary-300 mb-2">Nouveau mot de passe <span class="text-secondary-500">(laisser vide pour garder l'actuel)</span></label>
                    <input type="password" name="password" id="password" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                    @error('password')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-secondary-300 mb-2">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 space-y-6">
                <h2 class="text-lg font-semibold border-b border-secondary-700 pb-4">Affectation</h2>

                @if($user->companies->count() > 0)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-secondary-300 mb-2">Entreprises actuelles</label>
                        <div class="space-y-2">
                            @foreach($user->companies as $userCompany)
                                <div class="flex items-center justify-between p-3 bg-secondary-700 rounded-lg">
                                    <span class="font-medium">{{ $userCompany->name }}</span>
                                    <span class="px-2 py-1 bg-primary-500/20 text-primary-400 rounded text-sm">
                                        {{ ucfirst($userCompany->pivot->role) }}
                                        @if($userCompany->pivot->is_default) (défaut) @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label for="company_id" class="block text-sm font-medium text-secondary-300 mb-2">Ajouter/Modifier une entreprise</label>
                    <select name="company_id" id="company_id" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Sélectionner une entreprise --</option>
                        @foreach($companies as $company)
                            @php
                                $existingRole = $user->companies->firstWhere('id', $company->id)?->pivot->role;
                            @endphp
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                                @if($existingRole) (actuel: {{ ucfirst($existingRole) }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company_role" class="block text-sm font-medium text-secondary-300 mb-2">Rôle dans l'entreprise</label>
                    <select name="company_role" id="company_role" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @foreach($companyRoles as $value => $label)
                            <option value="{{ $value }}" {{ old('company_role', 'user') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-secondary-400">
                        Le rôle détermine les permissions de l'utilisateur dans l'entreprise sélectionnée.
                    </p>
                    @error('company_role')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 space-y-6">
                <h2 class="text-lg font-semibold border-b border-secondary-700 pb-4">Privilèges Spéciaux</h2>

                @if($user->id !== auth()->id())
                    <label class="flex items-center gap-3 p-4 bg-danger-500/10 border border-danger-500/30 rounded-xl cursor-pointer">
                        <input type="checkbox" name="is_superadmin" value="1" {{ old('is_superadmin', $user->is_superadmin) ? 'checked' : '' }} class="text-danger-500 focus:ring-danger-500 bg-secondary-600 border-secondary-500 rounded">
                        <div>
                            <span class="font-medium text-danger-400">Superadmin</span>
                            <p class="text-sm text-secondary-400">Accès au panneau d'administration global</p>
                        </div>
                    </label>
                @else
                    <div class="p-4 bg-secondary-700 rounded-xl">
                        <p class="text-secondary-400">Vous ne pouvez pas modifier vos propres privilèges superadmin.</p>
                    </div>
                @endif

                <label class="flex items-center gap-3 p-4 bg-secondary-700 rounded-xl cursor-pointer">
                    <input type="checkbox" name="email_verified" value="1" {{ old('email_verified', $user->email_verified_at) ? 'checked' : '' }} class="text-primary-500 focus:ring-primary-500 bg-secondary-600 border-secondary-500 rounded">
                    <div>
                        <span class="font-medium">Email vérifié</span>
                        <p class="text-sm text-secondary-400">
                            @if($user->email_verified_at)
                                Vérifié le {{ $user->email_verified_at->format('d/m/Y H:i') }}
                            @else
                                Marquer l'email comme vérifié
                            @endif
                        </p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('admin.users.show', $user) }}" class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
