<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsolidateExchangeRates extends Command
{
    protected $signature = 'exchange:consolidate-rates
                            {--days-to-keep=1 : Keep full records for this many days}
                            {--permanent-years=2 : Permanently delete records older than this many years}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--stats : Show statistics before and after cleanup}';

    protected $description = 'Consolidate old exchange rate records to 1 record per day per currency';

    public function handle()
    {
        $this->info('🚀 Consolidating Exchange Rates');

        $daysToKeep = (int) $this->option('days-to-keep');
        $permanentYears = (int) $this->option('permanent-years');
        $dryRun = $this->option('dry-run');
        $showStats = $this->option('stats');

        $keepFromDate = now()->startOfDay();
        $permanentCutoffDate = now()->subYears($permanentYears);

        if ($showStats) {
            $this->showCurrentStats();
        }

        $this->info("\n📋 Configuration:");
        $this->info("   - Keep FULL records for last {$daysToKeep} day(s)");
        $this->info("   - Keep all records from: {$keepFromDate->format('Y-m-d')} onwards");
        $this->info("   - Consolidate days BEFORE: {$keepFromDate->format('Y-m-d')}");
        $this->info("   - Mode: " . ($dryRun ? 'DRY RUN' : 'LIVE DELETE'));
        $this->newLine();

        $totalDeleted = 0;

        // Get all currencies with records
        $currencies = DB::table('exchange_rates')
            ->select('currency_id')
            ->groupBy('currency_id')
            ->get();

        foreach ($currencies as $currency) {
            $this->info("Processing currency ID: {$currency->currency_id}");
            $deleted = $this->consolidateCurrencyRates($currency->currency_id, $keepFromDate, $permanentCutoffDate, $dryRun);
            $totalDeleted += $deleted;
            $this->newLine();
        }

        $this->showFinalSummary($totalDeleted, $dryRun);

        return Command::SUCCESS;
    }

    private function consolidateCurrencyRates($currencyId, $keepFromDate, $permanentCutoffDate, $dryRun)
    {
        // Step 1: Permanent delete old records
        $permanentDelete = ExchangeRate::where('currency_id', $currencyId)
            ->where('created_at', '<', $permanentCutoffDate);

        $permanentCount = $permanentDelete->count();
        if ($permanentCount > 0) {
            if (!$dryRun) {
                $permanentDelete->forceDelete();
                $this->info("   ✅ Permanently deleted {$permanentCount} old records");
            } else {
                $this->info("   🔍 Would delete {$permanentCount} old records permanently");
            }
        }

        // IMPORTANT: Get dates that are BEFORE today (not including today)
        $today = now()->startOfDay();

        // Get all dates that have records before today
        $dates = ExchangeRate::where('currency_id', $currencyId)
            ->where('created_at', '<', $today)  // Only records before today
            ->selectRaw('DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        if ($dates->isEmpty()) {
            $this->info("   ✅ No records need consolidation (no records before today)");
            return 0;
        }

        $this->info("   Found " . $dates->count() . " days to process (before today)");
        $progressBar = $this->output->createProgressBar($dates->count());
        $progressBar->start();

        $totalDeleted = 0;

        foreach ($dates as $dateData) {
            $date = $dateData->date;

            // Define the exact start and end of that day
            $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
            $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();

            // Get all records for that day
            $records = ExchangeRate::where('currency_id', $currencyId)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $recordCount = $records->count();

            if ($recordCount <= 1) {
                $this->line("   Date {$date}: {$recordCount} record(s) - nothing to consolidate");
                $progressBar->advance();
                continue;
            }

            // Keep the latest record
            $keepRecord = $records->first();
            $duplicatesToDelete = $records->slice(1);

            $this->line("   Date {$date}: Found {$recordCount} records, keeping latest from " . $keepRecord->created_at->format('H:i:s'));

            if (!$dryRun) {
                foreach ($duplicatesToDelete as $duplicate) {
                    $duplicate->forceDelete();
                    $totalDeleted++;
                }

                // Mark the kept record as consolidated
                $keepRecord->update([
                    'source_name' => 'Consolidated',
                    'market_notes' => "Daily Summary (Consolidated from " . ($duplicatesToDelete->count() + 1) . " records)"
                ]);
            } else {
                $totalDeleted += $duplicatesToDelete->count();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("   ✅ Consolidated {$totalDeleted} records for currency ID {$currencyId}");

        return $totalDeleted;
    }

    private function showCurrentStats()
    {
        $totalRecords = ExchangeRate::count();
        $totalCurrencies = ExchangeRate::select('currency_id')->distinct()->count();

        $this->info("\n📈 Current Statistics:");
        $this->info("   - Total records: {$totalRecords}");
        $this->info("   - Total currencies: {$totalCurrencies}");

        // Show records per day for last 5 days
        $recentDays = ExchangeRate::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        if ($recentDays->isNotEmpty()) {
            $this->info("   - Records per day (last 5 days):");
            foreach ($recentDays as $day) {
                $this->info("      - {$day->date}: {$day->count} records");
            }
        }
    }

    private function showFinalSummary($totalDeleted, $dryRun)
    {
        $this->info("==========================================");
        $this->info("📊 Cleanup Summary:");
        $this->info("   - Records Consolidated: {$totalDeleted}");

        if (!$dryRun) {
            $remaining = ExchangeRate::count();
            $this->info("   - Total Records Remaining: {$remaining}");
            $this->info("\n✅ Cleanup completed successfully!");
        } else {
            $this->warn("\n⚠️  DRY RUN COMPLETE - No rows were actually deleted.");
        }
        $this->info("==========================================");
    }
}
