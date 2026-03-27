<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CBMRateService
{
    protected $apiUrl;
    protected $timeout;
    protected $cacheMinutes;

    public function __construct()
    {
        $this->apiUrl = config('cbm.api_url', 'https://forex.cbm.gov.mm/api/latest');
        $this->timeout = (int) config('cbm.timeout', 30);
        $this->cacheMinutes = (int) config('cbm.cache_minutes', 5);
    }

    public function fetchCurrentRates()
    {
        $cacheKey = 'cbm_rates_current';
        $cacheMinutes = (int) $this->cacheMinutes;

        return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get($this->apiUrl);

                if ($response->successful()) {
                    $data = $response->json();

                    // Handle official CBM response
                    if (isset($data['rates']) && is_array($data['rates'])) {
                        $parsedRates = $this->parseRates($data['rates'], $data);
                        Log::info('CBM Rates fetched successfully', [
                            'count' => count($parsedRates),
                            'currencies' => array_keys($parsedRates)
                        ]);
                        return $parsedRates;
                    }
                }

                Log::warning('CBM API returned unsuccessful response', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return $this->getFallbackRates();
            } catch (\Exception $e) {
                Log::error('Failed to fetch CBM rates', [
                    'error' => $e->getMessage()
                ]);
                return $this->getFallbackRates();
            }
        });
    }

    // In CBMRateService.php, update parseRates method
    protected function parseRates($ratesData, $metadata = [])
    {
        $parsedRates = [];

        // Handle official CBM format
        foreach ($ratesData as $currencyCode => $rateValue) {
            // Skip if not a currency (some APIs have metadata)
            if (in_array($currencyCode, ['info', 'description', 'timestamp'])) {
                continue;
            }

            $cbmRate = is_numeric($rateValue) ? (float) $rateValue : (float) str_replace(',', '', $rateValue);

            // Your existing currency lookup and rate calculation
            $currency = \App\Models\Currency::where('code', $currencyCode)->first();

            if ($currency && $currency->use_cbm_auto_fetch) {
                $calculatedRates = $currency->calculateRatesFromCBM($cbmRate);
                $parsedRates[$currencyCode] = [
                    'currency_code' => $currencyCode,
                    'cbm_rate' => $cbmRate,
                    'conversion_factor' => (float) $calculatedRates['cbm_conversion_factor'],
                    'working_rate' => (float) $calculatedRates['working_rate'],
                    'buy_rate' => (float) $calculatedRates['buy_rate'],
                    'sell_rate' => (float) $calculatedRates['sell_rate'],
                    'fetched_at' => now()->toIso8601String(),
                    'api_timestamp' => $metadata['timestamp'] ?? null,
                ];
            }
        }

        return $parsedRates;
    }

    public function fetchRateForCurrency($currencyCode)
    {
        $rates = $this->fetchCurrentRates();
        return $rates[strtoupper($currencyCode)] ?? null;
    }

    protected function getFallbackRates()
    {
        try {
            $lastRates = \App\Models\ExchangeRate::where('is_verified', true)
                ->with('currency')
                ->latest('rate_date')
                ->get()
                ->groupBy(function ($rate) {
                    return $rate->currency->code;
                })
                ->map(function ($rates) {
                    $latest = $rates->first();
                    $factors = is_array($latest->factors) ? $latest->factors : [];

                    return [
                        'currency_code' => $latest->currency->code,
                        'cbm_rate' => (float) ($latest->cbm_rate ?? 0),
                        'conversion_factor' => (float) ($factors['cbm_conversion_factor'] ?? 1),
                        'working_rate' => (float) ($factors['working_rate'] ?? $latest->buy_rate),
                        'buy_rate' => (float) $latest->buy_rate,
                        'sell_rate' => (float) $latest->sell_rate,
                        'buy_spread_applied' => $factors['buy_spread_applied'] ?? null,
                        'sell_spread_applied' => $factors['sell_spread_applied'] ?? null,
                        'spread_type' => $factors['spread_type'] ?? 'percentage',
                        'is_fallback' => true,
                        'fetched_at' => now()->toIso8601String(),
                    ];
                })
                ->toArray();

            return $lastRates;
        } catch (\Exception $e) {
            Log::error('Failed to get fallback rates', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function isAvailable()
    {
        try {
            $response = Http::timeout(5)->get($this->apiUrl);
            return $response->successful() && isset($response->json()['rates']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function clearCache()
    {
        Cache::forget('cbm_rates_current');
        Log::info('CBM rates cache cleared');
    }
}
