@props([
    'header' => null,
    'footer' => null,
    'padding' => true,
    'hover' => false,
])

<div {{ $attributes->merge([
    'class' => 'card' . ($hover ? ' card-hover' : '')
]) }}>
    @if($header)
        <div class="card-header">
            {{ $header }}
        </div>
    @endif

    @if($padding)
        <div class="card-body">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif

    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
