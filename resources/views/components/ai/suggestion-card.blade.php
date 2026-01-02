@props([
    'suggestion',
    'dismissible' => true,
])

@php
    $priorityColors = [
        'critical' => 'border-danger-500 bg-danger-50 dark:bg-danger-900/20',
        'high' => 'border-warning-500 bg-warning-50 dark:bg-warning-900/20',
        'medium' => 'border-info-500 bg-info-50 dark:bg-info-900/20',
        'low' => 'border-secondary-300 bg-secondary-50 dark:bg-secondary-800/20',
    ];

    $iconColors = [
        'critical' => 'text-danger-600',
        'high' => 'text-warning-600',
        'medium' => 'text-info-600',
        'low' => 'text-secondary-600',
    ];

    $borderColor = $priorityColors[$suggestion['priority']] ?? $priorityColors['low'];
    $iconColor = $iconColors[$suggestion['priority']] ?? $iconColors['low'];
@endphp

<div
    x-data="{ open: true, processing: false }"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="border-l-4 rounded-r-lg p-4 {{ $borderColor }} relative"
    data-suggestion-id="{{ $suggestion['id'] }}"
>
    <!-- Dismiss Button -->
    @if($dismissible)
        <button
            @click="open = false"
            class="absolute top-2 right-2 text-secondary-400 hover:text-secondary-600 transition-colors"
            title="Ignorer cette suggestion"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif

    <div class="flex items-start gap-4 pr-8">
        <!-- Icon -->
        <div class="flex-shrink-0 mt-1">
            <div class="w-10 h-10 rounded-full bg-white dark:bg-secondary-800 flex items-center justify-center shadow-sm">
                @if($suggestion['icon'] === 'mail')
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                @elseif($suggestion['icon'] === 'calendar')
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                @elseif($suggestion['icon'] === 'alert-triangle')
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                @elseif($suggestion['icon'] === 'tag')
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                @elseif($suggestion['icon'] === 'alert-circle')
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                @elseif($suggestion['icon'] === 'trending-down')
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                    </svg>
                @elseif($suggestion['icon'] === 'trending-up')
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                    </svg>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <!-- Header -->
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-sm font-semibold text-secondary-900 dark:text-white">
                    {{ $suggestion['title'] }}
                </h3>
                @if(isset($suggestion['type']))
                    <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full
                        {{ $suggestion['type'] === 'alert' ? 'bg-danger-100 text-danger-800' : '' }}
                        {{ $suggestion['type'] === 'action' ? 'bg-primary-100 text-primary-800' : '' }}
                        {{ $suggestion['type'] === 'recommendation' ? 'bg-info-100 text-info-800' : '' }}
                        {{ $suggestion['type'] === 'warning' ? 'bg-warning-100 text-warning-800' : '' }}
                    ">
                        {{ ucfirst($suggestion['type']) }}
                    </span>
                @endif
            </div>

            <!-- Description -->
            <p class="text-sm text-secondary-700 dark:text-secondary-300 mb-3">
                {{ $suggestion['description'] }}
            </p>

            <!-- Actions -->
            @if(isset($suggestion['actions']) && !empty($suggestion['actions']))
                <div class="flex flex-wrap gap-2">
                    @foreach($suggestion['actions'] as $action)
                        <button
                            @click="processing = true; executeSuggestionAction('{{ $action['action'] }}', {{ json_encode($action['params'] ?? []) }})"
                            :disabled="processing"
                            class="btn btn-{{ $action['style'] ?? 'secondary' }} btn-sm"
                        >
                            <span x-show="!processing">{{ $action['label'] }}</span>
                            <span x-show="processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement...
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.executeSuggestionAction = async function(action, params) {
        try {
            const response = await fetch('{{ route("ai.assistant.execute") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ action, params })
            });

            const data = await response.json();

            if (data.success) {
                window.showToast(data.message || 'Action exécutée avec succès', 'success');

                // Handle redirect
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }

                // Refresh page if needed
                if (data.refresh) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                window.showToast(data.message || 'Erreur lors de l\'exécution', 'error');
            }
        } catch (error) {
            console.error('Suggestion action error:', error);
            window.showToast('Erreur lors de l\'exécution de l\'action', 'error');
        }
    };
</script>
@endpush
