@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'rows' => 3,
])

@php
    $hasError = $errors->has($name);
    $inputClass = 'form-input' . ($hasError ? ' form-input-error' : '');
    $textareaId = $name;
    $hintId = $hint ? "{$name}-hint" : null;
    $errorId = $hasError ? "{$name}-error" : null;
    $describedBy = collect([$hintId, $errorId])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $textareaId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500" aria-hidden="true">*</span>
                <span class="sr-only">(obligatoire)</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $textareaId }}"
        placeholder="{{ $placeholder }}"
        rows="{{ $rows }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if($required) aria-required="true" @endif
        @if($hasError) aria-invalid="true" @endif
        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except(['class'])->merge(['class' => $inputClass]) }}
    >{{ old($name, $value) }}</textarea>

    @if($hint)
        <p id="{{ $hintId }}" class="form-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $errorId }}" class="form-error" role="alert">{{ $message }}</p>
    @enderror
</div>
