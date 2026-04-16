<?php
// app/Console/Commands/MonitorDisk.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MonitorDisk extends Command
{
    protected $signature = 'monitor:disk {--threshold=80 : Alert threshold percentage}';
    protected $description = 'Monitor disk usage and send alerts';

    public function handle()
    {
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $usagePercent = round(($diskUsed / $diskTotal) * 100, 2);

        $this->info("💿 Disk Usage: {$usagePercent}%");

        // Send to Forge dashboard
        if ($usagePercent >= $this->option('threshold')) {
            $this->warn("⚠️ Disk usage exceeded {$this->option('threshold')}%!");

            // Send alert (configure webhook)
            $webhook = config('services.discord.webhook');
            if ($webhook) {
                Http::post($webhook, [
                    'content' => "🚨 **Disk Alert**\nServer: " . gethostname() . "\nUsage: {$usagePercent}%\nFree: " . round($diskFree / 1024 / 1024 / 1024, 2) . " GB"
                ]);
            }

            // Log alert
            Log::warning('Disk usage high', [
                'percent' => $usagePercent,
                'free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
                'used_gb' => round($diskUsed / 1024 / 1024 / 1024, 2)
            ]);
        }

        return Command::SUCCESS;
    }
}
