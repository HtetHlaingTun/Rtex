<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\WorldGoldSnapshot;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchWorldGoldPriceCommand extends Command
{
    protected $signature = 'gold:fetch';
    protected $description = 'Fetch global USD gold and calculate SGD price for snapshots using dynamic USD-MMK rate';

    public function handle()
    {
        $this->info('Starting multi-market gold price fetch...');

        try {
            $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

            // 1. FETCH GLOBAL GOLD (USD)
            $goldResponse = Http::withHeaders(['User-Agent' => $userAgent])
                ->timeout(15)
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/GC=F');

            // 2. FETCH USD/SGD EXCHANGE RATE
            $rateResponse = Http::withHeaders(['User-Agent' => $userAgent])
                ->timeout(15)
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/USDSGD=X');

            if (!$goldResponse->successful() || !$rateResponse->successful()) {
                throw new \Exception('Yahoo Finance unreachable. Gold: ' . $goldResponse->status() . ' Rate: ' . $rateResponse->status());
            }

            // Extract Data
            $goldMeta = $goldResponse->json('chart.result.0.meta');
            $rateMeta = $rateResponse->json('chart.result.0.meta');

            if (!$goldMeta || !$rateMeta) {
                throw new \Exception('Invalid JSON structure from Yahoo.');
            }

            $currentPriceUsd = floatval($goldMeta['regularMarketPrice']);
            $usdSgdRate      = floatval($rateMeta['regularMarketPrice']);
            $currentPriceSgd = $currentPriceUsd * $usdSgdRate;

            // ============ FETCH DYNAMIC USD-MMK RATE ============
            $mmkRate = $this->getDynamicUsdMmkRate();

            if (!$mmkRate) {
                $this->error('❌ MMK rate not found. Please run bank sync first.');
                Log::error('Gold fetch failed: No USD-MMK rate available');
                return Command::FAILURE;
            }

            $this->info("📊 Using USD-MMK rate: {$mmkRate} (from bank_avg with markup)");
            // ====================================================

            $now = Carbon::now('Asia/Singapore');

            // Duplicate check within same minute
            $lastSnapshot = WorldGoldSnapshot::latest('fetched_at')->first();

            if ($lastSnapshot && $lastSnapshot->fetched_at->diffInMinutes($now) < 1) {
                if (
                    (float)$lastSnapshot->usd_price === $currentPriceUsd &&
                    (float)$lastSnapshot->usd_mmk_rate === (float)$mmkRate
                ) {
                    $this->line('<fg=gray>Price unchanged within the same minute. Skipping.</>');
                    return Command::SUCCESS;
                }
            }

            // Calculate Changes
            $previousSnapshot = WorldGoldSnapshot::latest('id')->first();
            $previousPriceUsd = $previousSnapshot?->usd_price ?? $currentPriceUsd;
            $change           = round($currentPriceUsd - $previousPriceUsd, 2);
            $changePercent    = $previousPriceUsd > 0 ? round(($change / $previousPriceUsd) * 100, 4) : 0;

            // Calculate MMK prices using dynamic rate
            $mmkPriceNew = WorldGoldSnapshot::convertToMmkNew($currentPriceUsd, $mmkRate);
            $mmkPriceOld = WorldGoldSnapshot::convertToMmkOld($currentPriceUsd, $mmkRate);
            $mmkPrice    = WorldGoldSnapshot::convertToMmk($currentPriceUsd, $mmkRate);

            $this->info("💰 Gold Price Calculation:");
            $this->info("   USD Gold: \${$currentPriceUsd}");
            $this->info("   USD-MMK Rate: {$mmkRate}");
            $this->info("   New System (16.329g): {$mmkPriceNew} MMK");
            $this->info("   Old System (16.606g): {$mmkPriceOld} MMK");

            // Save Snapshot
            WorldGoldSnapshot::create([
                'usd_price'      => $currentPriceUsd,
                'sgd_price'      => $currentPriceSgd,
                'usd_sgd_rate'   => $usdSgdRate,
                'change'         => $change,
                'change_percent' => $changePercent,
                'day_high'       => floatval($goldMeta['regularMarketDayHigh'] ?? 0),
                'day_low'        => floatval($goldMeta['regularMarketDayLow'] ?? 0),
                'previous_close' => floatval($goldMeta['previousClose'] ?? $currentPriceUsd),
                'usd_mmk_rate'   => $mmkRate,
                'mmk_price'      => $mmkPrice,
                'mmk_price_new'  => $mmkPriceNew,
                'mmk_price_old'  => $mmkPriceOld,
                'fetched_at'     => $now,
            ]);

            $this->info("✅ SUCCESS: USD \${$currentPriceUsd} | SGD \${$currentPriceSgd} | MMK {$mmkPriceNew}");
            $this->info("   Spread: " . ($mmkPriceNew - $mmkPriceOld) . " MMK between systems");

            $this->performCleanup();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ FAILED: ' . $e->getMessage());
            Log::error('FetchWorldGoldPriceCommand Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get dynamic USD-MMK rate from bank_avg system
     * This uses the same rates as your exchange display
     */
    private function getDynamicUsdMmkRate(): ?float
    {
        // Method 1: Get the latest exchange rate from database (most accurate)
        $usdCurrency = Currency::where('code', 'USD')->first();

        if ($usdCurrency) {
            $latestRate = ExchangeRate::where('currency_id', $usdCurrency->id)
                ->where('status', 'verified')
                ->where('is_verified', true)
                ->latest('created_at')
                ->first();

            if ($latestRate) {
                // Use mid rate (average of buy and sell) for gold calculation
                $midRate = ($latestRate->buy_rate + $latestRate->sell_rate) / 2;
                return round($midRate, 2);
            }

            // Fallback: use stored avg_bank_rate from currency
            if ($usdCurrency->avg_bank_rate > 0) {
                return round($usdCurrency->avg_bank_rate * (1 + ($usdCurrency->bank_markup_percentage / 100)), 2);
            }
        }

        // Method 2: Use WorldGoldSnapshot method
        $rate = WorldGoldSnapshot::getUsdMmkRate();

        if ($rate) {
            return $rate;
        }

        // Method 3: Last resort - manual fetch from exchange_rates table
        $lastRate = ExchangeRate::whereHas('currency', function ($q) {
            $q->where('code', 'USD');
        })
            ->where('is_verified', true)
            ->latest('created_at')
            ->first();

        if ($lastRate) {
            return round(($lastRate->buy_rate + $lastRate->sell_rate) / 2, 2);
        }

        return null;
    }

    /**
     * Clean up old snapshots - keep only one per day
     */
    private function performCleanup(): void
    {
        $cutoff = Carbon::now('Asia/Singapore')->subDays(7)->startOfDay();

        // For older records, keep only the latest one per day
        $oldDates = WorldGoldSnapshot::where('fetched_at', '<', $cutoff)
            ->selectRaw('DATE(fetched_at) as date')
            ->groupBy('date')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('date');

        foreach ($oldDates as $date) {
            $records = WorldGoldSnapshot::whereDate('fetched_at', $date)
                ->orderBy('fetched_at', 'desc')
                ->get();

            if ($records->count() <= 1) continue;

            $keeper = $records->first();

            // Update day high/low for the keeper
            $keeper->update([
                'day_high' => max($records->pluck('usd_price')->toArray()),
                'day_low'  => min($records->pluck('usd_price')->toArray()),
            ]);

            // Delete all other records for this day
            WorldGoldSnapshot::whereDate('fetched_at', $date)
                ->where('id', '!=', $keeper->id)
                ->forceDelete();

            $this->line("<fg=yellow>🗑️  Cleaned up {$date}: Kept 1 record (consolidated).</>");
        }

        // Keep only last 90 days of data
        $ninetyDaysAgo = Carbon::now('Asia/Singapore')->subDays(90);
        $deletedCount = WorldGoldSnapshot::where('fetched_at', '<', $ninetyDaysAgo)->forceDelete();

        if ($deletedCount > 0) {
            $this->info("🗑️  Deleted {$deletedCount} records older than 90 days");
        }
    }
}
