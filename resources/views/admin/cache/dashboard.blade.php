@extends('layouts.app')

@section('title', 'Cache Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cache Dashboard</h1>
            <p class="text-gray-600 mt-1">Surveillance et gestion du cache système</p>
        </div>

        <div class="flex gap-2">
            <form action="{{ route('admin.cache.warmup') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    🔥 Préchauffer
                </button>
            </form>

            <form action="{{ route('admin.cache.optimize') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    ⚡ Optimiser
                </button>
            </form>

            <form action="{{ route('admin.cache.clear') }}" method="POST" class="inline"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir vider tout le cache ?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    🗑️ Vider
                </button>
            </form>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-4">
            {{ session('info') }}
        </div>
    @endif

    {{-- Cache Driver Info --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 flex items-center">
            <span class="mr-2">⚙️</span> Configuration Cache
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Driver</p>
                <p class="text-2xl font-bold text-gray-900">{{ strtoupper($cacheDriver) }}</p>
            </div>

            @if (isset($metrics['status']))
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Statut</p>
                    <p class="text-2xl font-bold {{ $metrics['status'] === 'connected' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $metrics['status'] === 'connected' ? '✅ Connecté' : '❌ Erreur' }}
                    </p>
                </div>
            @endif

            @if (isset($metrics['version']))
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Version</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['version'] }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Redis Metrics --}}
    @if ($cacheDriver === 'redis' && isset($metrics['status']) && $metrics['status'] === 'connected')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Memory Usage --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Mémoire Utilisée</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $metrics['memory_used'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Peak: {{ $metrics['memory_peak'] }}</p>
                    </div>
                    <div class="text-4xl">💾</div>
                </div>
            </div>

            {{-- Total Keys --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Clés Totales</p>
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($metrics['total_keys']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Évictions: {{ number_format($metrics['evicted_keys']) }}</p>
                    </div>
                    <div class="text-4xl">🔑</div>
                </div>
            </div>

            {{-- Hit Rate --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Taux de Succès</p>
                        <p class="text-2xl font-bold text-green-600">{{ $metrics['hit_rate'] }}%</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($metrics['hits']) }} hits / {{ number_format($metrics['misses']) }} miss</p>
                    </div>
                    <div class="text-4xl">🎯</div>
                </div>
            </div>

            {{-- Uptime --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Uptime</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ $metrics['uptime'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $metrics['connected_clients'] }} clients</p>
                    </div>
                    <div class="text-4xl">⏱️</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Database Cache Metrics --}}
    @if ($cacheDriver === 'database' && isset($metrics['status']) && $metrics['status'] === 'connected')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Keys --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Clés Totales</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($metrics['total_keys']) }}</p>
                    </div>
                    <div class="text-4xl">🔑</div>
                </div>
            </div>

            {{-- Valid Keys --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Clés Valides</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($metrics['valid_keys']) }}</p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
            </div>

            {{-- Expired Keys --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Clés Expirées</p>
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($metrics['expired_keys']) }}</p>
                        @if ($metrics['expired_keys'] > 100)
                            <p class="text-xs text-orange-500 mt-1">⚠️ Optimisation recommandée</p>
                        @endif
                    </div>
                    <div class="text-4xl">⏰</div>
                </div>
            </div>

            {{-- Table Size --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Taille Table</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $metrics['table_size_mb'] }} MB</p>
                    </div>
                    <div class="text-4xl">💾</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Top Cache Keys --}}
    @if (!empty($topKeys))
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center">
                <span class="mr-2">📊</span> Top Clés Cache (par taille)
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Clé
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Taille
                            </th>
                            @if (isset($topKeys[0]['type']))
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                            @endif
                            @if (isset($topKeys[0]['ttl']))
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    TTL
                                </th>
                            @endif
                            @if (isset($topKeys[0]['expires_at']))
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Expire à
                                </th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($topKeys as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 font-mono">
                                    {{ Str::limit($item['key'], 60) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $item['size'] }}
                                </td>
                                @if (isset($item['type']))
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <span class="px-2 py-1 bg-gray-100 rounded">{{ $item['type'] }}</span>
                                    </td>
                                @endif
                                @if (isset($item['ttl']))
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $item['ttl'] }}
                                    </td>
                                @endif
                                @if (isset($item['expires_at']))
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $item['expires_at'] }}
                                    </td>
                                @endif
                                <td class="px-6 py-4 text-sm">
                                    <form action="{{ route('admin.cache.clear-key') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Hit Rate Statistics (Redis only) --}}
    @if ($cacheDriver === 'redis' && isset($hitRate['rate']) && $hitRate['rate'] !== 'N/A')
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 flex items-center">
                <span class="mr-2">📈</span> Statistiques de Performance
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Cache Hits</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($hitRate['hits']) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Cache Misses</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($hitRate['misses']) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Taux de Succès</p>
                    <div class="relative pt-1">
                        <div class="flex items-center justify-center mb-2">
                            <span class="text-3xl font-bold text-blue-600">{{ $hitRate['rate'] }}%</span>
                        </div>
                        <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                            <div style="width:{{ $hitRate['rate'] }}%"
                                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center
                                        {{ $hitRate['rate'] >= 80 ? 'bg-green-500' : ($hitRate['rate'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($hitRate['rate'] < 50)
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        ⚠️ <strong>Attention:</strong> Le taux de succès du cache est faible (< 50%).
                        Considérez d'augmenter les TTL ou de préchauffer le cache plus fréquemment.
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- Error Display --}}
    @if (isset($metrics['error']))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <p class="font-semibold">Erreur de connexion au cache:</p>
            <p class="text-sm">{{ $metrics['error'] }}</p>
        </div>
    @endif
</div>
@endsection
