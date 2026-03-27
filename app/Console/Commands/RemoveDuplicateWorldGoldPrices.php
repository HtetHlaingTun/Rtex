<?php

namespace App\Console\Commands;

use App\Models\GoldPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateWorldGoldPrices extends Command
{
    protected $signature = 'gold:cleanup-world-gold-duplicates';
    protected $description = 'Remove duplicate world_gold_usd values from gold_prices table';

    public function handle()
    {
        $this->info('Cleaning up duplicate world_gold_usd values...');

        // Keep only one record per minute with world_gold_usd
        $minuteGroups = GoldPrice::whereNotNull('world_gold_usd')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as minute_group'))
            ->groupBy('minute_group')
            ->pluck('minute_group');

        foreach ($minuteGroups as $minute) {
            // Get the first record for this minute
            $keepId = GoldPrice::whereNotNull('world_gold_usd')
                ->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") = ?', [$minute])
                ->orderBy('id')
                ->value('id');

            // Set world_gold_usd to null for all other records in this minute
            GoldPrice::whereNotNull('world_gold_usd')
                ->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") = ?', [$minute])
                ->where('id', '!=', $keepId)
                ->update(['world_gold_usd' => null]);
        }

        $this->info('✅ Cleanup complete!');
        return Command::SUCCESS;
    }
}
