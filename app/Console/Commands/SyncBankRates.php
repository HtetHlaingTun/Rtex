<?php

namespace App\Console\Commands;

use App\Services\BankAggregatorService;
use Illuminate\Console\Command;

class SyncBankRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:sync-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update exchange rates from Myanmar private banks';

    /**
     * Execute the console command.
     */
    public function handle(BankAggregatorService $service): void
    {
        $this->info('Starting bank rate synchronization...');

        try {
            $service->syncRates();
            $this->info('Success: All bank rates have been updated.');
        } catch (\Exception $e) {
            $this->error('Failed to sync rates: ' . $e->getMessage());
        }
    }
}
