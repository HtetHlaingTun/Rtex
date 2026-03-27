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
    protected $description = 'Fetch current world gold price from Yahoo Finance and save snapshot';

    public function handle()
    {
        $this->info('Starting gold price fetch from Yahoo Finance...');

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->timeout(15)->get('https://query1.finance.yahoo.com/v8/finance/chart/GC=F');

            if (!$response->successful()) {
                throw new \Exception('Yahoo Finance API unreachable. HTTP Status: ' . $response->status());
            }

            $result = $response->json('chart.result.0');
            $meta = $result['meta'] ?? null;

            if (!$meta || !isset($meta['regularMarketPrice'])) {
                throw new \Exception('Invalid data structure received from Yahoo Finance.');
            }

            $currentPrice = floatval($meta['regularMarketPrice']);
            $mmkRate = WorldGoldSnapshot::getUsdMmkRate();

            if (!$mmkRate) {
                $this->error('MMK rate not available in database.');
                return Command::FAILURE;
            }

            $now = Carbon::now('Asia/Singapore');

            // 🔥 IMPROVED DUPLICATE CHECK
            $lastSnapshot = WorldGoldSnapshot::where('fetched_at', '>=', $now->copy()->subMinutes(5))
                ->latest('fetched_at')
                ->first();

            if ($lastSnapshot) {
                $priceSame = (float)$lastSnapshot->usd_price === $currentPrice;
                $rateSame = (float)$lastSnapshot->usd_mmk_rate === (float)$mmkRate;
                $sameMinute = $lastSnapshot->fetched_at->format('Y-m-d H:i') === $now->format('Y-m-d H:i');

                if ($sameMinute && $priceSame && $rateSame) {
                    $this->line('<fg=gray>Price and rate unchanged in same minute. Skipping snapshot.</>');
                    return Command::SUCCESS;
                }
            }

            // Create snapshot
            $previousPrice = WorldGoldSnapshot::latest('id')->first()?->usd_price ?? $currentPrice;
            $change = round($currentPrice - $previousPrice, 2);
            $changePercent = $previousPrice > 0 ? round(($change / $previousPrice) * 100, 4) : 0;
            $previousClose = floatval($meta['previousClose'] ?? $previousPrice);

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
                'fetched_at'     => $now,
            ]);

            $this->info("✅ SUCCESS: Global Gold \${$currentPrice}");

            // Cleanup old records
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

        if ($oldDates->isEmpty()) {
            return;
        }

        foreach ($oldDates as $date) {
            $records = WorldGoldSnapshot::whereDate('fetched_at', $date)
                ->orderBy('fetched_at', 'desc')
                ->get();

            if ($records->count() <= 1) continue;

            $keeper = $records->first();

            $dayHigh = $records->max('usd_price');
            $dayLow = $records->min('usd_price');

            $keeper->update([
                'day_high' => $dayHigh,
                'day_low'  => $dayLow,
            ]);

            WorldGoldSnapshot::whereDate('fetched_at', $date)
                ->where('id', '!=', $keeper->id)
                ->forceDelete();

            $this->line("<fg=yellow>Squashed {$date}: Kept 1 record</>");
        }
    }
}
