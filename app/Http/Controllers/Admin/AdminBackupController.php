<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminBackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display backups dashboard
     */
    public function index(Request $request)
    {
        $query = Backup::with('creator');

        // Filter by type
        if ($request->filled('type')) {
            $query->type($request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter automatic vs manual
        if ($request->filled('source')) {
            if ($request->source === 'automatic') {
                $query->automatic();
            } elseif ($request->source === 'manual') {
                $query->manual();
            }
        }

        $backups = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Statistics
        $stats = Cache::remember('admin.backups.stats', now()->addMinutes(5), function () {
            return [
                'total' => Backup::count(),
                'completed' => Backup::completed()->count(),
                'failed' => Backup::failed()->count(),
                'total_size' => Backup::completed()->sum('size'),
                'last_backup' => Backup::completed()->latest('completed_at')->first()?->completed_at,
                'database_backups' => Backup::type('database')->completed()->count(),
                'files_backups' => Backup::type('files')->completed()->count(),
                'full_backups' => Backup::type('full')->completed()->count(),
            ];
        });

        return view('admin.backups.index', compact('backups', 'stats'));
    }

    /**
     * Create new backup
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:database,files,full',
            'retention_days' => 'nullable|integer|min:1|max:365',
        ]);

        $retentionDays = $validated['retention_days'] ?? 30;

        try {
            $backup = match($validated['type']) {
                'database' => $this->backupService->createDatabaseBackup(false, $retentionDays),
                'files' => $this->backupService->createFilesBackup(false, $retentionDays),
                'full' => $this->backupService->createFullBackup(false, $retentionDays),
            };

            if ($backup->isCompleted()) {
                AuditLog::log('backup', "Backup created: {$backup->name}");
                return back()->with('success', 'Backup créé avec succès.');
            } else {
                return back()->with('error', 'Échec de la création du backup: ' . $backup->error_message);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la création du backup: ' . $e->getMessage());
        }
    }

    /**
     * Download backup
     */
    public function download(Backup $backup)
    {
        try {
            AuditLog::log('backup', "Backup downloaded: {$backup->name}");
            return $this->backupService->downloadBackup($backup);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du téléchargement: ' . $e->getMessage());
        }
    }

    /**
     * Delete backup
     */
    public function destroy(Backup $backup)
    {
        $name = $backup->name;

        $backup->deleteFile();
        $backup->delete();

        AuditLog::log('backup', "Backup deleted: {$name}");

        return back()->with('success', 'Backup supprimé.');
    }

    /**
     * Delete expired backups
     */
    public function deleteExpired()
    {
        $count = $this->backupService->deleteExpiredBackups();

        AuditLog::log('backup', "{$count} expired backups deleted");

        return back()->with('success', "{$count} backup(s) expiré(s) supprimé(s).");
    }

    /**
     * Get backup settings
     */
    public function settings()
    {
        $settings = [
            'auto_backup_enabled' => env('AUTO_BACKUP_ENABLED', false),
            'auto_backup_type' => env('AUTO_BACKUP_TYPE', 'database'),
            'auto_backup_frequency' => env('AUTO_BACKUP_FREQUENCY', 'daily'),
            'auto_backup_retention_days' => env('AUTO_BACKUP_RETENTION_DAYS', 30),
            'auto_backup_time' => env('AUTO_BACKUP_TIME', '02:00'),
        ];

        return view('admin.backups.settings', compact('settings'));
    }

    /**
     * Update backup settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'auto_backup_enabled' => 'boolean',
            'auto_backup_type' => 'required|in:database,files,full',
            'auto_backup_frequency' => 'required|in:hourly,daily,weekly',
            'auto_backup_retention_days' => 'required|integer|min:1|max:365',
            'auto_backup_time' => 'required|date_format:H:i',
        ]);

        // Update .env file (simplified - in production use a proper config management)
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        $updates = [
            'AUTO_BACKUP_ENABLED' => $validated['auto_backup_enabled'] ?? false ? 'true' : 'false',
            'AUTO_BACKUP_TYPE' => $validated['auto_backup_type'],
            'AUTO_BACKUP_FREQUENCY' => $validated['auto_backup_frequency'],
            'AUTO_BACKUP_RETENTION_DAYS' => $validated['auto_backup_retention_days'],
            'AUTO_BACKUP_TIME' => $validated['auto_backup_time'],
        ];

        foreach ($updates as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);

        AuditLog::log('backup', 'Backup settings updated');

        return back()->with('success', 'Paramètres de backup mis à jour.');
    }

    /**
     * Get real-time stats for AJAX
     */
    public function stats()
    {
        $stats = [
            'total' => Backup::count(),
            'completed_today' => Backup::completed()->whereDate('completed_at', today())->count(),
            'total_size' => Backup::completed()->sum('size'),
            'last_backup' => Backup::completed()->latest('completed_at')->first()?->completed_at?->diffForHumans(),
        ];

        return response()->json($stats);
    }
}
