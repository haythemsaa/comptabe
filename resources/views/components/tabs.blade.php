@props([
    'tabs' => [],
    'active' => null,
])

<div {{ $attributes->merge(['class' => '']) }} x-data="{ activeTab: '{{ $active ?? array_key_first($tabs) }}' }">
    <!-- Tab Headers -->
    <div class="flex border-b border-secondary-200 dark:border-secondary-700 mb-6 overflow-x-auto">
        @foreach($tabs as $key => $label)
            <button
                type="button"
                @click="activeTab = '{{ $key }}'"
                class="px-4 py-2 font-medium text-sm border-b-2 transition-colors whitespace-nowrap"
                :class="activeTab === '{{ $key }}'
                    ? 'border-primary-500 text-primary-600'
                    : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300'"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- Tab Content -->
    {{ $slot }}
</div>
