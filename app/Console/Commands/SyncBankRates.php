<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BankAggregatorService;

class SyncBankRates extends Command
{
    protected $signature = 'banks:sync-rates';
    protected $description = 'Sync exchange rates from banks and Yahoo';

    private BankAggregatorService $aggregator;

    public function __construct(BankAggregatorService $aggregator)
    {
        parent::__construct();
        $this->aggregator = $aggregator;
    }

    public function handle()
    {
        $this->info('🔄 Starting exchange rate sync...');

        try {
            $this->aggregator->syncRates();
            $this->info('✅ Sync completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
