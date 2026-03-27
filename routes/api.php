<?php

use App\Models\ExchangeRate;
use App\Models\WorldGoldSnapshot;
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
