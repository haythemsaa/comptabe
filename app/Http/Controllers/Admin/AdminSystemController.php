<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class AdminSystemController extends Controller
{
    public function health()
    {
        $checks = [];

        // Database Check
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'message' => 'Connexion MySQL active',
                'details' => 'Version: ' . DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'error',
                'message' => 'Erreur de connexion',
                'details' => $e->getMessage(),
            ];
        }

        // Cache Check
        try {
            Cache::put('health_check', 'ok', 10);
            $cacheValue = Cache::get('health_check');
            $checks['cache'] = [
                'status' => $cacheValue === 'ok' ? 'ok' : 'warning',
                'message' => $cacheValue === 'ok' ? 'Cache fonctionnel' : 'Cache partiellement fonctionnel',
                'details' => 'Driver: ' . config('cache.default'),
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'error',
                'message' => 'Erreur cache',
                'details' => $e->getMessage(),
            ];
        }

        // Storage Check
        try {
            $testFile = 'health_check_' . time() . '.txt';
            Storage::put($testFile, 'test');
            Storage::delete($testFile);
            $checks['storage'] = [
                'status' => 'ok',
                'message' => 'Stockage accessible',
                'details' => 'Driver: ' . config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            $checks['storage'] = [
                'status' => 'error',
                'message' => 'Erreur stockage',
                'details' => $e->getMessage(),
            ];
        }

        // Queue Check
        try {
            $queueDriver = config('queue.default');
            $checks['queue'] = [
                'status' => 'ok',
                'message' => 'File d\'attente configuree',
                'details' => 'Driver: ' . $queueDriver,
            ];

            if ($queueDriver === 'database') {
                $pendingJobs = DB::table('jobs')->count();
                $failedJobs = DB::table('failed_jobs')->count();
                $checks['queue']['details'] .= " | En attente: {$pendingJobs} | Echecs: {$failedJobs}";

                if ($failedJobs > 0) {
                    $checks['queue']['status'] = 'warning';
                }
            }
        } catch (\Exception $e) {
            $checks['queue'] = [
                'status' => 'warning',
                'message' => 'Queue non configuree',
                'details' => $e->getMessage(),
            ];
        }

        // Mail Check
        $mailDriver = config('mail.default');
        $checks['mail'] = [
            'status' => $mailDriver !== 'log' ? 'ok' : 'warning',
            'message' => $mailDriver !== 'log' ? 'Mail configure' : 'Mail en mode log',
            'details' => 'Driver: ' . $mailDriver,
        ];

        // Disk Space Check
        $freeSpace = disk_free_space(base_path());
        $totalSpace = disk_total_space(base_path());
        $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        $checks['disk'] = [
            'status' => $usedPercent < 80 ? 'ok' : ($usedPercent < 90 ? 'warning' : 'error'),
            'message' => sprintf('%.1f%% utilise', $usedPercent),
            'details' => sprintf('Libre: %s / Total: %s', $this->formatBytes($freeSpace), $this->formatBytes($totalSpace)),
        ];

        // PHP Check
        $checks['php'] = [
            'status' => 'ok',
            'message' => 'PHP ' . PHP_VERSION,
            'details' => 'Memory limit: ' . ini_get('memory_limit') . ' | Max upload: ' . ini_get('upload_max_filesize'),
        ];

        // Laravel Check
        $checks['laravel'] = [
            'status' => 'ok',
            'message' => 'Laravel ' . app()->version(),
            'details' => 'Environment: ' . app()->environment() . ' | Debug: ' . (config('app.debug') ? 'On' : 'Off'),
        ];

        // Sessions Check
        try {
            $sessionDriver = config('session.driver');
            $checks['sessions'] = [
                'status' => 'ok',
                'message' => 'Sessions actives',
                'details' => 'Driver: ' . $sessionDriver,
            ];
        } catch (\Exception $e) {
            $checks['sessions'] = [
                'status' => 'error',
                'message' => 'Erreur sessions',
                'details' => $e->getMessage(),
            ];
        }

        // Logs Check
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            $logSize = File::size($logPath);
            $checks['logs'] = [
                'status' => $logSize < 100 * 1024 * 1024 ? 'ok' : 'warning', // 100MB
                'message' => 'Fichier log: ' . $this->formatBytes($logSize),
                'details' => 'Derniere modification: ' . date('d/m/Y H:i', File::lastModified($logPath)),
            ];
        } else {
            $checks['logs'] = [
                'status' => 'ok',
                'message' => 'Pas de fichier log',
                'details' => 'Le fichier log n\'existe pas encore',
            ];
        }

        // Overall status
        $hasError = collect($checks)->contains(fn($c) => $c['status'] === 'error');
        $hasWarning = collect($checks)->contains(fn($c) => $c['status'] === 'warning');
        $overallStatus = $hasError ? 'error' : ($hasWarning ? 'warning' : 'ok');

        // System info
        $systemInfo = [
            'hostname' => gethostname(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? base_path(),
            'php_sapi' => php_sapi_name(),
            'loaded_extensions' => count(get_loaded_extensions()),
            'uptime' => $this->getUptime(),
        ];

        // Recent errors from logs
        $recentErrors = $this->getRecentErrors();

        return view('admin.system.health', compact('checks', 'overallStatus', 'systemInfo', 'recentErrors'));
    }

    public function phpinfo()
    {
        // Disable in production for security
        if (app()->isProduction()) {
            abort(403, 'PHPInfo is disabled in production environment.');
        }

        // Optional: IP whitelist check
        $allowedIps = config('app.phpinfo_allowed_ips', []);
        if (!empty($allowedIps) && !in_array(request()->ip(), $allowedIps)) {
            abort(403, 'Access denied from this IP address.');
        }

        ob_start();
        phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_ENVIRONMENT);
        $phpinfo = ob_get_clean();

        // Extract only the body content
        preg_match('/<body[^>]*>(.*?)<\/body>/is', $phpinfo, $matches);
        $phpinfoBody = $matches[1] ?? $phpinfo;

        // Remove sensitive environment variables from display
        $sensitivePatterns = [
            '/DB_PASSWORD[^<]*<[^>]*>[^<]*<[^>]*>([^<]*)/i',
            '/APP_KEY[^<]*<[^>]*>[^<]*<[^>]*>([^<]*)/i',
            '/MAIL_PASSWORD[^<]*<[^>]*>[^<]*<[^>]*>([^<]*)/i',
            '/(API_KEY|SECRET|TOKEN|PASSWORD)[^<]*<[^>]*>[^<]*<[^>]*>([^<]*)/i',
        ];
        foreach ($sensitivePatterns as $pattern) {
            $phpinfoBody = preg_replace($pattern, '$0 [HIDDEN]', $phpinfoBody);
        }

        return view('admin.system.phpinfo', compact('phpinfoBody'));
    }

    public function logs(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = $request->get('lines', 100);

        if (!File::exists($logFile)) {
            $content = 'Aucun fichier de log trouve.';
        } else {
            // Read last N lines
            $file = new \SplFileObject($logFile, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();

            $startLine = max(0, $totalLines - $lines);
            $file->seek($startLine);

            $content = '';
            while (!$file->eof()) {
                $content .= $file->fgets();
            }
        }

        return view('admin.system.logs', compact('content', 'lines'));
    }

    public function clearLogs()
    {
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        return back()->with('success', 'Fichier de log vide.');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function getUptime()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'N/A (Windows)';
        }

        $uptime = @file_get_contents('/proc/uptime');
        if ($uptime) {
            $seconds = (int) explode(' ', $uptime)[0];
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);

            return "{$days}j {$hours}h {$minutes}m";
        }

        return 'N/A';
    }

    private function getRecentErrors()
    {
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        $errors = [];

        // Simple regex to find error entries
        preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?\.ERROR: (.+?)(?=\[|$)/s', $content, $matches, PREG_SET_ORDER);

        foreach (array_slice(array_reverse($matches), 0, 10) as $match) {
            $errors[] = [
                'date' => $match[1],
                'message' => substr(trim($match[2]), 0, 200),
            ];
        }

        return $errors;
    }
}
