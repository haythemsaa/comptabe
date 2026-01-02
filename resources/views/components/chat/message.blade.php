@props(['message'])

<div class="flex" :class="'{{ $message['role'] }}' === 'user' ? 'justify-end' : 'justify-start'">
    <div class="max-w-[85%] rounded-lg p-3 shadow-sm"
         :class="'{{ $message['role'] }}' === 'user'
             ? 'bg-primary-500 text-white'
             : ('{{ $message['error'] ?? false }}' ? 'bg-danger-100 dark:bg-danger-900 text-danger-800 dark:text-danger-200' : 'bg-white dark:bg-secondary-800 text-secondary-900 dark:text-secondary-100')">

        <!-- Assistant avatar and content -->
        <template x-if="'{{ $message['role'] }}' === 'assistant'">
            <div class="flex items-start gap-2">
                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <!-- Content -->
                    <div class="prose prose-sm dark:prose-invert max-w-none" x-html="marked.parse('{{ addslashes($message['content'] ?? '') }}')"></div>

                    <!-- Tool calls -->
                    <template x-if="'{{ !empty($message['tool_calls']) }}' && {{ json_encode($message['tool_calls'] ?? []) }}.length > 0">
                        <div class="mt-2 space-y-2">
                            <template x-for="(tool, index) in {{ json_encode($message['tool_calls'] ?? []) }}" :key="index">
                                <div class="text-xs bg-secondary-100 dark:bg-secondary-700 rounded p-2 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-lg">🔧</span>
                                        <span class="font-medium truncate" x-text="tool.name"></span>
                                    </div>
                                    <template x-if="tool.requires_confirmation && !tool.confirmed">
                                        <button @click="confirmTool(tool.execution_id)"
                                                type="button"
                                                class="btn btn-sm btn-warning flex-shrink-0">
                                            Confirmer
                                        </button>
                                    </template>
                                    <template x-if="tool.status === 'success'">
                                        <span class="text-success-500 flex-shrink-0">✓</span>
                                    </template>
                                    <template x-if="tool.status === 'error'">
                                        <span class="text-danger-500 flex-shrink-0" :title="tool.error">✗</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Timestamp -->
                    <div class="text-xs opacity-70 mt-1" x-text="new Date('{{ $message['created_at'] }}').toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' })"></div>
                </div>
            </div>
        </template>

        <!-- User message -->
        <template x-if="'{{ $message['role'] }}' === 'user'">
            <div>
                <div class="whitespace-pre-wrap break-words">{{ $message['content'] }}</div>
                <div class="text-xs opacity-80 mt-1 text-right" x-text="new Date('{{ $message['created_at'] }}').toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' })"></div>
            </div>
        </template>
    </div>
</div>
