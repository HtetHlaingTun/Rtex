<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CBMRateService;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FetchCBMRates extends Command
{
    protected $signature = 'cbm:fetch 
                            {--auto-verify : Automatically verify fetched rates}
                            {--dry-run : Preview without saving}
                            {--currency= : Specific currency code to fetch}
                            {--date= : Fetch rates for specific date (Y-m-d)}';

    protected $description = 'Fetch exchange rates from CBM and store history, preventing duplicate entries.';

    protected $cbmService;

    public function __construct(CBMRateService $cbmService)
    {
        parent::__construct();
        $this->cbmService = $cbmService;
    }

    public function handle()
    {
        $this->info('Starting CBM rates fetch...');

        $autoVerify = $this->option('auto-verify');
        $dryRun = $this->option('dry-run');
        $specificCurrency = $this->option('currency');

        // Use provided date or default to today
        $dateInput = $this->option('date');
        $rateDate = $dateInput ? Carbon::parse($dateInput) : now();

        $this->comment("Target Date: {$rateDate->format('Y-m-d')}");

        $rates = $this->cbmService->fetchCurrentRates();

        if (empty($rates)) {
            $this->error('No rates fetched from CBM API');
            return 1;
        }

        if ($specificCurrency) {
            $specificCurrency = strtoupper($specificCurrency);
            if (isset($rates[$specificCurrency])) {
                $rates = [$specificCurrency => $rates[$specificCurrency]];
            } else {
                $this->error("Currency {$specificCurrency} not found.");
                return 1;
            }
        }

        if ($dryRun) {
            $this->warn('DRY RUN - Simulation only.');
        }

        $results = $this->storeLatestRates($rates, $autoVerify, $rateDate, $dryRun);
        $this->displaySummary($results);

        return $results['errors'] > 0 ? 1 : 0;
    }

    protected function storeLatestRates($rates, $autoVerify, $rateDate, $dryRun)
    {
        $results = ['success' => 0, 'errors' => 0, 'updated' => 0, 'created' => 0];

        foreach ($rates as $currencyCode => $rateData) {
            $currency = Currency::where('code', $currencyCode)->first();

            if (!$currency) {
                $this->warn("Skipping: Currency {$currencyCode} does not exist in database.");
                $results['errors']++;
                continue;
            }

            try {
                DB::transaction(function () use ($currency, $rateData, $autoVerify, $rateDate, $dryRun, &$results) {
                    $buyRate = round(floatval($rateData['buy_rate'] ?? 0), 4);
                    $sellRate = round(floatval($rateData['sell_rate'] ?? 0), 4);
                    $cbmRate = round(floatval($rateData['cbm_rate'] ?? 0), 4);


                    // 1. Fetch the absolute last entry
                    $lastRecord = ExchangeRate::where('currency_id', $currency->id)
                        ->latest('id')
                        ->first();

                    if ($lastRecord) {
                        // Check if the last record was created TODAY
                        $isSameDay = $lastRecord->created_at->isToday();

                        // Check if the price is the same
                        $lastBuy = number_format((float)$lastRecord->buy_rate, 4, '.', '');
                        $currentBuy = number_format($buyRate, 4, '.', '');
                        $lastSell = number_format((float)$lastRecord->sell_rate, 4, '.', '');
                        $currentSell = number_format($sellRate, 4, '.', '');
                        $isSamePrice = ($lastBuy === $currentBuy && $lastSell === $currentSell);

                        // ONLY skip if it is the same day AND the same price
                        if ($isSameDay && $isSamePrice) {
                            $this->line(" - {$currency->code}: Already recorded today with same price. Skipping...");
                            return;
                        }
                    }

                    if ($dryRun) {
                        $this->info(" - {$currency->code}: Would create new record ({$buyRate}/{$sellRate})");
                        $results['created']++;
                        return;
                    }

                    // 3. Trend Calculation
                    $previousBuyRate = $lastRecord ? (float)$lastRecord->buy_rate : null;
                    $changePercentage = null;
                    $trend = 'stable';

                    if ($previousBuyRate && $previousBuyRate > 0) {
                        $changePercentage = (($buyRate - $previousBuyRate) / $previousBuyRate) * 100;
                        if ($changePercentage > 0.001) $trend = 'up';
                        elseif ($changePercentage < -0.001) $trend = 'down';
                    }

                    // 4. Create Record
                    $newRate = ExchangeRate::create([
                        'currency_id'       => $currency->id,
                        'rate_date'         => $rateDate->format('Y-m-d'),
                        'buy_rate'          => $buyRate,
                        'sell_rate'         => $sellRate,
                        'mid_rate'          => ($buyRate + $sellRate) / 2,
                        'cbm_rate'          => $cbmRate,
                        'previous_buy_rate' => $previousBuyRate,
                        'change_percentage' => $changePercentage,
                        'market_trend'      => $trend,
                        'source_name'       => 'CBM Auto-Fetch',
                        'status'            => $autoVerify ? 'verified' : 'pending',
                        'is_verified'       => $autoVerify,
                        'verified_at'       => $autoVerify ? now() : null,
                        'created_by'        => 1,
                        'updated_by'        => 1,
                        'factors'           => [
                            'fetched_at' => now()->toDateTimeString(),
                            'cbm_factor' => $rateData['conversion_factor'] ?? 1,
                        ],
                    ]);

                    $this->info("✓ Recorded: {$currency->code} | Trend: {$trend} (" . round($changePercentage ?? 0, 2) . "%)");
                    $results['created']++;
                });
            } catch (\Exception $e) {
                $this->error("✗ Failed for {$currencyCode}: " . $e->getMessage());
                $results['errors']++;
            }
        }
        return $results;
    }

    protected function displaySummary($results)
    {
        $this->line('');
        $this->info('  FETCH SUMMARY');
        $this->line("  ✅ Created: {$results['created']}");
        $this->line("  ❌ Errors:  {$results['errors']}");
        $this->line(str_repeat('-', 20));
    }
}
