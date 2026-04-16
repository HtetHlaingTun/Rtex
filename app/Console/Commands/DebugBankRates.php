<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BankScraperService;

class DebugBankRates extends Command
{
    protected $signature = 'debug:bank-rates {currency=USD}';
    protected $description = 'Debug raw bank rates';

    private BankScraperService $scraper;

    public function __construct(BankScraperService $scraper)
    {
        parent::__construct();
        $this->scraper = $scraper;
    }

    public function handle()
    {
        $currency = $this->argument('currency');

        $this->info("Fetching raw rates for {$currency}...");

        $rates = $this->scraper->fetchAll($currency);

        $this->table(
            ['Bank', 'Buy Rate', 'Sell Rate', 'Mid Rate'],
            collect($rates)->map(fn($r, $bank) => [
                strtoupper($bank),
                number_format($r['buy'] ?? 0, 2),
                number_format($r['sell'] ?? 0, 2),
                number_format((($r['buy'] ?? 0) + ($r['sell'] ?? 0)) / 2, 2),
            ])
        );

        return Command::SUCCESS;
    }
}
