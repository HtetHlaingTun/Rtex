<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\WorldGoldSnapshot;
use App\Models\Currency;
use App\Services\CBMRateService;

use Inertia\Inertia;

class WelcomeController extends Controller
{
    protected $cbmService;

    public function __construct(CBMRateService $cbmService)
    {
        $this->cbmService = $cbmService;
    }

    public function index()
    {
        // 1. Get all active currencies
        $currencies = Currency::where('is_active', true)->get();

        // 2. Fetch the latest 2 rates for EACH currency to calculate trend
        $latestRates = $currencies->map(function ($currency) {
            $history = ExchangeRate::where('currency_id', $currency->id)
                ->where('is_verified', true)
                ->orderBy('created_at', 'desc')
                ->take(2)
                ->get();

            $current = $history->first();
            $previous = $history->count() > 1 ? $history->last() : $current;

            if (!$current) return null;

            $factors = is_array($current->factors)
                ? $current->factors
                : json_decode($current->factors, true) ?? [];

            return [
                'id' => $current->id,
                'created_at' => $current->created_at->toIso8601String(),
                'currency' => [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'name' => $currency->name,
                    'symbol' => $currency->symbol,
                ],
                'buy_rate' => $current->buy_rate,
                'sell_rate' => $current->sell_rate,
                // These are CRITICAL for the TrendIcon to match History
                'prev_buy_rate' => $previous->buy_rate,
                'prev_sell_rate' => $previous->sell_rate,

                'cbm_rate' => $current->cbm_rate,
                'change_percentage' => $current->change_percentage,
                'market_trend' => $current->market_trend,
            ];
        })->filter()->values();

        // 3. Gold Logic (Keeping your logic but ensuring 2 snapshots for trend)
        $goldSnapshots = WorldGoldSnapshot::orderBy('created_at', 'desc')->take(2)->get();
        $latestGold = $goldSnapshots->first();
        $previousGold = $goldSnapshots->last();

        return Inertia::render('Welcome', [
            'breadcrumbs' => [
                ['label' => 'Home', 'route' => 'welcome']
            ],

            'canLogin' => true,
            'canRegister' => true,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
            'rates' => $latestRates,

            'gold' => $latestGold ? [
                'usd_mmk_rate' => $latestGold->usd_mmk_rate,
                'mmk_price_new' => $latestGold->mmk_price_new,
                'mmk_price_old' => $latestGold->mmk_price_old,
                'world_gold_price' => $latestGold->usd_price,
                'usd_price' => $latestGold->usd_price,

                'sgd_price' => $latestGold->sgd_price,
                'usd_sgd_rate' => $latestGold->usd_sgd_rate,
                // trend data
                'prev_mmk_price_new' => $previousGold?->mmk_price_new,
                'prev_mmk_price_old' => $previousGold?->mmk_price_old,
                'prev_world_gold_price' => $previousGold?->usd_price,
                'prev_sgd_price' => $previousGold?->sgd_price,
            ] : null,
        ]);
    }
}
