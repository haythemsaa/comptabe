@props([
    'name' => null,
    'src' => null,
    'size' => 'md',
    'color' => null,
])

@php
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg',
        'xl' => 'w-16 h-16 text-xl',
        '2xl' => 'w-24 h-24 text-3xl',
    ];

    // Generate initials from name
    $initials = '';
    if ($name) {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
        } else {
            $initials = strtoupper(substr($name, 0, 2));
        }
    }

    // Generate consistent color from name
    if (!$color && $name) {
        $colors = ['primary', 'success', 'warning', 'danger', 'info'];
        $hash = crc32($name);
        $color = $colors[$hash % count($colors)];
    }
    $color = $color ?? 'secondary';

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div {{ $attributes->merge([
    'class' => $sizeClass . ' rounded-full overflow-hidden flex items-center justify-center font-semibold flex-shrink-0'
]) }}>
    @if($src)
        <img
            src="{{ $src }}"
            alt="{{ $name }}"
            class="w-full h-full object-cover"
        >
    @else
        <div class="w-full h-full flex items-center justify-center bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-600">
            {{ $initials ?: '?' }}
        </div>
    @endif
</div>
