<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FuelCalibration;

class FuelPriceService
{
    /**
     * Fetch global gasoline price with dual API fallback
     */
    public function fetchGlobalGasPrice(): ?float
    {
        // Try EIA first (Free, Official, Unlimited)
        $price = $this->fetchFromEIA();
        if ($price) {
            Log::info("Fuel price from EIA: \${$price}/gallon");
            return $price;
        }

        // Fallback to CommodityPriceAPI
        $price = $this->fetchFromCommodityAPI();
        if ($price) {
            Log::info("Fuel price from CommodityAPI: \${$price}/gallon");
            return $price;
        }

        Log::warning("All fuel APIs failed, using fallback price");
        return 3.06;
    }

    /**
     * Fetch from EIA API
     */
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

    /**
     * Fetch from CommodityPriceAPI
     */
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

    /**
     * Calculate all regional prices
     */
    public function calculatePrices(float $globalPrice, float $usdRate, ?float $factor = null): array
    {
        $factor = $factor ?? FuelCalibration::getFactor();

        // Base calculation
        $usdPerLiter = $globalPrice / 3.785;
        $baseImportMmk = $usdPerLiter * $usdRate;
        $calibratedBase = $baseImportMmk * $factor;

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

        $prices = [];
        foreach ($regions as $region => $multiplier) {
            $base = $calibratedBase * $multiplier;
            $prices[$region] = [
                'octane_92' => round($base / 5) * 5,
                'octane_95' => round($base / 5) * 5 + 210,
                'diesel' => round($base / 5) * 5 - 300,
                'premium_diesel' => round($base / 5) * 5 - 150,
            ];
        }

        return [
            'inputs' => [
                'global_price' => round($globalPrice, 2),
                'usd_rate' => round($usdRate, 2),
                'base_import' => round($baseImportMmk),
                'calibrated_base' => round($calibratedBase),
                'calibration_factor' => round($factor, 4),
            ],
            'prices' => $prices,
        ];
    }

    /**
     * Check API health status
     */
    public function checkApiHealth(): array
    {
        return [
            'eia' => $this->checkEiaApi(),
            'commodity' => $this->checkCommodityApi(),
        ];
    }

    private function checkEiaApi(): array
    {
        try {
            $start = microtime(true);
            $response = Http::timeout(5)
                ->get('https://api.eia.gov/v2/petroleum/pri/gnd/data/', [
                    'api_key' => env('EIA_API_KEY'),
                    'length' => 1
                ]);
            $latency = round((microtime(true) - $start) * 1000);

            return [
                'status' => $response->successful(),
                'latency_ms' => $latency,
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    private function checkCommodityApi(): array
    {
        try {
            $start = microtime(true);
            $response = Http::timeout(5)
                ->withHeaders(['x-api-key' => env('COMMODITY_API_KEY')])
                ->get('https://api.commoditypriceapi.com/v2/rates/latest', [
                    'symbols' => 'RB-SPOT'
                ]);
            $latency = round((microtime(true) - $start) * 1000);

            return [
                'status' => $response->successful(),
                'latency_ms' => $latency,
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }
}
