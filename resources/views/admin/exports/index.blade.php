<x-admin-layout>
    <x-slot name="title">Exports</x-slot>
    <x-slot name="header">Centre d'Export</x-slot>

    <div class="mb-6">
        <p class="text-secondary-400">Exportez vos donnees au format CSV ou JSON pour analyse externe ou sauvegarde.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($exports as $key => $export)
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 hover:border-primary-500/50 transition-colors">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                        @if($export['icon'] === 'building')
                            <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        @elseif($export['icon'] === 'users')
                            <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        @elseif($export['icon'] === 'credit-card')
                            <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        @elseif($export['icon'] === 'document')
                            <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @elseif($export['icon'] === 'receipt')
                            <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        @endif
                    </div>
                    <span class="px-3 py-1 bg-secondary-700 rounded-full text-sm font-medium">
                        {{ number_format($export['count']) }} lignes
                    </span>
                </div>

                <h3 class="text-lg font-semibold mb-2">{{ $export['label'] }}</h3>
                <p class="text-secondary-400 text-sm mb-4">{{ $export['description'] }}</p>

                <div class="flex gap-2">
                    <a href="{{ route('admin.exports.download', ['type' => $key, 'format' => 'csv']) }}"
                       class="flex-1 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-center text-sm font-medium transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            CSV
                        </span>
                    </a>
                    <a href="{{ route('admin.exports.download', ['type' => $key, 'format' => 'json']) }}"
                       class="flex-1 px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-center text-sm font-medium transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            JSON
                        </span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Export Info -->
    <div class="mt-8 bg-secondary-800 rounded-xl border border-secondary-700 p-6">
        <h3 class="text-lg font-semibold mb-4">Informations sur les exports</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-secondary-300 mb-2">Format CSV</h4>
                <ul class="text-sm text-secondary-400 space-y-1">
                    <li>- Separateur: point-virgule (;)</li>
                    <li>- Encodage: UTF-8 avec BOM</li>
                    <li>- Compatible Excel, Google Sheets, etc.</li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium text-secondary-300 mb-2">Format JSON</h4>
                <ul class="text-sm text-secondary-400 space-y-1">
                    <li>- Format structure pour integrations</li>
                    <li>- Encodage: UTF-8</li>
                    <li>- Ideal pour import dans d'autres systemes</li>
                </ul>
            </div>
        </div>

        <div class="mt-4 p-4 bg-info-500/10 border border-info-500/30 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-info-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-info-300">
                    <strong>Note:</strong> Les exports volumineux (factures clients, logs d'audit) sont limites aux 10 000 dernieres entrees pour des raisons de performance.
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
