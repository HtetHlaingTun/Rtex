<?php

namespace App\Console\Commands;

use App\Events\GoldPriceUpdated;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoldPrice;
use App\Models\GoldType;
use App\Models\WorldGoldSnapshot;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoldSyncCommand extends Command
{
    protected $signature = 'gold:sync
                            {--fetch-only   : Only fetch world gold price}
                            {--save-only    : Only save hourly prices (skip fetch)}
                            {--consolidate  : Only run consolidation/cleanup}
                            {--dry-run      : Show what would be deleted without deleting}
                            {--days-keep=2  : Days of full records to keep before consolidating}';

    protected $description = 'Unified gold sync: fetch world price → save Myanmar prices → consolidate old data';

    public function handle(): int
    {
        $fetchOnly   = $this->option('fetch-only');
        $saveOnly    = $this->option('save-only');
        $consolidate = $this->option('consolidate');

        // If a specific flag is passed, run only that step
        if ($fetchOnly)   return $this->stepFetch();
        if ($saveOnly)    return $this->stepSave();
        if ($consolidate) return $this->stepConsolidate();

        // Default: full pipeline
        $this->info('🚀 Gold Sync Pipeline Starting...');

        if ($this->stepFetch() === Command::FAILURE) {
            return Command::FAILURE;
        }

        if ($this->stepSave() === Command::FAILURE) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
        // Note: consolidation runs separately at midnight via scheduler
    }

    // =========================================================================
    // STEP 1 — FETCH WORLD GOLD PRICE
    // =========================================================================

    private function stepFetch(): int
    {
        $this->info('📡 [Step 1] Fetching world gold price...');

        try {
            $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

            $goldRes = Http::withHeaders(['User-Agent' => $agent])->timeout(15)
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/GC=F');

            $rateRes = Http::withHeaders(['User-Agent' => $agent])->timeout(15)
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/USDSGD=X');

            if (!$goldRes->successful() || !$rateRes->successful()) {
                throw new \Exception("Yahoo Finance unreachable. Gold: {$goldRes->status()} Rate: {$rateRes->status()}");
            }

            $goldMeta = $goldRes->json('chart.result.0.meta');
            $rateMeta = $rateRes->json('chart.result.0.meta');

            if (!$goldMeta || !$rateMeta) {
                throw new \Exception('Invalid JSON structure from Yahoo Finance.');
            }

            $usdPrice    = floatval($goldMeta['regularMarketPrice']);
            $usdSgdRate  = floatval($rateMeta['regularMarketPrice']);
            $sgdPrice    = $usdPrice * $usdSgdRate;

            $mmkRate = $this->getDynamicUsdMmkRate();
            if (!$mmkRate) {
                $this->error('❌ No USD-MMK rate found. Run bank sync first.');
                return Command::FAILURE;
            }

            $now = Carbon::now('Asia/Singapore');

            // Skip if same price within same minute
            $last = WorldGoldSnapshot::latest('fetched_at')->first();
            if (
                $last && $last->fetched_at->diffInMinutes($now) < 1
                && (float)$last->usd_price === $usdPrice
                && (float)$last->usd_mmk_rate === (float)$mmkRate
            ) {
                $this->line('<fg=gray>Same price within the minute — skipping snapshot.</>');
                return Command::SUCCESS;
            }

            $prev          = $last?->usd_price ?? $usdPrice;
            $change        = round($usdPrice - $prev, 2);
            $changePct     = $prev > 0 ? round(($change / $prev) * 100, 4) : 0;

            $mmkPriceNew = WorldGoldSnapshot::convertToMmkNew($usdPrice, $mmkRate);
            $mmkPriceOld = WorldGoldSnapshot::convertToMmkOld($usdPrice, $mmkRate);
            $mmkPrice    = WorldGoldSnapshot::convertToMmk($usdPrice, $mmkRate);

            WorldGoldSnapshot::create([
                'usd_price'      => $usdPrice,
                'sgd_price'      => $sgdPrice,
                'usd_sgd_rate'   => $usdSgdRate,
                'change'         => $change,
                'change_percent' => $changePct,
                'day_high'       => floatval($goldMeta['regularMarketDayHigh'] ?? 0),
                'day_low'        => floatval($goldMeta['regularMarketDayLow'] ?? 0),
                'previous_close' => floatval($goldMeta['previousClose'] ?? $usdPrice),
                'usd_mmk_rate'   => $mmkRate,
                'mmk_price'      => $mmkPrice,
                'mmk_price_new'  => $mmkPriceNew,
                'mmk_price_old'  => $mmkPriceOld,
                'fetched_at'     => $now,
            ]);

            $this->info("✅ Snapshot saved: USD \${$usdPrice} | MMK {$mmkPriceNew} (new) / {$mmkPriceOld} (old)");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Fetch failed: ' . $e->getMessage());
            Log::error('GoldSyncCommand@stepFetch: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    // =========================================================================
    // STEP 2 — SAVE HOURLY MYANMAR GOLD PRICES
    // =========================================================================

    private function stepSave(): int
    {
        $this->info('💾 [Step 2] Saving Myanmar gold prices...');

        try {
            $latest = WorldGoldSnapshot::latest('fetched_at')->first();

            if (!$latest?->usd_mmk_rate) {
                $this->warn('No snapshot or MMK rate found.');
                return Command::FAILURE;
            }

            $mmkCurrencyId = Currency::whereRaw('UPPER(TRIM(code)) = ?', ['MMK'])->value('id')
                ?? Currency::first()?->id ?? 1;

            $activeTypes = GoldType::where('is_active', true)->where('category', 'myanmar')->get();

            if ($activeTypes->isEmpty()) {
                $this->warn('No active Myanmar gold types found.');
                return Command::SUCCESS;
            }

            $now = Carbon::now('Asia/Singapore');

            foreach ($activeTypes as $type) {
                $currentPrice = $type->system === 'old' ? $latest->mmk_price_old : $latest->mmk_price_new;
                if (!$currentPrice) continue;

                // Skip if a record already exists for this exact minute
                $existsThisMinute = GoldPrice::where('gold_type_id', $type->id)
                    ->where('source_type', 'auto')
                    ->whereBetween('created_at', [$now->copy()->startOfMinute(), $now->copy()->endOfMinute()])
                    ->exists();

                if ($existsThisMinute) {
                    $this->line("<fg=yellow>⚠  Skipping {$type->name}: already saved this minute.</>");
                    continue;
                }

                $lastRecord     = GoldPrice::where('gold_type_id', $type->id)->where('source_type', 'auto')->latest()->first();
                $hasRecordToday = GoldPrice::where('gold_type_id', $type->id)->where('source_type', 'auto')
                    ->whereDate('created_at', $now->toDateString())->exists();

                // Skip stable price if we already have a record today and it's recent
                if ($lastRecord && (float)$lastRecord->price === (float)$currentPrice) {
                    if ($hasRecordToday) {
                        if ($now->diffInMinutes($lastRecord->created_at) < 10) {
                            $this->line("<fg=gray>Skipping {$type->name}: stable price, recent record exists.</>");
                            continue;
                        }
                    } else {
                        $this->line("<fg=blue>💎 Daily heartbeat for {$type->name} (price stable overnight).</>");
                    }
                }

                $todayStr = $now->toDateString();
                $stats    = GoldPrice::where('gold_type_id', $type->id)->where('price_date', $todayStr)
                    ->selectRaw('MAX(price) as high, MIN(price) as low, MIN(price) as open')->first();

                $previousPrice = $lastRecord?->price ?? $currentPrice;

                $newRecord = GoldPrice::create([
                    'gold_type_id'      => $type->id,
                    'price_date'        => $todayStr,
                    'price'             => $currentPrice,
                    'currency_id'       => $mmkCurrencyId,
                    'source_type'       => 'auto',
                    'opening_price'     => $stats->open ?? $currentPrice,
                    'high_price'        => max($stats->high ?? 0, $currentPrice),
                    'low_price'         => min($stats->low ?? $currentPrice, $currentPrice),
                    'previous_price'    => $previousPrice,
                    'change_percentage' => $previousPrice > 0
                        ? round((($currentPrice - $previousPrice) / $previousPrice) * 100, 4) : 0,
                    'trend'             => $currentPrice > $previousPrice ? 'up' : ($currentPrice < $previousPrice ? 'down' : 'stable'),
                    'world_gold_usd'    => $latest->usd_price,
                    'usd_mmk_rate'      => $latest->usd_mmk_rate,
                    'status'            => 'verified',
                    'is_verified'       => 1,
                    'verified_at'       => $now,
                    'market_notes'      => "Hourly Sync: World \${$latest->usd_price}",
                    'created_by'        => 1,
                ]);

                event(new GoldPriceUpdated($newRecord, $latest->usd_mmk_rate, $latest));
                $this->info("✅ Saved: {$type->name} → " . number_format($currentPrice) . ' MMK');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Save failed: ' . $e->getMessage());
            Log::error('GoldSyncCommand@stepSave: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    // =========================================================================
    // STEP 3 — CONSOLIDATE & CLEANUP OLD DATA
    // =========================================================================

    private function stepConsolidate(): int
    {
        $dryRun      = $this->option('dry-run');
        $daysToKeep  = (int) $this->option('days-keep');
        $cutoffDate  = now()->subDays($daysToKeep)->startOfDay();
        $purgeDate   = now()->subYears(2);

        $this->info('🧹 [Step 3] Consolidating old gold data...');
        $this->info('   Mode: ' . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->info("   Consolidating records before: {$cutoffDate->toDateString()}");

        // --- Permanent purge (> 2 years) ---
        $gpOld = GoldPrice::withTrashed()->where('created_at', '<', $purgeDate)->count();
        $wsOld = WorldGoldSnapshot::withTrashed()->where('fetched_at', '<', $purgeDate)->count();

        if ($gpOld || $wsOld) {
            if (!$dryRun) {
                GoldPrice::withTrashed()->where('created_at', '<', $purgeDate)->forceDelete();
                WorldGoldSnapshot::withTrashed()->where('fetched_at', '<', $purgeDate)->forceDelete();
                $this->info("🗑  Purged {$gpOld} gold prices and {$wsOld} snapshots older than 2 years.");
            } else {
                $this->info("🔍 Would purge {$gpOld} gold prices and {$wsOld} snapshots (> 2 years).");
            }
        }

        // --- Consolidate gold_prices ---
        $gpDeleted = $this->consolidateTable(
            model: GoldPrice::class,
            groupColumns: ['price_date', 'gold_type_id'],
            dateColumn: 'price_date',
            cutoff: $cutoffDate,
            dryRun: $dryRun,
            label: 'gold_prices'
        );

        // --- Consolidate world_gold_snapshots ---
        $wsDeleted = $this->consolidateSnapshots($cutoffDate, $dryRun);

        // --- Nullify duplicate world_gold_usd within same minute ---
        $this->cleanupDuplicateWorldGoldUsd($dryRun);

        // --- Squash intra-day records older than 30 days in gold_prices ---
        $this->squashOldIntraDayRecords($dryRun);

        $this->info('==========================================');
        $this->info("✅ Consolidation done. gold_prices: -{$gpDeleted} | snapshots: -{$wsDeleted}");
        $this->info('==========================================');

        return Command::SUCCESS;
    }

    private function consolidateTable(string $model, array $groupColumns, string $dateColumn, Carbon $cutoff, bool $dryRun, string $label): int
    {
        $groups = $model::withTrashed()
            ->where($dateColumn, '<', $cutoff)
            ->select($groupColumns)
            ->groupBy(...$groupColumns)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $deleted = 0;

        foreach ($groups as $group) {
            $query = $model::withTrashed();
            foreach ($groupColumns as $col) {
                $query->where($col, $group->$col);
            }
            $records = $query->orderBy('created_at', 'desc')->get();
            if ($records->count() <= 1) continue;

            $keep       = $records->first();
            $duplicates = $records->slice(1);

            if (!$dryRun) {
                foreach ($duplicates as $dup) {
                    $dup->forceDelete();
                    $deleted++;
                }
                if ($keep->trashed()) $keep->restore();
                $keep->update(['market_notes' => 'Daily Close (' . ($duplicates->count() + 1) . ' records consolidated)']);
            } else {
                $deleted += $duplicates->count();
            }
        }

        $this->info("   {$label}: " . ($dryRun ? 'Would delete' : 'Deleted') . " {$deleted} duplicates.");
        return $deleted;
    }

    private function consolidateSnapshots(Carbon $cutoff, bool $dryRun): int
    {
        $dates = WorldGoldSnapshot::withTrashed()
            ->where('fetched_at', '<', $cutoff)
            ->selectRaw('DATE(fetched_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->having('cnt', '>', 1)
            ->get();

        $deleted = 0;

        foreach ($dates as $row) {
            $records = WorldGoldSnapshot::withTrashed()
                ->whereDate('fetched_at', $row->date)
                ->orderBy('fetched_at', 'desc')
                ->get();

            if ($records->count() <= 1) continue;

            $keep       = $records->first();
            $duplicates = $records->slice(1);

            if (!$dryRun) {
                foreach ($duplicates as $dup) {
                    $dup->forceDelete();
                    $deleted++;
                }
                if ($keep->trashed()) $keep->restore();
                $keep->update([
                    'day_high'     => $records->max('usd_price'),
                    'day_low'      => $records->min('usd_price'),
                    'market_notes' => 'Daily consolidated (' . $records->count() . ' records)',
                ]);
            } else {
                $deleted += $duplicates->count();
            }
        }

        $this->info("   world_gold_snapshots: " . ($dryRun ? 'Would delete' : 'Deleted') . " {$deleted} duplicates.");
        return $deleted;
    }

    private function cleanupDuplicateWorldGoldUsd(bool $dryRun): void
    {
        $minuteGroups = GoldPrice::whereNotNull('world_gold_usd')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") as minute_group'))
            ->groupBy('minute_group')
            ->pluck('minute_group');

        $nullified = 0;
        foreach ($minuteGroups as $minute) {
            $keepId = GoldPrice::whereNotNull('world_gold_usd')
                ->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") = ?', [$minute])
                ->orderBy('id')
                ->value('id');

            if (!$dryRun) {
                $nullified += GoldPrice::whereNotNull('world_gold_usd')
                    ->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") = ?', [$minute])
                    ->where('id', '!=', $keepId)
                    ->update(['world_gold_usd' => null]);
            }
        }

        if ($nullified > 0) {
            $this->info("   world_gold_usd: nullified {$nullified} duplicate entries per minute.");
        }
    }

    private function squashOldIntraDayRecords(bool $dryRun): void
    {
        $cutoff = Carbon::now('Asia/Singapore')->subDays(30)->toDateString();

        $typeIds = GoldType::where('is_active', true)->where('category', 'myanmar')->pluck('id');

        foreach ($typeIds as $typeId) {
            $dates = GoldPrice::where('gold_type_id', $typeId)
                ->where('source_type', 'auto')
                ->where('price_date', '<', $cutoff)
                ->pluck('price_date')
                ->unique();

            foreach ($dates as $date) {
                $records = GoldPrice::where('gold_type_id', $typeId)
                    ->where('source_type', 'auto')
                    ->where('price_date', $date)
                    ->orderBy('created_at')
                    ->get();

                if ($records->count() <= 1) continue;

                $first  = $records->first();
                $last   = $records->last();

                if (!$dryRun) {
                    $last->update([
                        'opening_price' => $first->price,
                        'closing_price' => $last->price,
                        'high_price'    => $records->max('price'),
                        'low_price'     => $records->min('price'),
                        'market_notes'  => 'Daily Close Summary (collapsed ' . $records->count() . ' snapshots)',
                        'trend'         => $last->price > $first->price ? 'up' : ($last->price < $first->price ? 'down' : 'stable'),
                    ]);

                    GoldPrice::where('gold_type_id', $typeId)
                        ->where('source_type', 'auto')
                        ->where('price_date', $date)
                        ->where('id', '!=', $last->id)
                        ->forceDelete();
                }

                $this->line("   Squashed {$date} (type {$typeId}): kept 1 of {$records->count()} records.");
            }
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function getDynamicUsdMmkRate(): ?float
    {
        $usdCurrency = Currency::where('code', 'USD')->first();

        if ($usdCurrency) {
            $rate = ExchangeRate::where('currency_id', $usdCurrency->id)
                ->where('status', 'verified')
                ->where('is_verified', true)
                ->latest('created_at')
                ->first();

            if ($rate) {
                return round(($rate->buy_rate + $rate->sell_rate) / 2, 2);
            }

            if ($usdCurrency->avg_bank_rate > 0) {
                return round($usdCurrency->avg_bank_rate * (1 + ($usdCurrency->bank_markup_percentage / 100)), 2);
            }
        }

        $rate = WorldGoldSnapshot::getUsdMmkRate();
        if ($rate) return $rate;

        $lastRate = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'USD'))
            ->where('is_verified', true)
            ->latest('created_at')
            ->first();

        return $lastRate ? round(($lastRate->buy_rate + $lastRate->sell_rate) / 2, 2) : null;
    }
}
