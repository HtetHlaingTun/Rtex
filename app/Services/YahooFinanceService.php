<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooFinanceService
{
    public function getUsdToTargetRate($targetCurrency)
    {
        try {
            $symbol = "USD{$targetCurrency}=X";
            $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}";

            $response = Http::withOptions([
                'verify' => false,  // Disable SSL verification for local testing
                'timeout' => 10,
                'allow_redirects' => true,
            ])->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ])->get($url);

            Log::info("Yahoo API response for {$symbol}: Status " . $response->status());

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            // Check if result exists
            if (!isset($data['chart']['result'][0])) {
                Log::warning("No chart result for {$symbol}");
                return null;
            }

            $meta = $data['chart']['result'][0]['meta'] ?? null;

            if ($meta && isset($meta['regularMarketPrice'])) {
                return (float) $meta['regularMarketPrice'];
            }

            if ($meta && isset($meta['previousClose'])) {
                return (float) $meta['previousClose'];
            }

            Log::warning("No price data for {$symbol}");
            return null;
        } catch (\Exception $e) {
            Log::error("Failed to fetch USD/{$targetCurrency}: " . $e->getMessage());
            return null;
        }
    }
}
