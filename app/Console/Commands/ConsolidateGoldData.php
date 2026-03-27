<?php

namespace App\Console\Commands;

use App\Models\GoldPrice;
use App\Models\WorldGoldSnapshot;
use Illuminate\Console\Command;

use Carbon\Carbon;

class ConsolidateGoldData extends Command
{
    protected $signature = 'gold:consolidate-daily
                            {--days-to-keep=1 : Keep full records for this many days (default: 1)}
                            {--permanent-years=2 : Permanently delete records older than this many years}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--stats : Show statistics before and after cleanup}';

    protected $description = 'Consolidate old records to 1 record per gold_type per day for gold_prices';

    public function handle()
    {
        $this->info('🚀 Consolidating Gold Data - Daily Cleanup');

        $daysToKeep = (int) $this->option('days-to-keep');
        $permanentYears = (int) $this->option('permanent-years');
        $dryRun = $this->option('dry-run');
        $showStats = $this->option('stats');

        // Logic: If daysToKeep = 1, we start of day (00:00:00) of TODAY.
        // Everything BEFORE today gets consolidated.
        $consolidateBeforeDate = now()->subDays($daysToKeep - 1)->startOfDay();
        $permanentCutoffDate = now()->subYears($permanentYears);

        if ($showStats) {
            $this->showCurrentStats();
        }

        $this->info("\n📋 Configuration:");
        $this->info("   - Keeping ALL records from: {$consolidateBeforeDate->format('Y-m-d')} (Today)");
        $this->info("   - Consolidating records BEFORE: {$consolidateBeforeDate->format('Y-m-d')}");
        $this->info("   - Mode: " . ($dryRun ? 'DRY RUN' : 'LIVE DELETE'));
        $this->newLine();

        // Step 1: Permanent Purge
        $this->performPermanentPurge($permanentCutoffDate, $dryRun);

        // Step 2: Consolidate gold_prices
        $this->info("📊 Step 2: Consolidating gold_prices table");
        $totalGoldPricesDeleted = $this->consolidateGoldPrices($consolidateBeforeDate, $dryRun);
        $this->newLine();

        // Step 3: Consolidate world_gold_snapshots
        $this->info("📊 Step 3: Consolidating world_gold_snapshots table");
        $totalSnapshotsDeleted = $this->consolidateWorldGoldSnapshots($consolidateBeforeDate, $dryRun);
        $this->newLine();

        $this->showFinalSummary($totalGoldPricesDeleted, $totalSnapshotsDeleted, 0, $dryRun);

        return Command::SUCCESS;
    }

    private function consolidateGoldPrices($consolidateBeforeDate, $dryRun)
    {
        // 1. Include withTrashed() so we can clean up the March 24/25 mess
        $groups = GoldPrice::withTrashed()
            ->where('price_date', '<', $consolidateBeforeDate)
            ->select('price_date', 'gold_type_id')
            ->groupBy('price_date', 'gold_type_id')
            ->get();

        $this->info("   Found " . $groups->count() . " unique (date, gold_type) groups to process");

        $totalDeleted = 0;
        foreach ($groups as $group) {
            $records = GoldPrice::withTrashed()
                ->where('price_date', $group->price_date)
                ->where('gold_type_id', $group->gold_type_id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($records->count() <= 1) continue;

            $keep = $records->first();
            $duplicates = $records->slice(1);

            if (!$dryRun) {
                foreach ($duplicates as $dup) {
                    $dup->forceDelete();
                    $totalDeleted++;
                }
                if ($keep->trashed()) $keep->restore();

                $keep->update([
                    'market_notes' => "Daily Close (" . ($duplicates->count() + 1) . " records)"
                ]);
            } else {
                $totalDeleted += $duplicates->count();
            }
        }
        $this->info("   ✅ gold_prices: Force Deleted {$totalDeleted} records");
        return $totalDeleted;
    }

    private function consolidateWorldGoldSnapshots($consolidateBeforeDate, $dryRun)
    {
        // 1. Re-add withTrashed() here to find soft-deleted records
        $dates = WorldGoldSnapshot::withTrashed()
            ->where('fetched_at', '<', $consolidateBeforeDate)
            ->selectRaw('DATE(fetched_at) as date')
            ->groupBy('date')
            ->get();

        $totalDeleted = 0;
        foreach ($dates as $dateData) {
            // 2. Add withTrashed() here too
            $records = WorldGoldSnapshot::withTrashed()
                ->whereDate('fetched_at', $dateData->date)
                ->orderBy('fetched_at', 'desc')
                ->get();

            if ($records->count() <= 1) continue;

            $keep = $records->first();
            $duplicates = $records->slice(1);

            if (!$dryRun) {
                foreach ($duplicates as $dup) {
                    $dup->forceDelete(); // Use forceDelete to actually clear space
                    $totalDeleted++;
                }

                // Restore the kept record if it was soft-deleted
                if ($keep->trashed()) {
                    $keep->restore();
                }

                $keep->update([
                    'market_notes' => "Daily Summary (" . ($duplicates->count() + 1) . " records)"
                ]);
            } else {
                $totalDeleted += $duplicates->count();
            }
        }
        return $totalDeleted;
    }

    private function performPermanentPurge($cutoff, $dryRun)
    {
        $this->info("🗑️  Step 1: Permanently deleting records older than {$cutoff->format('Y-m-d')}...");

        // Use withTrashed() now that the model is updated
        $gpCount = GoldPrice::withTrashed()->where('created_at', '<', $cutoff)->count();
        $wsCount = WorldGoldSnapshot::withTrashed()->where('fetched_at', '<', $cutoff)->count();

        if ($gpCount === 0 && $wsCount === 0) {
            $this->info("   ✅ No records older than 2 years found.");
            return;
        }

        if (!$dryRun) {
            GoldPrice::withTrashed()->where('created_at', '<', $cutoff)->forceDelete();
            WorldGoldSnapshot::withTrashed()->where('fetched_at', '<', $cutoff)->forceDelete();
            $this->info("   ✅ Purged {$gpCount} GoldPrices and {$wsCount} Snapshots.");
        } else {
            $this->info("   🔍 DRY RUN: Would purge {$gpCount} GoldPrices and {$wsCount} Snapshots.");
        }
    }

    private function showCurrentStats()
    {
        $this->info("\n📈 Current Statistics:");

        // Gold prices stats
        $goldPricesTotal = GoldPrice::count();
        $goldPricesGroups = GoldPrice::selectRaw('DATE(created_at) as date, gold_type_id, COUNT(*) as count')
            ->groupBy('date', 'gold_type_id')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $this->info("   gold_prices:");
        $this->info("      - Total records: {$goldPricesTotal}");
        $this->info("      - Records per day/gold_type (last 5):");

        $displayed = 0;
        foreach ($goldPricesGroups as $group) {
            if ($displayed >= 5) break;
            $this->info("         - {$group->date} (type_id: {$group->gold_type_id}): {$group->count} records");
            $displayed++;
        }

        $this->newLine();

        // World gold snapshots stats
        $snapshotsTotal = WorldGoldSnapshot::count();
        $snapshotsDays = WorldGoldSnapshot::selectRaw('COUNT(DISTINCT DATE(fetched_at)) as days')->first()->days ?? 0;
        $this->info("   world_gold_snapshots:");
        $this->info("      - Total records: {$snapshotsTotal}");
        $this->info("      - Total unique days: {$snapshotsDays}");

        $recentSnapshots = WorldGoldSnapshot::selectRaw('DATE(fetched_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        if ($recentSnapshots->isNotEmpty()) {
            $this->info("      - Records per day (last 5 days):");
            foreach ($recentSnapshots as $day) {
                $this->info("         - {$day->date}: {$day->count} records");
            }
        }

        $this->newLine();
    }

    private function showFinalSummary($goldPricesDeleted, $snapshotsDeleted, $permanentDeleted, $dryRun)
    {
        $this->info("==========================================");
        $this->info("📊 Cleanup Summary:");

        if ($permanentDeleted > 0) {
            $this->info("   - Permanently Purged: {$permanentDeleted}");
        }

        $this->info("   - gold_prices Consolidated: {$goldPricesDeleted}");
        $this->info("   - world_gold_snapshots Consolidated: {$snapshotsDeleted}");

        if (!$dryRun) {
            $remainingGold = GoldPrice::count();
            $remainingSnapshots = WorldGoldSnapshot::count();
            $this->info("   - gold_prices Remaining: {$remainingGold}");
            $this->info("   - world_gold_snapshots Remaining: {$remainingSnapshots}");
            $this->info("\n✅ Cleanup completed successfully!");
        } else {
            $this->warn("\n⚠️  DRY RUN COMPLETE - No rows were actually deleted.");
        }
        $this->info("==========================================");
    }
}
