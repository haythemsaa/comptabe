@extends('layouts.app')

@section('title', 'Nouveau Projet')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm mb-2">
            <a href="{{ route('projects.index') }}" class="text-secondary-500 hover:text-primary-600">Projets</a>
            <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-secondary-900 dark:text-white">Nouveau Projet</span>
        </nav>
        <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Créer un nouveau projet</h1>
    </div>

    <form action="{{ route('projects.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Informations générales -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Informations générales</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="name" class="label">Nom du projet <span class="text-danger-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="input @error('name') border-danger-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reference" class="label">Référence</label>
                    <input type="text" id="reference" name="reference" value="{{ old('reference', $reference) }}" class="input" placeholder="PRJ-2026-0001">
                </div>

                <div>
                    <label for="partner_id" class="label">Client</label>
                    <select id="partner_id" name="partner_id" class="input">
                        <option value="">-- Sélectionner un client --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="label">Description</label>
                    <textarea id="description" name="description" rows="3" class="input">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="status" class="label">Statut <span class="text-danger-500">*</span></label>
                    <select id="status" name="status" required class="input">
                        @foreach(\App\Models\Project::STATUSES as $key => $config)
                            <option value="{{ $key }}" {{ old('status', 'draft') == $key ? 'selected' : '' }}>{{ $config['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="label">Priorité <span class="text-danger-500">*</span></label>
                    <select id="priority" name="priority" required class="input">
                        @foreach(\App\Models\Project::PRIORITIES as $key => $config)
                            <option value="{{ $key }}" {{ old('priority', 'medium') == $key ? 'selected' : '' }}>{{ $config['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="manager_id" class="label">Chef de projet</label>
                    <select id="manager_id" name="manager_id" class="input">
                        <option value="">-- Sélectionner --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="color" class="label">Couleur</label>
                    <input type="color" id="color" name="color" value="{{ old('color', '#3B82F6') }}" class="input h-10">
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Planification</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="label">Date de début</label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="input">
                </div>

                <div>
                    <label for="end_date" class="label">Date de fin prévue</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="input">
                </div>

                <div>
                    <label for="estimated_hours" class="label">Heures estimées</label>
                    <input type="number" id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours') }}" min="0" class="input" placeholder="40">
                </div>
            </div>
        </div>

        <!-- Budget et facturation -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Budget et facturation</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="billing_type" class="label">Type de facturation <span class="text-danger-500">*</span></label>
                    <select id="billing_type" name="billing_type" required class="input">
                        @foreach(\App\Models\Project::BILLING_TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('billing_type', 'time_materials') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="budget" class="label">Budget (€)</label>
                    <input type="number" id="budget" name="budget" value="{{ old('budget') }}" step="0.01" min="0" class="input" placeholder="0.00">
                </div>

                <div>
                    <label for="hourly_rate" class="label">Taux horaire (€/h)</label>
                    <input type="number" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate') }}" step="0.01" min="0" class="input" placeholder="75.00">
                </div>
            </div>
        </div>

        <!-- Équipe -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Équipe du projet</h2>

            <div class="space-y-2">
                @foreach($users as $user)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-secondary-200 dark:border-secondary-700 hover:bg-secondary-50 dark:hover:bg-dark-200 cursor-pointer transition">
                        <input type="checkbox" name="members[]" value="{{ $user->id }}"
                            {{ in_array($user->id, old('members', [])) ? 'checked' : '' }}
                            class="w-4 h-4 text-primary-600 rounded border-secondary-300 focus:ring-primary-500">
                        <div class="flex-1">
                            <span class="text-secondary-900 dark:text-white font-medium">{{ $user->full_name }}</span>
                            <span class="text-sm text-secondary-500 ml-2">{{ $user->email }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer le projet</button>
        </div>
    </form>
</div>
@endsection
