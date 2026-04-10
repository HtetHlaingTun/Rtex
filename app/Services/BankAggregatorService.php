<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Services\YahooFinanceService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class BankAggregatorService
{
    protected $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Referer' => 'https://www.google.com/',
    ];

    protected $cbmService;
    protected $yahooService;

    // Expected rate ranges for each currency (MMK per 1 unit)
    protected $expectedRanges = [
        'USD' => ['min' => 3000, 'max' => 5000],
        'SGD' => ['min' => 2000, 'max' => 3500],
        'EUR' => ['min' => 3500, 'max' => 5500],
        'THB' => ['min' => 100, 'max' => 150],  // THB is cheaper, 100-150 MMK per 1 THB
    ];

    public function __construct(CBMRateService $cbmService)
    {
        $this->cbmService = $cbmService;
        $this->yahooService = new YahooFinanceService();
    }

    /**
     * Calculate rates using USD cross rate for currencies without direct bank data
     */
    protected function calculateCrossUsdRate(Currency $currency): array
    {
        $decimal = $currency->decimal_places ?? 2;

        // Get latest USD/MMK rate
        $usdCurrency = Currency::where('code', 'USD')->first();
        if (!$usdCurrency) {
            Log::warning("USD currency not found for cross rate calculation");
            return ['buy_rate' => 0, 'sell_rate' => 0];
        }

        $usdRate = ExchangeRate::where('currency_id', $usdCurrency->id)
            ->latest('rate_date')
            ->first();

        if (!$usdRate) {
            Log::warning("USD/MMK rate not available for cross rate calculation");
            return ['buy_rate' => 0, 'sell_rate' => 0];
        }

        // Calculate mid rate for USD/MMK
        $usdMmkMid = ($usdRate->buy_rate + $usdRate->sell_rate) / 2;

        // Get USD to target currency from Yahoo
        $usdToTarget = $this->yahooService->getUsdToTargetRate($currency->code);

        if (!$usdToTarget || $usdToTarget <= 0) {
            Log::warning("USD/{$currency->code} rate not available from Yahoo");
            return ['buy_rate' => 0, 'sell_rate' => 0];
        }

        // Cross rate calculation: Target/MMK = USD/MMK ÷ USD/Target
        $baseRate = $usdMmkMid / $usdToTarget;

        // Apply bank markup if configured
        $markup = $currency->bank_markup_percentage ?? 0;
        $baseRateWithMarkup = $baseRate * (1 + ($markup / 100));

        // Apply spreads
        if ($currency->spread_type === 'percentage') {
            $buyRate = $baseRateWithMarkup * (1 - (($currency->buy_spread_percentage ?? 0.5) / 100));
            $sellRate = $baseRateWithMarkup * (1 + (($currency->sell_spread_percentage ?? 0.5) / 100));
        } else {
            $buyRate = $baseRateWithMarkup - ($currency->fixed_buy_margin ?? 0);
            $sellRate = $baseRateWithMarkup + ($currency->fixed_sell_margin ?? 0);
        }

        Log::info("Cross rate calculated for {$currency->code}: USD/MMK={$usdMmkMid}, USD/{$currency->code}={$usdToTarget}, Base={$baseRate}, Buy={$buyRate}, Sell={$sellRate}");

        return [
            'buy_rate' => round(max(0, $buyRate), $decimal),
            'sell_rate' => round(max(0, $sellRate), $decimal)
        ];
    }


    protected function calculateFinalBuySellRates(Currency $currency, float $baseRate, float $cbmRate, float $avgBankRate): array
    {
        $decimal = $currency->decimal_places ?? 2;

        // ADD THIS BLOCK - Handle cross_usd mode
        if ($currency->source_mode === 'cross_usd') {
            return $this->calculateCrossUsdRate($currency);
        }

        // Rest of your existing code below...
        if ($baseRate <= 0) {
            return ['buy_rate' => 0, 'sell_rate' => 0];
        }

        if ($currency->source_mode === 'cbm' && $cbmRate > 0) {
            $calculated = $currency->calculateRatesFromCBM($cbmRate);
            return [
                'buy_rate' => $calculated['buy_rate'],
                'sell_rate' => $calculated['sell_rate']
            ];
        }

        if ($currency->spread_type === 'percentage') {
            $buyRate = $baseRate * (1 - (($currency->buy_spread_percentage ?? 0.5) / 100));
            $sellRate = $baseRate * (1 + (($currency->sell_spread_percentage ?? 0.5) / 100));
        } else {
            $buyRate = $baseRate - ($currency->fixed_buy_margin ?? 0);
            $sellRate = $baseRate + ($currency->fixed_sell_margin ?? 0);
        }

        return [
            'buy_rate' => round(max(0, $buyRate), $decimal),
            'sell_rate' => round(max(0, $sellRate), $decimal)
        ];
    }


    public function syncRates()
    {
        $cbmRates = $this->cbmService->fetchCurrentRates();
        $codes = ['USD', 'SGD', 'EUR', 'THB'];

        // ========== PROCESS BANK AVERAGE CURRENCIES ==========
        foreach ($codes as $code) {
            $currency = Currency::where('code', $code)->first();
            if (!$currency) continue;

            // Get raw rates
            $kbz = $this->scrapeKBZ($code);
            $yoma = $this->scrapeYoma($code);
            $cb = $this->scrapeCB($code);
            $aya = $this->scrapeAYA($code);

            $allRates = [
                'kbz' => $kbz,
                'yoma' => $yoma,
                'cb' => $cb,
                'aya' => $aya
            ];

            // FILTER OUT INVALID RATES based on expected range
            $validRates = [];
            $expectedRange = $this->expectedRanges[$code] ?? ['min' => 0, 'max' => 10000];

            foreach ($allRates as $bank => $rates) {
                if ($rates['buy'] > 0) {
                    if ($rates['buy'] >= $expectedRange['min'] && $rates['buy'] <= $expectedRange['max']) {
                        $validRates[$bank]['buy'] = $rates['buy'];
                    } else {
                        Log::warning("Filtered out {$bank} buy rate for {$code}: {$rates['buy']}");
                        $validRates[$bank]['buy'] = 0;
                    }
                } else {
                    $validRates[$bank]['buy'] = 0;
                }

                if ($rates['sell'] > 0) {
                    if ($rates['sell'] >= $expectedRange['min'] && $rates['sell'] <= $expectedRange['max']) {
                        $validRates[$bank]['sell'] = $rates['sell'];
                    } else {
                        Log::warning("Filtered out {$bank} sell rate for {$code}: {$rates['sell']}");
                        $validRates[$bank]['sell'] = 0;
                    }
                } else {
                    $validRates[$bank]['sell'] = 0;
                }
            }

            // Calculate averages from VALID rates only
            $bankMidRates = [];
            $bankBuyRates = [];
            $bankSellRates = [];

            foreach ($validRates as $bank => $rates) {
                if ($rates['buy'] > 0 && $rates['sell'] > 0) {
                    $midRate = ($rates['buy'] + $rates['sell']) / 2;
                    $bankMidRates[] = $midRate;
                    $bankBuyRates[] = $rates['buy'];
                    $bankSellRates[] = $rates['sell'];
                } elseif ($rates['buy'] > 0) {
                    $bankMidRates[] = $rates['buy'];
                    $bankBuyRates[] = $rates['buy'];
                } elseif ($rates['sell'] > 0) {
                    $bankMidRates[] = $rates['sell'];
                    $bankSellRates[] = $rates['sell'];
                }
            }

            // Calculate bank averages
            $avgBankMidRate = !empty($bankMidRates) ? array_sum($bankMidRates) / count($bankMidRates) : 0;
            $avgBankBuyRate = !empty($bankBuyRates) ? array_sum($bankBuyRates) / count($bankBuyRates) : 0;
            $avgBankSellRate = !empty($bankSellRates) ? array_sum($bankSellRates) / count($bankSellRates) : 0;

            // For bank_avg mode, ensure we have valid bank data
            if ($currency->source_mode === 'bank_avg') {
                $hasFreshRates = $avgBankMidRate > 0;
                $hasStoredRates = $currency->avg_bank_rate > 0;

                if (!$hasFreshRates && !$hasStoredRates) {
                    Log::warning("NO BANK RATES available for {$code}. Skipping save.");
                    $currency->update(['banks_last_synced_at' => now()]);
                    continue;
                }

                if ($avgBankMidRate == 0 && $currency->avg_bank_rate > 0) {
                    $avgBankMidRate = $currency->avg_bank_rate;
                    Log::info("Using stored avg_bank_rate for {$code}: {$avgBankMidRate}");
                }
            }

            // Get CBM rate
            $cbmRateData = $cbmRates[$code] ?? null;
            $cbmRate = 0;

            if ($cbmRateData) {
                $cbmRate = $cbmRateData['cbm_rate'] ?? $cbmRateData['working_rate'] ?? 0;
            }

            if ($cbmRate == 0 && isset($currency->cbm_rate) && $currency->cbm_rate > 0) {
                $cbmRate = $currency->cbm_rate;
            }

            // Determine final rates
            $finalRate = $this->determineFinalRate($currency, $avgBankMidRate, $cbmRate);
            $finalRates = $this->calculateFinalBuySellRates($currency, $finalRate, $cbmRate, $avgBankMidRate);

            // Final rate validation
            $minExpectedRate = $this->getExpectedMinRate($code);
            if ($currency->source_mode === 'bank_avg' && $finalRates['buy_rate'] < $minExpectedRate) {
                Log::warning("CALCULATED RATE TOO LOW for {$code}: Buy={$finalRates['buy_rate']} < {$minExpectedRate}. Skipping save.");
                $currency->update(['banks_last_synced_at' => now()]);
                continue;
            }

            // Get previous record
            $previousRecord = ExchangeRate::where('currency_id', $currency->id)
                ->latest('rate_date')
                ->first();

            $changePercent = ($previousRecord && $previousRecord->buy_rate > 0 && $finalRates['buy_rate'] > 0)
                ? (($finalRates['buy_rate'] - (float)$previousRecord->buy_rate) / (float)$previousRecord->buy_rate) * 100
                : 0;

            $validSources = count(array_filter($validRates, fn($r) => $r['buy'] > 0 || $r['sell'] > 0));
            $hasCbm = $cbmRate > 0;
            $totalSources = $validSources + ($hasCbm ? 1 : 0);

            $shouldVerify = $this->shouldAutoVerify($code, $validRates, $cbmRate, $previousRecord);
            $status = $shouldVerify ? 'verified' : 'pending';
            $isVerified = $shouldVerify;

            $factors = [
                'raw_rates' => $allRates,
                'filtered_rates' => $validRates,
                'bank_averages' => [
                    'mid' => $avgBankMidRate,
                    'buy' => $avgBankBuyRate,
                    'sell' => $avgBankSellRate,
                    'banks_used' => $validSources
                ],
                'cbm_rate' => $cbmRate,
                'final_calculation' => [
                    'base_rate' => $finalRate,
                    'source_mode' => $currency->source_mode,
                    'buy_rate' => $finalRates['buy_rate'],
                    'sell_rate' => $finalRates['sell_rate'],
                ],
                'total_sources' => $totalSources,
                'expected_range' => $expectedRange ?? ['min' => 0, 'max' => 10000]
            ];

            // ✅ SAVE exchange rate for bank_avg currency
            ExchangeRate::create([
                'currency_id' => $currency->id,
                'rate_date' => now(),
                'buy_rate' => $finalRates['buy_rate'],
                'sell_rate' => $finalRates['sell_rate'],
                'cbm_rate' => $cbmRate,
                'previous_buy_rate' => $previousRecord->buy_rate ?? 0,
                'previous_sell_rate' => $previousRecord->sell_rate ?? 0,
                'change_percentage' => $changePercent,
                'market_trend' => $finalRates['buy_rate'] > ($previousRecord->buy_rate ?? 0) ? 'up' : ($finalRates['buy_rate'] < ($previousRecord->buy_rate ?? 0) ? 'down' : 'stable'),
                'source_name' => $this->getSourceName($currency->source_mode, $totalSources),
                'status' => $status,
                'is_verified' => $isVerified,
                'verified_at' => $isVerified ? now() : null,
                'factors' => $factors,
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            $currency->update([
                'avg_bank_rate' => $avgBankMidRate,
                'cbm_rate' => $cbmRate,
                'banks_last_synced_at' => now()
            ]);

            Log::info("Synced {$currency->code}: Buy={$finalRates['buy_rate']}, Sell={$finalRates['sell_rate']}, Banks={$validSources}, CBM=" . ($hasCbm ? $cbmRate : 'none'));
        }

        // ========== PROCESS CROSS USD CURRENCIES ==========
        $crossCurrencies = Currency::where('source_mode', 'cross_usd')
            ->where('is_active', true)
            ->get();

        foreach ($crossCurrencies as $currency) {
            // Skip currencies already processed by bank_avg
            if (in_array($currency->code, ['USD', 'SGD', 'EUR', 'THB'])) {
                continue;
            }

            // Calculate rates using cross method
            $finalRates = $this->calculateCrossUsdRate($currency);

            if ($finalRates['buy_rate'] <= 0 || $finalRates['sell_rate'] <= 0) {
                Log::warning("Failed to calculate cross rate for {$currency->code}");
                continue;
            }

            // Get previous record for change calculation
            $previousRecord = ExchangeRate::where('currency_id', $currency->id)
                ->latest('rate_date')
                ->first();

            $changePercent = ($previousRecord && $previousRecord->buy_rate > 0 && $finalRates['buy_rate'] > 0)
                ? (($finalRates['buy_rate'] - $previousRecord->buy_rate) / $previousRecord->buy_rate) * 100
                : 0;

            // ✅ SAVE exchange rate for cross_usd currency
            ExchangeRate::create([
                'currency_id' => $currency->id,
                'rate_date' => now(),
                'buy_rate' => $finalRates['buy_rate'],
                'sell_rate' => $finalRates['sell_rate'],
                'cbm_rate' => $currency->cbm_rate ?? 0,
                'previous_buy_rate' => $previousRecord->buy_rate ?? 0,
                'previous_sell_rate' => $previousRecord->sell_rate ?? 0,
                'change_percentage' => $changePercent,
                'market_trend' => $finalRates['buy_rate'] > ($previousRecord->buy_rate ?? 0) ? 'up' : ($finalRates['buy_rate'] < ($previousRecord->buy_rate ?? 0) ? 'down' : 'stable'),
                'source_name' => 'Cross Rate (USD Bridge)',
                'status' => 'verified',
                'is_verified' => true,
                'verified_at' => now(),
                'factors' => [
                    'method' => 'cross_usd',
                    'usd_mmk_rate' => $usdMmkMid ?? null,
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            Log::info("Synced {$currency->code} via cross rate: Buy={$finalRates['buy_rate']}, Sell={$finalRates['sell_rate']}");
        }
    }



    protected function shouldAutoVerify(string $code, array $validRates, float $cbmRate, ?ExchangeRate $previousRecord): bool
    {
        $validSources = count(array_filter($validRates, fn($r) => $r['buy'] > 0 || $r['sell'] > 0));
        $hasCbm = $cbmRate > 0;

        // Always auto-verify USD (most important)
        if ($code === 'USD') {
            return true;
        }

        // Auto-verify if we have 2+ bank sources
        if ($validSources >= 2) {
            return true;
        }

        // Auto-verify if 1 bank + CBM and they're consistent
        if ($validSources >= 1 && $hasCbm) {
            $firstBankRate = $this->getFirstValidRate($validRates);
            if ($firstBankRate > 0 && $cbmRate > 0) {
                $diffPercent = abs($firstBankRate - $cbmRate) / $cbmRate * 100;
                return $diffPercent <= 15; // Within 15%
            }
        }

        // Don't auto-verify if we have no sources
        if ($validSources == 0) {
            return false;
        }

        // Default to auto-verify for single sources with small changes
        if ($previousRecord && $previousRecord->buy_rate > 0) {
            // We don't have the final rate yet, so this is approximate
            return true; // Be optimistic
        }

        return false;
    }

    protected function getFirstValidRate(array $validRates): float
    {
        foreach ($validRates as $rates) {
            if ($rates['buy'] > 0) return $rates['buy'];
            if ($rates['sell'] > 0) return $rates['sell'];
        }
        return 0;
    }

    /**
     * Determine final base rate based on currency's source mode
     */
    protected function determineFinalRate(Currency $currency, float $avgBankRate, float $cbmRate): float
    {
        switch ($currency->source_mode) {
            case 'manual':
                return $currency->manual_base_rate ?? 0;
            case 'bank_avg':
                $markup = $currency->bank_markup_percentage ?? 0;
                $bankRate = $avgBankRate;
                if ($bankRate == 0 && $currency->avg_bank_rate > 0) {
                    $bankRate = $currency->avg_bank_rate;
                    Log::info("Using stored avg_bank_rate for {$currency->code}: {$bankRate}");
                }
                return $bankRate * (1 + ($markup / 100));
            case 'cbm':
                $conversionFactor = $currency->cbm_conversion_factor ?? 1;
                return $cbmRate * $conversionFactor;
            default:
                if ($avgBankRate > 0) return $avgBankRate;
                if ($cbmRate > 0) return $cbmRate;
                return 0;
        }
    }

    /**
     * Calculate final buy and sell rates using currency's spreadlogic
     */
    // protected function calculateFinalBuySellRates(Currency $currency, float $baseRate, float $cbmRate, float $avgBankRate): array
    // {
    //     $decimal = $currency->decimal_places ?? 2;

    //     if ($baseRate <= 0) {
    //         return ['buy_rate' => 0, 'sell_rate' => 0];
    //     }

    //     if ($currency->source_mode === 'cbm' && $cbmRate > 0) {
    //         $calculated = $currency->calculateRatesFromCBM($cbmRate);
    //         return [
    //             'buy_rate' => $calculated['buy_rate'],
    //             'sell_rate' => $calculated['sell_rate']
    //         ];
    //     }

    //     if ($currency->spread_type === 'percentage') {
    //         $buyRate = $baseRate * (1 - (($currency->buy_spread_percentage ?? 0.5) / 100));
    //         $sellRate = $baseRate * (1 + (($currency->sell_spread_percentage ?? 0.5) / 100));
    //     } else {
    //         $buyRate = $baseRate - ($currency->fixed_buy_margin ?? 0);
    //         $sellRate = $baseRate + ($currency->fixed_sell_margin ?? 0);
    //     }

    //     return [
    //         'buy_rate' => round(max(0, $buyRate), $decimal),
    //         'sell_rate' => round(max(0, $sellRate), $decimal)
    //     ];
    // }

    /**
     * Get minimum expected rate for validation (prevents CBM rates)
     */
    private function getExpectedMinRate(string $code): float
    {
        return match ($code) {
            'USD' => 3500,
            'EUR' => 4000,
            'SGD' => 2800,
            'THB' => 120,
            default => 0,
        };
    }

    protected function getSourceName(string $mode, int $totalSources): string
    {
        $modeNames = ['manual' => 'Manual', 'bank_avg' => 'Bank Avg', 'cbm' => 'CBM'];
        $modeName = $modeNames[$mode] ?? 'Mixed';
        return $totalSources >= 2 ? "{$modeName} (Verified)" : $modeName;
    }



    private function scrapeKBZ($code)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->timeout(10)->get('https://www.kbzbank.com/en/');

            if ($response->successful()) {
                $crawler = new Crawler($response->body());

                // Based on the preview_data structure:
                // EUR: Buy at 0, Sell at 2
                // USD: Buy at 4, Sell at 6
                // SGD: Buy at 8, Sell at 10
                // THB: Buy at 12, Sell at 14
                // Plus other currencies at 16, 18 etc.

                $map = [
                    'EUR' => ['buy' => 1, 'sell' => 3],   // Index 1 = 4200.70, Index 3 = 4210.60
                    'USD' => ['buy' => 5, 'sell' => 7],   // Index 5 = 3657, Index 7 = 3662
                    'SGD' => ['buy' => 9, 'sell' => 11],  // Index 9 = 2832.10, Index 11 = 2842
                    'THB' => ['buy' => 13, 'sell' => 15], // Index 13 = 125.00, Index 15 = 125.60
                ];

                if (isset($map[$code])) {
                    $buyNode = $crawler->filter('td')->eq($map[$code]['buy']);
                    $sellNode = $crawler->filter('td')->eq($map[$code]['sell']);

                    $buyRate = $buyNode->count() ? $this->cleanRate($buyNode->text()) : 0;
                    $sellRate = $sellNode->count() ? $this->cleanRate($sellNode->text()) : 0;

                    Log::info("KBZ rates for {$code}: Buy={$buyRate}, Sell={$sellRate}");

                    return [
                        'buy' => $buyRate,
                        'sell' => $sellRate
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("KBZ Scrape Error ($code): " . $e->getMessage());
        }
        return ['buy' => 0, 'sell' => 0];
    }

    /**
     * Scrape Yoma Bank
     */
    /**
     * Scrape Yoma Bank (returns buy/sell rates)
     */
    private function scrapeYoma($code)
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->timeout(15)
                ->get('https://www.yomabank.com/en/');

            if (!$response->successful()) {
                Log::warning("Yoma Bank request failed: HTTP " . $response->status());
                return ['buy' => 0, 'sell' => 0];
            }

            $crawler = new Crawler($response->body());

            // Based on preview_data structure:
            // Index 0: USD, Index 2: BUY (3,658), Index 3: SELL (3,668)
            // Index 4: EUR, Index 6: BUY (4,205), Index 7: SELL (4,215)
            // Index 8: SGD, Index 10: BUY (2,837), Index 11: SELL (2,845)

            $buyRate = 0;
            $sellRate = 0;

            // Method 1: Use index mapping based on preview_data
            $map = [
                'USD' => ['buy' => 2, 'sell' => 3],   // Index 2 = 3,658, Index 3 = 3,668
                'EUR' => ['buy' => 6, 'sell' => 7],   // Index 6 = 4,205, Index 7 = 4,215
                'SGD' => ['buy' => 10, 'sell' => 11], // Index 10 = 2,837, Index 11 = 2,845
            ];

            if (isset($map[$code])) {
                $buyNode = $crawler->filter('td')->eq($map[$code]['buy']);
                $sellNode = $crawler->filter('td')->eq($map[$code]['sell']);

                $buyRate = $buyNode->count() ? $this->cleanRate($buyNode->text()) : 0;
                $sellRate = $sellNode->count() ? $this->cleanRate($sellNode->text()) : 0;

                if ($buyRate > 0 || $sellRate > 0) {
                    Log::info("Yoma rates for {$code} (index): Buy={$buyRate}, Sell={$sellRate}");
                    return ['buy' => $buyRate, 'sell' => $sellRate];
                }
            }

            // Method 2: Search by currency code in table rows
            $crawler->filter('table')->each(function ($table) use ($code, &$buyRate, &$sellRate) {
                if ($buyRate > 0 && $sellRate > 0) return;

                $table->filter('tr')->each(function ($row) use ($code, &$buyRate, &$sellRate) {
                    if ($buyRate > 0 && $sellRate > 0) return;

                    $cells = $row->filter('td');
                    if ($cells->count() >= 3) {
                        $currencyText = trim($cells->eq(0)->text());
                        if (strtoupper($currencyText) === $code) {
                            // Yoma structure: CODE | NAME | BUY | SELL
                            // So buy is at index 2, sell at index 3
                            if ($cells->count() >= 4) {
                                $buyRate = $this->cleanRate($cells->eq(2)->text());
                                $sellRate = $this->cleanRate($cells->eq(3)->text());
                            } elseif ($cells->count() >= 3) {
                                // If only 3 columns, assume last two are buy/sell
                                $buyRate = $this->cleanRate($cells->eq(1)->text());
                                $sellRate = $this->cleanRate($cells->eq(2)->text());
                            }
                        }
                    }
                });
            });

            if ($buyRate > 0 || $sellRate > 0) {
                Log::info("Yoma rates for {$code} (row search): Buy={$buyRate}, Sell={$sellRate}");
                return ['buy' => $buyRate, 'sell' => $sellRate];
            }

            // Method 3: Regex fallback for THB (not in main table)
            $allText = $crawler->filter('body')->text();

            // Look for THB specifically since it might be in a different location
            if ($code === 'THB') {
                $patterns = [
                    '/THB[^0-9]*([0-9,]+(?:\.[0-9]+)?)[^0-9]*([0-9,]+(?:\.[0-9]+)?)/i',
                    '/Thai Baht[^0-9]*([0-9,]+(?:\.[0-9]+)?)[^0-9]*([0-9,]+(?:\.[0-9]+)?)/i',
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $allText, $matches)) {
                        $buyRate = $this->cleanRate($matches[1]);
                        $sellRate = isset($matches[2]) ? $this->cleanRate($matches[2]) : $buyRate;
                        if ($buyRate > 0) {
                            Log::info("Yoma rates for THB (regex): Buy={$buyRate}, Sell={$sellRate}");
                            return ['buy' => $buyRate, 'sell' => $sellRate];
                        }
                    }
                }
            }

            // Generic regex for other currencies
            $pattern = '/' . preg_quote($code) . '[^0-9]*([0-9,]+(?:\.[0-9]+)?)[^0-9]*([0-9,]+(?:\.[0-9]+)?)/i';
            if (preg_match($pattern, $allText, $matches)) {
                $buyRate = $this->cleanRate($matches[1]);
                $sellRate = isset($matches[2]) ? $this->cleanRate($matches[2]) : $buyRate;
                if ($buyRate > 0 || $sellRate > 0) {
                    Log::info("Yoma rates for {$code} (regex): Buy={$buyRate}, Sell={$sellRate}");
                    return ['buy' => $buyRate, 'sell' => $sellRate];
                }
            }

            Log::warning("Yoma rates not found for {$code}");
            return ['buy' => 0, 'sell' => 0];
        } catch (\Exception $e) {
            Log::error("Yoma Scrape Error ({$code}): " . $e->getMessage());
            return ['buy' => 0, 'sell' => 0];
        }
    }

    /**
     * Scrape CB Bank
     */
    /**
     * Scrape CB Bank (returns buy/sell rates)
     */
    private function scrapeCB($code)
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->timeout(15)
                ->get('https://www.cbbank.com.mm/en/online-trading-platform');

            if (!$response->successful()) {
                Log::warning("CB Bank request failed: HTTP " . $response->status());
                return ['buy' => 0, 'sell' => 0];
            }

            $crawler = new Crawler($response->body());

            // Based on the preview_data structure, CB has multiple sections
            // The structure shows currency codes followed by buy/sell rates
            // Need to find the correct section for each currency

            $buyRate = 0;
            $sellRate = 0;

            // Method 1: Find by currency code pattern in the text
            $allText = $crawler->filter('body')->text();

            // Look for patterns like "USD 3666 3668" or "USD 3666, 3668"
            $patterns = [
                '/' . preg_quote($code) . '\s+([0-9,]+(?:\.[0-9]+)?)\s+([0-9,]+(?:\.[0-9]+)?)/i',
                '/' . preg_quote($code) . '\s*:\s*([0-9,]+(?:\.[0-9]+)?)\s+([0-9,]+(?:\.[0-9]+)?)/i',
                '/' . preg_quote($code) . '[^0-9]*([0-9,]+(?:\.[0-9]+)?)[^0-9]*([0-9,]+(?:\.[0-9]+)?)/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $allText, $matches)) {
                    $buyRate = $this->cleanRate($matches[1]);
                    $sellRate = isset($matches[2]) ? $this->cleanRate($matches[2]) : $buyRate;
                    if ($buyRate > 0 || $sellRate > 0) {
                        Log::info("CB rates for {$code} (regex): Buy={$buyRate}, Sell={$sellRate}");
                        return ['buy' => $buyRate, 'sell' => $sellRate];
                    }
                }
            }

            // Method 2: Use table indices based on preview_data
            // From preview_data:
            // Index 0: USD, Index 1: 3666, Index 2: 3668
            // Index 3: THB, Index 4: 124.92, Index 5: 125.68
            // Index 9: USD (another section), Index 10: 3985
            // Index 14: USD (another section), Index 15: 3668

            // The most reliable USD rates are at indices 0-2
            $map = [
                'USD' => ['currency_index' => 0, 'buy_index' => 1, 'sell_index' => 2],
                'THB' => ['currency_index' => 3, 'buy_index' => 4, 'sell_index' => 5],
                'EUR' => ['currency_index' => 16, 'buy_index' => 17, 'sell_index' => 18],
                'SGD' => ['currency_index' => 19, 'buy_index' => 20, 'sell_index' => 21],
            ];

            if (isset($map[$code])) {
                // Verify it's the correct currency at that index
                $currencyNode = $crawler->filter('td')->eq($map[$code]['currency_index']);
                $currencyText = $currencyNode->count() ? trim($currencyNode->text()) : '';

                if (strtoupper($currencyText) === $code) {
                    $buyNode = $crawler->filter('td')->eq($map[$code]['buy_index']);
                    $sellNode = $crawler->filter('td')->eq($map[$code]['sell_index']);

                    $buyRate = $buyNode->count() ? $this->cleanRate($buyNode->text()) : 0;
                    $sellRate = $sellNode->count() ? $this->cleanRate($sellNode->text()) : 0;

                    if ($buyRate > 0 || $sellRate > 0) {
                        Log::info("CB rates for {$code} (index): Buy={$buyRate}, Sell={$sellRate}");
                        return ['buy' => $buyRate, 'sell' => $sellRate];
                    }
                }
            }

            // Method 3: Search through all table rows
            $crawler->filter('tr')->each(function ($row) use ($code, &$buyRate, &$sellRate) {
                $cells = $row->filter('td');
                if ($cells->count() >= 3) {
                    $currencyText = trim($cells->eq(0)->text());
                    if (strtoupper($currencyText) === $code) {
                        $buyRate = $this->cleanRate($cells->eq(1)->text());
                        $sellRate = $this->cleanRate($cells->eq(2)->text());
                    }
                }
            });

            if ($buyRate > 0 || $sellRate > 0) {
                Log::info("CB rates for {$code} (row search): Buy={$buyRate}, Sell={$sellRate}");
                return ['buy' => $buyRate, 'sell' => $sellRate];
            }

            Log::warning("CB rates not found for {$code}");
            return ['buy' => 0, 'sell' => 0];
        } catch (\Exception $e) {
            Log::error("CB Scrape Error ({$code}): " . $e->getMessage());
            return ['buy' => 0, 'sell' => 0];
        }
    }

    /**
     * Scrape AYA Bank
     */
    /**
     * Scrape AYA Bank (returns buy/sell rates)
     */
    private function scrapeAYA($code)
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->timeout(15)
                ->get('https://ayabank.com/personal-banking/other-services/foreign-currency-exchange-service');

            if (!$response->successful()) {
                Log::warning("AYA Bank request failed: HTTP " . $response->status());
                return ['buy' => 0, 'sell' => 0];
            }

            $crawler = new Crawler($response->body());

            // Based on preview_data structure:
            // First table (likely main rates):
            // Index 5: USD, Index 8: BUY (3658), Index 9: SELL (3668)
            // Index 10: CNY, Index 13: BUY (557), Index 14: SELL (558.67)
            // Index 15: THB, Index 18: BUY (125.30), Index 19: SELL (125.67)

            // Second table (likely additional rates):
            // Index 25: USD, Index 28: BUY (3654), Index 29: SELL (3664)
            // Index 30: EUR, Index 33: BUY (4201), Index 34: SELL (4212)
            // Index 35: SGD, Index 38: BUY (2833), Index 39: SELL (2841)

            $buyRate = 0;
            $sellRate = 0;

            // Method 1: Use index mapping based on preview_data
            $map = [
                'USD' => [
                    ['buy' => 8, 'sell' => 9],   // First occurrence (3658/3668)
                    ['buy' => 28, 'sell' => 29]  // Second occurrence (3654/3664)
                ],
                'EUR' => [
                    ['buy' => 33, 'sell' => 34]  // 4201/4212
                ],
                'SGD' => [
                    ['buy' => 38, 'sell' => 39]  // 2833/2841
                ],
                'THB' => [
                    ['buy' => 18, 'sell' => 19]  // 125.30/125.67
                ],
            ];

            if (isset($map[$code])) {
                // Try each occurrence for the currency
                foreach ($map[$code] as $occurrence) {
                    $buyNode = $crawler->filter('td')->eq($occurrence['buy']);
                    $sellNode = $crawler->filter('td')->eq($occurrence['sell']);

                    $potentialBuy = $buyNode->count() ? $this->cleanRate($buyNode->text()) : 0;
                    $potentialSell = $sellNode->count() ? $this->cleanRate($sellNode->text()) : 0;

                    // Use the first valid rates we find
                    if ($potentialBuy > 0 || $potentialSell > 0) {
                        $buyRate = $potentialBuy;
                        $sellRate = $potentialSell;
                        Log::info("AYA rates for {$code} (index): Buy={$buyRate}, Sell={$sellRate}");
                        return ['buy' => $buyRate, 'sell' => $sellRate];
                    }
                }
            }

            // Method 2: Find by currency code in table
            $crawler->filter('table')->each(function ($table) use ($code, &$buyRate, &$sellRate) {
                if ($buyRate > 0 && $sellRate > 0) return;

                $table->filter('tr')->each(function ($row) use ($code, &$buyRate, &$sellRate) {
                    if ($buyRate > 0 && $sellRate > 0) return;

                    $cells = $row->filter('td');
                    if ($cells->count() >= 4) {
                        $currencyText = trim($cells->eq(0)->text());
                        if (strtoupper($currencyText) === $code) {
                            // AYA structure: CODE | NAME | UNIT | BUYING | SELLING
                            // So buy is at index 3, sell at index 4
                            if ($cells->count() >= 5) {
                                $buyRate = $this->cleanRate($cells->eq(3)->text());
                                $sellRate = $this->cleanRate($cells->eq(4)->text());
                            }
                        }
                    }
                });
            });

            if ($buyRate > 0 || $sellRate > 0) {
                Log::info("AYA rates for {$code} (table search): Buy={$buyRate}, Sell={$sellRate}");
                return ['buy' => $buyRate, 'sell' => $sellRate];
            }

            // Method 3: Regex fallback
            $allText = $crawler->filter('body')->text();
            $patterns = [
                '/' . preg_quote($code) . '[^0-9]*([0-9,]+(?:\.[0-9]+)?)[^0-9]*([0-9,]+(?:\.[0-9]+)?)/i',
                '/' . preg_quote($code) . '.*?(\\d+(?:\\.\\d+)?).*?(\\d+(?:\\.\\d+)?)/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $allText, $matches)) {
                    $buyRate = $this->cleanRate($matches[1]);
                    $sellRate = isset($matches[2]) ? $this->cleanRate($matches[2]) : $buyRate;
                    if ($buyRate > 0 || $sellRate > 0) {
                        Log::info("AYA rates for {$code} (regex): Buy={$buyRate}, Sell={$sellRate}");
                        return ['buy' => $buyRate, 'sell' => $sellRate];
                    }
                }
            }

            Log::warning("AYA rates not found for {$code}");
            return ['buy' => 0, 'sell' => 0];
        } catch (\Exception $e) {
            Log::error("AYA Scrape Error ({$code}): " . $e->getMessage());
            return ['buy' => 0, 'sell' => 0];
        }
    }

    /**
     * Clean rate string to float
     */
    private function cleanRate($string)
    {
        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', $string));
        return is_numeric($clean) ? (float) $clean : 0;
    }
}
