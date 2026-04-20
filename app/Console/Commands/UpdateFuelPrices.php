<?php
// app/Console/Commands/UpdateFuelPrices.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\FuelPrice;
use App\Models\FuelCalibration;
use App\Services\FuelPriceService;
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

        // Fetch global gas price
        $fuelService = new FuelPriceService();
        $globalPrice = $fuelService->fetchGlobalGasPrice();
        $this->info("Global Gas Price: \${$globalPrice}/gallon");

        // Get calibration factor
        $factor = FuelCalibration::getFactor();
        $this->info("Calibration Factor: {$factor}");

        // Calculate prices
        $calculated = $fuelService->calculatePrices($globalPrice, $usdMmkRate, $factor);

        $this->info("Base Import: " . $calculated['inputs']['base_import'] . " MMK/liter");
        $this->info("Calibrated Base: " . $calculated['inputs']['calibrated_base'] . " MMK/liter");

        // Get previous prices for trends
        $previousPrices = FuelPrice::whereDate('created_at', '<', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get()
            ->keyBy('region');

        $saved = 0;
        foreach ($calculated['prices'] as $region => $prices) {
            $prev = $previousPrices->get($region);

            $change92 = $change95 = $changeDiesel = $changePremium = 0;
            if ($prev) {
                $change92 = $this->calcChange($prices['octane_92'], $prev->octane_92);
                $change95 = $this->calcChange($prices['octane_95'], $prev->octane_95);
                $changeDiesel = $this->calcChange($prices['diesel'], $prev->diesel);
                $changePremium = $this->calcChange($prices['premium_diesel'], $prev->premium_diesel ?? $prev->diesel + 150);
            }

            FuelPrice::create([
                'region' => $region,
                'octane_92' => $prices['octane_92'],
                'octane_95' => $prices['octane_95'],
                'diesel' => $prices['diesel'],
                'premium_diesel' => $prices['premium_diesel'],
                'global_usd_reference' => $globalPrice,
                'market_usd_rate' => round($usdMmkRate),
                'change_percent_92' => $change92,
                'change_percent_95' => $change95,
                'change_percent_diesel' => $changeDiesel,
                'change_percent_premium_diesel' => $changePremium,
            ]);

            $saved++;
            $this->info("✓ {$region}: 92={$prices['octane_92']}, 95={$prices['octane_95']}");
        }

        // Update calibration record with current context
        FuelCalibration::first()?->update([
            'global_price_at_calibration' => $globalPrice,
            'usd_mmk_at_calibration' => round($usdMmkRate, 2),
        ]);

        $this->info("✅ Saved {$saved} fuel price records!");

        // Cleanup old records
        $deleted = FuelPrice::where('created_at', '<', Carbon::now()->subMonths(6))->delete();
        $this->info("🗑️ Deleted {$deleted} old records");

        return 0;
    }

    private function calcChange($new, $old): float
    {
        return $old > 0 ? round((($new - $old) / $old) * 100, 2) : 0;
    }
}
