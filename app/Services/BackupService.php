<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupService
{
    /**
     * Create a database backup
     */
    public function createDatabaseBackup(bool $isAutomatic = false, ?int $retentionDays = 30): Backup
    {
        $backup = Backup::create([
            'name' => 'database-' . now()->format('Y-m-d_His') . '.sql',
            'type' => 'database',
            'path' => 'backups/database-' . now()->format('Y-m-d_His') . '.sql',
            'status' => 'pending',
            'created_by' => auth()->id(),
            'is_automatic' => $isAutomatic,
            'retention_days' => $retentionDays,
            'expires_at' => now()->addDays($retentionDays),
        ]);

        try {
            $backup->update(['status' => 'running', 'started_at' => now()]);

            $databaseName = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');

            // Create backup directory
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $filePath = storage_path('app/' . $backup->path);

            // Use mysqldump
            $command = sprintf(
                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($databaseName),
                escapeshellarg($filePath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || !File::exists($filePath) || File::size($filePath) === 0) {
                throw new \Exception('Database backup failed: ' . implode("\n", $output));
            }

            $size = File::size($filePath);

            $metadata = [
                'database' => $databaseName,
                'tables_count' => DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?', [$databaseName])[0]->count ?? 0,
            ];

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'size' => $size,
                'metadata' => $metadata,
            ]);

            Log::info('Database backup created successfully', ['backup_id' => $backup->id, 'size' => $size]);

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Database backup failed', ['backup_id' => $backup->id, 'error' => $e->getMessage()]);
        }

        return $backup->fresh();
    }

    /**
     * Create files backup
     */
    public function createFilesBackup(bool $isAutomatic = false, ?int $retentionDays = 30): Backup
    {
        $backup = Backup::create([
            'name' => 'files-' . now()->format('Y-m-d_His') . '.zip',
            'type' => 'files',
            'path' => 'backups/files-' . now()->format('Y-m-d_His') . '.zip',
            'status' => 'pending',
            'created_by' => auth()->id(),
            'is_automatic' => $isAutomatic,
            'retention_days' => $retentionDays,
            'expires_at' => now()->addDays($retentionDays),
        ]);

        try {
            $backup->update(['status' => 'running', 'started_at' => now()]);

            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $zipPath = storage_path('app/' . $backup->path);
            $storagePath = storage_path('app/public');

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Failed to create zip archive');
            }

            $files = File::allFiles($storagePath);
            $filesCount = 0;

            foreach ($files as $file) {
                $relativePath = str_replace($storagePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $zip->addFile($file->getPathname(), $relativePath);
                $filesCount++;
            }

            $zip->close();

            $size = File::size($zipPath);

            $metadata = [
                'files_count' => $filesCount,
                'source_path' => 'storage/app/public',
            ];

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'size' => $size,
                'metadata' => $metadata,
            ]);

            Log::info('Files backup created successfully', ['backup_id' => $backup->id, 'size' => $size]);

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Files backup failed', ['backup_id' => $backup->id, 'error' => $e->getMessage()]);
        }

        return $backup->fresh();
    }

    /**
     * Create full backup
     */
    public function createFullBackup(bool $isAutomatic = false, ?int $retentionDays = 30): Backup
    {
        $backup = Backup::create([
            'name' => 'full-' . now()->format('Y-m-d_His') . '.zip',
            'type' => 'full',
            'path' => 'backups/full-' . now()->format('Y-m-d_His') . '.zip',
            'status' => 'pending',
            'created_by' => auth()->id(),
            'is_automatic' => $isAutomatic,
            'retention_days' => $retentionDays,
            'expires_at' => now()->addDays($retentionDays),
        ]);

        try {
            $backup->update(['status' => 'running', 'started_at' => now()]);

            $dbBackup = $this->createDatabaseBackup($isAutomatic, $retentionDays);

            if (!$dbBackup->isCompleted()) {
                throw new \Exception('Database backup failed: ' . $dbBackup->error_message);
            }

            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $zipPath = storage_path('app/' . $backup->path);
            $storagePath = storage_path('app/public');
            $dbBackupPath = storage_path('app/' . $dbBackup->path);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Failed to create zip archive');
            }

            $zip->addFile($dbBackupPath, 'database/' . basename($dbBackupPath));

            $files = File::allFiles($storagePath);
            $filesCount = 0;

            foreach ($files as $file) {
                $relativePath = str_replace($storagePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $zip->addFile($file->getPathname(), 'files/' . $relativePath);
                $filesCount++;
            }

            $zip->close();

            $size = File::size($zipPath);

            $metadata = [
                'files_count' => $filesCount,
                'database' => env('DB_DATABASE'),
                'includes' => ['database', 'files'],
            ];

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
                'size' => $size,
                'metadata' => $metadata,
            ]);

            $dbBackup->deleteFile();
            $dbBackup->delete();

            Log::info('Full backup created successfully', ['backup_id' => $backup->id, 'size' => $size]);

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Full backup failed', ['backup_id' => $backup->id, 'error' => $e->getMessage()]);
        }

        return $backup->fresh();
    }

    /**
     * Delete expired backups
     */
    public function deleteExpiredBackups(): int
    {
        $expiredBackups = Backup::expired()->completed()->get();
        $count = 0;

        foreach ($expiredBackups as $backup) {
            $backup->deleteFile();
            $backup->delete();
            $count++;
        }

        Log::info("Deleted {$count} expired backups");

        return $count;
    }

    /**
     * Download backup
     */
    public function downloadBackup(Backup $backup)
    {
        if (!$backup->fileExists()) {
            throw new \Exception('Backup file not found');
        }

        $filePath = storage_path('app/' . $backup->path);

        return response()->download($filePath, $backup->name);
    }
}
