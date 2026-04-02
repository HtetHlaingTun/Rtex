<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoldPrice;
use App\Models\GoldType;
use App\Models\WorldGoldSnapshot;
use App\Services\CBMRateService;
use Inertia\Inertia;
use Carbon\Carbon;

class WelcomeController extends Controller
{
    protected $cbmService;

    public function __construct(CBMRateService $cbmService)
    {
        $this->cbmService = $cbmService;
    }

    public function index()
    {
        // Get all active currencies
        $currencies = Currency::where('is_active', true)->get();

        $latestRates = $currencies->map(function ($currency) {
            $current = ExchangeRate::where('currency_id', $currency->id)
                ->where('is_verified', true)
                ->where('status', 'verified')
                ->latest('created_at')
                ->first();

            if (!$current) {
                return null;
            }

            $previous = ExchangeRate::where('currency_id', $currency->id)
                ->where('is_verified', true)
                ->where('id', '<', $current->id)
                ->latest('created_at')
                ->first();

            return [
                'id' => $current->id,
                'created_at' => $current->created_at->toIso8601String(),
                'currency' => [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'name' => $currency->name,
                    'symbol' => $currency->symbol,
                    'bank_markup' => (float) ($currency->bank_markup_percentage ?? 0),
                ],
                'buy_rate' => (float) $current->buy_rate,
                'sell_rate' => (float) $current->sell_rate,
                'prev_buy_rate' => $previous ? (float) $previous->buy_rate : (float) $current->buy_rate,
                'prev_sell_rate' => $previous ? (float) $previous->sell_rate : (float) $current->sell_rate,
                'cbm_rate' => (float) $current->cbm_rate,
                'change_percentage' => (float) ($current->change_percentage ?? 0),
                'market_trend' => $current->market_trend ?? 'stable',
                'avg_bank_rate' => (float) ($currency->avg_bank_rate ?? 0),
                'spread' => (float) ($current->sell_rate - $current->buy_rate),
            ];
        })->filter()->values();

        // Get 7-day old snapshot for momentum calculation
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $sevenDayOldSnapshot = WorldGoldSnapshot::where('fetched_at', '>=', $sevenDaysAgo)
            ->orderBy('fetched_at', 'asc')
            ->first();

        // Gold Logic
        $goldSnapshots = WorldGoldSnapshot::orderBy('created_at', 'desc')->take(2)->get();
        $latestGold = $goldSnapshots->first();
        $previousGold = $goldSnapshots->last();

        // world gold 
        $goldTypes = GoldType::with(['latestVerifiedPrice', 'prices' => function ($q) {
            $q->latest('price_date')->limit(1);
        }])->get();

        // Count pending gold prices
        $pendingGoldCount = GoldPrice::where('status', 'pending')->count();

        // Get latest world gold snapshot
        $worldGoldSnapshot = WorldGoldSnapshot::latest('fetched_at')->first();

        // Get latest SGD rate from the WorldGoldSnapshot
        $sgdRate = null;
        if ($worldGoldSnapshot && $worldGoldSnapshot->usd_sgd_rate) {
            $sgdRate = [
                'usd_sgd_rate' => $worldGoldSnapshot->usd_sgd_rate,
                'sgd_price' => $worldGoldSnapshot->sgd_price,
                'change' => $worldGoldSnapshot->change,
                'change_percent' => $worldGoldSnapshot->change_percent,
                'fetched_at' => $worldGoldSnapshot->fetched_at,
            ];
        }

        return Inertia::render('Welcome', [
            'breadcrumbs' => [
                ['label' => 'Home', 'route' => 'welcome']
            ],
            'canLogin' => true,
            'canRegister' => true,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
            'rates' => $latestRates,
            'system_info' => [
                'cbm_available' => $this->cbmService->isAvailable(),
                'last_sync' => ExchangeRate::latest('created_at')->first()?->created_at?->format('H:i:s') ?? 'Never',
                'active_currencies' => $currencies->count(),
                'calculation_method' => 'Bank Average with Markup',
                'active_banks' => 4,
            ],
            'gold' => $latestGold ? [
                'usd_mmk_rate' => $latestGold->usd_mmk_rate,
                'mmk_price_new' => $latestGold->mmk_price_new,
                'mmk_price_old' => $latestGold->mmk_price_old,
                'world_gold_price' => $latestGold->usd_price,
                'usd_price' => $latestGold->usd_price,
                'sgd_price' => $latestGold->sgd_price,
                'usd_sgd_rate' => $latestGold->usd_sgd_rate,
                'prev_mmk_price_new' => $previousGold?->mmk_price_new,
                'prev_mmk_price_old' => $previousGold?->mmk_price_old,
                'prev_world_gold_price' => $previousGold?->usd_price,
                'prev_sgd_price' => $previousGold?->sgd_price,
                'seven_day_old_price' => $sevenDayOldSnapshot?->usd_price, // Add 7-day old price
                'fetched_at' => $latestGold->fetched_at,
                'updated_at' => $latestGold->updated_at,
            ] : null,
            'goldTypes' => $goldTypes,
            'pending_gold_count' => $pendingGoldCount,
            'worldGoldSnapshot' => $worldGoldSnapshot,
            'sgdRate' => $sgdRate,
        ]);
    }
}
