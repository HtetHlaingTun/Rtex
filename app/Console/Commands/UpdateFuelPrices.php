<?php

namespace App\Console\Commands;

use App\Models\FuelCalibration;
use App\Models\FuelPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateFuelPrices extends Command
{
    protected $signature = 'fuel:prices-update';
    protected $description = 'Update fuel prices from global market data';

    public function handle()
    {
        $this->info('🔄 Fetching fuel prices...');

        // ✅ FIX: Get USD rate from exchange_rates table (same as controller)
        $usdRate = $this->getUsdMmkRate();

        if ($usdRate <= 0) {
            $this->error('USD rate is 0. Cannot update fuel prices.');
            return 1;
        }

        $this->info("USD/MMK Rate: " . round($usdRate, 2));

        // Fetch global gas price
        $globalPrice = $this->fetchGlobalGasPrice() ?? 3.06;
        $this->info("Global Gas Price: \${$globalPrice}/gallon");

        // Get calibration factor
        $calibration = FuelCalibration::first();
        $factor = $calibration ? $calibration->calibration_factor : 1.4000;
        $this->info("Calibration Factor: {$factor}");

        // Calculate base price
        $usdPerLiter = $globalPrice / 3.785;
        $baseImportMmk = $usdPerLiter * $usdRate;
        $calibratedBaseMmk = $baseImportMmk * $factor;

        $this->info("Base Import: " . round($baseImportMmk) . " MMK/liter");
        $this->info("Calibrated Base: " . round($calibratedBaseMmk) . " MMK/liter");

        // Regional multipliers
        $regions = [
            'yangon' => 1.000,
            'mandalay' => 1.012,
            'naypyidaw' => 1.008,
            'ayeyarwady' => 1.005,
            'bago' => 1.007,
            'magway' => 1.009,
            'sagaing' => 1.011,
            'thanintharyi' => 1.015,
        ];

        // Get previous prices for trend calculation
        $previousPrices = FuelPrice::whereDate('created_at', '<', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get()
            ->keyBy('region');

        $saved = 0;

        foreach ($regions as $region => $multiplier) {
            $regionalBase = $calibratedBaseMmk * $multiplier;

            $octane92 = round($regionalBase / 5) * 5;
            $octane95 = $octane92 + 210;
            $diesel = $octane92 - 300;
            $premiumDiesel = $diesel + 150;

            // Calculate trends
            $prev = $previousPrices->get($region);
            $change92 = $change95 = $changeDiesel = $changePremiumDiesel = 0;

            if ($prev) {
                $change92 = $prev->octane_92 > 0 ? round((($octane92 - $prev->octane_92) / $prev->octane_92) * 100, 2) : 0;
                $change95 = $prev->octane_95 > 0 ? round((($octane95 - $prev->octane_95) / $prev->octane_95) * 100, 2) : 0;
                $changeDiesel = $prev->diesel > 0 ? round((($diesel - $prev->diesel) / $prev->diesel) * 100, 2) : 0;
                $prevPremiumDiesel = $prev->premium_diesel ?? ($prev->diesel + 150);
                $changePremiumDiesel = $prevPremiumDiesel > 0 ? round((($premiumDiesel - $prevPremiumDiesel) / $prevPremiumDiesel) * 100, 2) : 0;
            }

            FuelPrice::create([
                'region' => $region,
                'octane_92' => $octane92,
                'octane_95' => $octane95,
                'diesel' => $diesel,
                'premium_diesel' => $premiumDiesel,
                'global_usd_reference' => $globalPrice,
                'market_usd_rate' => round($usdRate),
                'change_percent_92' => $change92,
                'change_percent_95' => $change95,
                'change_percent_diesel' => $changeDiesel,
                'change_percent_premium_diesel' => $changePremiumDiesel,
            ]);

            $saved++;
            $this->info("✓ {$region}: 92={$octane92}, 95={$octane95}, Diesel={$diesel}");
        }

        $this->info("✅ Saved {$saved} fuel price records!");

        // Clean up old records
        $cutoffDate = Carbon::now()->subMonths(6);
        $deleted = FuelPrice::where('created_at', '<', $cutoffDate)->delete();
        $this->info("🗑️ Deleted {$deleted} old records");

        return 0;
    }

    /**
     * ✅ Get USD/MMK rate from exchange_rates table (SAME AS CONTROLLER)
     */
    private function getUsdMmkRate(): float
    {
        $usd = DB::table('currencies')->where('code', 'USD')->first();

        if (!$usd) {
            return 4157.42;
        }

        $latestRate = DB::table('exchange_rates')
            ->where('currency_id', $usd->id)
            ->where('is_verified', 1)
            ->latest('rate_date')
            ->first();

        if ($latestRate) {
            if ($latestRate->mid_rate && $latestRate->mid_rate > 0) {
                return (float) $latestRate->mid_rate;
            }
            if ($latestRate->buy_rate > 0 && $latestRate->sell_rate > 0) {
                return ($latestRate->buy_rate + $latestRate->sell_rate) / 2;
            }
        }

        return 4157.42;
    }

    private function fetchGlobalGasPrice()
    {
        // Try EIA first
        $price = $this->fetchFromEIA();
        if ($price) {
            $this->info("📡 Source: EIA Official");
            return $price;
        }

        // Fallback to CommodityPriceAPI
        $price = $this->fetchFromCommodityAPI();
        if ($price) {
            $this->info("📡 Source: CommodityPriceAPI");
            return $price;
        }

        return null;
    }

    private function fetchFromEIA(): ?float
    {
        $apiKey = env('EIA_API_KEY');
        if (!$apiKey) return null;

        try {
            $response = Http::timeout(10)
                ->get('https://api.eia.gov/v2/petroleum/pri/gnd/data/', [
                    'api_key' => $apiKey,
                    'frequency' => 'daily',
                    'data' => ['value'],
                    'facets' => ['product' => ['EPMR'], 'series' => ['RBR']],
                    'sort' => [['column' => 'period', 'direction' => 'desc']],
                    'length' => 1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $value = $data['response']['data'][0]['value'] ?? null;
                return $value ? round($value / 100, 2) : null;
            }
        } catch (\Exception $e) {
            Log::warning('EIA API failed: ' . $e->getMessage());
        }

        return null;
    }

    private function fetchFromCommodityAPI(): ?float
    {
        $apiKey = env('COMMODITY_API_KEY');
        if (!$apiKey) return null;

        try {
            $response = Http::timeout(10)
                ->withHeaders(['x-api-key' => $apiKey])
                ->get('https://api.commoditypriceapi.com/v2/rates/latest', [
                    'symbols' => 'RB-SPOT'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['rates']['RB-SPOT'] ??
                    $data['rates']['RB-SPOT'] ??
                    null;
            }
        } catch (\Exception $e) {
            Log::warning('CommodityAPI failed: ' . $e->getMessage());
        }

        return null;
    }
}
