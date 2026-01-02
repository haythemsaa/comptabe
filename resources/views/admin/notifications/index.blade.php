<x-admin-layout>
    <x-slot name="title">Notifications</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Notifications</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.notifications.preferences') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Préférences
                </a>
                <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                        Tout marquer comme lu
                    </button>
                </form>
                <form action="{{ route('admin.notifications.delete-read') }}" method="POST" class="inline" onsubmit="return confirm('Supprimer toutes les notifications lues?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-danger-500/20 text-danger-400 hover:bg-danger-500/30 rounded-lg transition-colors">
                        Supprimer lues
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Non lues</p>
                    <p class="text-2xl font-bold text-warning-400">{{ $stats['unread'] }}</p>
                </div>
                <svg class="w-10 h-10 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Total</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <svg class="w-10 h-10 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['today'] }}</p>
                </div>
                <svg class="w-10 h-10 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <div class="divide-y divide-secondary-700">
            @forelse($notifications as $notification)
                <div class="p-4 hover:bg-secondary-700/50 transition-colors {{ $notification->read_at ? 'opacity-60' : '' }}">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
                            @if(($notification->data['severity'] ?? 'info') === 'critical') bg-danger-500/20
                            @elseif(($notification->data['severity'] ?? 'info') === 'warning') bg-warning-500/20
                            @else bg-primary-500/20
                            @endif">
                            @if(($notification->data['icon'] ?? '') === 'error')
                                <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif(($notification->data['icon'] ?? '') === 'company')
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-white">{{ $notification->data['title'] ?? 'Notification' }}</h3>
                                    <p class="text-sm text-secondary-300 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-secondary-500">
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                        @if(!$notification->read_at)
                                            <span class="px-2 py-1 rounded-full bg-primary-500/20 text-primary-400">Nouveau</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if(isset($notification->data['action_url']))
                                        <a href="{{ $notification->data['action_url'] }}" class="px-3 py-1 text-sm bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                                            {{ $notification->data['action_text'] ?? 'Voir' }}
                                        </a>
                                    @endif
                                    @if(!$notification->read_at)
                                        <button onclick="markAsRead('{{ $notification->id }}')" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Marquer comme lu">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    @endif
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-danger-400" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-secondary-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-lg font-medium">Aucune notification</p>
                    <p class="text-sm mt-1">Vous serez notifié des événements importants ici</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function markAsRead(notificationId) {
            fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }

        // Poll for new notifications every 30 seconds
        setInterval(() => {
            fetch('/admin/notifications/latest')
                .then(response => response.json())
                .then(data => {
                    if (data.unread_count > {{ $stats['unread'] }}) {
                        // New notification received, reload
                        window.location.reload();
                    }
                });
        }, 30000);
    </script>
    @endpush
</x-admin-layout>
