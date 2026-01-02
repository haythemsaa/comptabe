@props(['rows' => 5])

<div class="card animate-pulse">
    <div class="card-header">
        <div class="h-5 bg-secondary-200 dark:bg-secondary-700 rounded w-40"></div>
    </div>
    <div class="card-body p-0">
        <div class="divide-y divide-secondary-200 dark:divide-secondary-700">
            @for($i = 0; $i < $rows; $i++)
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 bg-secondary-200 dark:bg-secondary-700 rounded-full"></div>
                    <div class="flex-1">
                        <div class="h-4 bg-secondary-200 dark:bg-secondary-700 rounded w-3/4 mb-2"></div>
                        <div class="h-3 bg-secondary-200 dark:bg-secondary-700 rounded w-1/2"></div>
                    </div>
                </div>
                <div class="h-4 bg-secondary-200 dark:bg-secondary-700 rounded w-20"></div>
            </div>
            @endfor
        </div>
    </div>
</div>
