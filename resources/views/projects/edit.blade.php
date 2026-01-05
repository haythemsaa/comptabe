@extends('layouts.app')

@section('title', 'Modifier le projet')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm mb-2">
            <a href="{{ route('projects.index') }}" class="text-secondary-500 hover:text-primary-600">Projets</a>
            <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('projects.show', $project) }}" class="text-secondary-500 hover:text-primary-600">{{ $project->reference ?? $project->name }}</a>
            <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-secondary-900 dark:text-white">Modifier</span>
        </nav>
        <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Modifier le projet</h1>
    </div>

    <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Informations générales -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Informations générales</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="name" class="label">Nom du projet <span class="text-danger-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $project->name) }}" required class="input @error('name') border-danger-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reference" class="label">Référence</label>
                    <input type="text" id="reference" name="reference" value="{{ old('reference', $project->reference) }}" class="input">
                </div>

                <div>
                    <label for="partner_id" class="label">Client</label>
                    <select id="partner_id" name="partner_id" class="input">
                        <option value="">-- Sélectionner un client --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id', $project->partner_id) == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="label">Description</label>
                    <textarea id="description" name="description" rows="3" class="input">{{ old('description', $project->description) }}</textarea>
                </div>

                <div>
                    <label for="status" class="label">Statut <span class="text-danger-500">*</span></label>
                    <select id="status" name="status" required class="input">
                        @foreach(\App\Models\Project::STATUSES as $key => $config)
                            <option value="{{ $key }}" {{ old('status', $project->status) == $key ? 'selected' : '' }}>{{ $config['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="label">Priorité <span class="text-danger-500">*</span></label>
                    <select id="priority" name="priority" required class="input">
                        @foreach(\App\Models\Project::PRIORITIES as $key => $config)
                            <option value="{{ $key }}" {{ old('priority', $project->priority) == $key ? 'selected' : '' }}>{{ $config['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="manager_id" class="label">Chef de projet</label>
                    <select id="manager_id" name="manager_id" class="input">
                        <option value="">-- Sélectionner --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id', $project->manager_id) == $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="color" class="label">Couleur</label>
                    <input type="color" id="color" name="color" value="{{ old('color', $project->color ?? '#3B82F6') }}" class="input h-10">
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Planification</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="label">Date de début prévue</label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="input">
                </div>

                <div>
                    <label for="end_date" class="label">Date de fin prévue</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}" class="input">
                </div>

                <div>
                    <label for="actual_start_date" class="label">Date de début réelle</label>
                    <input type="date" id="actual_start_date" name="actual_start_date" value="{{ old('actual_start_date', $project->actual_start_date?->format('Y-m-d')) }}" class="input">
                </div>

                <div>
                    <label for="actual_end_date" class="label">Date de fin réelle</label>
                    <input type="date" id="actual_end_date" name="actual_end_date" value="{{ old('actual_end_date', $project->actual_end_date?->format('Y-m-d')) }}" class="input">
                </div>

                <div>
                    <label for="estimated_hours" class="label">Heures estimées</label>
                    <input type="number" id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours', $project->estimated_hours) }}" min="0" class="input">
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
                            <option value="{{ $key }}" {{ old('billing_type', $project->billing_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="budget" class="label">Budget (€)</label>
                    <input type="number" id="budget" name="budget" value="{{ old('budget', $project->budget) }}" step="0.01" min="0" class="input">
                </div>

                <div>
                    <label for="hourly_rate" class="label">Taux horaire (€/h)</label>
                    <input type="number" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate', $project->hourly_rate) }}" step="0.01" min="0" class="input">
                </div>
            </div>
        </div>

        <!-- Équipe -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Équipe du projet</h2>

            @php
                $currentMembers = $project->members->pluck('id')->toArray();
            @endphp

            <div class="space-y-2">
                @foreach($users as $user)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-secondary-200 dark:border-secondary-700 hover:bg-secondary-50 dark:hover:bg-dark-200 cursor-pointer transition">
                        <input type="checkbox" name="members[]" value="{{ $user->id }}"
                            {{ in_array($user->id, old('members', $currentMembers)) ? 'checked' : '' }}
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
            <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>
@endsection
