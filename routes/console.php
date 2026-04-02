<?php


use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');







// --- Gold Rates ---
Schedule::command('gold:fetch')
    ->everyTwoMinutes()
    ->appendOutputTo(storage_path('logs/gold_sync.log'));

Schedule::command('gold:save-hourly')
    ->everyTwoMinutes()
    ->appendOutputTo(storage_path('logs/gold_sync.log'));

Schedule::command('gold:consolidate-daily --days-to-keep=1')
    ->everySixHours()

    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/gold-consolidation.log'));

// --- Monthly Stats with Permanent Purge ---
Schedule::command('gold:consolidate-daily --permanent-years=2 --stats')
    ->monthly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/gold-consolidation-monthly.log'));






// --- CBM Fetching ---

Schedule::command('banks:sync-rates')
    ->everyThirtyMinutes()
    ->between('6:00', '22:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/banks-sync.log'));



Schedule::command('cbm:fetch --auto-verify')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cbm-fetch.log'));


// Test CBM connection and clear cache
Schedule::command('cbm:test --clear-cache')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cbm-test.log'));





// --- Cleanup & Maintenance ---
Schedule::command('exchange:consolidate-rates --days-to-keep=1')
    ->everySixHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/exchange-consolidation.log'));



Schedule::command('exchange:consolidate-rates --permanent-years=2 --stats')
    ->monthly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/gold-consolidation-monthly.log'));
