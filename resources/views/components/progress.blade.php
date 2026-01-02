@props([
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'size' => 'md',
    'showLabel' => false,
    'label' => null,
])

@php
    $percentage = $max > 0 ? min(100, ($value / $max) * 100) : 0;

    $sizeClasses = [
        'sm' => 'h-1',
        'md' => 'h-2',
        'lg' => 'h-3',
        'xl' => 'h-4',
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    @if($showLabel)
        <div class="flex justify-between items-center mb-1">
            <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">
                {{ $label ?? '' }}
            </span>
            <span class="text-sm text-secondary-500">
                {{ number_format($percentage, 0) }}%
            </span>
        </div>
    @endif

    <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full {{ $sizeClass }} overflow-hidden">
        <div
            class="bg-{{ $color }}-500 {{ $sizeClass }} rounded-full transition-all duration-500 ease-out"
            style="width: {{ $percentage }}%"
        ></div>
    </div>
</div>
