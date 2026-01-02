<div class="flex items-center gap-4 px-6 py-4 hover:bg-secondary-50 dark:hover:bg-dark-500 transition-colors">
    <div class="flex items-center gap-3" style="padding-left: {{ $level * 24 }}px">
        @if($category->children->isNotEmpty())
            <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @else
            <div class="w-4"></div>
        @endif

        <div
            class="w-8 h-8 rounded-lg flex items-center justify-center"
            style="background-color: {{ $category->color ?? '#6B7280' }}20; color: {{ $category->color ?? '#6B7280' }}"
        >
            @if($category->icon)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            @endif
        </div>

        <div>
            <div class="flex items-center gap-2">
                <span class="font-medium text-secondary-900 dark:text-white">{{ $category->name }}</span>
                @if(!$category->is_active)
                    <span class="text-xs px-1.5 py-0.5 bg-secondary-100 dark:bg-secondary-800 text-secondary-500 rounded">Inactif</span>
                @endif
            </div>
            <div class="text-xs text-secondary-500 dark:text-secondary-400">
                {{ $category->products_count }} produits
            </div>
        </div>
    </div>

    <div class="flex-1"></div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('settings.product-categories.edit', $category) }}"
            class="p-2 text-secondary-400 hover:text-secondary-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors"
            title="Modifier"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </a>
        @if($category->products_count === 0 && $category->children->isEmpty())
            <form action="{{ route('settings.product-categories.destroy', $category) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="p-2 text-secondary-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/30 rounded-lg transition-colors"
                    title="Supprimer"
                    onclick="return confirm('Supprimer cette catégorie ?')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        @endif
    </div>
</div>

@if($category->children->isNotEmpty())
    @foreach($category->children as $child)
        @include('settings.product-categories._category-row', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
