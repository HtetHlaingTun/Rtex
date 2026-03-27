<?php

namespace App\Console\Commands;

use App\Events\GoldPriceUpdated;
use App\Models\Currency;
use App\Models\GoldPrice;
use App\Models\GoldType;
use App\Models\WorldGoldSnapshot;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SaveHourlyGoldPriceCommand extends Command
{
    protected $signature = 'gold:save-hourly';
    protected $description = 'Sync latest world gold snapshot to local Myanmar gold prices table';

    public function handle()
    {
        $this->info('Starting Hourly Gold Price Sync...');

        try {
            // 1. Get the most recent snapshot
            $latest = WorldGoldSnapshot::latest('fetched_at')->first();

            if (!$latest || !$latest->usd_mmk_rate) {
                $this->warn('No recent snapshot or MMK rate found. Run gold:fetch first.');
                return Command::FAILURE;
            }

            $this->line("Using latest snapshot from: {$latest->fetched_at} (Price: \${$latest->usd_price})");

            // 2. Ensure MMK currency exists
            $mmkCurrencyId = Currency::whereRaw('UPPER(TRIM(code)) = ?', ['MMK'])->value('id')
                ?? Currency::first()?->id ?? 1;

            // 3. Process active Myanmar gold types
            $activeTypes = GoldType::where('is_active', true)
                ->where('category', 'myanmar')
                ->get();

            if ($activeTypes->isEmpty()) {
                $this->warn('No active gold types found for category "myanmar".');
                return Command::SUCCESS;
            }

            $now = Carbon::now('Asia/Singapore');

            foreach ($activeTypes as $type) {
                $currentPrice = ($type->system === 'old')
                    ? $latest->mmk_price_old
                    : $latest->mmk_price_new;

                if (!$currentPrice) continue;

                // 🔥 CRITICAL FIX: Check for existing record in the same minute
                $existingRecord = GoldPrice::where('gold_type_id', $type->id)
                    ->where('source_type', 'auto')
                    ->where('created_at', '>=', $now->copy()->startOfMinute())
                    ->where('created_at', '<=', $now->copy()->endOfMinute())
                    ->first();

                if ($existingRecord) {
                    $this->line("<fg=yellow>⚠️  Skipping {$type->name}: Record already exists for this minute ({$now->format('H:i')}).</>");
                    continue;
                }

                $lastRecord = GoldPrice::where('gold_type_id', $type->id)
                    ->where('source_type', 'auto')
                    ->latest('created_at')
                    ->first();

                // Skip if price hasn't changed AND last record was within last 10 minutes
                if ($lastRecord && (float)$lastRecord->price === (float)$currentPrice) {
                    $minutesSinceLast = $now->diffInMinutes($lastRecord->created_at);
                    if ($minutesSinceLast < 10) {
                        $this->line("<fg=gray>Skipping {$type->name}: No price change in last {$minutesSinceLast} minutes.</>");
                        continue;
                    }
                }

                $todayString = $now->toDateString();

                $stats = GoldPrice::where('gold_type_id', $type->id)
                    ->where('price_date', $todayString)
                    ->selectRaw('MAX(price) as high, MIN(price) as low, MIN(price) as open')
                    ->first();

                $previousPrice = $lastRecord?->price ?? $currentPrice;

                $newPriceRecord = GoldPrice::create([
                    'gold_type_id'      => $type->id,
                    'price_date'        => $todayString,
                    'price'             => $currentPrice,
                    'currency_id'       => $mmkCurrencyId,
                    'source_type'       => 'auto',
                    'opening_price'     => $stats->open ?? $currentPrice,
                    'high_price'        => max($stats->high ?? 0, $currentPrice),
                    'low_price'         => min($stats->low ?? $currentPrice, $currentPrice),
                    'previous_price'    => $previousPrice,
                    'change_percentage' => $previousPrice > 0 ? round((($currentPrice - $previousPrice) / $previousPrice) * 100, 4) : 0,
                    'trend'             => $currentPrice > $previousPrice ? 'up' : ($currentPrice < $previousPrice ? 'down' : 'stable'),
                    'world_gold_usd'    => $latest->usd_price,
                    'usd_mmk_rate'      => $latest->usd_mmk_rate,
                    'status'            => 'verified',
                    'is_verified'       => 1,
                    'verified_at'       => $now,
                    'market_notes'      => "Hourly Sync: World \${$latest->usd_price}",
                    'created_by'        => 1,
                ]);

                event(new GoldPriceUpdated($newPriceRecord, $latest->usd_mmk_rate, $latest));

                $this->info("✅ CREATED: {$type->name} at " . number_format($currentPrice) . " MMK");
            }

            // 4. Run cleanup for old records
            $this->cleanupOldRecords();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('SaveHourlyGoldPriceCommand Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function cleanupOldRecords(): void
    {
        $cutoffDate = Carbon::now('Asia/Singapore')->subDays(30)->toDateString();

        $this->info("Squashing records older than $cutoffDate...");

        $activeTypeIds = GoldType::where('is_active', true)
            ->where('category', 'myanmar')
            ->pluck('id');

        foreach ($activeTypeIds as $typeId) {
            $oldDates = GoldPrice::where('gold_type_id', $typeId)
                ->where('source_type', 'auto')
                ->where('price_date', '<', $cutoffDate)
                ->pluck('price_date')
                ->unique();

            foreach ($oldDates as $date) {
                $dayQuery = GoldPrice::where('gold_type_id', $typeId)
                    ->where('source_type', 'auto')
                    ->where('price_date', $date);

                if ($dayQuery->count() <= 1) continue;

                $allDayRecords = $dayQuery->orderBy('created_at', 'asc')->get();
                $first = $allDayRecords->first();
                $last = $allDayRecords->last();

                $keeper = $last;

                $keeper->update([
                    'opening_price' => $first->price,
                    'closing_price' => $last->price,
                    'high_price'    => $allDayRecords->max('price'),
                    'low_price'     => $allDayRecords->min('price'),
                    'market_notes'  => "Daily Close Summary (Collapsed " . $allDayRecords->count() . " snapshots)",
                    'trend'         => ($last->price > $first->price) ? 'up' : (($last->price < $first->price) ? 'down' : 'stable'),
                ]);

                GoldPrice::where('gold_type_id', $typeId)
                    ->where('source_type', 'auto')
                    ->where('price_date', $date)
                    ->where('id', '!=', $keeper->id)
                    ->forceDelete();

                $this->line("Processed $date: Kept 1 summary record");
            }
        }
    }
}
