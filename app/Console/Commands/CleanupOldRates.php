<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExchangeRate;
use Carbon\Carbon;

class CleanupOldRates extends Command
{
    protected $signature = 'cbm:cleanup 
                            {--days=30 : Delete archived rates older than X days}
                            {--force : Force deletion without confirmation}';

    protected $description = 'Clean up old archived exchange rates';

    public function handle()
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $cutoffDate = Carbon::now()->subDays($days);

        // Count records to be deleted
        $count = ExchangeRate::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->count();

        if ($count === 0) {
            $this->info("No archived rates older than {$days} days found.");
            return 0;
        }

        $this->info("Found {$count} archived rates older than {$days} days.");

        // Show sample of rates to be deleted (first 5)
        $sampleRates = ExchangeRate::onlyTrashed()
            ->with('currency')
            ->where('deleted_at', '<', $cutoffDate)
            ->take(5)
            ->get();

        if ($sampleRates->isNotEmpty()) {
            $this->line('');
            $this->info('Sample records to be deleted:');
            foreach ($sampleRates as $rate) {
                $this->line("  - ID: {$rate->id}, Currency: {$rate->currency->code}, Date: {$rate->rate_date}, Archived: {$rate->deleted_at->format('Y-m-d H:i:s')}");
            }

            if ($count > 5) {
                $this->line("  ... and " . ($count - 5) . " more records");
            }
        }

        $this->line('');

        // Confirm deletion
        if (!$force) {
            if (!$this->confirm('Are you sure you want to permanently delete these records?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Perform deletion
        $deleted = ExchangeRate::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->forceDelete();

        $this->info('');
        $this->info("✅ Successfully deleted {$deleted} archived rates.");

        return 0;
    }
}
