<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Currency;

class CBMRateService
{
    protected $apiUrls;
    protected $timeout;
    protected $cacheMinutes;

    public function __construct()
    {
        $this->apiUrls = [
            'https://forex.cbm.gov.mm/api/latest',
            'https://cbm.gov.mm/api/latest',
            'https://forex.cbm.gov.mm/index.php/api/latest',
            'http://forex.cbm.gov.mm/api/latest',
        ];

        $this->timeout = (int) config('cbm.timeout', 30);
        $this->cacheMinutes = (int) config('cbm.cache_minutes', 5);
    }

    public function fetchCurrentRates()
    {
        $cacheKey = 'cbm_rates_current';

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () {
            foreach ($this->apiUrls as $apiUrl) {
                try {
                    Log::info("Trying CBM API: {$apiUrl}");

                    $response = Http::timeout($this->timeout)
                        ->withHeaders([
                            'Accept' => 'application/json',
                            'User-Agent' => 'Mozilla/5.0',
                        ])
                        ->get($apiUrl);

                    if ($response->successful()) {
                        $data = $response->json();

                        if (isset($data['rates']) && is_array($data['rates'])) {
                            Log::info("CBM API successful from: {$apiUrl}");
                            return $this->parseRates($data['rates'], $data);
                        }

                        if (isset($data['data']) && is_array($data['data'])) {
                            Log::info("CBM API successful (alternative format) from: {$apiUrl}");
                            return $this->parseRates($data['data'], $data);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("CBM API error for {$apiUrl}: " . $e->getMessage());
                    continue;
                }
            }

            Log::warning("All CBM APIs failed, using fallback rates");
            return $this->getFallbackRates();
        });
    }

    protected function parseRates($ratesData, $metadata = [])
    {
        $parsedRates = [];

        foreach ($ratesData as $currencyCode => $rateValue) {
            if (in_array($currencyCode, ['info', 'description', 'timestamp', 'date'])) {
                continue;
            }

            $cbmRate = is_numeric($rateValue) ? (float) $rateValue : (float) str_replace(',', '', $rateValue);

            // Store for reference - these rates are NOT used in bank_avg mode
            $parsedRates[$currencyCode] = [
                'currency_code' => $currencyCode,
                'cbm_rate' => $cbmRate,
                'working_rate' => $cbmRate,
                'buy_rate' => round($cbmRate * 0.995, 2),
                'sell_rate' => round($cbmRate * 1.005, 2),
                'fetched_at' => now()->toIso8601String(),
                'api_timestamp' => $metadata['timestamp'] ?? null,
                'is_reference' => true,
            ];

            // Only update the cbm_rate field in currencies table (for reference)
            $currency = Currency::where('code', $currencyCode)->first();
            if ($currency && $cbmRate > 0) {
                $currency->cbm_rate = $cbmRate;
                $currency->save();
                Log::info("Updated reference CBM rate for {$currencyCode}: {$cbmRate}");
            }
        }

        return $parsedRates;
    }

    protected function getFallbackRates()
    {
        // Get stored CBM rates from currency table
        try {
            $currencies = Currency::where('cbm_rate', '>', 0)->get();

            if ($currencies->count() > 0) {
                $rates = [];
                foreach ($currencies as $currency) {
                    $rates[$currency->code] = [
                        'currency_code' => $currency->code,
                        'cbm_rate' => (float) $currency->cbm_rate,
                        'working_rate' => (float) $currency->cbm_rate,
                        'buy_rate' => round((float) $currency->cbm_rate * 0.995, 2),
                        'sell_rate' => round((float) $currency->cbm_rate * 1.005, 2),
                        'is_fallback' => true,
                        'is_reference' => true,
                        'fetched_at' => now()->toIso8601String(),
                    ];
                }

                if (!empty($rates)) {
                    Log::info("Using stored CBM rates as fallback");
                    return $rates;
                }
            }
        } catch (\Exception $e) {
            Log::warning("Could not get stored CBM rates: " . $e->getMessage());
        }

        // Ultimate fallback - hardcoded for reference only
        Log::warning("Using hardcoded fallback CBM rates (reference only)");
        return [
            'USD' => ['currency_code' => 'USD', 'cbm_rate' => 2100, 'is_reference' => true],
            'SGD' => ['currency_code' => 'SGD', 'cbm_rate' => 1628.73, 'is_reference' => true],
            'EUR' => ['currency_code' => 'EUR', 'cbm_rate' => 2414.37, 'is_reference' => true],
            'THB' => ['currency_code' => 'THB', 'cbm_rate' => 63.90, 'is_reference' => true],
        ];
    }

    public function fetchRateForCurrency($currencyCode)
    {
        $rates = $this->fetchCurrentRates();
        return $rates[strtoupper($currencyCode)] ?? null;
    }

    public function isAvailable()
    {
        foreach ($this->apiUrls as $apiUrl) {
            try {
                $response = Http::timeout(5)->get($apiUrl);
                if ($response->successful() && isset($response->json()['rates'])) {
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return false;
    }

    public function clearCache()
    {
        Cache::forget('cbm_rates_current');
        Log::info('CBM rates cache cleared');
    }
}
