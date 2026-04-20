<?php
// app/Console/Commands/CleanupOldFuelPrices.php

namespace App\Console\Commands;

use App\Models\FuelPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldFuelPrices extends Command
{
    protected $signature = 'fuel:cleanup-old-records 
                            {--months=6 : Keep records for this many months}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Delete fuel price records older than specified months';

    public function handle()
    {
        $months = (int) $this->option('months');
        $dryRun = $this->option('dry-run');

        $cutoffDate = Carbon::now()->subMonths($months);

        $this->info('🗑️ Fuel Price Cleanup');
        $this->info("   Keeping records newer than: {$cutoffDate->toDateString()}");
        $this->info("   Deleting records older than: {$months} months");
        $this->newLine();

        // Count records to delete (grouped by region for better reporting)
        $oldRecords = FuelPrice::where('created_at', '<', $cutoffDate);
        $countByRegion = $oldRecords->clone()
            ->select('region', DB::raw('COUNT(*) as count'))
            ->groupBy('region')
            ->get();

        $totalToDelete = $oldRecords->count();

        if ($totalToDelete === 0) {
            $this->info('✅ No old records to delete.');
            return Command::SUCCESS;
        }

        // Show summary
        $this->info('📊 Records to delete by region:');
        $this->newLine();

        $tableData = [];
        foreach ($countByRegion as $row) {
            $tableData[] = [$row->region, $row->count];
        }
        $this->table(['Region', 'Count'], $tableData);
        $this->newLine();
        $this->info("Total records to delete: {$totalToDelete}");
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN - No records will be deleted');

            // Show sample of old records
            $sample = $oldRecords->orderBy('created_at')->take(5)->get();
            if ($sample->isNotEmpty()) {
                $this->info('Sample of oldest records:');
                foreach ($sample as $record) {
                    $this->line("   - {$record->region}: {$record->created_at->toDateString()} (ID: {$record->id})");
                }
            }

            return Command::SUCCESS;
        }

        // Confirm deletion
        if (!$this->confirm("Delete {$totalToDelete} records permanently?")) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Delete records
        $deleted = $oldRecords->delete();

        $this->newLine();
        $this->info('✅ Cleanup completed!');
        $this->info("   Deleted: {$deleted} records");
        $this->info("   Kept records from last {$months} months");

        return Command::SUCCESS;
    }
}
