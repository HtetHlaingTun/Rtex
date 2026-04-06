<?php

use App\Http\Controllers\CurrencyExchangeRate;
use App\Http\Controllers\GoldPriceController;
use App\Http\Controllers\GoldTypeController;
use App\Http\Controllers\ProfileController;
use App\Services\BankAggregatorService;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\DomCrawler\Crawler;

// ============================================
// INCLUDE PUBLIC ROUTES
// ============================================
require base_path('routes/guest.php');

// ============================================
// AUTHENTICATED USER ROUTES
// ============================================
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ============================================
// ADMIN ROUTES (with auth + admin middleware)
// ============================================
Route::middleware(['auth', 'verified', 'is_admin', 'session.timeout'])->group(function () {

    // Currency Management
    Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyExchangeRate::class, 'index'])->name('index');
        Route::get('/refresh', [CurrencyExchangeRate::class, 'refresh'])->name('refresh');
        Route::get('/history/{currency}', [CurrencyExchangeRate::class, 'history'])->name('history');
        Route::get('/factors', [CurrencyExchangeRate::class, 'factors'])->name('factors');
        Route::match(['put', 'post'], '/{currency}/factor', [CurrencyExchangeRate::class, 'updateFactor'])->name('update-factor');
        Route::get('/pending', [CurrencyExchangeRate::class, 'pending'])->name('pending');
        Route::patch('/{rate}/verify', [CurrencyExchangeRate::class, 'verify'])->name('verify');
        Route::delete('/rates/{rate}', [CurrencyExchangeRate::class, 'destroyRate'])->name('destroy-rate');
        Route::get('/settings', [CurrencyExchangeRate::class, 'settings'])->name('settings');
        Route::post('/settings', [CurrencyExchangeRate::class, 'storeType'])->name('store-type');
        Route::delete('/{currency}', [CurrencyExchangeRate::class, 'destroy'])->name('destroy');
    });

    // Gold Price Management
    Route::prefix('gold')->name('gold.')->group(function () {
        Route::get('/gold/index', [GoldPriceController::class, 'index'])->name('index');
        Route::get('/history/{id}', [GoldPriceController::class, 'history'])->name('history');
        Route::get('/create', [GoldPriceController::class, 'create'])->name('create');
        Route::post('/', [GoldPriceController::class, 'store'])->name('store');
        Route::get('/pending', [GoldPriceController::class, 'pending'])->name('pending');
        Route::patch('/{goldPrice}/approve', [GoldPriceController::class, 'approve'])->name('approve');
    });

    // Gold Types Management
    Route::prefix('gold-types')->name('gold-types.')->group(function () {
        Route::get('/create', [GoldTypeController::class, 'create'])->name('create');
        Route::post('/', [GoldTypeController::class, 'store'])->name('store');
        Route::delete('/{goldTypes}', [GoldTypeController::class, 'destroy'])->name('destroy');
    });

    // Gold History API (Admin only)
    Route::get('/api/gold-history', [GoldPriceController::class, 'getGoldHistory'])->name('api.gold-history');
});

// ============================================
// PROFILE ROUTES
// ============================================
Route::middleware(['auth', 'is_admin', 'session.timeout'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// DEBUG ROUTES (Development only)
// ============================================
if (app()->environment('local')) {
    Route::get('/debug-gold', function () {
        $count = \App\Models\GoldPrice::count();
        $latest = \App\Models\GoldPrice::latest()->first();

        return response()->json([
            'count' => $count,
            'latest' => $latest,
            'all' => \App\Models\GoldPrice::latest()->limit(5)->get()
        ]);
    });

    Route::get('/test-scraper', function () {
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
            'timeout' => 10,
            'verify' => false,
        ]);

        try {
            $response = $client->request('GET', 'https://www.yomabank.com/en/rates/');
            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);

            $allData = $crawler->filter('td')->each(function ($node, $i) {
                return "Index {$i}: " . trim($node->text());
            });

            return response()->json([
                'status' => 'Success',
                'instruction' => 'Check preview_data to find the correct index, then update eq()',
                'preview_data' => $allData,
                'test_rate_at_index_2' => $crawler->filter('td')->count() > 2
                    ? $crawler->filter('td')->eq(2)->text()
                    : 'Index 2 not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    Route::get('/test-bank-sync', function (BankAggregatorService $service) {
        set_time_limit(0);
        $service->syncRates();
        return "Sync completed!";
    });
}

// ============================================
// FALLBACK ROUTE
// ============================================
Route::fallback(function () {
    return redirect('/');
});

// ============================================
// AUTHENTICATION ROUTES (Laravel UI)
// ============================================
require __DIR__ . '/auth.php';
require __DIR__ . '/user.php';
