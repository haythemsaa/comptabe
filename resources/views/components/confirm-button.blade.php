@props([
    'action',
    'method' => 'POST',
    'title' => 'Confirmer',
    'message' => 'Êtes-vous sûr de vouloir effectuer cette action ?',
    'confirmLabel' => 'Confirmer',
    'cancelLabel' => 'Annuler',
    'variant' => 'danger',
])

@php
    $id = 'confirm-' . uniqid();
@endphp

<div x-data="{ open: false }">
    <span @click="open = true">
        {{ $slot }}
    </span>

    <!-- Confirmation Modal -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div
                class="fixed inset-0 transition-opacity bg-secondary-500/75 dark:bg-secondary-900/75"
                @click="open = false"
            ></div>

            <!-- Modal panel -->
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-secondary-800 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto rounded-full bg-{{ $variant }}-100 dark:bg-{{ $variant }}-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="w-6 h-6 text-{{ $variant }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium text-secondary-900 dark:text-white">
                            {{ $title }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-secondary-500 dark:text-secondary-400">
                                {{ $message }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <form action="{{ $action }}" method="POST" class="inline">
                        @csrf
                        @if(strtoupper($method) !== 'POST')
                            @method($method)
                        @endif
                        <button type="submit" class="btn btn-{{ $variant }} w-full sm:w-auto">
                            {{ $confirmLabel }}
                        </button>
                    </form>
                    <button type="button" @click="open = false" class="btn btn-secondary w-full sm:w-auto mt-3 sm:mt-0">
                        {{ $cancelLabel }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
