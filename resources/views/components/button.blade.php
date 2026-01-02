@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => false,
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'ariaLabel' => null,
])

@php
    $baseClasses = 'btn';

    // Variant classes
    $variantClasses = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'success' => 'btn-success',
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'info' => 'btn-info',
        'ghost' => 'btn-ghost',
        'link' => 'text-primary-600 hover:text-primary-700 hover:underline',
    ];

    // Size classes
    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']);
    $classes .= ' ' . ($sizeClasses[$size] ?? '');

    if ($icon) {
        $classes .= ' btn-icon';
    }

    if ($loading || $disabled) {
        $classes .= ' opacity-50 cursor-not-allowed';
    }

    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href)
        href="{{ $href }}"
    @else
        type="{{ $type }}"
    @endif
    @if($disabled || $loading)
        {{ $tag === 'button' ? 'disabled' : 'aria-disabled=true' }}
    @endif
    @if($loading) aria-busy="true" @endif
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="sr-only">Chargement...</span>
    @endif
    {{ $slot }}
</{{ $tag }}>
