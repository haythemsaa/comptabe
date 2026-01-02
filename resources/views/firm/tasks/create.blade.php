<x-firm-layout>
    <x-slot name="title">Nouvelle tache</x-slot>
    <x-slot name="header">Creer une tache</x-slot>

    <div class="max-w-3xl">
        <form action="{{ route('firm.tasks.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Client Selection -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-medium text-white mb-4">Client</h3>

                <div>
                    <label for="client_mandate_id" class="block text-sm font-medium text-secondary-300 mb-2">Client *</label>
                    <select name="client_mandate_id" id="client_mandate_id" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Selectionner un client --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_mandate_id', $mandate?->id) == $client->id ? 'selected' : '' }}>
                                {{ $client->company->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_mandate_id')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Task Details -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-medium text-white mb-4">Details de la tache</h3>

                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-secondary-300 mb-2">Titre *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500"
                            placeholder="ex: Declaration TVA Q4 2024">
                        @error('title')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-secondary-300 mb-2">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Description detaillee de la tache...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Task Type -->
                        <div>
                            <label for="task_type" class="block text-sm font-medium text-secondary-300 mb-2">Type de tache *</label>
                            <select name="task_type" id="task_type" required
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                                @foreach(\App\Models\MandateTask::TYPE_LABELS as $value => $label)
                                    <option value="{{ $value }}" {{ old('task_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('task_type')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-secondary-300 mb-2">Priorite *</label>
                            <select name="priority" id="priority" required
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Basse</option>
                                <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normale</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Haute</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Fiscal Year -->
                        <div>
                            <label for="fiscal_year" class="block text-sm font-medium text-secondary-300 mb-2">Annee fiscale</label>
                            <select name="fiscal_year" id="fiscal_year"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Aucune --</option>
                                @for($year = now()->year + 1; $year >= now()->year - 5; $year--)
                                    <option value="{{ $year }}" {{ old('fiscal_year', now()->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Period -->
                        <div>
                            <label for="period" class="block text-sm font-medium text-secondary-300 mb-2">Periode</label>
                            <input type="text" name="period" id="period" value="{{ old('period') }}"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500"
                                placeholder="ex: Q4, Janvier, Annuel...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planning -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-medium text-white mb-4">Planification</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-secondary-300 mb-2">Date d'echeance</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @error('due_date')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reminder Date -->
                    <div>
                        <label for="reminder_date" class="block text-sm font-medium text-secondary-300 mb-2">Date de rappel</label>
                        <input type="date" name="reminder_date" id="reminder_date" value="{{ old('reminder_date') }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @error('reminder_date')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assigned To -->
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-secondary-300 mb-2">Assigne a</label>
                        <select name="assigned_to" id="assigned_to"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Non assigne --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Estimated Hours -->
                    <div>
                        <label for="estimated_hours" class="block text-sm font-medium text-secondary-300 mb-2">Heures estimees</label>
                        <input type="number" name="estimated_hours" id="estimated_hours" value="{{ old('estimated_hours') }}"
                            step="0.25" min="0"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500"
                            placeholder="ex: 2.5">
                    </div>
                </div>

                <!-- Is Billable -->
                <div class="mt-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_billable" value="1" {{ old('is_billable') ? 'checked' : '' }}
                            class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                        <span class="text-secondary-300">Tache facturable</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('firm.tasks.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-white transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors">
                    Creer la tache
                </button>
            </div>
        </form>
    </div>
</x-firm-layout>
