<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BankAggregatorService
{
    private BankScraperService $scraper;
    private YahooFinanceService $yahoo;
    private CBMRateService $cbmService;

    // Realistic market ranges for validation
    private const VALID_RANGES = [
        'USD' => ['min' => 3500, 'max' => 5000],
        'SGD' => ['min' => 2500, 'max' => 3800],
        'EUR' => ['min' => 4000, 'max' => 5500],
        'THB' => ['min' => 110, 'max' => 150],
        'MYR' => ['min' => 700, 'max' => 1000],
        'CNY' => ['min' => 450, 'max' => 650],
        'JPY' => ['min' => 20, 'max' => 35],
        'KRW' => ['min' => 2.5, 'max' => 5],
        'HKD' => ['min' => 450, 'max' => 650],
        'NZD' => ['min' => 2200, 'max' => 3500],
        'AUD' => ['min' => 2400, 'max' => 3600],
        'CAD' => ['min' => 2700, 'max' => 3900],
        'CHF' => ['min' => 4000, 'max' => 5500],
        'INR' => ['min' => 45, 'max' => 65],
    ];

    public function __construct(
        CBMRateService $cbmService,
        BankScraperService $scraper,
        YahooFinanceService $yahoo
    ) {
        $this->cbmService = $cbmService;
        $this->scraper = $scraper;
        $this->yahoo = $yahoo;
    }



    /**
     * Main sync method - called by schedule
     */
    /**
     * Main sync method - called by schedule
     */
    /**
     * Main sync method - called by schedule
     */
    /**
     * Main sync method - called by schedule
     */
    public function syncRates(): void
    {
        Log::info("=== SYNC RATES START ===");

        // REMOVE THIS LINE - DON'T TRUNCATE!
        // \App\Models\ExchangeRate::truncate();

        // Step 1: Get USD bank rate (raw bank average)
        $usdBankRate = $this->getDirectBankRate('USD');

        if ($usdBankRate <= 0) {
            Log::error("Cannot get USD bank rate");
            return;
        }

        Log::info("USD Bank Rate (raw): " . $usdBankRate);

        // Step 2: Get USD currency settings
        $usdCurrency = \App\Models\Currency::where('code', 'USD')->first();
        $usdMarkup = ($usdCurrency->bank_markup_percentage ?? 15) / 100;
        $usdBuySpread = ($usdCurrency->buy_spread_percentage ?? 2) / 100;
        $usdSellSpread = ($usdCurrency->sell_spread_percentage ?? 0.5) / 100;

        // Step 3: Calculate USD final rates
        $usdBaseWithMarkup = $usdBankRate * (1 + $usdMarkup);
        $usdBuy = round($usdBaseWithMarkup * (1 - $usdBuySpread), 2);
        $usdSell = round($usdBaseWithMarkup * (1 + $usdSellSpread), 2);
        $usdMid = ($usdBuy + $usdSell) / 2;

        Log::info("USD Calculated:", [
            'bank_rate' => $usdBankRate,
            'markup' => $usdMarkup * 100 . '%',
            'buy' => $usdBuy,
            'sell' => $usdSell,
            'mid' => $usdMid
        ]);

        // Step 4: Save USD rate (ADD, don't replace)
        \App\Models\ExchangeRate::create([
            'currency_id' => $usdCurrency->id,
            'rate_date' => now(),
            'buy_rate' => $usdBuy,
            'sell_rate' => $usdSell,
            'cbm_rate' => 0,
            'source_name' => 'Bank Average (Live)',
            'status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // Step 5: Get USD mid from database (or use calculated)
        $usdMid = ($usdBuy + $usdSell) / 2;

        // Step 6: Process all other currencies
        $currencies = \App\Models\Currency::where('is_active', true)
            ->where('code', '!=', 'USD')
            ->get();

        foreach ($currencies as $currency) {
            $this->saveCrossRate($currency, $usdMid);
        }

        Log::info("=== SYNC RATES COMPLETED ===");
    }

    /**
     * Save cross rate for a currency based on USD mid rate
     */
    private function saveCrossRate($currency, float $usdMid): void
    {
        $code = $currency->code;

        // Get USD to target from Yahoo
        $usdToTarget = $this->yahoo->getUsdToTargetRate($code);

        if (!$usdToTarget || $usdToTarget <= 0) {
            Log::warning("No Yahoo rate for {$code}, skipping");
            return;
        }

        // Calculate base cross rate
        $baseRate = $usdMid / $usdToTarget;

        // Apply markup from currency settings
        $markup = ($currency->bank_markup_percentage ?? 1.0) / 100;
        $withMarkup = $baseRate * (1 + $markup);

        // Apply buy/sell spreads
        $buySpread = ($currency->buy_spread_percentage ?? 0.5) / 100;
        $sellSpread = ($currency->sell_spread_percentage ?? 0.5) / 100;

        $buyRate = round($withMarkup * (1 - $buySpread), 2);
        $sellRate = round($withMarkup * (1 + $sellSpread), 2);

        // DON'T DELETE - just add new record
        \App\Models\ExchangeRate::create([
            'currency_id' => $currency->id,
            'rate_date' => now(),
            'buy_rate' => $buyRate,
            'sell_rate' => $sellRate,
            'cbm_rate' => 0,
            'source_name' => "Cross Rate (USD Bridge)",
            'status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
            'factors' => [
                'usd_mid' => $usdMid,
                'usd_to_target' => $usdToTarget,
                'markup_percent' => $currency->bank_markup_percentage,
                'buy_spread_percent' => $currency->buy_spread_percentage,
                'sell_spread_percent' => $currency->sell_spread_percentage,
            ],
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        Log::info("✓ Saved {$code}: Buy={$buyRate}, Sell={$sellRate}");
    }

    /**
     * Save USD rate to database
     */
    private function saveUsdRate(Currency $currency, float $buyRate, float $sellRate, float $bankRate): void
    {
        ExchangeRate::create([
            'currency_id' => $currency->id,
            'rate_date' => now(),
            'buy_rate' => $buyRate,
            'sell_rate' => $sellRate,
            'cbm_rate' => 0,
            'previous_buy_rate' => 0,
            'previous_sell_rate' => 0,
            'change_percentage' => 0,
            'market_trend' => 'stable',
            'source_name' => "Bank Average (Live)",
            'status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
            'factors' => [
                'bank_rate' => $bankRate,
                'markup_percent' => $currency->bank_markup_percentage,
                'buy_spread_percent' => $currency->buy_spread_percentage,
                'sell_spread_percent' => $currency->sell_spread_percentage,
            ],
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        Log::info("✓ Saved USD: Buy={$buyRate}, Sell={$sellRate}");
    }

    /**
     * Sync cross currencies using USD as base
     */
    private function syncCrossRate(Currency $currency, float $usdMmkMidRate): bool
    {
        $code = $currency->code;

        try {
            // Get USD to target from Yahoo
            $usdToTarget = $this->yahoo->getUsdToTargetRate($code);

            if (!$usdToTarget || $usdToTarget <= 0) {
                Log::warning("No Yahoo rate for {$code}, skipping");
                return false;
            }

            // Step 1: Calculate base cross rate from USD
            $baseCrossRate = $usdMmkMidRate / $usdToTarget;

            // Step 2: Apply bank markup from currency settings
            $markupPercent = $currency->bank_markup_percentage ?? 1.0;
            $baseWithMarkup = $baseCrossRate * (1 + ($markupPercent / 100));

            // Step 3: Apply buy/sell spreads
            $buySpreadPercent = $currency->buy_spread_percentage ?? 0.5;
            $sellSpreadPercent = $currency->sell_spread_percentage ?? 0.5;

            $buyRate = $baseWithMarkup * (1 - ($buySpreadPercent / 100));
            $sellRate = $baseWithMarkup * (1 + ($sellSpreadPercent / 100));

            $decimal = $currency->decimal_places ?? 2;
            $buyRate = round($buyRate, $decimal);
            $sellRate = round($sellRate, $decimal);

            // Log the calculation for debugging
            Log::info("{$code} calculation:", [
                'usd_mmk_mid' => $usdMmkMidRate,
                'usd_to_target' => $usdToTarget,
                'base_cross' => round($baseCrossRate, 2),
                'markup_percent' => $markupPercent,
                'with_markup' => round($baseWithMarkup, 2),
                'buy_spread' => $buySpreadPercent,
                'sell_spread' => $sellSpreadPercent,
                'final_buy' => $buyRate,
                'final_sell' => $sellRate,
            ]);

            // Save to database
            ExchangeRate::create([
                'currency_id' => $currency->id,
                'rate_date' => now(),
                'buy_rate' => $buyRate,
                'sell_rate' => $sellRate,
                'cbm_rate' => 0,
                'previous_buy_rate' => 0,
                'previous_sell_rate' => 0,
                'change_percentage' => 0,
                'market_trend' => 'stable',
                'source_name' => "Cross Rate (USD Bridge)",
                'status' => 'verified',
                'is_verified' => true,
                'verified_at' => now(),
                'factors' => [
                    'usd_mmk_mid' => $usdMmkMidRate,
                    'usd_to_target' => $usdToTarget,
                    'markup_percent' => $markupPercent,
                    'buy_spread_percent' => $buySpreadPercent,
                    'sell_spread_percent' => $sellSpreadPercent,
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            Log::info("✓ Saved {$code}: Buy={$buyRate}, Sell={$sellRate}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to sync {$code}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get direct bank rate for a currency (REAL MARKET RATE)
     */
    private function getDirectBankRate(string $code): float
    {
        try {
            $rawRates = $this->scraper->fetchAll($code);

            $validRates = [];

            foreach ($rawRates as $bank => $rates) {
                // Extract the correct values - the scraper returns ['buy' => x, 'sell' => y]
                $buy = is_array($rates) ? ($rates['buy'] ?? 0) : 0;
                $sell = is_array($rates) ? ($rates['sell'] ?? 0) : 0;

                if ($buy > 0 && $sell > 0) {
                    $mid = ($buy + $sell) / 2;

                    // Validate against realistic range
                    $range = self::VALID_RANGES[$code] ?? ['min' => 0, 'max' => PHP_INT_MAX];
                    if ($mid >= $range['min'] && $mid <= $range['max']) {
                        $validRates[] = $mid;
                        Log::debug("Valid {$code} rate from {$bank}: {$mid}");
                    } else {
                        Log::warning("Filtered out {$bank} rate for {$code}: {$mid} (range: {$range['min']}-{$range['max']})");
                    }
                }
            }

            if (empty($validRates)) {
                Log::warning("No valid bank rates for {$code}");
                return 0;
            }

            // Return average of all valid rates
            $average = array_sum($validRates) / count($validRates);
            Log::info("Direct bank rate for {$code}: {$average} (from " . count($validRates) . " banks)");
            return round($average, 2);
        } catch (\Exception $e) {
            Log::error("Error getting bank rate for {$code}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sync a single currency using market USD rate
     */
    private function syncCurrencyRate(Currency $currency, float $usdMarketRate): bool
    {
        $code = $currency->code;

        try {
            // For USD, use direct bank rate
            if ($code === 'USD') {
                $midRate = $usdMarketRate;
                $source = "Direct Bank Rate";
            } else {
                // For other currencies, derive from USD using Yahoo
                $usdToTarget = $this->yahoo->getUsdToTargetRate($code);

                if (!$usdToTarget || $usdToTarget <= 0) {
                    Log::warning("No Yahoo rate for {$code}, trying direct bank rate...");
                    $directRate = $this->getDirectBankRate($code);
                    if ($directRate > 0) {
                        $midRate = $directRate;
                        $source = "Direct Bank Rate";
                    } else {
                        Log::warning("No rate available for {$code}");
                        return false;
                    }
                } else {
                    // Derive: Target/MMK = USD/MMK ÷ USD/Target
                    $midRate = $usdMarketRate / $usdToTarget;
                    $source = "Cross Rate (via USD/Yahoo)";

                    Log::info("Derived {$code}: USD/MMK={$usdMarketRate}, USD/{$code}={$usdToTarget}, Result={$midRate}");
                }
            }

            // Apply markup from currency settings
            $markup = ($currency->bank_markup_percentage ?? 1.0) / 100;
            $midRateWithMarkup = $midRate * (1 + $markup);

            // Apply buy/sell spreads
            $buySpread = ($currency->buy_spread_percentage ?? 0.5) / 100;
            $sellSpread = ($currency->sell_spread_percentage ?? 0.5) / 100;

            $buyRate = $midRateWithMarkup * (1 - $buySpread);
            $sellRate = $midRateWithMarkup * (1 + $sellSpread);

            $decimal = $currency->decimal_places ?? 2;
            $buyRate = round($buyRate, $decimal);
            $sellRate = round($sellRate, $decimal);

            // Validate rates are reasonable
            if ($buyRate <= 0 || $sellRate <= 0 || $buyRate > 100000) {
                Log::error("Invalid rates for {$code}: Buy={$buyRate}, Sell={$sellRate}");
                return false;
            }

            // Get previous rate for trend
            $previous = ExchangeRate::where('currency_id', $currency->id)
                ->latest('rate_date')
                ->first();

            $changePct = 0;
            $trend = 'stable';

            if ($previous && $previous->buy_rate > 0) {
                $changePct = round(($buyRate - $previous->buy_rate) / $previous->buy_rate * 100, 2);
                $trend = $changePct > 0.1 ? 'up' : ($changePct < -0.1 ? 'down' : 'stable');
            }

            // Save to database
            ExchangeRate::create([
                'currency_id' => $currency->id,
                'rate_date' => now(),
                'buy_rate' => $buyRate,
                'sell_rate' => $sellRate,
                'cbm_rate' => 0,
                'previous_buy_rate' => $previous->buy_rate ?? 0,
                'previous_sell_rate' => $previous->sell_rate ?? 0,
                'change_percentage' => $changePct,
                'market_trend' => $trend,
                'source_name' => $source,
                'status' => 'verified',
                'is_verified' => true,
                'verified_at' => now(),
                'factors' => [
                    'usd_market_rate' => $usdMarketRate,
                    'markup' => $markup,
                    'buy_spread' => $buySpread,
                    'sell_spread' => $sellSpread,
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            // Update currency with latest rate
            $currency->update([
                'last_sync_rate' => $buyRate,
                'last_synced_at' => now(),
            ]);

            Log::info("✓ Synced {$code}: Buy={$buyRate}, Sell={$sellRate}, Source={$source}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to sync {$code}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get market USD/MMK rate (alias for getDirectBankRate)
     */
    public function getMarketUsdMmkMid(): float
    {
        // Get USD rate from exchange_rates table
        $usdCurrency = Currency::where('code', 'USD')->first();
        if (!$usdCurrency) {
            return 0;
        }

        $usdRate = ExchangeRate::where('currency_id', $usdCurrency->id)
            ->where('is_verified', true)
            ->latest('rate_date')
            ->first();

        if ($usdRate) {
            return ($usdRate->buy_rate + $usdRate->sell_rate) / 2;
        }

        // Fallback to bank scraping
        return $this->getDirectBankRate('USD');
    }

    /**
     * Force refresh all caches
     */
    public function forceRefresh(): void
    {
        Cache::flush();
        Log::info("All caches cleared");
    }

    /**
     * Get rate health for monitoring
     */
    public function getRateHealth(): array
    {
        $currencies = Currency::where('is_active', true)->get();
        $health = [];

        foreach ($currencies as $currency) {
            $latestRate = ExchangeRate::where('currency_id', $currency->id)
                ->latest('rate_date')
                ->first();

            if ($latestRate) {
                $health[$currency->code] = [
                    'rate' => ($latestRate->buy_rate + $latestRate->sell_rate) / 2,
                    'confidence' => 0.9,
                    'sources' => [$latestRate->source_name ?? 'Unknown'],
                    'is_reliable' => true,
                ];
            } else {
                $health[$currency->code] = [
                    'rate' => 0,
                    'confidence' => 0,
                    'sources' => ['none'],
                    'is_reliable' => false,
                ];
            }
        }

        return $health;
    }


    public function debugGetDirectBankRate(string $code): float
    {
        return $this->getDirectBankRate($code);
    }

    public function debugSync(): void
    {
        Log::info("=== DEBUG SYNC START ===");

        // Clear all existing rates
        \App\Models\ExchangeRate::truncate();
        Log::info("Cleared all exchange rates");

        // Get USD bank rate
        $usdBankRate = $this->getDirectBankRate('USD');
        Log::info("USD Bank Rate (raw): " . $usdBankRate);

        // Get USD currency settings
        $usdCurrency = \App\Models\Currency::where('code', 'USD')->first();
        $usdMarkup = ($usdCurrency->bank_markup_percentage ?? 15) / 100;
        $usdBuySpread = ($usdCurrency->buy_spread_percentage ?? 2) / 100;
        $usdSellSpread = ($usdCurrency->sell_spread_percentage ?? 0.5) / 100;

        // Calculate USD final rates
        $usdBaseWithMarkup = $usdBankRate * (1 + $usdMarkup);
        $usdBuyRate = round($usdBaseWithMarkup * (1 - $usdBuySpread), 2);
        $usdSellRate = round($usdBaseWithMarkup * (1 + $usdSellSpread), 2);
        $usdMid = ($usdBuyRate + $usdSellRate) / 2;

        Log::info("USD Calculated:", [
            'bank_rate' => $usdBankRate,
            'markup' => $usdMarkup * 100 . '%',
            'base_with_markup' => round($usdBaseWithMarkup, 2),
            'buy' => $usdBuyRate,
            'sell' => $usdSellRate,
            'mid' => $usdMid
        ]);

        // Save USD
        \App\Models\ExchangeRate::create([
            'currency_id' => $usdCurrency->id,
            'rate_date' => now(),
            'buy_rate' => $usdBuyRate,
            'sell_rate' => $usdSellRate,
            'cbm_rate' => 0,
            'source_name' => 'DEBUG USD',
            'status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // Get EUR currency
        $eurCurrency = \App\Models\Currency::where('code', 'EUR')->first();
        $usdToEur = $this->yahoo->getUsdToTargetRate('EUR');
        Log::info("USD/EUR from Yahoo: " . $usdToEur);

        // Calculate EUR
        $eurMarkup = ($eurCurrency->bank_markup_percentage ?? 2) / 100;
        $eurBuySpread = ($eurCurrency->buy_spread_percentage ?? 2) / 100;
        $eurSellSpread = ($eurCurrency->sell_spread_percentage ?? 0.5) / 100;

        $eurBase = $usdMid / $usdToEur;
        $eurWithMarkup = $eurBase * (1 + $eurMarkup);
        $eurBuyRate = round($eurWithMarkup * (1 - $eurBuySpread), 2);
        $eurSellRate = round($eurWithMarkup * (1 + $eurSellSpread), 2);

        Log::info("EUR Calculated:", [
            'usd_mid' => $usdMid,
            'usd_to_eur' => $usdToEur,
            'base' => round($eurBase, 2),
            'markup' => $eurMarkup * 100 . '%',
            'with_markup' => round($eurWithMarkup, 2),
            'buy' => $eurBuyRate,
            'sell' => $eurSellRate
        ]);

        // Save EUR
        \App\Models\ExchangeRate::create([
            'currency_id' => $eurCurrency->id,
            'rate_date' => now(),
            'buy_rate' => $eurBuyRate,
            'sell_rate' => $eurSellRate,
            'cbm_rate' => 0,
            'source_name' => 'DEBUG EUR',
            'status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        Log::info("=== DEBUG SYNC END ===");
        echo "Debug sync completed! Check storage/logs/laravel.log for details.\n";
    }
}
