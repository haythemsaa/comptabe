@props([
    'name',
])

<div
    x-show="activeTab === '{{ $name }}'"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    {{ $attributes }}
>
    {{ $slot }}
</div>
