<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Currency;

/**
 * Dynamic multi-source rate fetcher
 * No hardcoded rates - everything is derived from live sources
 */
class MultiSourceRateService
{
    private const CACHE_TTL = 300; // 5 minutes

    private BankScraperService $bankScraper;
    private CBMRateService $cbmService;
    private YahooFinanceService $yahooService;

    // Source reliability weights (can be adjusted)
    private array $sourceWeights = [
        'bank' => 0.60,      // Banks have real market rates
        'yahoo' => 0.30,     // Yahoo provides global trends
        'cbm' => 0.10,       // CBM is reference only
    ];

    public function __construct(
        BankScraperService $bankScraper,
        CBMRateService $cbmService,
        YahooFinanceService $yahooService
    ) {
        $this->bankScraper = $bankScraper;
        $this->cbmService = $cbmService;
        $this->yahooService = $yahooService;
    }

    /**
     * Get the best possible rate for a currency
     * No hardcoded fallbacks - only real data
     */
    public function getOptimalRate(string $currencyCode): array
    {
        $cacheKey = "optimal_rate_" . strtoupper($currencyCode);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($currencyCode) {
            return $this->calculateOptimalRate(strtoupper($currencyCode));
        });
    }

    /**
     * Calculate optimal rate by combining all available sources
     */
    private function calculateOptimalRate(string $currencyCode): array
    {
        $sources = $this->fetchAllSources($currencyCode);
        $rates = [];
        $totalPossibleWeight = 0;

        // 1. Process direct MMK rates from banks (most reliable)
        if ($this->hasDirectBankRates($currencyCode, $sources['bank_rates'] ?? [])) {
            $bankRate = $this->calculateBankAverage($sources['bank_rates'][$currencyCode] ?? []);
            if ($bankRate > 0) {
                $bankConfidence = $this->calculateBankConfidence($sources['bank_rates'][$currencyCode] ?? []);
                $weight = $this->sourceWeights['bank'] * $bankConfidence;
                $rates[] = [
                    'value' => $bankRate,
                    'source' => 'bank',
                    'weight' => $weight,
                    'confidence' => $bankConfidence,
                ];
                $totalPossibleWeight += $this->sourceWeights['bank'];
            }
        }

        // 2. Derive from USD via Yahoo (for all currencies including USD itself for cross-check)
        $derivedRate = $this->deriveFromUsd($currencyCode, $sources);
        if ($derivedRate > 0) {
            $yahooConfidence = $this->getYahooConfidence($currencyCode, $sources);
            $weight = $this->sourceWeights['yahoo'] * $yahooConfidence;
            $rates[] = [
                'value' => $derivedRate,
                'source' => 'yahoo_derived',
                'weight' => $weight,
                'confidence' => $yahooConfidence,
            ];
            $totalPossibleWeight += $this->sourceWeights['yahoo'];
        }

        // 3. Use CBM as reference (least weight)
        $cbmRate = $this->getCbmRate($currencyCode, $sources);
        if ($cbmRate > 0) {
            $weight = $this->sourceWeights['cbm'] * 0.5; // CBM always lower confidence
            $rates[] = [
                'value' => $cbmRate,
                'source' => 'cbm',
                'weight' => $weight,
                'confidence' => 0.5,
            ];
            $totalPossibleWeight += $this->sourceWeights['cbm'];
        }

        // Calculate weighted average
        if (empty($rates)) {
            return $this->emergencyFallback($currencyCode, $sources);
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($rates as $rate) {
            $totalWeight += $rate['weight'];
            $weightedSum += $rate['value'] * $rate['weight'];
        }

        $optimalRate = $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
        $actualConfidence = $totalPossibleWeight > 0 ? $totalWeight / $totalPossibleWeight : 0;

        // Log the calculation for debugging
        Log::info("Optimal rate for {$currencyCode}:", [
            'rate' => round($optimalRate, 4),
            'confidence' => round($actualConfidence, 4),
            'sources' => array_map(fn($r) => $r['source'], $rates),
            'breakdown' => array_map(fn($r) => [
                'source' => $r['source'],
                'value' => round($r['value'], 4),
                'weight' => round($r['weight'], 4),
            ], $rates),
        ]);

        return [
            'rate' => round($optimalRate, 4),
            'confidence' => round($actualConfidence, 4),
            'sources_used' => array_map(fn($r) => $r['source'], $rates),
            'breakdown' => $rates,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Fetch from ALL available sources
     */
    private function fetchAllSources(string $currencyCode): array
    {
        return [
            'bank_rates' => $this->fetchBankRates($currencyCode),
            'yahoo_rates' => $this->fetchYahooRates($currencyCode),
            'cbm_rates' => $this->fetchCbmRates(),
        ];
    }

    /**
     * Fetch from all banks dynamically
     */
    private function fetchBankRates(string $currencyCode): array
    {
        try {
            $allBanks = $this->bankScraper->fetchAll($currencyCode);

            $validRates = [];
            foreach ($allBanks as $bank => $rates) {
                if (($rates['buy'] ?? 0) > 0 || ($rates['sell'] ?? 0) > 0) {
                    // Use mid rate
                    $midRate = ($rates['buy'] + $rates['sell']) / 2;
                    if ($midRate > 0) {
                        $validRates[$bank] = $midRate;
                    }
                }
            }

            if (!empty($validRates)) {
                Log::debug("Bank rates for {$currencyCode}:", $validRates);
            }

            return [$currencyCode => $validRates];
        } catch (\Exception $e) {
            Log::warning("Bank fetch failed for {$currencyCode}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a currency has direct MMK rates from banks
     */
    private function hasDirectBankRates(string $currencyCode, array $bankRates): bool
    {
        if (empty($bankRates)) return false;
        return isset($bankRates[$currencyCode]) && count($bankRates[$currencyCode]) > 0;
    }

    /**
     * Calculate weighted average from bank rates
     */
    private function calculateBankAverage(array $bankRates): float
    {
        if (empty($bankRates)) return 0;

        // Bank reliability weights (can be adjusted based on your experience)
        $bankWeights = [
            'kbz' => 1.0,
            'cb' => 1.0,
            'yoma' => 0.9,
            'aya' => 0.9,
        ];

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($bankRates as $bank => $rate) {
            $weight = $bankWeights[$bank] ?? 0.8;
            $totalWeight += $weight;
            $weightedSum += $rate * $weight;
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Calculate confidence based on number of banks providing rates
     */
    private function calculateBankConfidence(array $bankRates): float
    {
        $count = count($bankRates);
        if ($count >= 3) return 0.95;
        if ($count >= 2) return 0.85;
        if ($count >= 1) return 0.70;
        return 0;
    }

    /**
     * Derive MMK rate from USD using Yahoo's global FX rates
     * This is the key method that makes the system dynamic
     */
    private function deriveFromUsd(string $targetCurrency, array $sources): float
    {
        // First, get the best USD/MMK rate
        $usdMmkRate = $this->getBestUsdMmkRate($sources);

        if ($usdMmkRate <= 0) {
            Log::debug("Cannot derive {$targetCurrency}: No USD/MMK rate available");
            return 0;
        }

        // For USD itself, return the USD/MMK rate
        if ($targetCurrency === 'USD') {
            return $usdMmkRate;
        }

        // Get USD to target from Yahoo
        $usdToTarget = $this->yahooService->getUsdToTargetRate($targetCurrency);

        if (!$usdToTarget || $usdToTarget <= 0) {
            Log::debug("Cannot derive {$targetCurrency}: No USD/{$targetCurrency} rate from Yahoo");
            return 0;
        }

        // Derive: Target/MMK = (USD/MMK) / (USD/Target)
        $derivedRate = $usdMmkRate / $usdToTarget;

        Log::info("Derived {$targetCurrency}/MMK: USD/MMK={$usdMmkRate}, USD/{$targetCurrency}={$usdToTarget}, Result={$derivedRate}");

        return $derivedRate;
    }

    /**
     * Get the best USD/MMK rate from all available sources
     * This is the foundation for all cross rates
     */
    private function getBestUsdMmkRate(array $sources): float
    {
        $usdRates = [];

        // Check bank rates for USD
        $bankUsdRates = $sources['bank_rates']['USD'] ?? [];
        if (!empty($bankUsdRates)) {
            $bankAvg = $this->calculateBankAverage($bankUsdRates);
            if ($bankAvg > 0) {
                $usdRates[] = [
                    'value' => $bankAvg,
                    'source' => 'bank',
                    'weight' => 1.0,
                ];
            }
        }

        // Check if Yahoo can give us USD/MMK (usually not, but try)
        $yahooUsdMmk = $sources['yahoo_rates']['USD'] ?? null;
        if ($yahooUsdMmk && $yahooUsdMmk > 0) {
            $usdRates[] = [
                'value' => $yahooUsdMmk,
                'source' => 'yahoo',
                'weight' => 0.5,
            ];
        }

        // Check CBM reference for USD
        $cbmUsdRate = $sources['cbm_rates']['USD']['cbm_rate'] ?? 0;
        if ($cbmUsdRate > 0) {
            $usdRates[] = [
                'value' => $cbmUsdRate,
                'source' => 'cbm',
                'weight' => 0.3,
            ];
        }

        if (empty($usdRates)) {
            return 0;
        }

        // Calculate weighted average
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($usdRates as $rate) {
            $totalWeight += $rate['weight'];
            $weightedSum += $rate['value'] * $rate['weight'];
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Fetch Yahoo rates (only USD pairs)
     */
    private function fetchYahooRates(string $currencyCode): array
    {
        try {
            if ($currencyCode === 'MMK') {
                return [];
            }

            $rate = $this->yahooService->getUsdToTargetRate($currencyCode);
            if ($rate && $rate > 0) {
                return [$currencyCode => $rate];
            }

            return [];
        } catch (\Exception $e) {
            Log::warning("Yahoo fetch failed for {$currencyCode}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Yahoo confidence based on rate freshness
     */
    private function getYahooConfidence(string $currencyCode, array $sources): float
    {
        $rate = $sources['yahoo_rates'][$currencyCode] ?? null;
        if ($rate && $rate > 0) {
            // Yahoo rates are generally reliable
            return 0.80;
        }
        return 0;
    }

    /**
     * Fetch CBM reference rates
     */
    private function fetchCbmRates(): array
    {
        try {
            return $this->cbmService->fetchCurrentRates();
        } catch (\Exception $e) {
            Log::warning("CBM fetch failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CBM rate for a currency
     */
    private function getCbmRate(string $currencyCode, array $sources): float
    {
        return $sources['cbm_rates'][$currencyCode]['cbm_rate'] ?? 0;
    }

    /**
     * Emergency fallback when no direct data is available
     * Uses any available cross rate from any currency
     */
    private function emergencyFallback(string $currencyCode, array $sources): array
    {
        Log::warning("EMERGENCY FALLBACK for {$currencyCode} - No direct sources available");

        // Try to derive from any currency that has bank rates
        $availableCurrencies = array_keys($sources['bank_rates'] ?? []);

        foreach ($availableCurrencies as $bridgeCurrency) {
            if ($bridgeCurrency === $currencyCode) continue;

            // Get bank rate for bridge currency
            $bridgeBankRates = $sources['bank_rates'][$bridgeCurrency] ?? [];
            if (empty($bridgeBankRates)) continue;

            $bridgeMmkRate = $this->calculateBankAverage($bridgeBankRates);
            if ($bridgeMmkRate <= 0) continue;

            // Get Yahoo cross rate between bridge and target
            $bridgeToTarget = $this->getCrossRateViaYahoo($bridgeCurrency, $currencyCode);
            if ($bridgeToTarget > 0) {
                $derivedRate = $bridgeMmkRate * $bridgeToTarget;

                Log::info("Emergency fallback for {$currencyCode}: via {$bridgeCurrency} = {$derivedRate}");

                return [
                    'rate' => round($derivedRate, 4),
                    'confidence' => 0.25,
                    'sources_used' => ['emergency_cross'],
                    'breakdown' => [[
                        'value' => $derivedRate,
                        'source' => "emergency: {$bridgeCurrency}→{$currencyCode}",
                        'weight' => 0.5,
                        'confidence' => 0.25,
                    ]],
                    'timestamp' => now()->toIso8601String(),
                    'is_emergency' => true,
                ];
            }
        }

        // Ultimate fallback - return zero with error
        Log::error("No rate sources available for {$currencyCode}");

        return [
            'rate' => 0,
            'confidence' => 0,
            'sources_used' => ['none'],
            'breakdown' => [],
            'timestamp' => now()->toIso8601String(),
            'error' => 'No rate sources available',
        ];
    }

    /**
     * Get cross rate between two currencies via Yahoo
     */
    private function getCrossRateViaYahoo(string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) return 1.0;

        // Get both rates via USD
        $fromToUsd = $this->yahooService->getUsdToTargetRate($fromCurrency);
        $toToUsd = $this->yahooService->getUsdToTargetRate($toCurrency);

        if ($fromToUsd > 0 && $toToUsd > 0) {
            // From/To = (From/USD) / (To/USD)
            return $fromToUsd / $toToUsd;
        }

        return 0;
    }

    /**
     * Clear cache for a specific currency or all
     */
    public function clearCache(?string $currencyCode = null): void
    {
        if ($currencyCode) {
            Cache::forget("optimal_rate_" . strtoupper($currencyCode));
            Log::info("Cleared cache for {$currencyCode}");
        } else {
            // Clear all optimal rate caches
            $keys = Cache::get('optimal_rate_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget('optimal_rate_keys');
            Log::info("Cleared all optimal rate caches");
        }
    }
}
