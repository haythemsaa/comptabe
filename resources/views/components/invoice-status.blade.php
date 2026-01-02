@props([
    'status',
])

@php
    $statusConfig = [
        'draft' => [
            'label' => 'Brouillon',
            'color' => 'secondary',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        ],
        'validated' => [
            'label' => 'Validée',
            'color' => 'primary',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'sent' => [
            'label' => 'Envoyée',
            'color' => 'info',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>',
        ],
        'paid' => [
            'label' => 'Payée',
            'color' => 'success',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'partial' => [
            'label' => 'Partiel',
            'color' => 'warning',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'overdue' => [
            'label' => 'Échue',
            'color' => 'danger',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'cancelled' => [
            'label' => 'Annulée',
            'color' => 'danger',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
    ];

    $config = $statusConfig[$status] ?? [
        'label' => ucfirst($status),
        'color' => 'secondary',
        'icon' => null,
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge badge-' . $config['color'] . ' inline-flex items-center gap-1']) }}>
    @if($config['icon'])
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $config['icon'] !!}
        </svg>
    @endif
    {{ $config['label'] }}
</span>
