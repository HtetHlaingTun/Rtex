<?php

use App\Http\Controllers\CurrencyExchangeRate;
use App\Http\Controllers\GoldPriceController;
use App\Http\Controllers\GoldTypeController;
use App\Http\Controllers\ProfileController;
use App\Services\BankAggregatorService;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\UserAssetController;
use App\Http\Controllers\User\WatchlistController;
use App\Http\Controllers\User\AlertController;
use App\Http\Controllers\User\NotificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\DomCrawler\Crawler;







// Public pages
Route::get('/privacy', function () {
    return Inertia::render('Privacy');
})->name('privacy');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    // Send email or store in database
    \Illuminate\Support\Facades\Mail::raw(
        "From: {$validated['name']} ({$validated['email']})\n\n{$validated['message']}",
        function ($message) {
            $message->to('support@luckeymm.online')
                ->subject('Contact Form: ' . request('subject'));
        }
    );

    return back()->with('success', 'Message sent successfully!');
})->name('contact.store');

// ============================================
// INCLUDE PUBLIC ROUTES
// ============================================
require base_path('routes/guest.php');

// ============================================
// AUTHENTICATED USER ROUTES (Breeze/Inertia)
// ============================================
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ============================================
// USER ASSET ROUTES (for logged-in users)
// ============================================
Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('assets', UserAssetController::class);
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
    Route::post('/watchlist/bulk-destroy', [WatchlistController::class, 'bulkDestroy'])->name('watchlist.bulk-destroy');

    // Alert routes
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts', [AlertController::class, 'store'])->name('alerts.store');
    Route::put('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
    Route::patch('/alerts/{alert}/toggle', [AlertController::class, 'toggle'])->name('alerts.toggle');
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');
    Route::post('/alerts/bulk-destroy', [AlertController::class, 'bulkDestroy'])->name('alerts.bulk-destroy');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
});

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

    // Profile routes (admin)
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
// AUTHENTICATION ROUTES - ONLY ONE!
// ============================================
// IMPORTANT: Use ONLY ONE of these:

// OPTION A: For Laravel UI (Bootstrap) - Remove this if using Breeze
// require __DIR__ . '/auth.php';

// OPTION B: For Laravel Breeze (Inertia) - This is what you should use with Vue
require __DIR__ . '/auth.php';

// ============================================
// FALLBACK ROUTE
// ============================================
Route::fallback(function () {
    return redirect('/');
});
