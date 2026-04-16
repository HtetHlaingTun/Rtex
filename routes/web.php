<?php

use App\Helpers\BreadcrumbHelper;
use App\Http\Controllers\AdminBlogController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyExchangeRate;
use App\Http\Controllers\GoldPageController;
use App\Http\Controllers\GoldPriceController;
use App\Http\Controllers\GoldTypeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\TestEmailController;
use App\Http\Controllers\User\AlertController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\UserAssetController;
use App\Http\Controllers\User\WatchlistController;
use App\Models\BlogPost;
use App\Models\Currency;
use App\Services\BankAggregatorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\DomCrawler\Crawler;

// Google ads 
Route::get('/ads.txt', function () {
    return response("google.com, pub-4513869151029765, DIRECT, f08c47fec0942fa0", 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('/api/cross-rate-preview/{currency}', function ($currencyCode) {
    try {
        Log::info('Cross rate preview requested for: ' . $currencyCode);

        // Get USD rate from the exchange_rates table directly
        $usdCurrency = App\Models\Currency::where('code', 'USD')->first();
        if (!$usdCurrency) {
            return response()->json(['success' => false, 'message' => 'USD currency not found']);
        }

        $usdRate = App\Models\ExchangeRate::where('currency_id', $usdCurrency->id)
            ->where('is_verified', true)
            ->latest('rate_date')
            ->first();

        if (!$usdRate) {
            return response()->json(['success' => false, 'message' => 'USD rate not found']);
        }

        $usdMmkRate = ($usdRate->buy_rate + $usdRate->sell_rate) / 2;

        // Get Yahoo rate
        $yahoo = app(App\Services\YahooFinanceService::class);
        $usdToTarget = $yahoo->getUsdToTargetRate($currencyCode);

        if ($usdMmkRate > 0 && $usdToTarget && $usdToTarget > 0) {
            $calculatedRate = $usdMmkRate / $usdToTarget;

            return response()->json([
                'success' => true,
                'usd_mmk_rate' => round($usdMmkRate, 2),
                'usd_to_target' => round($usdToTarget, 4),
                'calculated_rate' => round($calculatedRate, 2),
                'formula' => round($usdMmkRate, 2) . " ÷ " . round($usdToTarget, 4) . " = " . round($calculatedRate, 2)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to calculate cross rate',
            'usd_mmk_rate' => $usdMmkRate,
            'usd_to_target' => $usdToTarget
        ]);
    } catch (\Exception $e) {
        Log::error('Cross rate API error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
});


Route::get('/rates', [RateController::class, 'index'])->name('rates.index');



Route::get('gold/index', [GoldPageController::class, 'index'])->name('goldPage.index');


Route::post('/subscribe', [NewsletterController::class, 'subscribe'])->name('subscribe');
Route::get('/unsubscribe/{email}', [NewsletterController::class, 'unsubscribe'])
    ->name('unsubscribe');

Route::get('/og-image', function () {
    return view('og-image');
});
Route::get('/terms', function () {
    return Inertia::render('TermOfServices', [
        'breadcrumbs' => BreadcrumbHelper::generate('Term Of Services')
    ]);
})->name('terms');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');


Route::get('/blog/{slug}/fb', function ($slug) {
    $post = BlogPost::where('slug', $slug)->where('is_published', true)->firstOrFail();

    $image = $post->featured_image
        ? (filter_var($post->featured_image, FILTER_VALIDATE_URL)
            ? $post->featured_image
            : asset($post->featured_image))
        : asset('default-og-image.jpg');

    return response()->view('facebook-share', [
        'post' => $post,
        'metaTitle' => $post->meta_title ?? $post->title,
        'metaDescription' => $post->meta_description ?? $post->excerpt,
        'metaImage' => $image,
        'metaUrl' => url()->current(),
    ]);
});


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

    return Inertia::render('Privacy', [
        'breadcrumbs' => BreadcrumbHelper::generate('Privacy')
    ]);
})->name('privacy');

// Public routes
Route::get('/contact', function () {
    return Inertia::render('Contact', [
        'breadcrumbs' => BreadcrumbHelper::generate('Contact')
    ]);
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
            $response = $client->request('GET', 'https://www.kbzbank.com/en/');
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






Route::get('/test-yahoo-direct', function () {
    // All currencies you want to fetch - easily expandable
    $currencies = [
        'USD',
        'SGD',
        'EUR',
        'THB',  // Your existing ones
        'JPY',
        'CNY',
        'NZD',         // New ones you requested
        'AUD',
        'CAD',
        'CHF',
        'GBP',  // Additional major currencies
        'MYR',
        'INR',
        'KRW',
        'HKD'   // Asian currencies
    ];

    $results = [];

    foreach ($currencies as $currency) {
        // Yahoo Finance symbol format: CURRENCYMMK=X
        $symbol = $currency . 'MMK=X';
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}";

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ],
                'timeout' => 10
            ]);

            $data = json_decode($response->getBody(), true);

            // Extract the current price from the response
            $result = $data['chart']['result'][0] ?? null;
            $meta = $result['meta'] ?? null;
            $quote = $result['indicators']['quote'][0] ?? null;

            if ($meta && $quote) {
                $currentPrice = $meta['regularMarketPrice'] ?? ($quote['close'][0] ?? null);
                $previousClose = $meta['previousClose'] ?? null;
                $change = $currentPrice ? $currentPrice - ($previousClose ?? $currentPrice) : null;
                $changePercent = ($previousClose && $change) ? ($change / $previousClose) * 100 : null;

                $results[$currency] = [
                    'symbol' => $symbol,
                    'rate' => $currentPrice,
                    'previous_close' => $previousClose,
                    'change' => $change,
                    'change_percent' => $changePercent,
                    'market_trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
                    'fetched_at' => now()->toIso8601String(),
                    'success' => true
                ];
            } else {
                $results[$currency] = [
                    'success' => false,
                    'error' => 'Invalid response structure',
                    'symbol' => $symbol
                ];
            }
        } catch (\Exception $e) {
            $results[$currency] = [
                'success' => false,
                'error' => $e->getMessage(),
                'symbol' => $symbol
            ];
        }

        // Add delay to avoid rate limiting
        usleep(300000); // 0.3 second delay (faster now)
    }

    // Summary statistics
    $successCount = count(array_filter($results, fn($r) => isset($r['success']) && $r['success'] === true));
    $failCount = count($currencies) - $successCount;

    return response()->json([
        'success' => true,
        'source' => 'Yahoo Finance',
        'fetched_at' => now()->toIso8601String(),
        'summary' => [
            'total' => count($currencies),
            'successful' => $successCount,
            'failed' => $failCount
        ],
        'rates' => $results
    ]);
});

Route::get('/test-brevo', [TestEmailController::class, 'sendTestEmail']);




Route::get('/final-fuel-test', function () {
    // 1. Get USD data from the currencies table
    $usd = DB::table('currencies')->where('code', 'USD')->first();

    if (!$usd) {
        return response()->json(['error' => 'USD not found in currencies table']);
    }

    // Use Bank Avg + your Markup percentage to get the 'Real' market rate
    // Formula: Bank Rate * (1 + (Markup / 100))
    $usdMmkRate = $usd->avg_bank_rate * (1 + ($usd->bank_markup_percentage / 100));

    // 2. Fetch Live Global Gas Price
    $apiKey = env('COMMODITY_API_KEY');
    $response = Http::withHeaders(['x-api-key' => $apiKey])
        ->get('https://api.commoditypriceapi.com/v2/rates/latest', [
            'symbols' => 'RB-SPOT'
        ]);

    if ($response->failed()) return $response->json();

    $globalPrice = $response->json()['data']['rates']['RB-SPOT'] ?? $response->json()['rates']['RB-SPOT'];

    // 3. Math Calibration
    $usdPerLiter = $globalPrice / 3.785;
    $baseMmk = $usdPerLiter * $usdMmkRate;

    // Using your 1.171 multiplier for Yangon 4735 target
    $ygn92 = round(($baseMmk * 1.171) / 5) * 5;

    return response()->json([
        'status' => 'SUCCESS',
        'usd_details' => [
            'base_bank_rate' => $usd->avg_bank_rate,
            'markup_applied' => $usd->bank_markup_percentage . '%',
            'final_calculated_usd_rate' => round($usdMmkRate, 2)
        ],
        'global_gas_usd' => $globalPrice,
        'yangon' => [
            '92' => $ygn92,
            '95' => $ygn92 + 210,
        ],
        'ayeyarwady' => [
            '92' => $ygn92 + 95,
            '95' => $ygn92 + 310,
        ]
    ]);
});
