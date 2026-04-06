<?php

use App\Http\Controllers\User\AlertController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserAssetController;
use App\Http\Controllers\User\WatchlistController;
use Illuminate\Support\Facades\Route;




Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('assets', UserAssetController::class);

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [ProfileController::class, 'updateProfilePhoto'])->name('profile.photo');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Two-factor route
    Route::put('/profile/two-factor', [ProfileController::class, 'updateTwoFactor'])->name('profile.two-factor');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

    // Asset routes
    Route::get('/assets', [UserAssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [UserAssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [UserAssetController::class, 'store'])->name('assets.store');
    Route::delete('/assets/{asset}', [UserAssetController::class, 'destroy'])->name('assets.destroy');

    // Watchlist routes
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
    // Put clear-all BEFORE the {notification} route
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::patch('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});


require __DIR__ . '/auth.php';
