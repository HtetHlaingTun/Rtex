<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateFuelPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-fuel-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Use your real market rate from your database or set it here
        $usdMmkRate = 5000;

        // Fetch from the v2 API using the Header for the Key
        $response = Http::withHeaders([
            'x-api-key' => env('COMMODITY_API_KEY')
        ])->get('https://api.commoditypriceapi.com/v2/rates/latest', [
            'symbols' => 'RBOB'
        ]);

        if ($response->successful()) {
            $globalPrice = $response->json()['data']['rates']['RBOB'];

            // Calculation (Calibration for Yangon 4735 MMK)
            $usdPerLiter = $globalPrice / 3.785;
            $baseMmk = $usdPerLiter * $usdMmkRate;
            $price92 = round(($baseMmk * 1.258) / 5) * 5;

            // Save to your FuelPrice model
            \App\Models\FuelPrice::create([
                'octane_92' => $price92,
                'octane_95' => $price92 + 160,
                'diesel'    => $price92 - 120,
                'global_usd_reference' => $globalPrice,
                'market_usd_rate'      => $usdMmkRate
            ]);

            $this->info("Success! 92 Price updated to: $price92 MMK");
        } else {
            $this->error("Failed to fetch: " . $response->status());
        }
    }
}
