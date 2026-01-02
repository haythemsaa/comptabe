<div x-data="chatWidget()" x-cloak>
    <!-- Floating button (bottom-right) -->
    <button @click="toggle()"
            type="button"
            class="fixed bottom-6 right-6 z-50 btn btn-primary rounded-full shadow-lg w-14 h-14 flex items-center justify-center hover:scale-110 transition-transform">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <span x-show="unreadCount > 0"
              x-text="unreadCount"
              class="absolute -top-1 -right-1 bg-danger-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
        </span>
    </button>

    <!-- Chat panel (slide from right) -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed bottom-0 right-0 w-full md:w-96 h-[600px] md:h-[700px] bg-white dark:bg-secondary-800 shadow-2xl md:rounded-t-xl z-50 flex flex-col"
         style="display: none;">

        <!-- Header -->
        <div class="p-4 border-b border-secondary-200 dark:border-secondary-700 flex justify-between items-center bg-primary-500 text-white md:rounded-t-xl">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg">Assistant ComptaBE</h3>
            </div>
            <button @click="toggle()"
                    type="button"
                    class="hover:bg-white/10 rounded p-1 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Messages list -->
        <div x-ref="messages"
             class="flex-1 overflow-y-auto p-4 space-y-4 bg-secondary-50 dark:bg-secondary-900">

            <!-- Empty state -->
            <template x-if="messages.length === 0 && !loading">
                <div class="flex flex-col items-center justify-center h-full text-center px-4">
                    <svg class="w-16 h-16 text-secondary-300 dark:text-secondary-600 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                    </svg>
                    <h4 class="font-semibold text-secondary-700 dark:text-secondary-300 mb-2">
                        Comment puis-je vous aider ?
                    </h4>
                    <p class="text-sm text-secondary-500 dark:text-secondary-400">
                        Posez-moi des questions sur vos factures, créez des devis, invitez des utilisateurs et plus encore.
                    </p>
                </div>
            </template>

            <!-- Messages -->
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[85%] rounded-lg p-3 shadow-sm"
                         :class="msg.role === 'user'
                             ? 'bg-primary-500 text-white'
                             : (msg.error ? 'bg-danger-100 dark:bg-danger-900 text-danger-800 dark:text-danger-200' : 'bg-white dark:bg-secondary-800 text-secondary-900 dark:text-secondary-100')">

                        <!-- Assistant message -->
                        <template x-if="msg.role === 'assistant'">
                            <div class="flex items-start gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <!-- Content -->
                                    <div class="prose prose-sm dark:prose-invert max-w-none" x-html="typeof marked !== 'undefined' ? marked.parse(msg.content || '') : (msg.content || '')"></div>

                                    <!-- Tool calls -->
                                    <template x-if="msg.tool_calls && msg.tool_calls.length > 0">
                                        <div class="mt-2 space-y-2">
                                            <template x-for="(tool, index) in msg.tool_calls" :key="index">
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
                                    <div class="text-xs opacity-70 mt-1" x-text="new Date(msg.created_at).toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' })"></div>
                                </div>
                            </div>
                        </template>

                        <!-- User message -->
                        <template x-if="msg.role === 'user'">
                            <div>
                                <div class="whitespace-pre-wrap break-words" x-text="msg.content"></div>
                                <div class="text-xs opacity-80 mt-1 text-right" x-text="new Date(msg.created_at).toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' })"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Typing indicator -->
            <div x-show="loading" class="flex gap-2 items-center">
                <div class="flex gap-1 bg-white dark:bg-secondary-800 rounded-lg px-4 py-3 shadow">
                    <span class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                    <span class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="p-4 border-t border-secondary-200 dark:border-secondary-700 bg-white dark:bg-secondary-800">
            <form @submit.prevent="sendMessage()" class="flex gap-2">
                <textarea x-model="currentMessage"
                          @keydown="handleKeydown($event)"
                          placeholder="Tapez votre message... (Entrée pour envoyer)"
                          rows="1"
                          :disabled="loading"
                          class="flex-1 resize-none rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                          style="max-height: 120px;"></textarea>
                <button type="submit"
                        :disabled="loading || !currentMessage.trim()"
                        class="btn btn-primary self-end disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
