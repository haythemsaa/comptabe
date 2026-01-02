@props([
    'color' => 'secondary',
    'size' => 'md',
    'rounded' => false,
    'dot' => false,
])

@php
    $sizeClasses = [
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-1',
        'lg' => 'text-sm px-3 py-1.5',
    ];

    $classes = 'badge badge-' . $color . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);

    if ($rounded) {
        $classes .= ' rounded-full';
    }
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></span>
    @endif
    {{ $slot }}
</span>
