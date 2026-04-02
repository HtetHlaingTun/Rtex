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

    protected $description = 'Fetch reference CBM rates (not used for actual exchange rates)';

    protected $cbmService;

    public function __construct(CBMRateService $cbmService)
    {
        parent::__construct();
        $this->cbmService = $cbmService;
    }

    public function handle()
    {
        $this->info('Starting CBM reference rates fetch...');

        $autoVerify = $this->option('auto-verify');
        $dryRun = $this->option('dry-run');
        $specificCurrency = $this->option('currency');

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

        $results = $this->storeReferenceRates($rates, $autoVerify, $rateDate, $dryRun);
        $this->displaySummary($results);

        return $results['errors'] > 0 ? 1 : 0;
    }

    protected function storeReferenceRates($rates, $autoVerify, $rateDate, $dryRun)
    {
        $results = ['success' => 0, 'errors' => 0, 'created' => 0];

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

                    // Only save if this is a REFERENCE record (marked as reference)
                    // These will NOT be displayed as main rates on the welcome page

                    if ($dryRun) {
                        $this->info(" - {$currency->code}: Would create reference record (CBM: {$cbmRate})");
                        $results['created']++;
                        return;
                    }

                    ExchangeRate::create([
                        'currency_id'       => $currency->id,
                        'rate_date'         => $rateDate->format('Y-m-d'),
                        'buy_rate'          => $buyRate,
                        'sell_rate'         => $sellRate,
                        'cbm_rate'          => $cbmRate,
                        'previous_buy_rate' => null,
                        'change_percentage' => null,
                        'market_trend'      => 'stable',
                        'source_name'       => 'CBM Reference',
                        'status'            => 'reference',
                        'is_verified'       => false,
                        'verified_at'       => null,
                        'created_by'        => 1,
                        'updated_by'        => 1,
                        'factors'           => [
                            'fetched_at' => now()->toDateTimeString(),
                            'is_reference' => true,
                            'cbm_rate' => $cbmRate,
                        ],
                    ]);

                    $this->info("✓ Stored reference CBM rate for {$currency->code}: {$cbmRate}");
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
        $this->info('  CBM REFERENCE FETCH SUMMARY');
        $this->line("  ✅ Reference records created: {$results['created']}");
        $this->line("  ❌ Errors:  {$results['errors']}");
        $this->line(str_repeat('-', 20));
        $this->line('  ℹ️  These are REFERENCE rates only. Actual exchange rates come from bank_avg mode.');
    }
}
