@props([
    'field',
    'value' => null,
    'name' => null,
    'disabled' => false,
])

@php
    $fieldName = $name ?? "custom_fields[{$field->slug}]";
    $fieldId = str_replace(['[', ']'], ['_', ''], $fieldName);
    $currentValue = $value ?? $field->default_value;
@endphp

<div {{ $attributes->merge(['class' => 'dynamic-field']) }}>
    <label for="{{ $fieldId }}" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
        {{ $field->label }}
        @if($field->is_required)
            <span class="text-danger-500">*</span>
        @endif
    </label>

    @switch($field->type)
        @case('text')
        @case('url')
        @case('email')
        @case('phone')
            <input
                type="{{ $field->type === 'email' ? 'email' : ($field->type === 'url' ? 'url' : ($field->type === 'phone' ? 'tel' : 'text')) }}"
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                value="{{ old($fieldName, $currentValue) }}"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full"
                @if($field->options['min_length'] ?? null) minlength="{{ $field->options['min_length'] }}" @endif
                @if($field->options['max_length'] ?? null) maxlength="{{ $field->options['max_length'] }}" @endif
                @if($field->options['pattern'] ?? null) pattern="{{ $field->options['pattern'] }}" @endif
                placeholder="{{ $field->description ?? '' }}"
            >
            @break

        @case('textarea')
        @case('richtext')
            <textarea
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                rows="{{ $field->options['rows'] ?? 3 }}"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full"
                @if($field->options['min_length'] ?? null) minlength="{{ $field->options['min_length'] }}" @endif
                @if($field->options['max_length'] ?? null) maxlength="{{ $field->options['max_length'] }}" @endif
                placeholder="{{ $field->description ?? '' }}"
            >{{ old($fieldName, $currentValue) }}</textarea>
            @break

        @case('number')
            <input
                type="number"
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                value="{{ old($fieldName, $currentValue) }}"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full"
                @if(isset($field->options['min'])) min="{{ $field->options['min'] }}" @endif
                @if(isset($field->options['max'])) max="{{ $field->options['max'] }}" @endif
                @if($field->options['step'] ?? null) step="{{ $field->options['step'] }}" @else step="1" @endif
            >
            @break

        @case('decimal')
        @case('currency')
            <div class="relative">
                <input
                    type="number"
                    id="{{ $fieldId }}"
                    name="{{ $fieldName }}"
                    value="{{ old($fieldName, $currentValue) }}"
                    {{ $field->is_required ? 'required' : '' }}
                    {{ $disabled ? 'disabled' : '' }}
                    class="form-input w-full {{ $field->type === 'currency' ? 'pr-8' : '' }}"
                    @if(isset($field->options['min'])) min="{{ $field->options['min'] }}" @endif
                    @if(isset($field->options['max'])) max="{{ $field->options['max'] }}" @endif
                    step="{{ $field->options['step'] ?? '0.01' }}"
                >
                @if($field->type === 'currency')
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">&euro;</span>
                @endif
            </div>
            @break

        @case('date')
            <input
                type="date"
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                value="{{ old($fieldName, $currentValue) }}"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full"
            >
            @break

        @case('datetime')
            <input
                type="datetime-local"
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                value="{{ old($fieldName, $currentValue) }}"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full"
            >
            @break

        @case('boolean')
            <div class="flex items-center">
                <input type="hidden" name="{{ $fieldName }}" value="0">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        id="{{ $fieldId }}"
                        name="{{ $fieldName }}"
                        value="1"
                        {{ old($fieldName, $currentValue) ? 'checked' : '' }}
                        {{ $disabled ? 'disabled' : '' }}
                        class="form-checkbox"
                    >
                    <span class="text-sm text-secondary-700 dark:text-secondary-300">{{ $field->description ?? 'Oui' }}</span>
                </label>
            </div>
            @break

        @case('select')
            <select
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-select w-full"
            >
                <option value="">-- Sélectionner --</option>
                @foreach(($field->options['choices'] ?? []) as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" {{ old($fieldName, $currentValue) == $optionValue ? 'selected' : '' }}>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>
            @break

        @case('multiselect')
            <div class="space-y-2">
                @foreach(($field->options['choices'] ?? []) as $optionValue => $optionLabel)
                    @php
                        $selectedValues = old($fieldName, is_array($currentValue) ? $currentValue : []);
                    @endphp
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="{{ $fieldName }}[]"
                            value="{{ $optionValue }}"
                            {{ in_array($optionValue, $selectedValues) ? 'checked' : '' }}
                            {{ $disabled ? 'disabled' : '' }}
                            class="form-checkbox"
                        >
                        <span class="text-sm text-secondary-700 dark:text-secondary-300">{{ $optionLabel }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('radio')
            <div class="space-y-2">
                @foreach(($field->options['choices'] ?? []) as $optionValue => $optionLabel)
                    <label class="flex items-center gap-2">
                        <input
                            type="radio"
                            name="{{ $fieldName }}"
                            value="{{ $optionValue }}"
                            {{ old($fieldName, $currentValue) == $optionValue ? 'checked' : '' }}
                            {{ $disabled ? 'disabled' : '' }}
                            class="form-radio"
                        >
                        <span class="text-sm text-secondary-700 dark:text-secondary-300">{{ $optionLabel }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('color')
            <div class="flex items-center gap-3">
                <input
                    type="color"
                    id="{{ $fieldId }}"
                    name="{{ $fieldName }}"
                    value="{{ old($fieldName, $currentValue ?? '#000000') }}"
                    {{ $disabled ? 'disabled' : '' }}
                    class="h-10 w-16 rounded border border-secondary-300 dark:border-secondary-600 cursor-pointer"
                >
                <input
                    type="text"
                    value="{{ old($fieldName, $currentValue ?? '#000000') }}"
                    class="form-input flex-1"
                    placeholder="#000000"
                    oninput="document.getElementById('{{ $fieldId }}').value = this.value"
                >
            </div>
            @break

        @case('file')
        @case('image')
            <input
                type="file"
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                {{ $field->is_required && !$currentValue ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full"
                @if($field->type === 'image') accept="image/*" @endif
            >
            @if($currentValue)
                <p class="mt-1 text-xs text-secondary-500">
                    Fichier actuel: {{ basename($currentValue) }}
                </p>
            @endif
            @break

        @case('json')
            <textarea
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                rows="4"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full font-mono text-sm"
                placeholder='{"key": "value"}'
            >{{ old($fieldName, is_array($currentValue) ? json_encode($currentValue, JSON_PRETTY_PRINT) : $currentValue) }}</textarea>
            @break

        @default
            <input
                type="text"
                id="{{ $fieldId }}"
                name="{{ $fieldName }}"
                value="{{ old($fieldName, $currentValue) }}"
                {{ $field->is_required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="form-input w-full"
            >
    @endswitch

    @if($field->description && !in_array($field->type, ['boolean']))
        <p class="mt-1 text-xs text-secondary-500 dark:text-secondary-400">{{ $field->description }}</p>
    @endif

    @error($fieldName)
        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
    @enderror
</div>
