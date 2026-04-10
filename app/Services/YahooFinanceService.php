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

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ])->timeout(10)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            $meta = $data['chart']['result'][0]['meta'] ?? null;

            if ($meta && isset($meta['regularMarketPrice'])) {
                return (float) $meta['regularMarketPrice'];
            }

            if ($meta && isset($meta['previousClose'])) {
                return (float) $meta['previousClose'];
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch USD/{$targetCurrency}: " . $e->getMessage());
        }

        return null;
    }
}
