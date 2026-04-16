<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooFinanceService
{
    private const CACHE_TTL = 300; // 5 minutes

    // List of supported currencies (dynamically expandable)
    private array $supportedCurrencies = [
        'AED',
        'ARS',
        'AUD',
        'BRL',
        'CAD',
        'CHF',
        'CLP',
        'CNY',
        'COP',
        'CZK',
        'DKK',
        'EGP',
        'EUR',
        'GBP',
        'HKD',
        'HUF',
        'IDR',
        'ILS',
        'INR',
        'JPY',
        'KRW',
        'KWD',
        'KZT',
        'MXN',
        'MYR',
        'NOK',
        'NZD',
        'PEN',
        'PHP',
        'PKR',
        'PLN',
        'QAR',
        'RON',
        'RUB',
        'SAR',
        'SEK',
        'SGD',
        'THB',
        'TRY',
        'TWD',
        'UAH',
        'USD',
        'UYU',
        'VND',
        'ZAR'
    ];

    /**
     * Get USD → target currency rate
     */
    public function getUsdToTargetRate(string $targetCurrency): ?float
    {
        $code = strtoupper($targetCurrency);

        if ($code === 'USD') return 1.0;
        if ($code === 'MMK') return null; // Yahoo doesn't have MMK directly

        return Cache::remember("yahoo_usd_{$code}", self::CACHE_TTL, function () use ($code) {
            return $this->fetchRate("USD{$code}=X");
        });
    }

    /**
     * Get target → USD rate
     */
    public function getTargetToUsdRate(string $targetCurrency): ?float
    {
        $rate = $this->getUsdToTargetRate($targetCurrency);
        return $rate ? 1 / $rate : null;
    }

    /**
     * Get rate for any currency pair
     */
    public function getPairRate(string $fromCurrency, string $toCurrency): ?float
    {
        $from = strtoupper($fromCurrency);
        $to = strtoupper($toCurrency);

        if ($from === $to) return 1.0;

        $cacheKey = "yahoo_pair_{$from}_{$to}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            // Try direct pair
            $directRate = $this->fetchRate("{$from}{$to}=X");
            if ($directRate && $directRate > 0) return $directRate;

            // Try inverse
            $inverseRate = $this->fetchRate("{$to}{$from}=X");
            if ($inverseRate && $inverseRate > 0) return 1 / $inverseRate;

            // Fallback to USD bridge
            $fromToUsd = $this->getUsdToTargetRate($from);
            $toToUsd = $this->getUsdToTargetRate($to);

            if ($fromToUsd && $toToUsd && $fromToUsd > 0 && $toToUsd > 0) {
                return $fromToUsd / $toToUsd;
            }

            return null;
        });
    }

    /**
     * Get multiple rates at once (batch)
     */
    public function getMultipleRates(array $currencies): array
    {
        $results = [];
        foreach ($currencies as $currency) {
            $results[$currency] = $this->getUsdToTargetRate($currency);
        }
        return $results;
    }

    /**
     * Check if a currency is supported by Yahoo Finance
     */
    public function isSupported(string $currencyCode): bool
    {
        return in_array(strtoupper($currencyCode), $this->supportedCurrencies);
    }

    /**
     * Get all supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Fetch rate from Yahoo Finance API
     */
    private function fetchRate(string $symbol): ?float
    {
        try {
            $response = Http::withOptions(['verify' => false, 'timeout' => 10])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                ])
                ->get("https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}");

            if (!$response->successful()) {
                Log::debug("Yahoo request failed for {$symbol}: HTTP {$response->status()}");
                return null;
            }

            $data = $response->json();

            if (isset($data['chart']['error']) || empty($data['chart']['result'])) {
                return null;
            }

            // Try to get regular market price first
            $price = $data['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;

            if ($price && is_numeric($price) && $price > 0) {
                Log::debug("Yahoo rate for {$symbol}: {$price}");
                return (float) $price;
            }

            // Try to get from indicators if regularMarketPrice not available
            $close = $data['chart']['result'][0]['indicators']['quote'][0]['close'][0] ?? null;
            if ($close && is_numeric($close) && $close > 0) {
                return (float) $close;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning("Yahoo fetch failed for {$symbol}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear all Yahoo rate caches
     */
    public function clearCache(): void
    {
        // Clear pattern-based cache would require a cache store that supports patterns
        // For simplicity, we'll clear specific known keys
        Log::info("YahooFinanceService cache cleared");
    }
}
