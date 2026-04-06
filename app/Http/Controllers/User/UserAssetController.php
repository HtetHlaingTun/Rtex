<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\UserAsset;
use App\Models\WorldGoldSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserAssetController extends Controller
{
    public function index()
    {
        $assets = UserAsset::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get current market rates
        $goldSnapshot    = WorldGoldSnapshot::latest()->first();
        $currentUsdRate  = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'USD'))->latest()->first();
        $currentSgdRate  = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'SGD'))->latest()->first();

        $usdMmk = $currentUsdRate ? ($currentUsdRate->buy_rate + $currentUsdRate->sell_rate) / 2 : 4385;
        $sgdMmk = $currentSgdRate ? ($currentSgdRate->buy_rate + $currentSgdRate->sell_rate) / 2 : 3408;

        $currentRates = [
            'usd_mmk'  => $usdMmk,
            'sgd_mmk'  => $sgdMmk,
            'usd_sgd'  => $usdMmk / $sgdMmk,
            'gold_usd' => $goldSnapshot?->usd_price ?? 4703,
            'gold_sgd' => $goldSnapshot?->sgd_price ?? 6050,
            'gold_mmk' => ($goldSnapshot?->usd_price ?? 4703) * $usdMmk,
        ];

        $assetsWithPnL = [];

        $totalValueMmk    = 0;
        $totalValueUsd    = 0;
        $totalValueSgd    = 0;
        $totalInvestedMmk = 0;
        $totalInvestedUsd = 0;
        $totalInvestedSgd = 0;

        foreach ($assets as $asset) {

            $purchaseUsdRate = $asset->purchase_usd_rate ?? 4385;
            $purchaseSgdRate = $asset->purchase_sgd_rate ?? 3408;

            // =========================
            // ✅ CURRENCY ASSETS
            // =========================
            if ($asset->type === 'currency') {

                $quantity    = (float) $asset->quantity;
                $purchaseRate = (float) $asset->purchase_price;

                $currentRate = $asset->purchase_currency === 'USD' ? $usdMmk : $sgdMmk;

                $purchaseValueMmk = $quantity * $purchaseRate;
                $currentValueMmk  = $quantity * $currentRate;

                $purchaseValueUsd = $asset->purchase_currency === 'USD' ? $quantity : $purchaseValueMmk / $usdMmk;
                $purchaseValueSgd = $asset->purchase_currency === 'SGD' ? $quantity : $purchaseValueMmk / $sgdMmk;
                $currentValueUsd  = $asset->purchase_currency === 'USD' ? $quantity : $currentValueMmk / $usdMmk;
                $currentValueSgd  = $asset->purchase_currency === 'SGD' ? $quantity : $currentValueMmk / $sgdMmk;

                $pnlMmk     = $currentValueMmk - $purchaseValueMmk;
                $pnlPercent = $purchaseRate > 0 ? (($currentRate - $purchaseRate) / $purchaseRate) * 100 : 0;

                $totalValueMmk    += $currentValueMmk;
                $totalValueUsd    += $currentValueUsd;
                $totalValueSgd    += $currentValueSgd;
                $totalInvestedMmk += $purchaseValueMmk;
                $totalInvestedUsd += $purchaseValueUsd;
                $totalInvestedSgd += $purchaseValueSgd;

                $assetsWithPnL[] = [
                    'id'                  => $asset->id,
                    'name'                => $asset->name,
                    'type'                => $asset->type,
                    'quantity'            => $quantity,
                    'purchase_price'      => $purchaseRate,
                    'purchase_currency'   => $asset->purchase_currency,
                    'purchase_date'       => $asset->purchase_date->format('Y-m-d'),
                    'purchase_value_mmk'  => round($purchaseValueMmk, 2),
                    'purchase_value_usd'  => round($purchaseValueUsd, 2),
                    'purchase_value_sgd'  => round($purchaseValueSgd, 2),
                    'current_value'       => $quantity,
                    'current_value_mmk'   => round($currentValueMmk, 2),
                    'current_value_usd'   => round($currentValueUsd, 2),
                    'current_value_sgd'   => round($currentValueSgd, 2),
                    'pnl_original'        => 0,
                    'pnl_mmk'             => round($pnlMmk, 2),
                    'pnl_usd'             => round($pnlMmk / $usdMmk, 2),
                    'pnl_sgd'             => round($pnlMmk / $sgdMmk, 2),
                    'pnl_percent'         => round($pnlPercent, 2),
                    'is_profit'           => $pnlMmk > 0,
                    'has_market_price'    => true,
                ];

                continue;
            }

            // =========================
            // ✅ GOLD ASSETS
            // =========================
            if ($asset->type === 'gold') {

                $pnlData       = $this->calculateGoldPnL($asset, $currentRates);
                $purchaseValues = $this->getGoldPurchaseValues($asset, $purchaseUsdRate, $purchaseSgdRate);
                $currentValues  = $pnlData['current_values'];

                $assetsWithPnL[] = [
                    'id'                  => $asset->id,
                    'name'                => $asset->name,
                    'type'                => $asset->type,
                    'quantity'            => (float) $asset->quantity,
                    'purchase_price'      => (float) $asset->purchase_price,
                    'purchase_currency'   => $asset->purchase_currency,
                    'purchase_date'       => $asset->purchase_date->format('Y-m-d'),
                    'purchase_value_mmk'  => $purchaseValues['mmk'],
                    'purchase_value_usd'  => $purchaseValues['usd'],
                    'purchase_value_sgd'  => $purchaseValues['sgd'],
                    'current_value'       => $currentValues['value_in_purchase_currency'],
                    'current_value_mmk'   => $currentValues['mmk'],
                    'current_value_usd'   => $currentValues['usd'],
                    'current_value_sgd'   => $currentValues['sgd'],
                    'pnl_original'        => $pnlData['pnl_original'],
                    'pnl_mmk'             => $pnlData['pnl_mmk'],
                    'pnl_usd'             => $pnlData['pnl_usd'],
                    'pnl_sgd'             => $pnlData['pnl_sgd'],
                    'pnl_percent'         => $pnlData['pnl_percent'],
                    'is_profit'           => $pnlData['pnl_original'] > 0,
                    'has_market_price'    => true,
                ];

                $totalValueMmk    += $currentValues['mmk'];
                $totalValueUsd    += $currentValues['usd'];
                $totalValueSgd    += $currentValues['sgd'];
                $totalInvestedMmk += $purchaseValues['mmk'];
                $totalInvestedUsd += $purchaseValues['usd'];
                $totalInvestedSgd += $purchaseValues['sgd'];

                continue;
            }

            // =========================
            // ✅ STATIC ASSETS (property, car, jewelry, crypto, other)
            // No live market price — current value = purchase value, P&L = 0
            // =========================
            $purchaseValueOriginal = (float) $asset->purchase_price * (float) $asset->quantity;

            $purchaseValueMmk = match ($asset->purchase_currency) {
                'USD'   => $purchaseValueOriginal * $purchaseUsdRate,
                'SGD'   => $purchaseValueOriginal * $purchaseSgdRate,
                default => $purchaseValueOriginal,
            };

            $purchaseValueUsd = round($purchaseValueMmk / $usdMmk, 2);
            $purchaseValueSgd = round($purchaseValueMmk / $sgdMmk, 2);

            $assetsWithPnL[] = [
                'id'                  => $asset->id,
                'name'                => $asset->name,
                'type'                => $asset->type,
                'quantity'            => (float) $asset->quantity,
                'purchase_price'      => (float) $asset->purchase_price,
                'purchase_currency'   => $asset->purchase_currency,
                'purchase_date'       => $asset->purchase_date->format('Y-m-d'),
                // Current = purchase (no market tracking)
                'purchase_value_mmk'  => round($purchaseValueMmk, 2),
                'purchase_value_usd'  => $purchaseValueUsd,
                'purchase_value_sgd'  => $purchaseValueSgd,
                'current_value'       => round($purchaseValueOriginal, 2),
                'current_value_mmk'   => round($purchaseValueMmk, 2),
                'current_value_usd'   => $purchaseValueUsd,
                'current_value_sgd'   => $purchaseValueSgd,
                // P&L always 0
                'pnl_original'        => 0,
                'pnl_mmk'             => 0,
                'pnl_usd'             => 0,
                'pnl_sgd'             => 0,
                'pnl_percent'         => 0,
                'is_profit'           => false,
                'has_market_price'    => false,
            ];

            $totalValueMmk    += $purchaseValueMmk;
            $totalValueUsd    += $purchaseValueUsd;
            $totalValueSgd    += $purchaseValueSgd;
            $totalInvestedMmk += $purchaseValueMmk;
            $totalInvestedUsd += $purchaseValueUsd;
            $totalInvestedSgd += $purchaseValueSgd;
        }

        return Inertia::render('Users/Assets/Index', [
            'assets' => $assetsWithPnL,
            'summary' => [
                'total_value_mmk'    => round($totalValueMmk, 2),
                'total_value_usd'    => round($totalValueUsd, 2),
                'total_value_sgd'    => round($totalValueSgd, 2),
                'total_invested_mmk' => round($totalInvestedMmk, 2),
                'total_invested_usd' => round($totalInvestedUsd, 2),
                'total_invested_sgd' => round($totalInvestedSgd, 2),
                'total_pnl_mmk'      => round($totalValueMmk - $totalInvestedMmk, 2),
                'total_pnl_usd'      => round($totalValueUsd - $totalInvestedUsd, 2),
                'total_pnl_sgd'      => round($totalValueSgd - $totalInvestedSgd, 2),
                'total_pnl_percent'  => $totalInvestedMmk > 0
                    ? round((($totalValueMmk - $totalInvestedMmk) / $totalInvestedMmk) * 100, 2)
                    : 0,
            ],
            'currentRates' => $currentRates,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Gold P&L helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function calculateGoldPnL($asset, $currentRates)
    {
        $purchaseValueOriginal = (float) $asset->purchase_price * (float) $asset->quantity;
        $currentValueOriginal  = $this->getGoldCurrentValueInPurchaseCurrency($asset, $currentRates);

        $pnlOriginal = $currentValueOriginal - $purchaseValueOriginal;
        $pnlPercent  = $purchaseValueOriginal > 0 ? ($pnlOriginal / $purchaseValueOriginal) * 100 : 0;

        $currentValues = $this->getGoldCurrentValuesInAllCurrencies($asset, $currentRates, $currentValueOriginal);

        $usdMmk = $currentRates['usd_mmk'] ?? 4387;
        $sgdMmk = $currentRates['sgd_mmk'] ?? 3408;

        $pnlMmk = match ($asset->purchase_currency) {
            'SGD'   => $pnlOriginal * $sgdMmk,
            'USD'   => $pnlOriginal * $usdMmk,
            default => $pnlOriginal,
        };

        return [
            'pnl_original'  => round($pnlOriginal, 2),
            'pnl_mmk'       => round($pnlMmk, 2),
            'pnl_usd'       => round($pnlMmk / $usdMmk, 2),
            'pnl_sgd'       => round($pnlMmk / $sgdMmk, 2),
            'pnl_percent'   => round($pnlPercent, 2),
            'current_values' => $currentValues,
        ];
    }

    private function getGoldCurrentValueInPurchaseCurrency($asset, $currentRates)
    {
        if ($asset->troy_ounces && $asset->troy_ounces > 0) {
            $usdMmk = $currentRates['usd_mmk'] ?? 4387;
            $pricePerTroyOz = match ($asset->purchase_currency) {
                'USD'   => $currentRates['gold_usd'] ?? 4703,
                'SGD'   => $currentRates['gold_sgd'] ?? 6050,
                'MMK'   => ($currentRates['gold_usd'] ?? 4703) * $usdMmk,
                default => $currentRates['gold_sgd'] ?? 6050,
            };
            return $asset->troy_ounces * $pricePerTroyOz;
        }

        return (float) $asset->purchase_price * (float) $asset->quantity;
    }

    private function getGoldCurrentValuesInAllCurrencies($asset, $currentRates, $currentValueOriginal)
    {
        $usdMmk = $currentRates['usd_mmk'] ?? 4387;
        $sgdMmk = $currentRates['sgd_mmk'] ?? 3408;

        [$mmk, $usd, $sgd] = match ($asset->purchase_currency) {
            'USD'   => [
                $currentValueOriginal * $usdMmk,
                $currentValueOriginal,
                ($currentValueOriginal * $usdMmk) / $sgdMmk,
            ],
            'SGD'   => [
                $currentValueOriginal * $sgdMmk,
                ($currentValueOriginal * $sgdMmk) / $usdMmk,
                $currentValueOriginal,
            ],
            default => [
                $currentValueOriginal,
                $currentValueOriginal / $usdMmk,
                $currentValueOriginal / $sgdMmk,
            ],
        };

        return [
            'value_in_purchase_currency' => round($currentValueOriginal, 2),
            'mmk' => round($mmk, 2),
            'usd' => round($usd, 2),
            'sgd' => round($sgd, 2),
        ];
    }

    private function getGoldPurchaseValues($asset, $purchaseUsdRate, $purchaseSgdRate)
    {
        $purchasePrice = (float) $asset->purchase_price;
        $quantity      = (float) $asset->quantity;

        [$mmk, $usd, $sgd] = match ($asset->purchase_currency) {
            'USD'   => [
                $purchasePrice * $quantity * $purchaseUsdRate,
                $purchasePrice * $quantity,
                ($purchasePrice * $quantity * $purchaseUsdRate) / $purchaseSgdRate,
            ],
            'SGD'   => [
                $purchasePrice * $quantity * $purchaseSgdRate,
                ($purchasePrice * $quantity * $purchaseSgdRate) / $purchaseUsdRate,
                $purchasePrice * $quantity,
            ],
            default => [
                $purchasePrice * $quantity,
                ($purchasePrice * $quantity) / $purchaseUsdRate,
                ($purchasePrice * $quantity) / $purchaseSgdRate,
            ],
        };

        return [
            'mmk' => round($mmk, 2),
            'usd' => round($usd, 2),
            'sgd' => round($sgd, 2),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Gold weight calculation
    // ──────────────────────────────────────────────────────────────────────────

    private function calculateGoldWeight($quantity, $productType, $customGrams = null)
    {
        $gramsPerTroyOz = 31.1035;

        [$grams, $troyOunces, $weightUnit, $kyattharType] = match ($productType) {
            '1oz'        => [$quantity * $gramsPerTroyOz, $quantity, 'oz', null],
            '50g'        => [$quantity * 50, $quantity * (50 / $gramsPerTroyOz), 'gram', null],
            '100g'       => [$quantity * 100, $quantity * (100 / $gramsPerTroyOz), 'gram', null],
            '1kyatthar'  => [$quantity * 16.329, $quantity * 0.525, 'kyatthar', 'new'],
            '10kyatthar' => [$quantity * 163.29, $quantity * 5.25, 'kyatthar', 'new'],
            'custom'     => $customGrams
                ? [$quantity * $customGrams, $quantity * ($customGrams / $gramsPerTroyOz), 'gram', null]
                : [0, 0, null, null],
            default      => [0, 0, null, null],
        };

        return [
            'grams'        => $grams,
            'troy_ounces'  => $troyOunces,
            'weight_unit'  => $weightUnit,
            'kyatthar_type' => $kyattharType,
            'product_type' => $productType,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  CRUD actions
    // ──────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $usdRate      = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'USD'))->latest()->first();
        $sgdRate      = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'SGD'))->latest()->first();
        $goldSnapshot = WorldGoldSnapshot::latest()->first();

        return Inertia::render('Users/Assets/Create', [
            'currentRates' => [
                'usd_mmk'  => $usdRate ? ($usdRate->buy_rate + $usdRate->sell_rate) / 2 : 4385,
                'sgd_mmk'  => $sgdRate ? ($sgdRate->buy_rate + $sgdRate->sell_rate) / 2 : 3408,
                'gold_usd' => $goldSnapshot?->usd_price ?? 4703,
                'gold_sgd' => $goldSnapshot?->sgd_price ?? 6050,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:gold,property,car,jewelry,crypto,currency,other',
            'quantity'          => 'required|numeric|min:0.0001',
            'purchase_price'    => 'required|numeric|min:0',
            'purchase_currency' => 'required|in:MMK,USD,SGD',
            'purchase_date'     => 'required|date',
            'description'       => 'nullable|string',
            'product_type'      => 'nullable|in:1oz,50g,100g,1kyatthar,10kyatthar,custom',
            'custom_grams'      => 'nullable|numeric|min:0.01',
        ]);

        $purchaseDate = \Carbon\Carbon::parse($request->purchase_date);

        $usdRate = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'USD'))
            ->whereDate('created_at', '<=', $purchaseDate)
            ->orderBy('created_at', 'desc')
            ->first();

        $sgdRate = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'SGD'))
            ->whereDate('created_at', '<=', $purchaseDate)
            ->orderBy('created_at', 'desc')
            ->first();

        $assetData = [
            'user_id'           => Auth::id(),
            'name'              => $validated['name'],
            'type'              => $validated['type'],
            'quantity'          => $validated['quantity'],
            'purchase_price'    => $validated['purchase_price'],
            'purchase_currency' => $validated['purchase_currency'],
            'purchase_usd_rate' => $usdRate ? ($usdRate->buy_rate + $usdRate->sell_rate) / 2 : 4385,
            'purchase_sgd_rate' => $sgdRate ? ($sgdRate->buy_rate + $sgdRate->sell_rate) / 2 : 3408,
            'purchase_date'     => $validated['purchase_date'],
            'description'       => $validated['description'] ?? null,
            'is_active'         => true,
        ];

        if ($validated['type'] === 'gold' && isset($validated['product_type'])) {
            $weightData = $this->calculateGoldWeight(
                $validated['quantity'],
                $validated['product_type'],
                $validated['custom_grams'] ?? null
            );

            $assetData['product_type']    = $weightData['product_type'];
            $assetData['weight_unit']     = $weightData['weight_unit'];
            $assetData['kyatthar_type']   = $weightData['kyatthar_type'];
            $assetData['weight_in_grams'] = $weightData['grams'];
            $assetData['troy_ounces']     = $weightData['troy_ounces'];
        }

        UserAsset::create($assetData);

        return redirect()->route('user.assets.index')->with('success', 'Asset added successfully');
    }

    public function destroy($id)
    {
        $asset = UserAsset::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $asset->update(['is_active' => false]);

        return redirect()->route('user.assets.index')->with('success', 'Asset removed');
    }
}
