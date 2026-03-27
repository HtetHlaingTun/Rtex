<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestCBMEndpoints extends Command
{
    protected $signature = 'cbm:test-endpoints';
    protected $description = 'Test different CBM API endpoints';

    public function handle()
    {
        $endpoints = [
            'https://api.cbm.gov.mm/api/v1/rates',
            'https://forex.cbm.gov.mm/api/latest',
            'https://www.cbm.gov.mm/api/rates',
            'https://cbm.gov.mm/api/rates',
        ];

        $this->info('Testing CBM API endpoints...');
        $this->line('');

        foreach ($endpoints as $endpoint) {
            $this->line("Testing: {$endpoint}");

            try {
                $response = Http::timeout(10)->get($endpoint);

                if ($response->successful()) {
                    $this->info("  ✅ SUCCESS - Status: {$response->status()}");
                    $data = $response->json();
                    $this->line("  Response: " . json_encode(array_keys($data)));
                    $this->line("");
                } else {
                    $this->error("  ❌ FAILED - Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ ERROR - " . $e->getMessage());
            }
        }

        return 0;
    }
}
