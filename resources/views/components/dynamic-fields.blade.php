@props([
    'fields',
    'values' => [],
    'prefix' => 'custom_fields',
    'disabled' => false,
])

@php
    // Group fields by their group property
    $groupedFields = $fields->groupBy('group');
@endphp

@if($fields->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'dynamic-fields-container space-y-6']) }}>
        @foreach($groupedFields as $groupName => $groupFields)
            @if($groupName)
                <div class="border-t border-secondary-200 dark:border-secondary-700 pt-6">
                    <h4 class="text-sm font-medium text-secondary-900 dark:text-white mb-4">{{ $groupName }}</h4>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($groupFields as $field)
                            <x-dynamic-field
                                :field="$field"
                                :value="$values[$field->slug] ?? null"
                                :name="$prefix . '[' . $field->slug . ']'"
                                :disabled="$disabled"
                            />
                        @endforeach
                    </div>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($groupFields as $field)
                        <x-dynamic-field
                            :field="$field"
                            :value="$values[$field->slug] ?? null"
                            :name="$prefix . '[' . $field->slug . ']'"
                            :disabled="$disabled"
                        />
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
@endif
