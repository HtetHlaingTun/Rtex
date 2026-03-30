<?php

namespace App\Console\Commands;

use App\Models\WorldGoldSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchWorldGoldPriceCommand extends Command
{
    protected $signature = 'gold:fetch';
    protected $description = 'Fetch global USD gold and calculate SGD price for snapshots';

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

            // Fetch MMK Rate from your model
            $mmkRate = WorldGoldSnapshot::getUsdMmkRate();
            if (!$mmkRate) {
                $this->error('MMK rate not found in database.');
                return Command::FAILURE;
            }

            $now = Carbon::now('Asia/Singapore');

            // --- IMPROVED DUPLICATE CHECK ---
            $lastSnapshot = WorldGoldSnapshot::latest('fetched_at')->first();

            if ($lastSnapshot && $lastSnapshot->fetched_at->diffInMinutes($now) < 1) {
                if ((float)$lastSnapshot->usd_price === $currentPriceUsd && (float)$lastSnapshot->usd_mmk_rate === (float)$mmkRate) {
                    $this->line('<fg=gray>Price unchanged within the same minute. Skipping.</>');
                    return Command::SUCCESS;
                }
            }

            // Calculate Changes
            $previousSnapshot = WorldGoldSnapshot::latest('id')->first();
            $previousPriceUsd = $previousSnapshot?->usd_price ?? $currentPriceUsd;
            $change           = round($currentPriceUsd - $previousPriceUsd, 2);
            $changePercent    = $previousPriceUsd > 0 ? round(($change / $previousPriceUsd) * 100, 4) : 0;

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
                'mmk_price'      => WorldGoldSnapshot::convertToMmk($currentPriceUsd, $mmkRate),
                'mmk_price_new'  => WorldGoldSnapshot::convertToMmkNew($currentPriceUsd, $mmkRate),
                'mmk_price_old'  => WorldGoldSnapshot::convertToMmkOld($currentPriceUsd, $mmkRate),
                'fetched_at'     => $now,
            ]);

            $this->info("✅ SUCCESS: USD \${$currentPriceUsd} | SGD \${$currentPriceSgd}");

            $this->performCleanup();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('FAILED: ' . $e->getMessage());
            Log::error('FetchWorldGoldPriceCommand Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function performCleanup(): void
    {
        $cutoff = Carbon::now('Asia/Singapore')->startOfDay();
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
            $keeper->update([
                'day_high' => $records->max('usd_price'),
                'day_low'  => $records->min('usd_price'),
            ]);

            WorldGoldSnapshot::whereDate('fetched_at', $date)
                ->where('id', '!=', $keeper->id)
                ->forceDelete();

            $this->line("<fg=yellow>Cleaned up {$date}: Record consolidated.</>");
        }
    }
}
