<x-admin-layout>
    <x-slot name="title">PHP Info</x-slot>
    <x-slot name="header">PHP Info</x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.system.health') }}" class="text-primary-400 hover:text-primary-300 text-sm">
            &larr; Retour a la sante systeme
        </a>
    </div>

    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <style>
            .phpinfo-container table { width: 100%; border-collapse: collapse; }
            .phpinfo-container td, .phpinfo-container th { padding: 8px 12px; border: 1px solid #374151; text-align: left; }
            .phpinfo-container th { background: #1f2937; color: #9ca3af; }
            .phpinfo-container td { background: #111827; color: #e5e7eb; }
            .phpinfo-container h1, .phpinfo-container h2 { color: #f3f4f6; padding: 16px; margin: 0; }
            .phpinfo-container h1 { font-size: 1.5rem; background: #374151; }
            .phpinfo-container h2 { font-size: 1.125rem; background: #1f2937; border-top: 1px solid #374151; }
            .phpinfo-container img { display: none; }
            .phpinfo-container a { color: #60a5fa; }
            .phpinfo-container .e { background: #1f2937 !important; color: #9ca3af !important; font-weight: 600; }
            .phpinfo-container .v { background: #111827 !important; color: #e5e7eb !important; word-break: break-all; }
        </style>
        <div class="phpinfo-container overflow-x-auto">
            {!! $phpinfoBody !!}
        </div>
    </div>
</x-admin-layout>
