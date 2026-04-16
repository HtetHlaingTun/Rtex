<?php

namespace App\Http\Controllers;

use App\Models\GoldPrice;
use App\Models\GoldType;
use App\Models\WorldGoldSnapshot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GoldPageController extends Controller
{
    public function index()
    {
        // Get latest world gold snapshot
        $latestSnapshot = WorldGoldSnapshot::latest('fetched_at')->first();

        // IMPORTANT: Get previous day close (SAME as WelcomeController)
        $yesterday = now()->subDay()->toDateString();
        $previousDayClose = WorldGoldSnapshot::whereDate('fetched_at', $yesterday)
            ->orderBy('fetched_at', 'desc')
            ->first();

        // Calculate changes using previous day close (NOT previous snapshot)
        $usdChange = 0;
        $usdChangePercent = 0;
        $sgdChange = 0;
        $sgdChangePercent = 0;

        if ($latestSnapshot && $previousDayClose) {
            $usdChange = $latestSnapshot->usd_price - $previousDayClose->usd_price;
            $usdChangePercent = $previousDayClose->usd_price > 0
                ? ($usdChange / $previousDayClose->usd_price) * 100
                : 0;

            $sgdChange = ($latestSnapshot->sgd_price ?? 0) - ($previousDayClose->sgd_price ?? 0);
            $sgdChangePercent = $previousDayClose->sgd_price > 0
                ? ($sgdChange / $previousDayClose->sgd_price) * 100
                : 0;
        }

        // Add calculated changes to the snapshot object
        if ($latestSnapshot) {
            $latestSnapshot->calculated_usd_change = $usdChange;
            $latestSnapshot->calculated_usd_change_percent = $usdChangePercent;
            $latestSnapshot->calculated_sgd_change = $sgdChange;
            $latestSnapshot->calculated_sgd_change_percent = $sgdChangePercent;
        }

        // Get gold types with their latest verified prices
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

        // Get world gold types
        $worldGold = GoldType::where('category', 'world')->get();

        // Get SGD rate
        $sgdRate = null;
        if ($latestSnapshot && $latestSnapshot->usd_sgd_rate) {
            $sgdRate = [
                'usd_sgd_rate' => $latestSnapshot->usd_sgd_rate,
                'sgd_price' => $latestSnapshot->sgd_price,
                'change' => $sgdChange,
                'change_percent' => $sgdChangePercent,
                'fetched_at' => $latestSnapshot->fetched_at,
            ];
        }

        $meta = [
            'title' => 'Live Gold Prices - 24K, 22K, 18K in MMK, USD, SGD | MMRatePro',
            'description' => 'Real-time gold prices in Myanmar Kyat (MMK), US Dollar (USD), and Singapore Dollar (SGD). Track 24K, 22K, and 18K gold rates updated live.',
            'keywords' => 'gold prices, 24k gold, 22k gold, 18k gold, gold rate in Myanmar, gold price today, MMK gold price',
        ];

        return Inertia::render('GoldPage/Index', [
            'breadcrumbs' => [
                ['label' => 'Home', 'route' => 'welcome'],
                ['label' => 'Gold', 'route' => 'goldPage.index'],
                ['label' => 'Live Gold Prices'],
            ],
            'worldGoldSnapshot' => $latestSnapshot,
            'goldTypes' => $goldTypes,
            'sgdRate' => $sgdRate,
            // IMPORTANT: Pass the previous day close values (same as WelcomeController)
            'previousDayUsdPrice' => $previousDayClose?->usd_price ?? 0,
            'previousDaySgdPrice' => $previousDayClose?->sgd_price ?? 0,
            'previousCloseDate' => $previousDayClose?->fetched_at,
            'myanmarGoldNew' => $myanmarGoldNew,
            'myanmarGoldOld' => $myanmarGoldOld,
            'previousDayMmkNew' => $myanmarGoldNew->first()['previous_day_price'] ?? null,
            'previousDayMmkOld' => $myanmarGoldOld->first()['previous_day_price'] ?? null,
            'meta' => $meta,
        ]);
    }
}
