@props([
    'label',
    'value',
    'icon' => null,
    'iconColor' => 'primary',
    'trend' => null,
    'trendValue' => null,
    'href' => null,
])

@php
    $wrapperTag = $href ? 'a' : 'div';
@endphp

<{{ $wrapperTag }}
    {{ $href ? 'href=' . $href : '' }}
    {{ $attributes->merge([
        'class' => 'card p-6' . ($href ? ' card-hover' : '')
    ]) }}
>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ $label }}</p>
            <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">{{ $value }}</p>

            @if($trend !== null)
                <div class="flex items-center gap-1 mt-2">
                    @if($trend === 'up')
                        <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span class="text-sm font-medium text-success-600">{{ $trendValue }}</span>
                    @elseif($trend === 'down')
                        <svg class="w-4 h-4 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <span class="text-sm font-medium text-danger-600">{{ $trendValue }}</span>
                    @else
                        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                        </svg>
                        <span class="text-sm font-medium text-secondary-500">{{ $trendValue }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if($icon)
            <div class="w-12 h-12 bg-{{ $iconColor }}-100 dark:bg-{{ $iconColor }}-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <div class="w-6 h-6 text-{{ $iconColor }}-600">
                    {!! $icon !!}
                </div>
            </div>
        @endif
    </div>

    {{ $slot }}
</{{ $wrapperTag }}>
