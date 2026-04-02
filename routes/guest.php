<?php

use App\Http\Controllers\CurrencyExchangeRate;
use App\Http\Controllers\GoldPriceController;
use App\Http\Controllers\WelcomeController;

use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';
// ============================================
// PUBLIC ROUTES - No authentication required
// ============================================

// Home page
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// ============================================
// PUBLIC API ENDPOINTS
// ============================================

// Live gold rate API (used by frontend)
Route::get('/api/live-gold', [GoldPriceController::class, 'getLiveRate'])->name('api.live-gold');

// Gold history API (public)
Route::get('/api/gold-history', [GoldPriceController::class, 'getGoldHistory'])->name('api.gold-history');

// ============================================
// GOLD HISTORY PAGES
// ============================================

// Individual gold type page (for international markets)
Route::get('/gold/{goldType}', [GoldPriceController::class, 'publicGoldType'])
    ->name('gold.public-history');

// System-based gold history (new_system, traditional, world_oz)
// This route handles both 'user.gold.history' and 'public.gold.history' names
Route::get('/gold-history/{type}', [GoldPriceController::class, 'publicGoldHistory'])
    ->name('user.gold.history')
    ->where('type', 'new_system|traditional|world_oz');

// Chart data for gold types
Route::get('/gold/chart-data/{id}', [GoldPriceController::class, 'chartData'])->name('gold.chart');

// ============================================
// PUBLIC EXCHANGE RATES
// ============================================

// Public exchange rate history
Route::get('/history/{currencies}', [CurrencyExchangeRate::class, 'guestHistory'])->name('history');

// Exchange rate chart data
Route::get('/history/{currency}/chart-data', [CurrencyExchangeRate::class, 'getChartData'])->name('history.chart-data');
