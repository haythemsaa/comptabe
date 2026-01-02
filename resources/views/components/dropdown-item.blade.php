@props([
    'href' => null,
    'icon' => null,
    'danger' => false,
])

@php
    $tag = $href ? 'a' : 'button';
    $baseClasses = 'w-full flex items-center gap-3 px-4 py-2 text-sm transition-colors';

    if ($danger) {
        $baseClasses .= ' text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20';
    } else {
        $baseClasses .= ' text-secondary-700 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-secondary-700';
    }
@endphp

<{{ $tag }}
    @if($href)
        href="{{ $href }}"
    @else
        type="button"
    @endif
    {{ $attributes->merge(['class' => $baseClasses]) }}
>
    @if($icon)
        <span class="w-4 h-4 flex-shrink-0">
            {!! $icon !!}
        </span>
    @endif
    {{ $slot }}
</{{ $tag }}>
