<x-admin-layout>
    <x-slot name="title">Détail du Log</x-slot>
    <x-slot name="header">Détail du Log d'Audit</x-slot>

    <div class="max-w-4xl">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-{{ $log->action_color }}-500/20 flex items-center justify-center">
                        <span class="w-3 h-3 rounded-full bg-{{ $log->action_color }}-400"></span>
                    </div>
                    <div>
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $log->action_color }}-500/20 text-{{ $log->action_color }}-400">
                            {{ ucfirst($log->action) }}
                        </span>
                        <p class="text-secondary-400 text-sm mt-1">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Description</h3>
                <p class="text-secondary-300">{{ $log->description }}</p>
            </div>

            <div class="grid grid-cols-2 gap-6 border-t border-secondary-700 pt-6">
                <div>
                    <h4 class="text-sm text-secondary-400 mb-2">Utilisateur</h4>
                    @if($log->user)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full {{ $log->user->is_superadmin ? 'bg-danger-500' : 'bg-primary-500/20' }} flex items-center justify-center font-bold {{ $log->user->is_superadmin ? 'text-white' : 'text-primary-400' }}">
                                {{ $log->user->initials }}
                            </div>
                            <div>
                                <a href="{{ route('admin.users.show', $log->user) }}" class="font-medium text-white hover:text-primary-400">
                                    {{ $log->user->full_name }}
                                </a>
                                <p class="text-sm text-secondary-500">{{ $log->user->email }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-secondary-500">Système</p>
                    @endif
                </div>

                <div>
                    <h4 class="text-sm text-secondary-400 mb-2">Entreprise</h4>
                    @if($log->company)
                        <a href="{{ route('admin.companies.show', $log->company) }}" class="font-medium text-white hover:text-primary-400">
                            {{ $log->company->name }}
                        </a>
                    @else
                        <p class="text-secondary-500">-</p>
                    @endif
                </div>

                <div>
                    <h4 class="text-sm text-secondary-400 mb-2">Adresse IP</h4>
                    <p class="font-mono">{{ $log->ip_address ?? '-' }}</p>
                </div>

                <div>
                    <h4 class="text-sm text-secondary-400 mb-2">User Agent</h4>
                    <p class="text-sm text-secondary-300 truncate" title="{{ $log->user_agent }}">{{ $log->user_agent ?? '-' }}</p>
                </div>

                @if($log->model_type)
                    <div>
                        <h4 class="text-sm text-secondary-400 mb-2">Type de Modèle</h4>
                        <p class="font-mono text-sm">{{ class_basename($log->model_type) }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm text-secondary-400 mb-2">ID du Modèle</h4>
                        <p class="font-mono text-sm">{{ $log->model_id }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($log->old_values || $log->new_values)
            <div class="grid grid-cols-2 gap-6 mb-6">
                @if($log->old_values)
                    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                        <h3 class="font-semibold mb-4 text-danger-400">Anciennes Valeurs</h3>
                        <pre class="text-sm text-secondary-300 bg-secondary-900 rounded-lg p-4 overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                @if($log->new_values)
                    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                        <h3 class="font-semibold mb-4 text-success-400">Nouvelles Valeurs</h3>
                        <pre class="text-sm text-secondary-300 bg-secondary-900 rounded-lg p-4 overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        @endif

        @if($log->metadata)
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
                <h3 class="font-semibold mb-4">Métadonnées</h3>
                <pre class="text-sm text-secondary-300 bg-secondary-900 rounded-lg p-4 overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-2 text-secondary-400 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux logs
        </a>
    </div>
</x-admin-layout>
