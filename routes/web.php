<?php

use App\Http\Controllers\AdminBlogController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyExchangeRate;
use App\Http\Controllers\GoldPriceController;
use App\Http\Controllers\GoldTypeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\AlertController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\UserAssetController;
use App\Http\Controllers\User\WatchlistController;
use App\Services\BankAggregatorService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\DomCrawler\Crawler;




Route::post('/subscribe', [NewsletterController::class, 'subscribe'])->name('subscribe');

Route::get('/og-image', function () {
    return view('og-image');
});
Route::get('/terms', function () {
    return Inertia::render('TermOfServices');
})->name('terms');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/generate-og-image', function () {
    // Create a simple image using GD (built into PHP, no package needed)
    $width = 1200;
    $height = 630;

    // Create image
    $image = imagecreatetruecolor($width, $height);

    // Colors
    $bgColor = imagecolorallocate($image, 26, 26, 46); // #1a1a2e
    $accentColor = imagecolorallocate($image, 245, 158, 11); // #f59e0b
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $subColor = imagecolorallocate($image, 136, 136, 136); // #888888

    // Fill background
    imagefill($image, 0, 0, $bgColor);

    // Add border
    imagerectangle($image, 10, 10, $width - 10, $height - 10, $accentColor);

    // Add main text
    $text = "MMRatePro";
    $fontSize = 5; // Built-in font size (1-5)
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    $x = ($width - $textWidth) / 2;
    $y = 280;
    imagestring($image, $fontSize, $x, $y, $text, $accentColor);

    // Add subtitle
    $subtitle = "Real-time Exchange Rates & Gold Prices";
    $subWidth = imagefontwidth(4) * strlen($subtitle);
    $subX = ($width - $subWidth) / 2;
    $subY = 380;
    imagestring($image, 4, $subX, $subY, $subtitle, $textColor);

    // Add domain
    $domain = "luckeymm.online";
    $domainWidth = imagefontwidth(3) * strlen($domain);
    $domainX = ($width - $domainWidth) / 2;
    $domainY = 520;
    imagestring($image, 3, $domainX, $domainY, $domain, $subColor);

    // Save the image
    $path = public_path('default-og-image.jpg');
    imagejpeg($image, $path, 90);
    imagedestroy($image);

    return response()->json([
        'success' => true,
        'message' => 'OG image created at: ' . $path,
        'url' => url('/default-og-image.jpg')
    ]);
});


// Public pages
Route::get('/privacy', function () {
    return Inertia::render('Privacy');
})->name('privacy');

// Public routes
Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Admin routes (add to your existing admin group)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::patch('/contacts/{contact}/mark-replied', [ContactController::class, 'markReplied'])->name('contacts.mark-replied');
});

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

    // Blog Management
    Route::get('/admin/blog', [AdminBlogController::class, 'index'])->name('admin.blog.index');
    Route::get('/admin/blog/create', [AdminBlogController::class, 'create'])->name('admin.blog.create');
    Route::post('/admin/blog', [AdminBlogController::class, 'store'])->name('admin.blog.store');
    Route::get('/admin/blog/{blog}/edit', [AdminBlogController::class, 'edit'])->name('admin.blog.edit');
    Route::put('/admin/blog/{blog}', [AdminBlogController::class, 'update'])->name('admin.blog.update');
    Route::delete('/admin/blog/{blog}', [AdminBlogController::class, 'destroy'])->name('admin.blog.destroy');
    Route::patch('/admin/blog/{blog}/toggle-publish', [AdminBlogController::class, 'togglePublish'])->name('admin.blog.toggle-publish');
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
