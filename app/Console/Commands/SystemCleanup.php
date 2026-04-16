<?php
// app/Console/Commands/SystemCleanup.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SystemCleanup extends Command
{
    protected $signature = 'system:cleanup 
                            {--days=7 : Days to keep logs and sessions}
                            {--releases=3 : Number of releases to keep}
                            {--force : Force cleanup without confirmation}';

    protected $description = 'Comprehensive system cleanup to free disk space';

    public function handle()
    {
        $this->info('🧹 Starting system cleanup...');
        $startDisk = disk_free_space('/');

        // 1. Clean Laravel logs
        $this->cleanLogs();

        // 2. Clean old releases
        $this->cleanReleases();

        // 3. Clean session files
        $this->cleanSessions();

        // 4. Clean cache files
        $this->cleanCache();

        // 5. Clean old queue jobs
        $this->cleanQueue();

        // 6. Optimize database
        $this->optimizeDatabase();

        // 7. Clean temporary files
        $this->cleanTempFiles();

        // 8. Report results
        $endDisk = disk_free_space('/');
        $freed = ($endDisk - $startDisk) / 1024 / 1024;

        $this->newLine();
        $this->info("✅ Cleanup completed!");
        $this->info("💾 Freed: " . number_format($freed, 2) . " MB");
        $this->info("💿 Available: " . number_format($endDisk / 1024 / 1024 / 1024, 2) . " GB");
    }

    private function cleanLogs()
    {
        $this->info('📝 Cleaning logs...');

        // Laravel logs
        $logPath = storage_path('logs');
        if (is_dir($logPath)) {
            $files = glob($logPath . '/*.log');
            $count = 0;
            foreach ($files as $file) {
                if (filemtime($file) < now()->subDays($this->option('days'))->timestamp) {
                    unlink($file);
                    $count++;
                }
            }
            // Keep main log file but truncate if > 100MB
            $mainLog = $logPath . '/laravel.log';
            if (file_exists($mainLog) && filesize($mainLog) > 100 * 1024 * 1024) {
                file_put_contents($mainLog, '');
            }
            $this->line("   ✓ Deleted {$count} old log files");
        }
    }

    private function cleanReleases()
    {
        $this->info('📦 Cleaning old releases...');
        $releasesPath = base_path('../releases');

        if (is_dir($releasesPath)) {
            $releases = glob($releasesPath . '/*', GLOB_ONLYDIR);
            usort($releases, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            $keep = (int) $this->option('releases');
            $removed = 0;

            foreach (array_slice($releases, $keep) as $release) {
                $this->line("   Removing: " . basename($release));
                exec("rm -rf {$release}");
                $removed++;
            }

            $this->line("   ✓ Removed {$removed} old releases");
        }
    }

    private function cleanSessions()
    {
        $this->info('🍪 Cleaning sessions...');
        $sessionPath = storage_path('framework/sessions');

        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            $removed = 0;
            $cutoff = now()->subDays($this->option('days'))->timestamp;

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoff) {
                    unlink($file);
                    $removed++;
                }
            }

            $this->line("   ✓ Removed {$removed} old sessions");
        }
    }

    private function cleanCache()
    {
        $this->info('🗑️ Cleaning cache...');

        // Framework cache
        $cachePath = storage_path('framework/cache/data');
        if (is_dir($cachePath)) {
            $files = glob($cachePath . '/*');
            $removed = 0;
            $cutoff = now()->subDays($this->option('days'))->timestamp;

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoff) {
                    unlink($file);
                    $removed++;
                }
            }
            $this->line("   ✓ Removed {$removed} old cache files");
        }

        // View cache
        $viewPath = storage_path('framework/views');
        if (is_dir($viewPath)) {
            $files = glob($viewPath . '/*');
            $removed = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $removed++;
                }
            }
            $this->line("   ✓ Cleared {$removed} view cache files");
        }

        // Application cache
        $this->call('cache:clear');
        $this->call('view:clear');
        $this->call('config:clear');
        $this->call('route:clear');
    }

    private function cleanQueue()
    {
        $this->info('⏳ Cleaning queue...');

        // Prune failed jobs older than 7 days
        $this->call('queue:prune-failed', ['--hours' => 168]);

        // Clean old jobs from database (if using database driver)
        try {
            DB::table('jobs')->where('created_at', '<', now()->subDays(7))->delete();
            DB::table('failed_jobs')->where('failed_at', '<', now()->subDays(30))->delete();
            $this->line("   ✓ Cleaned old queue jobs");
        } catch (\Exception $e) {
            // Table might not exist
        }
    }

    private function optimizeDatabase()
    {
        $this->info('🗄️ Optimizing database...');

        try {
            $tables = DB::select('SHOW TABLES');
            $database = DB::getDatabaseName();
            $tableKey = "Tables_in_{$database}";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
            }

            $this->line("   ✓ Database optimized");
        } catch (\Exception $e) {
            $this->line("   ⚠️ Could not optimize database: " . $e->getMessage());
        }
    }

    private function cleanTempFiles()
    {
        $this->info('🧹 Cleaning temporary files...');

        // Laravel temp files
        $tempPaths = [
            storage_path('app/temp'),
            storage_path('app/public/temp'),
            storage_path('framework/testing'),
        ];

        $removed = 0;
        foreach ($tempPaths as $path) {
            if (is_dir($path)) {
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < now()->subDays(1)->timestamp) {
                        unlink($file);
                        $removed++;
                    }
                }
            }
        }

        $this->line("   ✓ Removed {$removed} temp files");
    }
}
