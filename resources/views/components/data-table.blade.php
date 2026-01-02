@props([
    'headers' => [],
    'sortable' => false,
    'sortBy' => null,
    'sortDirection' => 'asc',
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="table">
        @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $key => $header)
                        @php
                            $headerText = is_array($header) ? ($header['label'] ?? $header) : $header;
                            $headerAlign = is_array($header) ? ($header['align'] ?? 'left') : 'left';
                            $headerWidth = is_array($header) ? ($header['width'] ?? null) : null;
                            $isSortable = is_array($header) ? ($header['sortable'] ?? false) : false;
                        @endphp
                        <th
                            @if($headerWidth) style="width: {{ $headerWidth }}" @endif
                            class="text-{{ $headerAlign }}"
                        >
                            @if($sortable && $isSortable)
                                <button
                                    type="button"
                                    wire:click="sortBy('{{ $key }}')"
                                    class="flex items-center gap-1 hover:text-primary-600 transition-colors"
                                >
                                    {{ $headerText }}
                                    @if($sortBy === $key)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            @endif
                                        </svg>
                                    @endif
                                </button>
                            @else
                                {{ $headerText }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            {{ $slot }}
        </tbody>

        @if(isset($footer))
            <tfoot>
                {{ $footer }}
            </tfoot>
        @endif
    </table>
</div>
