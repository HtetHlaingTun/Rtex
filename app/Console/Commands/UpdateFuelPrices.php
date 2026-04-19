<?php
// app/Console/Commands/UpdateFuelPrices.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\FuelPrice;
use Carbon\Carbon;

class UpdateFuelPrices extends Command
{
    protected $signature = 'fuel:prices-update';
    protected $description = 'Update fuel prices from global market data';

    public function handle()
    {
        $this->info('🔄 Fetching fuel prices...');

        // Get USD rate
        $usd = DB::table('currencies')->where('code', 'USD')->first();

        if (!$usd) {
            $this->error('USD currency not found!');
            return 1;
        }

        $usdMmkRate = $usd->avg_bank_rate * (1 + ($usd->bank_markup_percentage / 100));
        $this->info("USD/MMK Rate: " . round($usdMmkRate, 2));

        // Fetch global gas price with fallback
        $globalPrice = $this->fetchGlobalGasPrice();

        if (!$globalPrice) {
            $this->warn('Using fallback gas price');
            $globalPrice = 3.05; // Fallback value
        }

        $this->info("Global Gas Price: \${$globalPrice}/gallon");

        // Calculate for all regions
        $regions = [
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

        $saved = 0;

        foreach ($regions as $region => $multiplier) {
            $basePrice = round(($baseMmk * $multiplier) / 5) * 5;

            $octane92 = $basePrice;
            $octane95 = $basePrice + 210;
            $diesel = $basePrice - 300;

            // Calculate trends
            $prev = $previousPrices->get($region);
            $change92 = 0;
            $change95 = 0;
            $changeDiesel = 0;

            if ($prev) {
                $change92 = round((($octane92 - $prev->octane_92) / $prev->octane_92) * 100, 2);
                $change95 = round((($octane95 - $prev->octane_95) / $prev->octane_95) * 100, 2);
                $changeDiesel = round((($diesel - $prev->diesel) / $prev->diesel) * 100, 2);
            }

            FuelPrice::create([
                'region' => $region,
                'octane_92' => $octane92,
                'octane_95' => $octane95,
                'diesel' => $diesel,
                'global_usd_reference' => $globalPrice,
                'market_usd_rate' => round($usdMmkRate),
                'change_percent_92' => $change92,
                'change_percent_95' => $change95,
                'change_percent_diesel' => $changeDiesel,
            ]);

            $saved++;
            $this->info("✓ {$region}: 92={$octane92}, 95={$octane95}, Diesel={$diesel}");
        }

        $this->info("✅ Saved {$saved} fuel price records!");

        // Clean up old records (keep last 6 months)
        $cutoffDate = Carbon::now()->subMonths(6);
        $deleted = FuelPrice::where('created_at', '<', $cutoffDate)->delete();
        $this->info("🗑️ Deleted {$deleted} old records");

        return 0;
    }

    private function fetchGlobalGasPrice()
    {
        try {
            $apiKey = env('COMMODITY_API_KEY');

            if (!$apiKey) {
                $this->warn('COMMODITY_API_KEY not set in .env');
                return null;
            }

            $response = Http::timeout(10)
                ->retry(3, 100) // Retry 3 times with 100ms delay
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

            $this->warn('API returned status: ' . $response->status());
            return null;
        } catch (\Exception $e) {
            $this->warn('Failed to fetch global gas price: ' . $e->getMessage());
            return null;
        }
    }
}
