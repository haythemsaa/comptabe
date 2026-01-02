@props([
    'align' => 'right',
    'width' => '48',
])

@php
    $alignmentClasses = [
        'left' => 'left-0',
        'right' => 'right-0',
    ];

    $widthClasses = [
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        'auto' => 'w-auto',
    ];
@endphp

<div
    {{ $attributes->merge(['class' => 'relative']) }}
    x-data="{ open: false }"
    @click.away="open = false"
    @close.stop="open = false"
>
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClasses[$width] ?? $widthClasses['48'] }} {{ $alignmentClasses[$align] ?? $alignmentClasses['right'] }} rounded-xl bg-white dark:bg-secondary-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none py-1"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
