<?php

namespace App\Console\Commands;

use App\Models\GoldPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugWorldGoldDuplicates extends Command
{
    protected $signature = 'gold:debug-world-duplicates';
    protected $description = 'Debug duplicates in world gold display';

    public function handle()
    {
        $this->info('🔍 Debugging World Gold Display Duplicates');
        $this->newLine();

        // Check gold_prices table for duplicate world_gold_usd entries with same timestamp
        $duplicates = GoldPrice::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as minute_group'),
            'world_gold_usd',
            DB::raw('COUNT(*) as count'),
            DB::raw('GROUP_CONCAT(id) as ids'),
            DB::raw('GROUP_CONCAT(created_at) as timestamps'),
            DB::raw('GROUP_CONCAT(gold_type_id) as type_ids')
        )
            ->whereNotNull('world_gold_usd')
            ->where('world_gold_usd', '>', 0)
            ->groupBy('minute_group', 'world_gold_usd')
            ->having('count', '>', 1)
            ->orderBy('minute_group', 'desc')
            ->limit(20)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicates found in gold_prices with same world_gold_usd and timestamp!');
        } else {
            $this->warn("Found " . $duplicates->count() . " duplicate entries:\n");
            foreach ($duplicates as $dup) {
                $this->line("📅 {$dup->minute_group}");
                $this->line("   Price: \${$dup->world_gold_usd}");
                $this->line("   Count: {$dup->count} records");
                $this->line("   IDs: {$dup->ids}");
                $this->line("   Type IDs: {$dup->type_ids}");
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info('📊 Sample data from last 10 world gold records in gold_prices:');
        $samples = GoldPrice::whereNotNull('world_gold_usd')
            ->where('world_gold_usd', '>', 0)
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'created_at', 'world_gold_usd', 'gold_type_id']);

        foreach ($samples as $sample) {
            $this->line("   ID: {$sample->id} | {$sample->created_at} | \${$sample->world_gold_usd} | Type ID: {$sample->gold_type_id}");
        }

        $this->newLine();
        $this->info('📊 Check total records in gold_prices with world_gold_usd:');
        $total = GoldPrice::whereNotNull('world_gold_usd')->count();
        $this->info("   Total records: {$total}");

        // FIXED: Use "unique_count" instead of "unique" (reserved word)
        $uniqueTimestamps = GoldPrice::whereNotNull('world_gold_usd')
            ->select(DB::raw('COUNT(DISTINCT DATE_FORMAT(created_at, "%Y-%m-%d %H:%i")) as unique_count'))
            ->first();
        $this->info("   Unique timestamps: {$uniqueTimestamps->unique_count}");

        return Command::SUCCESS;
    }
}
