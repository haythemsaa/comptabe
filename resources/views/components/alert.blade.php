@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $typeConfig = [
        'info' => [
            'bg' => 'bg-info-50 dark:bg-info-900/20',
            'border' => 'border-info-200 dark:border-info-800',
            'text' => 'text-info-700 dark:text-info-300',
            'title' => 'text-info-900 dark:text-info-100',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'iconColor' => 'text-info-600',
        ],
        'success' => [
            'bg' => 'bg-success-50 dark:bg-success-900/20',
            'border' => 'border-success-200 dark:border-success-800',
            'text' => 'text-success-700 dark:text-success-300',
            'title' => 'text-success-900 dark:text-success-100',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'iconColor' => 'text-success-600',
        ],
        'warning' => [
            'bg' => 'bg-warning-50 dark:bg-warning-900/20',
            'border' => 'border-warning-200 dark:border-warning-800',
            'text' => 'text-warning-700 dark:text-warning-300',
            'title' => 'text-warning-900 dark:text-warning-100',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            'iconColor' => 'text-warning-600',
        ],
        'danger' => [
            'bg' => 'bg-danger-50 dark:bg-danger-900/20',
            'border' => 'border-danger-200 dark:border-danger-800',
            'text' => 'text-danger-700 dark:text-danger-300',
            'title' => 'text-danger-900 dark:text-danger-100',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'iconColor' => 'text-danger-600',
        ],
    ];

    $config = $typeConfig[$type] ?? $typeConfig['info'];
@endphp

<div
    {{ $attributes->merge([
        'class' => 'rounded-xl border p-4 ' . $config['bg'] . ' ' . $config['border'],
    ]) }}
    @if($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-transition
    @endif
>
    <div class="flex gap-4">
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 {{ $config['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $config['icon'] !!}
            </svg>
        </div>
        <div class="flex-1">
            @if($title)
                <h3 class="font-medium {{ $config['title'] }} mb-1">{{ $title }}</h3>
            @endif
            <div class="text-sm {{ $config['text'] }}">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <button
                type="button"
                @click="show = false"
                class="flex-shrink-0 {{ $config['text'] }} hover:opacity-75"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>
