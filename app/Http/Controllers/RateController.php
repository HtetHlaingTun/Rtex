<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Inertia\Inertia;

class RateController extends Controller
{
    public function index()
    {
        // Get all active currencies
        $currencies = Currency::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('code')
            ->get();

        $rates = [];

        foreach ($currencies as $currency) {
            // Get the latest verified exchange rate
            $latestRate = ExchangeRate::where('currency_id', $currency->id)
                ->where('is_verified', true)
                ->latest('rate_date')
                ->latest('created_at')
                ->first();

            if ($latestRate) {
                // Get previous rate for trend calculation
                $previousRate = ExchangeRate::where('currency_id', $currency->id)
                    ->where('is_verified', true)
                    ->where('created_at', '<', $latestRate->created_at)
                    ->latest('created_at')
                    ->first();

                $rates[] = [
                    'id' => $currency->id,
                    'currency' => [
                        'id' => $currency->id,
                        'code' => $currency->code,
                        'name' => $currency->name,
                        'symbol' => $currency->symbol,
                    ],
                    'buy_rate' => (float) $latestRate->buy_rate,
                    'sell_rate' => (float) $latestRate->sell_rate,
                    'prev_buy_rate' => $previousRate ? (float) $previousRate->buy_rate : null,
                    'prev_sell_rate' => $previousRate ? (float) $previousRate->sell_rate : null,
                    'change_percentage' => (float) ($latestRate->change_percentage ?? 0),
                    'market_trend' => $latestRate->market_trend ?? 'stable',
                    'created_at' => $latestRate->created_at,
                    'last_updated' => $latestRate->created_at,
                ];
            }
        }

        // Sort rates: USD first, then by code
        usort($rates, function ($a, $b) {
            if ($a['currency']['code'] === 'USD') return -1;
            if ($b['currency']['code'] === 'USD') return 1;
            return $a['currency']['code'] <=> $b['currency']['code'];
        });

        $meta = [
            'title' => 'Live Exchange Rates - USD, SGD, EUR to MMK | MMRatePro',
            'description' => 'Real-time foreign exchange rates in Myanmar Kyat (MMK). Track USD, SGD, EUR, THB and other major currencies with live updates every 30 minutes.',
            'keywords' => 'exchange rates, foreign exchange, USD to MMK, SGD to MMK, EUR to MMK, THB to MMK, Myanmar currency, live forex rates',
        ];

        return Inertia::render('Rates/Index', [
            'breadcrumbs' => [
                ['label' => 'Home', 'route' => 'welcome'],
                ['label' => 'Rate', 'route' => 'rates.index'],
                ['label' => 'Live Exchange Rates',],
            ],
            'rates' => $rates,
            'meta' => $meta,
        ]);
    }
}
