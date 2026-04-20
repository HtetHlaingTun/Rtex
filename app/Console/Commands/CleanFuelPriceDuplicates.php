<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanFuelPriceDuplicates extends Command
{
    protected $signature = 'fuel:clean-duplicates 
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove duplicate fuel price records keeping only the latest per day per region';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🔍 Scanning for duplicate fuel price records...');

        $regions = DB::table('fuel_prices')->select('region')->distinct()->pluck('region');
        $totalDeleted = 0;
        $totalKept = 0;

        foreach ($regions as $region) {
            $this->info("\n📌 Processing region: " . strtoupper($region));

            // Get dates with multiple records
            $dates = DB::table('fuel_prices')
                ->where('region', $region)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->having('count', '>', 1)
                ->pluck('date');

            if ($dates->isEmpty()) {
                $this->line("   ✅ No duplicates found");
                continue;
            }

            foreach ($dates as $date) {
                // Get all records for this region and date
                $records = DB::table('fuel_prices')
                    ->where('region', $region)
                    ->whereDate('created_at', $date)
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($records->count() > 1) {
                    $keep = $records->first();
                    $duplicates = $records->slice(1);
                    $idsToDelete = $duplicates->pluck('id');

                    if ($dryRun) {
                        $this->warn("   🔍 Would delete {$duplicates->count()} duplicate(s) for {$date}");
                        foreach ($duplicates as $dup) {
                            $this->line("      - ID: {$dup->id}, Time: {$dup->created_at}");
                        }
                    } else {
                        DB::table('fuel_prices')->whereIn('id', $idsToDelete)->delete();
                        $this->info("   ✅ Deleted {$duplicates->count()} duplicate(s) for {$date}, kept 1 record");
                        $totalDeleted += $duplicates->count();
                        $totalKept++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info('==========================================');
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN COMPLETE - No records were deleted');
            $this->info("   Would delete: {$totalDeleted} duplicate records");
        } else {
            $this->info('✅ Cleanup completed successfully!');
            $this->info("   Deleted: {$totalDeleted} duplicate records");
            $this->info("   Kept: {$totalKept} unique daily records");
        }
        $this->info('==========================================');

        return Command::SUCCESS;
    }
}
