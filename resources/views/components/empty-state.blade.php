@props([
    'title',
    'description' => null,
    'icon' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12']) }}>
    @if($icon)
        <div class="mx-auto w-16 h-16 text-secondary-300 dark:text-secondary-600 mb-4">
            {!! $icon !!}
        </div>
    @else
        <svg class="w-16 h-16 mx-auto text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    @endif

    <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-2">{{ $title }}</h3>

    @if($description)
        <p class="text-secondary-500 mb-6 max-w-sm mx-auto">{{ $description }}</p>
    @endif

    @if($action && $actionLabel)
        <a href="{{ $action }}" class="btn btn-primary">
            {{ $actionLabel }}
        </a>
    @endif

    {{ $slot }}
</div>
