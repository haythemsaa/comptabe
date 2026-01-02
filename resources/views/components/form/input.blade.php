@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'icon' => null,
    'suffix' => null,
])

@php
    $hasError = $errors->has($name);
    $inputClass = 'form-input' . ($hasError ? ' form-input-error' : '');
    $inputId = $name;
    $hintId = $hint ? "{$name}-hint" : null;
    $errorId = $hasError ? "{$name}-error" : null;
    $describedBy = collect([$hintId, $errorId])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger-500" aria-hidden="true">*</span>
                <span class="sr-only">(obligatoire)</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400" aria-hidden="true">
                {!! $icon !!}
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            @if($required) aria-required="true" @endif
            @if($hasError) aria-invalid="true" @endif
            @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except(['class'])->merge([
                'class' => $inputClass . ($icon ? ' pl-10' : '') . ($suffix ? ' pr-12' : '')
            ]) }}
        >

        @if($suffix)
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 text-sm" aria-hidden="true">
                {{ $suffix }}
            </span>
        @endif
    </div>

    @if($hint)
        <p id="{{ $hintId }}" class="form-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $errorId }}" class="form-error" role="alert">{{ $message }}</p>
    @enderror
</div>
