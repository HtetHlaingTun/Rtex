<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class MetaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        Inertia::share('meta', function () {
            return request()->route()->getAction('meta') ?? [
                'title' => 'MMRatePro - Live Exchange Rates & Gold Prices',
                'description' => 'Real-time USD, SGD, EUR, THB exchange rates to MMK. Live gold prices in Myanmar kyat.',
                'image' => url('/default-og-image.jpg'),
                'url' => url()->current(),
            ];
        });
    }
}
