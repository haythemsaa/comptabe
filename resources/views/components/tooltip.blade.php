@props([
    'text',
    'position' => 'top',
])

@php
    $positionClasses = [
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
    ];

    $arrowClasses = [
        'top' => 'top-full left-1/2 -translate-x-1/2 border-t-secondary-800',
        'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-b-secondary-800',
        'left' => 'left-full top-1/2 -translate-y-1/2 border-l-secondary-800',
        'right' => 'right-full top-1/2 -translate-y-1/2 border-r-secondary-800',
    ];
@endphp

<div
    {{ $attributes->merge(['class' => 'relative inline-block']) }}
    x-data="{ show: false }"
    @mouseenter="show = true"
    @mouseleave="show = false"
>
    {{ $slot }}

    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 {{ $positionClasses[$position] ?? $positionClasses['top'] }} pointer-events-none"
        style="display: none;"
    >
        <div class="px-2 py-1 text-xs font-medium text-white bg-secondary-800 dark:bg-secondary-900 rounded shadow-lg whitespace-nowrap">
            {{ $text }}
        </div>
    </div>
</div>
