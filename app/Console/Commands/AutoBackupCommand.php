<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class AutoBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:auto';

    /**
     * The console command description.
     */
    protected $description = 'Create automatic backup based on configuration';

    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if auto backup is enabled
        if (!env('AUTO_BACKUP_ENABLED', false)) {
            $this->info('Automatic backups are disabled');
            return 0;
        }

        $type = env('AUTO_BACKUP_TYPE', 'database');
        $retentionDays = env('AUTO_BACKUP_RETENTION_DAYS', 30);

        $this->info("Creating automatic {$type} backup...");

        try {
            $backup = match($type) {
                'database' => $this->backupService->createDatabaseBackup(true, $retentionDays),
                'files' => $this->backupService->createFilesBackup(true, $retentionDays),
                'full' => $this->backupService->createFullBackup(true, $retentionDays),
                default => throw new \Exception("Invalid backup type: {$type}"),
            };

            if ($backup->isCompleted()) {
                $this->info("✓ Backup created successfully: {$backup->name}");
                $this->info("  Size: {$backup->formatted_size}");
                $this->info("  Duration: {$backup->formatted_duration}");
            } else {
                $this->error("✗ Backup failed: {$backup->error_message}");
                return 1;
            }

            // Clean up expired backups
            $this->info('Cleaning up expired backups...');
            $count = $this->backupService->deleteExpiredBackups();
            $this->info("✓ Deleted {$count} expired backup(s)");

            return 0;

        } catch (\Exception $e) {
            $this->error("✗ Error: {$e->getMessage()}");
            return 1;
        }
    }
}
