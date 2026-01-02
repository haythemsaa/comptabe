<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $stats = [
            'companies' => Company::count(),
            'users' => User::count(),
            'invoices' => Invoice::count(),
            'audit_logs' => AuditLog::count(),
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'storage_used' => $this->getStorageUsed(),
        ];

        return view('admin.settings.index', compact('stats'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'max_companies_per_user' => 'nullable|integer|min:1|max:100',
            'default_trial_days' => 'nullable|integer|min:0|max:365',
            'peppol_default_provider' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        AuditLog::log('update', 'Paramètres système modifiés', null, null, $validated);

        return back()->with('success', 'Paramètres enregistrés.');
    }

    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');

        if ($type === 'views') {
            Artisan::call('view:clear');
            $message = 'Cache des vues vidé.';
        } elseif ($type === 'config') {
            Artisan::call('config:clear');
            $message = 'Cache de configuration vidé.';
        } else {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            $message = 'Tout le cache a été vidé.';
        }

        AuditLog::log('update', 'Cache système vidé: ' . $type);

        return back()->with('success', $message);
    }

    public function maintenance(Request $request)
    {
        $action = $request->input('action', 'down');

        if ($action === 'up') {
            Artisan::call('up');
            AuditLog::log('update', 'Mode maintenance désactivé');
            return back()->with('success', 'Mode maintenance désactivé.');
        } else {
            $secret = 'superadmin-' . substr(md5(auth()->id() . now()), 0, 10);
            Artisan::call('down', [
                '--secret' => $secret,
            ]);
            AuditLog::log('update', 'Mode maintenance activé');
            return back()->with('success', "Mode maintenance activé. URL secrète: /{$secret}");
        }
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            AuditLog::log('update', 'Migrations exécutées');

            return back()->with('success', 'Migrations exécutées avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function retryFailedJobs()
    {
        try {
            Artisan::call('queue:retry', ['id' => 'all']);
            AuditLog::log('update', 'Jobs échoués relancés');
            return back()->with('success', 'Les jobs échoués ont été relancés.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function storageLink()
    {
        try {
            Artisan::call('storage:link');
            AuditLog::log('update', 'Lien symbolique storage créé');
            return back()->with('success', 'Lien symbolique créé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    protected function getStorageUsed(): string
    {
        try {
            $path = storage_path('app/public');
            if (!is_dir($path)) {
                return '0 B';
            }
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    protected function getSettings(): array
    {
        return [
            'app_name' => $this->getSetting('app_name', config('app.name')),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'registration_enabled' => $this->getSetting('registration_enabled', true),
            'max_companies_per_user' => $this->getSetting('max_companies_per_user', 10),
            'default_trial_days' => $this->getSetting('default_trial_days', 14),
            'peppol_default_provider' => $this->getSetting('peppol_default_provider', ''),
        ];
    }

    protected function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => DB::connection()->getDatabaseName(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'disk_free' => $this->formatBytes(disk_free_space(base_path())),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'timezone' => config('app.timezone'),
        ];
    }

    protected function getSetting(string $key, $default = null)
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = DB::table('system_settings')->where('key', $key)->first();
            return $setting ? $this->castValue($setting->value, $setting->type) : $default;
        });
    }

    protected function setSetting(string $key, $value): void
    {
        $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string');

        DB::table('system_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'type' => $type,
                'updated_at' => now(),
            ]
        );

        Cache::forget("system_setting_{$key}");
    }

    protected function castValue($value, string $type)
    {
        return match($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
