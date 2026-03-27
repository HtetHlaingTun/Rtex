<?php


use App\Http\Controllers\CurrencyExchangeRate;
use App\Http\Controllers\GoldPriceController;
use App\Http\Controllers\GoldTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
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

Route::middleware(['auth', 'verified'])->group(function () {
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

Route::middleware(['auth', 'verified'])->group(function () {
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


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
