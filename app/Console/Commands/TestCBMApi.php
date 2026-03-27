<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CBMRateService;

class TestCBMApi extends Command
{
    protected $signature = 'cbm:test 
                            {--currency= : Test specific currency}
                            {--clear-cache : Clear cache before testing}';

    protected $description = 'Test CBM API connection and display rates';

    protected $cbmService;

    public function __construct(CBMRateService $cbmService)
    {
        parent::__construct();
        $this->cbmService = $cbmService;
    }

    public function handle()
    {
        if ($this->option('clear-cache')) {
            $this->cbmService->clearCache();
            $this->info('Cache cleared');
            $this->line('');
        }

        $this->info('Testing CBM API Connection...');
        $this->line('');

        $available = $this->cbmService->isAvailable();
        $status = $available ? '✅ Available' : '❌ Unavailable';
        $this->line("API Status: {$status}");

        if (!$available) {
            $this->error('CBM API is not available.');
            return 1;
        }

        $this->info('Fetching exchange rates...');
        $rates = $this->cbmService->fetchCurrentRates();

        if (empty($rates)) {
            $this->error('No rates returned from API');
            return 1;
        }

        $currency = $this->option('currency');

        if ($currency) {
            $currency = strtoupper($currency);
            if (isset($rates[$currency])) {
                $this->displayCurrencyDetails($currency, $rates[$currency]);
            } else {
                $this->error("Currency {$currency} not found");
                $this->line('Available: ' . implode(', ', array_keys($rates)));
            }
        } else {
            $this->displayAllRates($rates);
        }

        return 0;
    }

    protected function displayAllRates($rates)
    {
        $headers = ['Currency', 'CBM Rate', 'Factor', 'Working Rate', 'Buy Rate', 'Sell Rate'];
        $rows = [];

        foreach ($rates as $code => $data) {
            $rows[] = [
                $code,
                number_format($data['cbm_rate'], 2),
                number_format($data['conversion_factor'], 6),
                number_format($data['working_rate'], 2),
                number_format($data['buy_rate'], 2),
                number_format($data['sell_rate'], 2),
            ];
        }

        $this->table($headers, $rows);
    }

    protected function displayCurrencyDetails($code, $data)
    {
        $this->info("Rates for {$code}:");
        $this->line("  CBM Rate: " . number_format($data['cbm_rate'], 4));
        $this->line("  Conversion Factor: " . number_format($data['conversion_factor'], 6));
        $this->line("  Working Rate: " . number_format($data['working_rate'], 4));
        $this->line("  Buy Rate: " . number_format($data['buy_rate'], 4));
        $this->line("  Sell Rate: " . number_format($data['sell_rate'], 4));
        if (isset($data['api_timestamp'])) {
            $this->line("  API Timestamp: {$data['api_timestamp']}");
        }
        $this->line("  Fetched At: {$data['fetched_at']}");
    }
}
