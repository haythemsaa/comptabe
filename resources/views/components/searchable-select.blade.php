@props([
    'name',
    'label' => null,
    'placeholder' => 'Rechercher...',
    'options' => [],
    'value' => null,
    'required' => false,
    'displayKey' => 'name',
    'valueKey' => 'id',
    'hint' => null,
    'icon' => null,
])

@php
    $selectedValue = old($name, $value);
    $componentId = 'ss_' . str_replace(['.', '[', ']'], '_', $name) . '_' . uniqid();
@endphp

<div class="relative" x-data="{
    open: false,
    search: '',
    selected: null,
    selectedValue: {{ json_encode($selectedValue) }},
    options: [],
    filteredOptions: [],
    highlightedIndex: 0,

    init() {
        this.options = JSON.parse(document.getElementById('{{ $componentId }}_data').textContent);
        if (this.selectedValue) {
            this.selected = this.options.find(o => o.{{ $valueKey }} == this.selectedValue);
            if (this.selected) {
                this.search = this.selected.{{ $displayKey }};
            }
        }
        this.filteredOptions = this.options.slice(0, 10);
    },

    filter() {
        if (this.search.length === 0) {
            this.filteredOptions = this.options.slice(0, 10);
        } else {
            const s = this.search.toLowerCase();
            this.filteredOptions = this.options.filter(o => {
                const name = (o.{{ $displayKey }} || '').toLowerCase();
                const sub = (o.subtitle || '').toLowerCase();
                return name.includes(s) || sub.includes(s);
            }).slice(0, 10);
        }
        this.highlightedIndex = 0;
    },

    select(option) {
        this.selected = option;
        this.selectedValue = option.{{ $valueKey }};
        this.search = option.{{ $displayKey }};
        this.open = false;
        this.$dispatch('change', { value: option.{{ $valueKey }}, item: option });
    },

    clear() {
        this.selected = null;
        this.selectedValue = null;
        this.search = '';
        this.filteredOptions = this.options.slice(0, 10);
        this.$dispatch('change', { value: null, item: null });
    },

    onBlur() {
        setTimeout(() => {
            if (!this.selected) {
                this.search = '';
            } else {
                this.search = this.selected.{{ $displayKey }};
            }
            this.open = false;
        }, 150);
    }
}" @click.away="open = false" {{ $attributes }}>

    <!-- Data store -->
    <script type="application/json" id="{{ $componentId }}_data">@json($options)</script>

    @if($label)
        <label class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <!-- Hidden input -->
    <input type="hidden" name="{{ $name }}" :value="selectedValue" {{ $required ? 'required' : '' }}>

    <!-- Search input container -->
    <div class="relative group">
        <!-- Selected item display (when closed and selected) -->
        <div
            x-show="selected && !open"
            @click="open = true; $nextTick(() => $refs.searchInput.focus())"
            class="w-full flex items-center gap-3 bg-white dark:bg-secondary-800 border-2 border-secondary-200 dark:border-secondary-600 rounded-xl px-4 py-3 cursor-pointer hover:border-primary-300 dark:hover:border-primary-500 transition-all duration-200"
        >
            <!-- Avatar/Initials -->
            <template x-if="selected && selected.initials">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg shadow-primary-500/20">
                    <span x-text="selected.initials"></span>
                </div>
            </template>
            <template x-if="selected && !selected.initials">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-secondary-400 to-secondary-600 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </template>

            <div class="flex-1 min-w-0">
                <div class="font-medium text-secondary-900 dark:text-white truncate" x-text="selected?.{{ $displayKey }}"></div>
                <template x-if="selected?.subtitle">
                    <div class="text-sm text-secondary-500 truncate" x-text="selected.subtitle"></div>
                </template>
            </div>

            <!-- Change button -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click.stop="clear()"
                    class="p-1.5 rounded-lg text-secondary-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-colors"
                    title="Effacer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="p-1.5 rounded-lg text-secondary-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Search input (when open or nothing selected) -->
        <div x-show="!selected || open" class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-secondary-400" :class="open ? 'text-primary-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                type="text"
                x-ref="searchInput"
                x-model="search"
                @focus="open = true; filter()"
                @input="filter(); open = true"
                @blur="onBlur()"
                @keydown.down.prevent="highlightedIndex = Math.min(highlightedIndex + 1, filteredOptions.length - 1)"
                @keydown.up.prevent="highlightedIndex = Math.max(highlightedIndex - 1, 0)"
                @keydown.enter.prevent="if(filteredOptions[highlightedIndex]) select(filteredOptions[highlightedIndex])"
                @keydown.escape="open = false"
                placeholder="{{ $placeholder }}"
                class="w-full bg-white dark:bg-secondary-800 border-2 border-secondary-200 dark:border-secondary-600 rounded-xl text-secondary-900 dark:text-white placeholder-secondary-400 pl-12 pr-12 py-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 @error($name) border-danger-500 @enderror"
                autocomplete="off"
            >
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                <svg
                    class="w-5 h-5 text-secondary-400 transition-transform duration-200"
                    :class="open ? 'rotate-180 text-primary-500' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Dropdown -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="absolute z-50 w-full mt-2 bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-xl shadow-2xl shadow-secondary-900/10 dark:shadow-black/30 overflow-hidden"
        style="display: none;"
    >
        <!-- Search results header -->
        <div class="px-4 py-2 bg-secondary-50 dark:bg-secondary-900/50 border-b border-secondary-200 dark:border-secondary-700">
            <span class="text-xs font-medium text-secondary-500 uppercase tracking-wider">
                <span x-text="filteredOptions.length"></span> resultat(s)
            </span>
        </div>

        <!-- Options list -->
        <div class="max-h-64 overflow-y-auto overscroll-contain">
            <template x-if="filteredOptions.length === 0">
                <div class="px-4 py-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-secondary-100 dark:bg-secondary-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-secondary-500 text-sm">Aucun resultat trouve</p>
                    <p class="text-secondary-400 text-xs mt-1">Essayez avec d'autres termes</p>
                </div>
            </template>

            <template x-for="(option, index) in filteredOptions" :key="option.{{ $valueKey }}">
                <div
                    @click="select(option)"
                    @mouseenter="highlightedIndex = index"
                    :class="{
                        'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === index,
                        'bg-success-50 dark:bg-success-900/20': selectedValue == option.{{ $valueKey }} && highlightedIndex !== index
                    }"
                    class="px-4 py-3 cursor-pointer hover:bg-secondary-50 dark:hover:bg-secondary-700/50 transition-colors duration-100 flex items-center gap-3 border-b border-secondary-100 dark:border-secondary-700/50 last:border-0"
                >
                    <!-- Avatar/Initials -->
                    <template x-if="option.initials">
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-semibold shadow-sm"
                            :class="selectedValue == option.{{ $valueKey }}
                                ? 'bg-gradient-to-br from-success-400 to-success-600 text-white'
                                : 'bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/50 dark:to-primary-800/50 text-primary-700 dark:text-primary-300'"
                            x-text="option.initials"
                        ></div>
                    </template>
                    <template x-if="!option.initials">
                        <div class="w-10 h-10 rounded-xl bg-secondary-100 dark:bg-secondary-700 flex items-center justify-center">
                            <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </template>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-secondary-900 dark:text-white truncate" x-text="option.{{ $displayKey }}"></div>
                        <template x-if="option.subtitle">
                            <div class="text-sm text-secondary-500 truncate flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                                <span x-text="option.subtitle"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Status indicators -->
                    <div class="flex items-center gap-2">
                        <template x-if="option.peppol">
                            <span class="px-2 py-1 text-xs font-medium rounded-lg bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400">
                                Peppol
                            </span>
                        </template>
                        <svg
                            x-show="selectedValue == option.{{ $valueKey }}"
                            class="w-5 h-5 text-success-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer hint -->
        <div class="px-4 py-2 bg-secondary-50 dark:bg-secondary-900/50 border-t border-secondary-200 dark:border-secondary-700 flex items-center justify-between text-xs text-secondary-400">
            <span>
                <kbd class="px-1.5 py-0.5 rounded bg-secondary-200 dark:bg-secondary-700 font-mono">↑↓</kbd>
                naviguer
            </span>
            <span>
                <kbd class="px-1.5 py-0.5 rounded bg-secondary-200 dark:bg-secondary-700 font-mono">Enter</kbd>
                selectionner
            </span>
            <span>
                <kbd class="px-1.5 py-0.5 rounded bg-secondary-200 dark:bg-secondary-700 font-mono">Esc</kbd>
                fermer
            </span>
        </div>
    </div>

    @if($hint)
        <p class="mt-2 text-sm text-secondary-500 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $hint }}
        </p>
    @endif

    @error($name)
        <p class="mt-2 text-sm text-danger-500 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
