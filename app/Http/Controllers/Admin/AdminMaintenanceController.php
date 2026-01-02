<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class AdminMaintenanceController extends Controller
{
    public function index()
    {
        $maintenanceStatus = app()->isDownForMaintenance();

        // Get maintenance file info if exists
        $maintenanceData = null;
        if ($maintenanceStatus) {
            $maintenanceFile = storage_path('framework/down');
            if (file_exists($maintenanceFile)) {
                $maintenanceData = json_decode(file_get_contents($maintenanceFile), true);
            }
        }

        // System stats
        $stats = [
            'cache_size' => $this->getCacheSize(),
            'log_size' => $this->getLogSize(),
            'storage_size' => $this->getStorageSize(),
            'database_size' => $this->getDatabaseSize(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->format('Y-m-d H:i:s'),
            'uptime' => $this->getUptime(),
        ];

        return view('admin.maintenance.index', compact('maintenanceStatus', 'maintenanceData', 'stats'));
    }

    /**
     * Enable maintenance mode
     */
    public function enable(Request $request)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:500',
            'retry_after' => 'nullable|integer|min:60|max:86400',
            'secret' => 'nullable|string|max:255',
            'redirect_url' => 'nullable|url',
        ]);

        $options = [];

        if ($validated['message'] ?? null) {
            $options['render'] = 'errors::503';
            $options['message'] = $validated['message'];
        }

        if ($validated['retry_after'] ?? null) {
            $options['retry'] = $validated['retry_after'];
        }

        if ($validated['secret'] ?? null) {
            $options['secret'] = $validated['secret'];
        }

        if ($validated['redirect_url'] ?? null) {
            $options['redirect'] = $validated['redirect_url'];
        }

        // Put application in maintenance mode
        Artisan::call('down', $options);

        AuditLog::log('maintenance', 'Mode maintenance activé', null, [], $options);

        return back()->with('success', 'Mode maintenance activé.');
    }

    /**
     * Disable maintenance mode
     */
    public function disable()
    {
        Artisan::call('up');

        AuditLog::log('maintenance', 'Mode maintenance désactivé');

        return back()->with('success', 'Mode maintenance désactivé.');
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');

        switch ($type) {
            case 'config':
                Artisan::call('config:clear');
                $message = 'Cache de configuration vidé.';
                break;
            case 'route':
                Artisan::call('route:clear');
                $message = 'Cache des routes vidé.';
                break;
            case 'view':
                Artisan::call('view:clear');
                $message = 'Cache des vues vidé.';
                break;
            case 'cache':
                Artisan::call('cache:clear');
                $message = 'Cache applicatif vidé.';
                break;
            case 'all':
            default:
                Artisan::call('optimize:clear');
                $message = 'Tous les caches vidés.';
                break;
        }

        AuditLog::log('maintenance', "Cache vidé: {$type}");

        return back()->with('success', $message);
    }

    /**
     * Optimize application
     */
    public function optimize()
    {
        Artisan::call('optimize');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        AuditLog::log('maintenance', 'Application optimisée');

        return back()->with('success', 'Application optimisée avec succès.');
    }

    /**
     * Clear logs
     */
    public function clearLogs()
    {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        AuditLog::log('maintenance', 'Logs supprimés');

        return back()->with('success', 'Logs supprimés avec succès.');
    }

    /**
     * Run database migrations
     */
    public function migrate(Request $request)
    {
        $force = $request->boolean('force', false);

        if ($force) {
            Artisan::call('migrate', ['--force' => true]);
        } else {
            Artisan::call('migrate');
        }

        $output = Artisan::output();

        AuditLog::log('maintenance', 'Migrations exécutées', null, [], ['output' => $output]);

        return back()->with('success', 'Migrations exécutées avec succès.');
    }

    /**
     * Restart queue workers
     */
    public function restartQueue()
    {
        Artisan::call('queue:restart');

        AuditLog::log('maintenance', 'Queue workers redémarrés');

        return back()->with('success', 'Queue workers redémarrés.');
    }

    /**
     * Get cache size
     */
    protected function getCacheSize(): string
    {
        $path = storage_path('framework/cache');
        return $this->formatBytes($this->getDirSize($path));
    }

    /**
     * Get log size
     */
    protected function getLogSize(): string
    {
        $path = storage_path('logs');
        return $this->formatBytes($this->getDirSize($path));
    }

    /**
     * Get storage size
     */
    protected function getStorageSize(): string
    {
        $path = storage_path('app');
        return $this->formatBytes($this->getDirSize($path));
    }

    /**
     * Get database size (approximate)
     */
    protected function getDatabaseSize(): string
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = \DB::select("
                SELECT
                    SUM(data_length + index_length) as size
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);

            return $this->formatBytes($result[0]->size ?? 0);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get server uptime
     */
    protected function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'N/A (Windows)';
        }

        try {
            $uptime = shell_exec('uptime -p');
            return trim($uptime ?: 'N/A');
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get directory size
     */
    protected function getDirSize(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
