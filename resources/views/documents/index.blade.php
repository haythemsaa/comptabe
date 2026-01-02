<x-app-layout>
    <x-slot name="title">Archive de Documents</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Archive de Documents</span>
    @endsection

    <div x-data="documentArchive()" class="flex h-[calc(100vh-12rem)] gap-6">
        <!-- Sidebar - Folders & Tags -->
        <div class="w-72 flex-shrink-0 flex flex-col bg-white dark:bg-secondary-900 rounded-xl border border-secondary-200 dark:border-secondary-700 overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-secondary-200 dark:border-secondary-700">
                <button @click="sidebarTab = 'folders'" :class="sidebarTab === 'folders' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                    <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    Dossiers
                </button>
                <button @click="sidebarTab = 'tags'" :class="sidebarTab === 'tags' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                    <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Etiquettes
                </button>
            </div>

            <!-- Folders Tab -->
            <div x-show="sidebarTab === 'folders'" class="flex-1 flex flex-col overflow-hidden">
                <div class="p-3 border-b border-secondary-200 dark:border-secondary-700">
                    <button @click="openFolderModal()" class="btn btn-secondary btn-sm w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouveau dossier
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-2 space-y-1">
                    <!-- All Documents -->
                    <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ !request('folder') && !request('starred') && !request('tag') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-secondary-600 dark:text-secondary-400 hover:bg-secondary-100 dark:hover:bg-secondary-800' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="flex-1 text-sm font-medium">Tous les documents</span>
                        <span class="text-xs bg-secondary-100 dark:bg-secondary-800 px-2 py-0.5 rounded-full">{{ $stats['total'] }}</span>
                    </a>

                    <!-- Starred -->
                    <a href="{{ route('documents.index', ['starred' => 1]) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request('starred') ? 'bg-warning-50 dark:bg-warning-900/20 text-warning-700 dark:text-warning-400' : 'text-secondary-600 dark:text-secondary-400 hover:bg-secondary-100 dark:hover:bg-secondary-800' }} transition-colors">
                        <svg class="w-5 h-5 {{ request('starred') ? 'fill-current' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span class="flex-1 text-sm font-medium">Favoris</span>
                        <span class="text-xs bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400 px-2 py-0.5 rounded-full">{{ $stats['starred'] }}</span>
                    </a>

                    <!-- Recent -->
                    <a href="{{ route('documents.index', ['recent' => 1]) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request('recent') ? 'bg-info-50 dark:bg-info-900/20 text-info-700 dark:text-info-400' : 'text-secondary-600 dark:text-secondary-400 hover:bg-secondary-100 dark:hover:bg-secondary-800' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="flex-1 text-sm font-medium">Recents</span>
                        <span class="text-xs bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-400 px-2 py-0.5 rounded-full">{{ $stats['this_month'] }}</span>
                    </a>

                    <div class="border-t border-secondary-200 dark:border-secondary-700 my-2 mx-2"></div>

                    @forelse($folders as $folder)
                        <div class="group" x-data="{ open: {{ request('folder') == $folder->id || $folder->children->contains('id', request('folder')) ? 'true' : 'false' }} }">
                            <div class="flex items-center gap-1 px-3 py-2 rounded-lg {{ request('folder') == $folder->id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-secondary-600 dark:text-secondary-400 hover:bg-secondary-100 dark:hover:bg-secondary-800' }} transition-colors">
                                @if($folder->children->count() > 0)
                                    <button @click.stop="open = !open" class="p-1 -ml-1 rounded hover:bg-secondary-200 dark:hover:bg-secondary-700">
                                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                @else
                                    <span class="w-5"></span>
                                @endif
                                <a href="{{ route('documents.index', ['folder' => $folder->id]) }}" class="flex-1 flex items-center gap-2 min-w-0">
                                    <svg class="w-5 h-5 flex-shrink-0" style="color: {{ getColorHex($folder->color ?? 'gray') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium truncate">{{ $folder->name }}</span>
                                </a>
                                <span class="text-xs text-secondary-400">{{ $folder->document_count }}</span>
                                <button @click.stop="editFolder({{ json_encode($folder) }})" class="p-1 opacity-0 group-hover:opacity-100 text-secondary-400 hover:text-primary-600 rounded transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                            </div>
                            @if($folder->children->count() > 0)
                                <div x-show="open" x-collapse class="ml-4 space-y-1 mt-1">
                                    @foreach($folder->children as $child)
                                        <div class="group/child flex items-center gap-1 px-3 py-1.5 rounded-lg {{ request('folder') == $child->id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-secondary-600 dark:text-secondary-400 hover:bg-secondary-100 dark:hover:bg-secondary-800' }} transition-colors">
                                            <span class="w-4"></span>
                                            <a href="{{ route('documents.index', ['folder' => $child->id]) }}" class="flex-1 flex items-center gap-2 min-w-0">
                                                <svg class="w-4 h-4 flex-shrink-0" style="color: {{ getColorHex($child->color ?? 'gray') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                                <span class="text-sm truncate">{{ $child->name }}</span>
                                            </a>
                                            <span class="text-xs text-secondary-400">{{ $child->document_count }}</span>
                                            <button @click.stop="editFolder({{ json_encode($child) }})" class="p-1 opacity-0 group-hover/child:opacity-100 text-secondary-400 hover:text-primary-600 rounded transition-all">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-secondary-500">
                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <p class="text-sm">Aucun dossier</p>
                            <button @click="openFolderModal()" class="text-primary-600 hover:underline text-sm mt-1">Creer un dossier</button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tags Tab -->
            <div x-show="sidebarTab === 'tags'" x-cloak class="flex-1 flex flex-col overflow-hidden">
                <div class="p-3 border-b border-secondary-200 dark:border-secondary-700">
                    <button @click="openTagModal()" class="btn btn-secondary btn-sm w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle etiquette
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-2 space-y-1">
                    @forelse($tags as $tag)
                        <div class="group flex items-center gap-2 px-3 py-2 rounded-lg {{ request('tag') == $tag->id ? 'bg-secondary-100 dark:bg-secondary-800' : 'hover:bg-secondary-100 dark:hover:bg-secondary-800' }} transition-colors" @if(request('tag') == $tag->id) style="background-color: {{ getColorHex($tag->color, 50) }}20" @endif>
                            <a href="{{ route('documents.index', ['tag' => $tag->id]) }}" class="flex-1 flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full" style="background-color: {{ getColorHex($tag->color) }}"></span>
                                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300 truncate">{{ $tag->name }}</span>
                            </a>
                            <span class="text-xs text-secondary-400">{{ $tag->document_count }}</span>
                            <button @click.stop="editTag({{ json_encode($tag) }})" class="p-1 opacity-0 group-hover:opacity-100 text-secondary-400 hover:text-primary-600 rounded transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-6 text-secondary-500">
                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <p class="text-sm">Aucune etiquette</p>
                            <button @click="openTagModal()" class="text-primary-600 hover:underline text-sm mt-1">Creer une etiquette</button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Storage Info -->
            <div class="p-4 border-t border-secondary-200 dark:border-secondary-700 bg-secondary-50 dark:bg-secondary-800">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-secondary-600 dark:text-secondary-400">Stockage utilise</span>
                    @php
                        $bytes = (int) ($stats['storage_used'] ?? 0);
                        if ($bytes <= 0) {
                            $formattedSize = '0 B';
                        } else {
                            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                            $pow = floor(log($bytes) / log(1024));
                            $pow = min($pow, count($units) - 1);
                            $formattedSize = round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
                        }
                        $usedPercent = $bytes > 0 ? min(100, ($bytes / (1024 * 1024 * 1024)) * 100) : 0;
                    @endphp
                    <span class="font-medium text-secondary-900 dark:text-white">{{ $formattedSize }}</span>
                </div>
                <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-1.5">
                    <div class="bg-primary-500 h-1.5 rounded-full transition-all" style="width: {{ $usedPercent }}%"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header & Actions -->
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                        @if(request('starred'))
                            Favoris
                        @elseif(request('recent'))
                            Documents recents
                        @elseif(request('tag'))
                            {{ $tags->firstWhere('id', request('tag'))?->name ?? 'Documents' }}
                        @elseif(request('folder'))
                            {{ $folders->flatten()->firstWhere('id', request('folder'))?->name ?? 'Documents' }}
                        @else
                            Tous les documents
                        @endif
                    </h1>
                    <p class="text-secondary-600 dark:text-secondary-400">{{ $documents->total() }} document(s)</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="showAdvancedSearch = !showAdvancedSearch" class="btn btn-secondary" :class="showAdvancedSearch ? 'bg-primary-100 dark:bg-primary-900/30' : ''">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtres
                    </button>
                    <a href="{{ route('documents.archived') }}" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Archives
                    </a>
                    <a href="{{ route('documents.create') }}" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Telecharger
                    </a>
                </div>
            </div>

            <!-- Advanced Search Panel -->
            <div x-show="showAdvancedSearch" x-collapse class="mb-4">
                <div class="bg-white dark:bg-secondary-900 rounded-xl border border-secondary-200 dark:border-secondary-700 p-4">
                    <form action="{{ route('documents.index') }}" method="GET">
                        @if(request('folder'))
                            <input type="hidden" name="folder" value="{{ request('folder') }}">
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Recherche</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, description, contenu OCR..." class="form-input pl-10">
                                </div>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">Tous les types</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Year -->
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Annee fiscale</label>
                                <select name="year" class="form-select">
                                    <option value="">Toutes</option>
                                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Date debut</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Date fin</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
                            </div>

                            <!-- Tags -->
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Etiquettes</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tags as $tag)
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border cursor-pointer transition-colors border-secondary-200 dark:border-secondary-700 hover:opacity-80" style="{{ in_array($tag->id, (array)request('tags', [])) ? 'border-color: ' . getColorHex($tag->color) . '; background-color: ' . getColorHex($tag->color, 50) : '' }}">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="hidden" {{ in_array($tag->id, (array)request('tags', [])) ? 'checked' : '' }}>
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ getColorHex($tag->color) }}"></span>
                                            <span class="text-xs font-medium text-secondary-700 dark:text-secondary-300">{{ $tag->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <a href="{{ route('documents.index') }}" class="text-sm text-secondary-500 hover:text-secondary-700">Reinitialiser les filtres</a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Search (always visible) -->
            <div x-show="!showAdvancedSearch" class="mb-4">
                <form action="{{ route('documents.index') }}" method="GET" class="flex gap-2">
                    @if(request('folder'))
                        <input type="hidden" name="folder" value="{{ request('folder') }}">
                    @endif
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher dans les documents..." class="form-input pl-10">
                    </div>
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </form>
            </div>

            <!-- Active Tags Display -->
            @if($tags->isNotEmpty() && !request('tag'))
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="text-sm text-secondary-500">Filtrer par:</span>
                    @foreach($tags->take(8) as $tag)
                        <a href="{{ route('documents.index', array_merge(request()->except('tag'), ['tag' => $tag->id])) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium hover:opacity-80 transition-opacity" style="background-color: {{ getColorHex($tag->color, 100) }}; color: {{ getColorHex($tag->color, 800) }}">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ getColorHex($tag->color) }}"></span>
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Documents Grid -->
            <div class="flex-1 overflow-y-auto">
                @if($documents->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center">
                        <svg class="w-20 h-20 text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-secondary-700 dark:text-secondary-300 mb-2">Aucun document trouve</h3>
                        <p class="text-secondary-500 dark:text-secondary-400 mb-4">Commencez par telecharger vos premiers documents</p>
                        <a href="{{ route('documents.create') }}" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Telecharger des documents
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                        @foreach($documents as $document)
                            <div class="group relative bg-white dark:bg-secondary-900 rounded-xl border border-secondary-200 dark:border-secondary-700 overflow-hidden hover:shadow-lg hover:border-primary-300 dark:hover:border-primary-700 transition-all">
                                <!-- Thumbnail / Icon -->
                                <a href="{{ route('documents.show', $document) }}" class="block aspect-square bg-secondary-100 dark:bg-secondary-800 relative">
                                    @if($document->isImage())
                                        <img src="{{ route('documents.preview', $document) }}" alt="{{ $document->name }}" class="w-full h-full object-cover">
                                    @elseif($document->isPdf())
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-danger-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM6 20V4h7v5h5v11H6z"/>
                                                <path d="M8 12h2v6H8zm3 0h2v6h-2zm3 0h2v6h-2z"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    <!-- Star Button -->
                                    <button type="button" @click.prevent="toggleStar('{{ $document->id }}')" class="absolute top-2 right-2 p-1.5 rounded-full bg-white/90 dark:bg-secondary-800/90 hover:bg-white dark:hover:bg-secondary-800 shadow transition-all opacity-0 group-hover:opacity-100 {{ $document->is_starred ? '!opacity-100' : '' }}">
                                        <svg class="w-4 h-4 {{ $document->is_starred ? 'text-warning-500 fill-current' : 'text-secondary-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>

                                    <!-- Extension Badge -->
                                    <span class="absolute bottom-2 left-2 px-2 py-0.5 text-xs font-mono font-bold rounded bg-secondary-900/80 text-white uppercase">
                                        {{ $document->extension }}
                                    </span>

                                    <!-- Tags indicator -->
                                    @if($document->tags->count() > 0)
                                        <div class="absolute bottom-2 right-2 flex -space-x-1">
                                            @foreach($document->tags->take(3) as $tag)
                                                <span class="w-3 h-3 rounded-full border-2 border-white dark:border-secondary-800" style="background-color: {{ getColorHex($tag->color) }}"></span>
                                            @endforeach
                                        </div>
                                    @endif
                                </a>

                                <!-- Info -->
                                <div class="p-3">
                                    <a href="{{ route('documents.show', $document) }}" class="block font-medium text-secondary-900 dark:text-white text-sm truncate hover:text-primary-600 dark:hover:text-primary-400">
                                        {{ $document->name }}
                                    </a>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-secondary-500">{{ $document->formatted_size }}</span>
                                        <span class="text-xs text-secondary-500">{{ $document->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($document->notes)
                                        <div class="mt-2 flex items-start gap-1">
                                            <svg class="w-3 h-3 text-secondary-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                            </svg>
                                            <p class="text-xs text-secondary-500 line-clamp-2">{{ $document->notes }}</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Quick Actions -->
                                <div class="absolute bottom-14 right-2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('documents.download', $document) }}" class="p-1.5 rounded-lg bg-white dark:bg-secondary-800 shadow-lg hover:bg-secondary-100 dark:hover:bg-secondary-700" title="Telecharger">
                                        <svg class="w-4 h-4 text-secondary-600 dark:text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                    @if($document->hasPreview())
                                        <a href="{{ route('documents.preview', $document) }}" target="_blank" class="p-1.5 rounded-lg bg-white dark:bg-secondary-800 shadow-lg hover:bg-secondary-100 dark:hover:bg-secondary-700" title="Apercu">
                                            <svg class="w-4 h-4 text-secondary-600 dark:text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('documents.edit', $document) }}" class="p-1.5 rounded-lg bg-white dark:bg-secondary-800 shadow-lg hover:bg-secondary-100 dark:hover:bg-secondary-700" title="Modifier">
                                        <svg class="w-4 h-4 text-secondary-600 dark:text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($documents->hasPages())
                        <div class="mt-6">
                            {{ $documents->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Folder Modal -->
        <div x-show="folderModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="folderModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="folderModal = false" class="fixed inset-0 bg-secondary-500 dark:bg-secondary-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="folderModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-secondary-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="saveFolder()">
                        <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                            <h3 class="text-lg font-medium text-secondary-900 dark:text-white" x-text="editingFolder ? 'Modifier le dossier' : 'Nouveau dossier'"></h3>
                        </div>
                        <div class="px-6 py-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Nom du dossier *</label>
                                <input type="text" x-model="folderForm.name" class="form-input" required placeholder="ex: Factures 2024">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Dossier parent</label>
                                <select x-model="folderForm.parent_id" class="form-select">
                                    <option value="">-- Racine --</option>
                                    @foreach($folders as $folder)
                                        <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Couleur</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="color in colors" :key="color">
                                        <button type="button" @click="folderForm.color = color" :class="folderForm.color === color ? 'ring-2 ring-offset-2 ring-primary-500' : ''" class="w-8 h-8 rounded-lg transition-all" :style="'background-color: ' + getColorHex(color)"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-900 flex justify-between">
                            <button type="button" x-show="editingFolder && !editingFolder.is_system" @click="deleteFolder()" class="btn btn-danger">
                                Supprimer
                            </button>
                            <div class="flex gap-2 ml-auto">
                                <button type="button" @click="folderModal = false" class="btn btn-secondary">Annuler</button>
                                <button type="submit" class="btn btn-primary" x-text="editingFolder ? 'Enregistrer' : 'Creer'"></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tag Modal -->
        <div x-show="tagModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="tagModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="tagModal = false" class="fixed inset-0 bg-secondary-500 dark:bg-secondary-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="tagModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-secondary-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="saveTag()">
                        <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                            <h3 class="text-lg font-medium text-secondary-900 dark:text-white" x-text="editingTag ? 'Modifier l\'etiquette' : 'Nouvelle etiquette'"></h3>
                        </div>
                        <div class="px-6 py-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Nom de l'etiquette *</label>
                                <input type="text" x-model="tagForm.name" class="form-input" required placeholder="ex: Important, A traiter...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Couleur</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="color in colors" :key="color">
                                        <button type="button" @click="tagForm.color = color" :class="tagForm.color === color ? 'ring-2 ring-offset-2 ring-primary-500' : ''" class="w-8 h-8 rounded-full transition-all" :style="'background-color: ' + getColorHex(color)"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-900 flex justify-between">
                            <button type="button" x-show="editingTag" @click="deleteTag()" class="btn btn-danger">
                                Supprimer
                            </button>
                            <div class="flex gap-2 ml-auto">
                                <button type="button" @click="tagModal = false" class="btn btn-secondary">Annuler</button>
                                <button type="submit" class="btn btn-primary" x-text="editingTag ? 'Enregistrer' : 'Creer'"></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function documentArchive() {
            return {
                sidebarTab: 'folders',
                showAdvancedSearch: false,
                folderModal: false,
                tagModal: false,
                editingFolder: null,
                editingTag: null,
                folderForm: { name: '', parent_id: '', color: 'blue' },
                tagForm: { name: '', color: 'gray' },
                colors: ['gray', 'red', 'orange', 'yellow', 'green', 'teal', 'blue', 'indigo', 'purple', 'pink'],
                colorHex: {
                    'gray': '#6b7280',
                    'red': '#ef4444',
                    'orange': '#f97316',
                    'yellow': '#eab308',
                    'green': '#22c55e',
                    'teal': '#14b8a6',
                    'blue': '#3b82f6',
                    'indigo': '#6366f1',
                    'purple': '#a855f7',
                    'pink': '#ec4899'
                },
                getColorHex(color) {
                    return this.colorHex[color] || '#6b7280';
                },

                openFolderModal() {
                    this.editingFolder = null;
                    this.folderForm = { name: '', parent_id: '', color: 'blue' };
                    this.folderModal = true;
                },

                editFolder(folder) {
                    this.editingFolder = folder;
                    this.folderForm = {
                        name: folder.name,
                        parent_id: folder.parent_id || '',
                        color: folder.color || 'blue'
                    };
                    this.folderModal = true;
                },

                async saveFolder() {
                    const url = this.editingFolder
                        ? `/document-folders/${this.editingFolder.id}`
                        : '/document-folders';
                    const method = this.editingFolder ? 'PUT' : 'POST';

                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.folderForm)
                        });

                        if (response.ok) {
                            this.folderModal = false;
                            location.reload();
                        } else {
                            const data = await response.json();
                            alert(data.message || 'Une erreur est survenue');
                        }
                    } catch (error) {
                        console.error('Error saving folder:', error);
                        alert('Une erreur est survenue');
                    }
                },

                async deleteFolder() {
                    if (!confirm('Supprimer ce dossier? Les documents seront deplaces a la racine.')) return;

                    try {
                        const response = await fetch(`/document-folders/${this.editingFolder.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            this.folderModal = false;
                            location.reload();
                        } else {
                            const data = await response.json();
                            alert(data.message || 'Une erreur est survenue');
                        }
                    } catch (error) {
                        console.error('Error deleting folder:', error);
                    }
                },

                openTagModal() {
                    this.editingTag = null;
                    this.tagForm = { name: '', color: 'gray' };
                    this.tagModal = true;
                },

                editTag(tag) {
                    this.editingTag = tag;
                    this.tagForm = {
                        name: tag.name,
                        color: tag.color || 'gray'
                    };
                    this.tagModal = true;
                },

                async saveTag() {
                    const url = this.editingTag
                        ? `/document-tags/${this.editingTag.id}`
                        : '/document-tags';
                    const method = this.editingTag ? 'PUT' : 'POST';

                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.tagForm)
                        });

                        if (response.ok) {
                            this.tagModal = false;
                            location.reload();
                        } else {
                            const data = await response.json();
                            alert(data.message || 'Une erreur est survenue');
                        }
                    } catch (error) {
                        console.error('Error saving tag:', error);
                        alert('Une erreur est survenue');
                    }
                },

                async deleteTag() {
                    if (!confirm('Supprimer cette etiquette? Elle sera retiree de tous les documents.')) return;

                    try {
                        const response = await fetch(`/document-tags/${this.editingTag.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            this.tagModal = false;
                            location.reload();
                        } else {
                            const data = await response.json();
                            alert(data.message || 'Une erreur est survenue');
                        }
                    } catch (error) {
                        console.error('Error deleting tag:', error);
                    }
                },

                async toggleStar(documentId) {
                    try {
                        const response = await fetch(`/documents/${documentId}/star`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (response.ok) {
                            location.reload();
                        }
                    } catch (error) {
                        console.error('Error toggling star:', error);
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
