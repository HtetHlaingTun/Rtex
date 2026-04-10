<?php

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\WorldGoldSnapshot;
use App\Services\YahooFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public Market Pulse Data
Route::get('/market-pulse', function () {
    $snapshot = WorldGoldSnapshot::latest('fetched_at')->first();

    $rates = ExchangeRate::with('currency')
        ->where('status', 'verified')
        ->latest('rate_date')
        ->get()
        ->unique('currency_id')
        ->values(); // Reset array keys for JSON

    return response()->json([
        'status' => 'success',
        'data' => [
            'gold' => $snapshot,
            'currencies' => $rates,
        ],
        'timestamp' => now()->toDateTimeString()
    ]);
})->name('api.market.pulse');


Route::get('/cross-rate-preview/{code}', function ($code) {
    try {
        $currency = Currency::where('code', $code)->first();
        if (!$currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        // Get latest USD/MMK rate
        $usdCurrency = Currency::where('code', 'USD')->first();
        if (!$usdCurrency) {
            return response()->json(['error' => 'USD currency not found'], 404);
        }

        $usdRate = ExchangeRate::where('currency_id', $usdCurrency->id)
            ->latest('rate_date')
            ->first();

        if (!$usdRate) {
            return response()->json(['error' => 'USD rate not found'], 404);
        }

        $usdMmkMid = ($usdRate->buy_rate + $usdRate->sell_rate) / 2;

        // Get USD to target from Yahoo
        $yahooService = new YahooFinanceService();
        $usdToTarget = $yahooService->getUsdToTargetRate($code);

        if (!$usdToTarget || $usdToTarget <= 0) {
            return response()->json(['error' => 'Cross rate not available for ' . $code], 404);
        }

        $baseRate = $usdMmkMid / $usdToTarget;
        $markup = $currency->bank_markup_percentage ?? 2.0;
        $baseRateWithMarkup = $baseRate * (1 + ($markup / 100));

        // Apply spreads
        $buySpread = $currency->buy_spread_percentage ?? 0.5;
        $sellSpread = $currency->sell_spread_percentage ?? 0.5;
        $buyRate = $baseRateWithMarkup * (1 - ($buySpread / 100));
        $sellRate = $baseRateWithMarkup * (1 + ($sellSpread / 100));

        return response()->json([
            'success' => true,
            'currency' => $code,
            'usd_mmk_rate' => round($usdMmkMid, 2),
            'usd_to_target' => $usdToTarget,
            'calculated_rate' => round($baseRate, 4),
            'base_rate_with_markup' => round($baseRateWithMarkup, 2),
            'buy_rate' => round($buyRate, 2),
            'sell_rate' => round($sellRate, 2),
            'markup' => $markup,
            'formula' => "{$usdMmkMid} ÷ {$usdToTarget} = " . round($baseRate, 4)
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
