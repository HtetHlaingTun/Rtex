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

        // Get your local bank USD rate (for display)
        $usdCurrency = Currency::where('code', 'USD')->first();
        $localUsdSellRate = 0;
        $usdMmkMidRate = 0;

        if ($usdCurrency) {
            $usdRate = ExchangeRate::where('currency_id', $usdCurrency->id)
                ->where('is_verified', true)
                ->latest('created_at')
                ->first();

            if ($usdRate) {
                $localUsdSellRate = (float) $usdRate->sell_rate;
                $usdMmkMidRate = ($usdRate->buy_rate + $usdRate->sell_rate) / 2;
            }
        }

        // For GLOBAL REFERENCE, use a realistic market USD/MMK rate
        // This should be higher than bank rates to reflect black market
        // You can make this configurable in your admin panel
        $marketUsdRate = 4587; // Set this to your desired reference rate

        // Or calculate it dynamically: local USD rate + 7% markup
        // $marketUsdRate = $localUsdSellRate * 1.067; // 4,298 × 1.067 = 4,587

        // Initialize Yahoo service for global cross rates
        $yahooService = new \App\Services\YahooFinanceService();

        $latestRates = $currencies->map(function ($currency) use ($localUsdSellRate, $marketUsdRate, $yahooService) {
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

            $code = $currency->code;
            $localSellRate = (float) $current->sell_rate;

            // Calculate GLOBAL REFERENCE using the MARKET USD rate (not local bank rate)
            $globalReference = $localSellRate; // Default to local rate

            if ($code === 'USD') {
                // For USD, global reference is the market rate (4,587)
                $globalReference = $marketUsdRate;
            } else {
                // Get USD cross rate from Yahoo
                $usdToTarget = $yahooService->getUsdToTargetRate($code);
                if ($usdToTarget && $usdToTarget > 0 && $marketUsdRate > 0) {
                    // Calculate using MARKET USD rate, not local bank rate
                    $globalReference = $marketUsdRate / $usdToTarget;
                }
            }

            return [
                'id' => $current->id,
                'created_at' => $current->created_at->toIso8601String(),
                'currency' => [
                    'id' => $currency->id,
                    'code' => $code,
                    'name' => $currency->name,
                    'symbol' => $currency->symbol,
                    'bank_markup' => (float) ($currency->bank_markup_percentage ?? 0),
                ],
                'buy_rate' => (float) ($current->buy_rate ?? 0),
                'sell_rate' => $localSellRate,
                'prev_buy_rate' => $previous ? (float) $previous->buy_rate : (float) ($current->buy_rate ?? 0),
                'prev_sell_rate' => $previous ? (float) $previous->sell_rate : (float) ($current->sell_rate ?? 0),
                'cbm_rate' => (float) ($current->cbm_rate ?? 0),
                'change_percentage' => (float) ($current->change_percentage ?? 0),
                'market_trend' => $current->market_trend ?? 'stable',
                'avg_bank_rate' => (float) ($currency->avg_bank_rate ?? 0),
                'spread' => (float) (($current->sell_rate ?? 0) - ($current->buy_rate ?? 0)),
                'global_reference' => round($globalReference, 2),
                'is_local_rate' => in_array($code, ['USD', 'SGD', 'THB']),
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

        // Get Myanmar gold with proper data formatting
        $myanmarGoldNew = GoldType::where('category', 'myanmar')
            ->where('system', 'new')
            ->get()
            ->map(function ($goldType) {
                $latestPrice = GoldPrice::where('gold_type_id', $goldType->id)
                    ->where('status', 'verified')
                    ->orderBy('price_date', 'desc')
                    ->first();

                $yesterday = now()->subDay()->toDateString();
                $previousPrice = GoldPrice::where('gold_type_id', $goldType->id)
                    ->where('status', 'verified')
                    ->whereDate('price_date', $yesterday)
                    ->first();

                return [
                    'id' => $goldType->id,
                    'name' => $goldType->name,
                    'system' => $goldType->system,
                    'purity' => $goldType->purity,
                    'unit' => $goldType->unit,
                    'latest_verified_price' => $latestPrice ? (float) $latestPrice->price : 0,
                    'previous_day_price' => $previousPrice ? (float) $previousPrice->price : 0,
                    'created_at' => $latestPrice?->created_at,
                    'source_type' => $latestPrice?->source_type,
                ];
            });

        $myanmarGoldOld = GoldType::where('category', 'myanmar')
            ->where('system', 'old')
            ->get()
            ->map(function ($goldType) {
                $latestPrice = GoldPrice::where('gold_type_id', $goldType->id)
                    ->where('status', 'verified')
                    ->orderBy('price_date', 'desc')
                    ->first();

                $yesterday = now()->subDay()->toDateString();
                $previousPrice = GoldPrice::where('gold_type_id', $goldType->id)
                    ->where('status', 'verified')
                    ->whereDate('price_date', $yesterday)
                    ->first();

                return [
                    'id' => $goldType->id,
                    'name' => $goldType->name,
                    'system' => $goldType->system,
                    'purity' => $goldType->purity,
                    'unit' => $goldType->unit,
                    'latest_verified_price' => $latestPrice ? (float) $latestPrice->price : 0,
                    'previous_day_price' => $previousPrice ? (float) $previousPrice->price : 0,
                    'created_at' => $latestPrice?->created_at,
                    'source_type' => $latestPrice?->source_type,
                ];
            });

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

        $yesterday = now()->subDay()->toDateString();

        // Get the last record from yesterday
        $previousDayClose = WorldGoldSnapshot::whereDate('fetched_at', $yesterday)
            ->orderBy('fetched_at', 'desc')
            ->first();

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
                'seven_day_old_price' => $sevenDayOldSnapshot?->usd_price,
                'fetched_at' => $latestGold->fetched_at,
                'updated_at' => $latestGold->updated_at,
            ] : null,
            'goldTypes' => $goldTypes,
            'pending_gold_count' => $pendingGoldCount,
            'worldGoldSnapshot' => $worldGoldSnapshot,
            'sgdRate' => $sgdRate,
            'previousDayUsdPrice' => $previousDayClose?->usd_price,
            'previousDaySgdPrice' => $previousDayClose?->sgd_price,
            'previousCloseDate' => $previousDayClose?->fetched_at,
            'myanmarGoldNew' => $myanmarGoldNew,
            'myanmarGoldOld' => $myanmarGoldOld,
            'previousDayMmkNew' => $myanmarGoldNew->first()['previous_day_price'] ?? null,
            'previousDayMmkOld' => $myanmarGoldOld->first()['previous_day_price'] ?? null,
        ]);
    }
}
