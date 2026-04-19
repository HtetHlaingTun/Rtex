<?php
// app/Http/Controllers/FuelPriceController.php

namespace App\Http\Controllers;

use App\Models\FuelPrice;
use Carbon\Carbon;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FuelPriceController extends Controller
{
    public function index()
    {
        $prices = Cache::remember('fuel_prices', 1800, function () {
            return $this->calculateFuelPrices();
        });

        return response()->json([
            'success' => true,
            'data' => $prices,
            'last_updated' => now()->toISOString()
        ]);
    }

    private function calculateFuelPrices()
    {
        try {
            $usd = DB::table('currencies')->where('code', 'USD')->first();

            if (!$usd) {
                return $this->getFallbackPrices();
            }

            $usdMmkRate = $usd->avg_bank_rate * (1 + ($usd->bank_markup_percentage / 100));

            $apiKey = env('COMMODITY_API_KEY');
            $response = Http::timeout(10)
                ->withHeaders(['x-api-key' => $apiKey])
                ->get('https://api.commoditypriceapi.com/v2/rates/latest', [
                    'symbols' => 'RB-SPOT'
                ]);

            if ($response->failed()) {
                return $this->getFallbackPrices();
            }

            $globalPrice = $response->json()['data']['rates']['RB-SPOT'] ??
                $response->json()['rates']['RB-SPOT'] ?? null;

            $multipliers = [
                'yangon' => 1.171,
                'mandalay' => 1.185,
                'naypyidaw' => 1.178,
                'ayeyarwady' => 1.166,
                'bago' => 1.175,
                'magway' => 1.182,
                'sagaing' => 1.188,
                'thanintharyi' => 1.192,
            ];

            $usdPerLiter = $globalPrice / 3.785;
            $baseMmk = $usdPerLiter * $usdMmkRate;

            // Get previous prices for trend calculation
            $previousPrices = FuelPrice::whereDate('created_at', '<', Carbon::today())
                ->orderBy('created_at', 'desc')
                ->get()
                ->keyBy('region');

            $prices = [];
            foreach ($multipliers as $region => $multiplier) {
                $basePrice = round(($baseMmk * $multiplier) / 5) * 5;

                $octane92 = $basePrice;
                $octane95 = $basePrice + 210;
                $diesel = $basePrice - 300;

                // Get trends from previous prices
                $prev = $previousPrices->get($region);
                $trend92 = null;
                $trend95 = null;
                $trendDiesel = null;

                if ($prev) {
                    $trend92 = $this->calculateTrend($octane92, $prev->octane_92);
                    $trend95 = $this->calculateTrend($octane95, $prev->octane_95);
                    $trendDiesel = $this->calculateTrend($diesel, $prev->diesel);
                }

                $prices[$region] = [
                    '92' => $octane92,
                    '95' => $octane95,
                    'diesel' => $diesel,
                    'trend_92' => $trend92,
                    'trend_95' => $trend95,
                    'trend_diesel' => $trendDiesel,
                ];
            }

            return [
                'status' => 'success',
                'global_gas_usd' => round($globalPrice, 2),
                'usd_mmk_rate' => round($usdMmkRate, 2),
                'prices' => $prices,
                'disclaimer' => 'Prices are estimates based on global markets. Actual pump prices may vary.',
                'last_updated' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            Log::error('Fuel price calculation error: ' . $e->getMessage());
            return $this->getFallbackPrices();
        }
    }

    private function calculateTrend($current, $previous)
    {
        if (!$previous || $previous == 0) {
            return ['direction' => 'neutral', 'percent' => 0, 'icon' => '—', 'color' => 'text-slate-400'];
        }

        $percent = round((($current - $previous) / $previous) * 100, 2);

        if ($percent > 0) {
            return ['direction' => 'up', 'percent' => $percent, 'icon' => '▲', 'color' => 'text-emerald-600'];
        } elseif ($percent < 0) {
            return ['direction' => 'down', 'percent' => abs($percent), 'icon' => '▼', 'color' => 'text-rose-600'];
        } else {
            return ['direction' => 'neutral', 'percent' => 0, 'icon' => '—', 'color' => 'text-slate-400'];
        }
    }

    private function getFallbackPrices()
    {
        return [
            'status' => 'estimated',
            'global_gas_usd' => 3.06,
            'usd_mmk_rate' => 4174,
            'prices' => [
                'yangon' => ['92' => 3950, '95' => 4160, 'diesel' => 3650, 'trend_92' => null, 'trend_95' => null, 'trend_diesel' => null],
                'mandalay' => ['92' => 4000, '95' => 4210, 'diesel' => 3700, 'trend_92' => null, 'trend_95' => null, 'trend_diesel' => null],
                'naypyidaw' => ['92' => 3975, '95' => 4185, 'diesel' => 3675, 'trend_92' => null, 'trend_95' => null, 'trend_diesel' => null],
                'ayeyarwady' => ['92' => 4045, '95' => 4255, 'diesel' => 3745, 'trend_92' => null, 'trend_95' => null, 'trend_diesel' => null],
            ],
            'disclaimer' => 'Estimated prices based on previous calculations.',
            'last_updated' => now()->toISOString()
        ];
    }






    public function history($region = 'yangon')
    {
        // Get last 30 days of fuel prices for a region
        $history = FuelPrice::where('region', $region)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get(['id', 'octane_92', 'octane_95', 'diesel', 'created_at'])
            ->map(function ($item) {
                return [
                    'date' => $item->created_at->format('Y-m-d'),
                    'time' => $item->created_at->format('H:i'),
                    'octane_92' => $item->octane_92,
                    'octane_95' => $item->octane_95,
                    'diesel' => $item->diesel,
                    'trend_92' => $this->calculateTrendFromPrevious($item, 'octane_92'),
                    'trend_95' => $this->calculateTrendFromPrevious($item, 'octane_95'),
                    'trend_diesel' => $this->calculateTrendFromPrevious($item, 'diesel'),
                ];
            });

        return response()->json([
            'success' => true,
            'region' => $region,
            'history' => $history,
            'last_updated' => now()->toISOString()
        ]);
    }

    private function calculateTrendFromPrevious($current, $field)
    {
        $previous = FuelPrice::where('region', $current->region)
            ->where('created_at', '<', $current->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$previous) {
            return null;
        }

        $currentValue = $current->$field;
        $previousValue = $previous->$field;

        if ($previousValue == 0) return null;

        $percent = round((($currentValue - $previousValue) / $previousValue) * 100, 2);

        if ($percent > 0) {
            return ['direction' => 'up', 'percent' => $percent, 'icon' => '▲', 'color' => 'text-emerald-600'];
        } elseif ($percent < 0) {
            return ['direction' => 'down', 'percent' => abs($percent), 'icon' => '▼', 'color' => 'text-rose-600'];
        }

        return null;
    }
}
