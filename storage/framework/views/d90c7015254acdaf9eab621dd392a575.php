
<div x-data="notificationCenter()" x-cloak>
    
    <div class="relative">
        <button @click="toggleDropdown()"
                class="relative p-2 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-secondary-700 rounded-lg transition-colors"
                :class="{ 'bg-secondary-100 dark:bg-secondary-700': isOpen }">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            
            <span x-show="unreadCount > 0"
                  x-text="unreadCount > 99 ? '99+' : unreadCount"
                  class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 rounded-full"
                  :class="{
                      'bg-danger-500': criticalCount > 0,
                      'bg-warning-500': criticalCount === 0 && warningCount > 0,
                      'bg-info-500': criticalCount === 0 && warningCount === 0
                  }"></span>
        </button>

        
        <div x-show="isOpen"
             @click.away="isOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 w-96 bg-white dark:bg-secondary-800 rounded-lg shadow-xl border border-secondary-200 dark:border-secondary-700 overflow-hidden z-50"
             style="max-height: 600px;">

            
            <div class="px-4 py-3 bg-secondary-50 dark:bg-secondary-900 border-b border-secondary-200 dark:border-secondary-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">
                        Notifications
                    </h3>
                    <div class="flex gap-2">
                        <button @click="markAllAsRead()"
                                x-show="unreadCount > 0"
                                class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                            Tout marquer comme lu
                        </button>
                        <button @click="loadNotifications()"
                                class="text-secondary-600 dark:text-secondary-400 hover:text-secondary-700 dark:hover:text-secondary-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>

                
                <div class="flex gap-2 mt-2">
                    <button @click="filterSeverity = 'all'; loadNotifications()"
                            :class="filterSeverity === 'all' ? 'bg-primary-500 text-white' : 'bg-white dark:bg-secondary-700 text-secondary-700 dark:text-secondary-300'"
                            class="px-3 py-1 text-xs rounded-full">
                        Toutes
                    </button>
                    <button @click="filterSeverity = 'critical'; loadNotifications()"
                            :class="filterSeverity === 'critical' ? 'bg-danger-500 text-white' : 'bg-white dark:bg-secondary-700 text-secondary-700 dark:text-secondary-300'"
                            class="px-3 py-1 text-xs rounded-full flex items-center gap-1">
                        Critique <span x-show="criticalCount > 0" x-text="`(${criticalCount})`"></span>
                    </button>
                    <button @click="filterSeverity = 'warning'; loadNotifications()"
                            :class="filterSeverity === 'warning' ? 'bg-warning-500 text-white' : 'bg-white dark:bg-secondary-700 text-secondary-700 dark:text-secondary-300'"
                            class="px-3 py-1 text-xs rounded-full flex items-center gap-1">
                        Alerte <span x-show="warningCount > 0" x-text="`(${warningCount})`"></span>
                    </button>
                </div>
            </div>

            
            <div class="overflow-y-auto" style="max-height: 450px;">
                <template x-if="loading">
                    <div class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
                    </div>
                </template>

                <template x-if="!loading && notifications.length === 0">
                    <div class="flex flex-col items-center justify-center py-12 text-secondary-500 dark:text-secondary-400">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium">Aucune notification</p>
                        <p class="text-xs mt-1">Vous êtes à jour!</p>
                    </div>
                </template>

                <template x-if="!loading && notifications.length > 0">
                    <div>
                        <template x-for="notification in notifications" :key="notification.id">
                            <div @click="handleNotificationClick(notification)"
                                 class="px-4 py-3 border-b border-secondary-100 dark:border-secondary-700 hover:bg-secondary-50 dark:hover:bg-secondary-700/50 cursor-pointer transition-colors"
                                 :class="{ 'bg-primary-50 dark:bg-primary-900/20': !notification.read_at }">

                                <div class="flex gap-3">
                                    
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                             :class="{
                                                 'bg-danger-100 dark:bg-danger-900 text-danger-600 dark:text-danger-400': notification.severity === 'critical',
                                                 'bg-warning-100 dark:bg-warning-900 text-warning-600 dark:text-warning-400': notification.severity === 'warning',
                                                 'bg-info-100 dark:bg-info-900 text-info-600 dark:text-info-400': notification.severity === 'info'
                                             }">
                                            <template x-if="notification.icon === 'alert-circle'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </template>
                                            <template x-if="notification.icon === 'trending-down'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                                </svg>
                                            </template>
                                            <template x-if="notification.icon === 'refresh'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </template>
                                            <template x-if="notification.icon === 'file-text'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </template>
                                        </div>
                                    </div>

                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-secondary-900 dark:text-white" x-text="notification.title"></p>
                                            <span x-show="!notification.read_at" class="inline-block w-2 h-2 bg-primary-500 rounded-full flex-shrink-0"></span>
                                        </div>
                                        <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1" x-text="notification.message"></p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="text-xs text-secondary-500 dark:text-secondary-500" x-text="formatDate(notification.created_at)"></span>
                                            <template x-if="notification.action_url">
                                                <span class="text-xs text-primary-600 dark:text-primary-400 font-medium" x-text="notification.action_text || 'Voir'"></span>
                                            </template>
                                        </div>
                                    </div>

                                    
                                    <div class="flex-shrink-0">
                                        <button @click.stop="deleteNotification(notification.id)"
                                                class="text-secondary-400 hover:text-secondary-600 dark:text-secondary-500 dark:hover:text-secondary-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            
            <div class="px-4 py-3 bg-secondary-50 dark:bg-secondary-900 border-t border-secondary-200 dark:border-secondary-700">
                <button @click="deleteAllRead()"
                        class="w-full text-center text-sm text-secondary-600 dark:text-secondary-400 hover:text-secondary-800 dark:hover:text-secondary-200">
                    Supprimer les notifications lues
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationCenter', () => ({
        isOpen: false,
        loading: false,
        notifications: [],
        unreadCount: 0,
        criticalCount: 0,
        warningCount: 0,
        filterSeverity: 'all',

        init() {
            // Wait for axios to be available before loading
            const initNotifications = () => {
                if (typeof window.axios === 'undefined') {
                    setTimeout(initNotifications, 100);
                    return;
                }

                this.loadNotifications();
                this.loadUnreadCount();

                // Poll for new notifications every 60 seconds
                setInterval(() => {
                    if (!this.isOpen) {
                        this.loadUnreadCount();
                    }
                }, 60000);

                // Listen for custom notification event (from real-time updates)
                window.addEventListener('notification-received', () => {
                    this.loadNotifications();
                    this.loadUnreadCount();
                });
            };

            initNotifications();
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.loadNotifications();
            }
        },

        async loadNotifications() {
            if (typeof window.axios === 'undefined') {
                console.warn('Axios not loaded yet, skipping notification load');
                return;
            }

            this.loading = true;

            try {
                const params = new URLSearchParams({
                    per_page: 50,
                });

                if (this.filterSeverity !== 'all') {
                    params.append('severity', this.filterSeverity);
                }

                const response = await window.axios.get(`/api/notifications?${params}`);

                if (response.data.success) {
                    this.notifications = response.data.data;
                }
            } catch (error) {
                // Silently ignore 401 (not authenticated) errors
                if (error.response?.status !== 401) {
                    console.error('Error loading notifications:', error);
                }
            } finally {
                this.loading = false;
            }
        },

        async loadUnreadCount() {
            if (typeof window.axios === 'undefined') {
                console.warn('Axios not loaded yet, skipping unread count');
                return;
            }

            try {
                const response = await window.axios.get('/api/notifications/unread-count');

                if (response.data.success) {
                    this.unreadCount = response.data.data.count;
                    this.criticalCount = response.data.data.by_severity.critical || 0;
                    this.warningCount = response.data.data.by_severity.warning || 0;
                }
            } catch (error) {
                // Silently ignore 401 (not authenticated) errors
                if (error.response?.status !== 401) {
                    console.error('Error loading unread count:', error);
                }
            }
        },

        async handleNotificationClick(notification) {
            // Mark as read
            if (!notification.read_at) {
                await this.markAsRead(notification.id);
            }

            // Navigate to action URL
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },

        async markAsRead(notificationId) {
            if (typeof window.axios === 'undefined') return;
            try {
                await window.axios.post(`/api/notifications/${notificationId}/mark-as-read`);
                await this.loadNotifications();
                await this.loadUnreadCount();
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            if (typeof window.axios === 'undefined') return;
            try {
                await window.axios.post('/api/notifications/mark-all-as-read');
                await this.loadNotifications();
                await this.loadUnreadCount();
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },

        async deleteNotification(notificationId) {
            if (typeof window.axios === 'undefined') return;
            try {
                await window.axios.delete(`/api/notifications/${notificationId}`);
                await this.loadNotifications();
                await this.loadUnreadCount();
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        },

        async deleteAllRead() {
            if (!confirm('Supprimer toutes les notifications lues?')) {
                return;
            }
            if (typeof window.axios === 'undefined') return;

            try {
                await window.axios.delete('/api/notifications/read/all');
                await this.loadNotifications();
                await this.loadUnreadCount();
            } catch (error) {
                console.error('Error deleting read notifications:', error);
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'À l\'instant';
            if (diffMins < 60) return `Il y a ${diffMins} min`;
            if (diffHours < 24) return `Il y a ${diffHours}h`;
            if (diffDays < 7) return `Il y a ${diffDays}j`;

            return date.toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'short',
            });
        },
    }));
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\compta\resources\views/components/notifications/notification-center.blade.php ENDPATH**/ ?>