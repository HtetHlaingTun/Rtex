<?php


use App\Http\Controllers\CurrencyExchangeRate;
use App\Http\Controllers\GoldPriceController;
use App\Http\Controllers\GoldTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




// user side 
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/history/{currencies}', [CurrencyExchangeRate::class, 'guestHistory'])->name('history');
Route::get('/gold-history/{type}', [GoldPriceController::class, 'publicGoldHistory'])->name('public.gold.history')->where('type', 'new_system|traditional|world_oz');

Route::get('/gold/chart-data/{id}', [GoldPriceController::class, 'chartData'])->name('gold.chart');
Route::get('/history/{currency}/chart-data', [CurrencyExchangeRate::class, 'getChartData'])->name('history.chart-data');


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/debug-gold', function () {
    $count = \App\Models\GoldPrice::count();
    $latest = \App\Models\GoldPrice::latest()->first();

    return response()->json([
        'count' => $count,
        'latest' => $latest,
        'all' => \App\Models\GoldPrice::latest()->limit(5)->get()
    ]);
});


// admin side 

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Main rates display
    Route::get('/currencies', [CurrencyExchangeRate::class, 'index'])->name('currencies.index');
    Route::get('/currencies/refresh', [CurrencyExchangeRate::class, 'refresh'])->name('currencies.refresh');
    Route::get('/currencies/history/{currency}', [CurrencyExchangeRate::class, 'history'])->name('currencies.history');

    // Factor management
    Route::get('/currencies/factors', [CurrencyExchangeRate::class, 'factors'])->name('currencies.factors');
    Route::match(['put', 'post'], '/currencies/{currency}/factor', [CurrencyExchangeRate::class, 'updateFactor'])
        ->name('currencies.update-factor');

    // Pending rates verification
    Route::get('/currencies/pending', [CurrencyExchangeRate::class, 'pending'])->name('currencies.pending');
    Route::patch('/currencies/{rate}/verify', [CurrencyExchangeRate::class, 'verify'])->name('currencies.verify');
    Route::delete('/currencies/rates/{rate}', [CurrencyExchangeRate::class, 'destroyRate'])->name('currencies.destroy-rate');

    // Currency management
    Route::get('/settings/currencies', [CurrencyExchangeRate::class, 'settings'])->name('currencies.settings');
    Route::post('/settings/currencies', [CurrencyExchangeRate::class, 'storeType'])->name('currencies.store-type');
    Route::delete('/settings/currencies/{currency}', [CurrencyExchangeRate::class, 'destroy'])->name('currencies.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/gold/index', [GoldPriceController::class, 'index'])->name('gold.index');
    Route::get('/gold/history/{id}', [GoldPriceController::class, 'history'])->name('gold.history');
    Route::get('/gold/create', [GoldPriceController::class, 'create'])->name('gold.create');
    Route::post('/gold', [GoldPriceController::class, 'store'])->name('gold.store');

    Route::get('/gold/pending', [GoldPriceController::class, 'pending'])->name('gold.pending');
    Route::patch('/gold/{goldPrice}/approve', [GoldPriceController::class, 'approve'])->name('gold.approve');
    // api -----------------------------------
    Route::get('/api/live-gold', [GoldPriceController::class, 'getLiveRate'])->name('api.live-gold');
    Route::get('/api/gold-history', [GoldPriceController::class, 'getGoldHistory'])->name('api.gold-history');

    Route::get('/gold-types/create', [GoldTypeController::class, 'create'])->name('gold-types.create');
    Route::post('/gold-types', [GoldTypeController::class, 'store'])->name('gold-types.store');
    Route::delete('/gold-types/{goldTypes}', [GoldTypeController::class, 'destroy'])->name('gold-types.destroy');
});


Route::middleware('auth', 'admin')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::fallback(function () {
    return redirect('/');
});



Route::get('/debug-uob-raw', function () {
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0 Safari/537.36',
        'Accept' => 'text/html',
    ])->get('https://www.uob.com.sg/personal/invest/gold-and-precious-metals/gold-and-silver-prices.page');

    // If this says 403 or 429, you are being blocked by their Firewall.
    if ($response->failed()) {
        return "Status: " . $response->status() . " - You are blocked by the bank's firewall.";
    }

    $html = $response->body();

    // Check if the word 'Gold' even exists in the HTML they sent you
    $exists = str_contains($html, 'Gold Bullion') ? 'YES' : 'NO';

    return [
        'contains_data' => $exists,
        'html_length' => strlen($html),
        'preview' => substr(e($html), 0, 1000) // Look at this in your browser
    ];
});
Route::get('/test-sgd-final', function () {
    try {
        // 1. Get Gold USD (The one you said runs smoothly)
        $goldResponse = Http::withHeaders(['User-Agent' => 'Mozilla/5.0...'])
            ->get('https://query1.finance.yahoo.com/v8/finance/chart/GC=F');

        // 2. Get USD/SGD Rate (Standard Forex is usually 200 OK)
        $rateResponse = Http::withHeaders(['User-Agent' => 'Mozilla/5.0...'])
            ->get('https://query1.finance.yahoo.com/v8/finance/chart/USDSGD=X');

        if (!$goldResponse->successful() || !$rateResponse->successful()) {
            return "One source failed. Gold: " . $goldResponse->status() . " Rate: " . $rateResponse->status();
        }

        $priceUsd = $goldResponse->json('chart.result.0.meta.regularMarketPrice');
        $usdSgd = $rateResponse->json('chart.result.0.meta.regularMarketPrice');

        $priceSgd = $priceUsd * $usdSgd;

        return [
            'status' => 'Success',
            'gold_usd' => $priceUsd,
            'exchange_rate' => $usdSgd,
            'calculated_gold_sgd' => round($priceSgd, 2),
            'per_gram_sgd' => round($priceSgd / 31.1035, 2)
        ];
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
require __DIR__ . '/auth.php';
