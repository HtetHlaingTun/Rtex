<?php

namespace App\Jobs;

use App\Models\WorldGoldSnapshot;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchWorldGoldPrice
{
    use Queueable;

    public function handle(): void
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->timeout(10)->get('https://query1.finance.yahoo.com/v8/finance/chart/GC=F');

            if (!$response->successful()) {
                throw new \Exception('HTTP ' . $response->status());
            }

            $meta = $response->json('chart.result.0.meta');
            if (!$meta || !isset($meta['regularMarketPrice'])) {
                throw new \Exception('No price in response');
            }

            $currentPrice = round(floatval($meta['regularMarketPrice']), 2);
            $mmkRate = WorldGoldSnapshot::getUsdMmkRate();

            if (!$mmkRate) {
                Log::warning('FetchWorldGoldPrice: No MMK rate available, skipping save');
                return;
            }

            // --- START DUPLICATE CHECK LOGIC ---
            $lastSnapshot = WorldGoldSnapshot::latest('id')->first();

            if ($lastSnapshot) {
                $isSameDay = $lastSnapshot->created_at->isToday();

                // Compare current USD price and MMK rate to the last recorded ones
                $isSamePrice = (round((float)$lastSnapshot->usd_price, 2) === $currentPrice);
                $isSameRate = (round((float)$lastSnapshot->usd_mmk_rate, 2) === round((float)$mmkRate, 2));

                // If it's the SAME day AND nothing has changed (Price or Rate), skip it.
                if ($isSameDay && $isSamePrice && $isSameRate) {
                    // Log::info('World Gold: Price and Rate unchanged today. Skipping snapshot.');
                    return;
                }
            }
            // --- END DUPLICATE CHECK LOGIC ---

            $previousPrice = $lastSnapshot?->usd_price ?? $currentPrice;
            $change = round($currentPrice - $previousPrice, 2);
            $changePercent = $previousPrice > 0 ? round(($change / $previousPrice) * 100, 4) : 0;
            $previousClose = floatval($meta['previousClose'] ?? $lastSnapshot?->usd_price ?? $currentPrice);

            WorldGoldSnapshot::create([
                'usd_price'      => $currentPrice,
                'change'         => $change,
                'change_percent' => $changePercent,
                'day_high'       => floatval($meta['regularMarketDayHigh'] ?? 0),
                'day_low'        => floatval($meta['regularMarketDayLow'] ?? 0),
                'previous_close' => $previousClose,
                'usd_mmk_rate'   => $mmkRate,
                'mmk_price'      => WorldGoldSnapshot::convertToMmk($currentPrice, $mmkRate),
                'mmk_price_new'  => WorldGoldSnapshot::convertToMmkNew($currentPrice, $mmkRate),
                'mmk_price_old'  => WorldGoldSnapshot::convertToMmkOld($currentPrice, $mmkRate),
                'fetched_at'     => now()->timezone('Asia/Singapore'),
            ]);

            // Cleanup
            WorldGoldSnapshot::where('fetched_at', '<', now()->subDays(30))->delete();
        } catch (\Exception $e) {
            Log::error('FetchWorldGoldPrice failed: ' . $e->getMessage());
        }
    }
}
