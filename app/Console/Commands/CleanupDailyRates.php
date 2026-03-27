<?php

namespace App\Console\Commands;



use Illuminate\Console\Command;


class CleanupDailyRates extends Command
{
    protected $signature = 'cbm:cleanup-daily-rates';
    protected $description = 'Keep only the latest record for every single day in history';

    public function handle()
    {
        $this->info('🚀 Running Final Historical Purge...');

        $currencies = \App\Models\Currency::all();
        $totalDeleted = 0;

        foreach ($currencies as $currency) {
            $this->line("Processing {$currency->code}...");

            // 1. Get all unique dates for this currency
            // We use DATE() to strip the time from rate_date
            $dates = \App\Models\ExchangeRate::where('currency_id', $currency->id)
                ->selectRaw('DATE(rate_date) as simple_date')
                ->groupBy('simple_date')
                ->pluck('simple_date');

            foreach ($dates as $date) {
                // 2. Find the LATEST ID for this specific day
                $latestId = \App\Models\ExchangeRate::where('currency_id', $currency->id)
                    ->whereDate('rate_date', $date)
                    ->latest('id')
                    ->value('id');

                if ($latestId) {
                    // 3. Delete EVERY other record for that day
                    // We use forceDelete to ensure they disappear from the UI
                    $deleted = \App\Models\ExchangeRate::where('currency_id', $currency->id)
                        ->whereDate('rate_date', $date)
                        ->where('id', '!=', $latestId)
                        ->forceDelete();

                    if ($deleted > 0) {
                        $totalDeleted += $deleted;
                        $this->info("   ✅ [{$date}] Kept ID {$latestId}, removed {$deleted} duplicates.");
                    }
                }
            }
        }

        $this->info("--------------------------------------------------");
        $this->info("✨ Success! Total duplicates purged: {$totalDeleted}");
        $this->info("--------------------------------------------------");
    }
}
