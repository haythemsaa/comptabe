@extends('client-portal.layouts.portal')

@section('title', 'Mes Documents')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mes Documents</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Gérez vos documents comptables
            </p>
        </div>

        @if($access && $access->hasPermission('upload_documents'))
        <a href="{{ route('client-portal.documents.create', $company) }}"
           class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Uploader un document
        </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Type
                </label>
                <select name="type" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Tous</option>
                    @foreach(\App\Models\ClientDocument::TYPES as $key => $label)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date de début
                </label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date de fin
                </label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($documents as $document)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
            <div class="p-6">
                <!-- Icon & Type -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-shrink-0">
                        @if(str_starts_with($document->mime_type, 'image/'))
                        <svg class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        @elseif($document->mime_type === 'application/pdf')
                        <svg class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        @else
                        <svg class="h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">
                        {{ $document->type_label }}
                    </span>
                </div>

                <!-- Filename -->
                <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate mb-2" title="{{ $document->original_filename }}">
                    {{ $document->original_filename }}
                </h3>

                <!-- Description -->
                @if($document->description)
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
                    {{ $document->description }}
                </p>
                @endif

                <!-- Meta -->
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
                    <span>{{ $document->formatted_file_size }}</span>
                    <span>{{ $document->document_date?->format('d/m/Y') ?? $document->created_at->format('d/m/Y') }}</span>
                </div>

                <!-- Uploader -->
                <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ $document->uploadedBy->name }}
                </div>

                <!-- Actions -->
                <a href="{{ route('client-portal.documents.download', [$company, $document]) }}"
                   class="block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm font-medium transition">
                    <svg class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Télécharger
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun document</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Commencez par uploader votre premier document
            </p>
            @if($access && $access->hasPermission('upload_documents'))
            <div class="mt-6">
                <a href="{{ route('client-portal.documents.create', $company) }}"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Uploader un document
                </a>
            </div>
            @endif
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($documents->hasPages())
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow px-4 py-3">
        {{ $documents->links() }}
    </div>
    @endif
</div>
@endsection
