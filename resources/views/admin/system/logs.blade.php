<x-admin-layout>
    <x-slot name="title">Logs Systeme</x-slot>
    <x-slot name="header">Logs Laravel</x-slot>

    <div class="flex items-center justify-between mb-6">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-secondary-400 text-sm">Afficher:</label>
            <select name="lines" onchange="this.form.submit()" class="bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="50" {{ $lines == 50 ? 'selected' : '' }}>50 lignes</option>
                <option value="100" {{ $lines == 100 ? 'selected' : '' }}>100 lignes</option>
                <option value="200" {{ $lines == 200 ? 'selected' : '' }}>200 lignes</option>
                <option value="500" {{ $lines == 500 ? 'selected' : '' }}>500 lignes</option>
            </select>
        </form>

        <form action="{{ route('admin.system.logs.clear') }}" method="POST" onsubmit="return confirm('Vider le fichier de log?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-danger-500 hover:bg-danger-600 rounded-lg text-sm font-medium transition-colors">
                Vider les logs
            </button>
        </form>
    </div>

    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <div class="p-4 border-b border-secondary-700 flex items-center justify-between">
            <span class="text-sm text-secondary-400">storage/logs/laravel.log</span>
            <span class="text-sm text-secondary-500">{{ $lines }} dernieres lignes</span>
        </div>
        <div class="p-4 overflow-x-auto">
            <pre class="text-sm font-mono text-secondary-300 whitespace-pre-wrap break-all max-h-[600px] overflow-y-auto">{{ $content }}</pre>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.system.health') }}" class="text-primary-400 hover:text-primary-300 text-sm">
            &larr; Retour a la sante systeme
        </a>
    </div>
</x-admin-layout>
