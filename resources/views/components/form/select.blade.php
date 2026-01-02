@props([
    'name',
    'label' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
])

@php
    $hasError = $errors->has($name);
    $selectClass = 'form-select' . ($hasError ? ' form-input-error' : '');
    $selectedValue = old($name, $value);
    $selectId = $name;
    $hintId = $hint ? "{$name}-hint" : null;
    $errorId = $hasError ? "{$name}-error" : null;
    $describedBy = collect([$hintId, $errorId])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $selectId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500" aria-hidden="true">*</span>
                <span class="sr-only">(obligatoire)</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if($required) aria-required="true" @endif
        @if($hasError) aria-invalid="true" @endif
        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except(['class'])->merge(['class' => $selectClass]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if(is_array($options) && count($options) > 0)
            @foreach($options as $optValue => $optLabel)
                @if(is_array($optLabel))
                    {{-- Grouped options --}}
                    <optgroup label="{{ $optValue }}">
                        @foreach($optLabel as $subValue => $subLabel)
                            <option value="{{ $subValue }}" {{ $selectedValue == $subValue ? 'selected' : '' }}>
                                {{ $subLabel }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $optValue }}" {{ $selectedValue == $optValue ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endif
            @endforeach
        @endif

        {{ $slot }}
    </select>

    @if($hint)
        <p id="{{ $hintId }}" class="form-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $errorId }}" class="form-error" role="alert">{{ $message }}</p>
    @enderror
</div>
