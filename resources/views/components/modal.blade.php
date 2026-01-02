@props([
    'id',
    'title' => null,
    'size' => 'md',
    'closeable' => true,
])

@php
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
    ];
    $titleId = $title ? "{$id}-title" : null;
    $descId = "{$id}-desc";
@endphp

<dialog
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    @if($titleId) aria-labelledby="{{ $titleId }}" @endif
    aria-describedby="{{ $descId }}"
    {{ $attributes->merge(['class' => 'modal']) }}
>
    <div class="modal-box {{ $sizeClasses[$size] ?? $sizeClasses['md'] }} w-full" role="document">
        @if($closeable)
            <form method="dialog">
                <button
                    class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    aria-label="Fermer la fenêtre"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        @endif

        @if($title)
            <h3 id="{{ $titleId }}" class="font-bold text-lg mb-4 pr-8">{{ $title }}</h3>
        @endif

        <div id="{{ $descId }}">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="modal-action">
                {{ $footer }}
            </div>
        @endif
    </div>

    @if($closeable)
        <form method="dialog" class="modal-backdrop">
            <button aria-label="Fermer">close</button>
        </form>
    @endif
</dialog>
