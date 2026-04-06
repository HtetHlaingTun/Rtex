<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserAsset;
use App\Models\WorldGoldSnapshot;
use App\Models\ExchangeRate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $assets = UserAsset::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // ── Current market rates ────────────────────────────────────────────────
        $goldSnapshot = WorldGoldSnapshot::latest()->first();
        $usdRate      = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'USD'))->latest()->first();
        $sgdRate      = ExchangeRate::whereHas('currency', fn($q) => $q->where('code', 'SGD'))->latest()->first();

        $usdMmk = $usdRate ? ($usdRate->buy_rate + $usdRate->sell_rate) / 2 : 4387;
        $sgdMmk = $sgdRate ? ($sgdRate->buy_rate + $sgdRate->sell_rate) / 2 : 3408;

        $currentRates = [
            'usd_mmk'  => $usdMmk,
            'sgd_mmk'  => $sgdMmk,
            'usd_sgd'  => $usdMmk / $sgdMmk,
            'gold_usd' => $goldSnapshot?->usd_price ?? 4703,
            'gold_sgd' => $goldSnapshot?->sgd_price ?? 6050,
            'gold_mmk' => ($goldSnapshot?->usd_price ?? 4703) * $usdMmk,
        ];

        // ── Per-asset P&L ───────────────────────────────────────────────────────
        $assetsWithPnL    = [];
        $totalValueMmk    = 0;
        $totalInvestedMmk = 0;

        foreach ($assets as $asset) {
            $pnlData          = $this->calculateAssetPnL($asset, $currentRates);
            $assetsWithPnL[]  = $pnlData;
            $totalValueMmk   += $pnlData['current_value_mmk'];
            $totalInvestedMmk += $pnlData['purchase_value_mmk'];
        }

        // ── Portfolio totals ────────────────────────────────────────────────────
        $totalPnLMmk     = $totalValueMmk - $totalInvestedMmk;
        $totalPnLPercent = $totalInvestedMmk > 0 ? ($totalPnLMmk / $totalInvestedMmk) * 100 : 0;

        $totalValueUsd    = $totalValueMmk    / $usdMmk;
        $totalValueSgd    = $totalValueMmk    / $sgdMmk;
        $totalInvestedUsd = $totalInvestedMmk / $usdMmk;
        $totalInvestedSgd = $totalInvestedMmk / $sgdMmk;
        $totalPnLUsd      = $totalPnLMmk      / $usdMmk;
        $totalPnLSgd      = $totalPnLMmk      / $sgdMmk;

        return Inertia::render('Users/Dashboard', [
            'assets'           => $assetsWithPnL,
            'currentRates'     => $currentRates,
            'totalValue'       => round($totalValueMmk,    2),
            'totalInvested'    => round($totalInvestedMmk, 2),
            'totalPnL'         => round($totalPnLMmk,      2),
            'totalPnLPercent'  => round($totalPnLPercent,  2),
            'totalValueUsd'    => round($totalValueUsd,    2),
            'totalValueSgd'    => round($totalValueSgd,    2),
            'totalInvestedUsd' => round($totalInvestedUsd, 2),
            'totalInvestedSgd' => round($totalInvestedSgd, 2),
            'totalPnLUsd'      => round($totalPnLUsd,      2),
            'totalPnLSgd'      => round($totalPnLSgd,      2),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Main dispatcher
    // ═══════════════════════════════════════════════════════════════════════════
    private function calculateAssetPnL($asset, $currentRates): array
    {
        return match ($asset->type) {
            'currency' => $this->calcCurrency($asset, $currentRates),
            'gold'     => $this->calcGold($asset, $currentRates),
            default    => $this->calcStatic($asset, $currentRates),
        };
    }

    // ── Currency assets (USD/SGD holdings) ──────────────────────────────────────
    // For currency: quantity = amount held, purchase_price = exchange rate at purchase
    // Example: 800 USD at rate 3,700 MMK/USD
    private function calcCurrency($asset, $currentRates): array
    {
        $quantity     = (float) $asset->quantity;        // e.g., 800 USD
        $purchaseRate = (float) $asset->purchase_price;  // e.g., 3700 MMK per USD
        $usdMmk       = $currentRates['usd_mmk'] ?? 4387;
        $sgdMmk       = $currentRates['sgd_mmk'] ?? 3408;

        $currentRate = $asset->purchase_currency === 'USD' ? $usdMmk : $sgdMmk;

        // MMK calculations
        $purchaseValueMmk = $quantity * $purchaseRate;  // 800 × 3700 = 2,960,000
        $currentValueMmk  = $quantity * $currentRate;   // 800 × 4387 = 3,509,600
        $pnlMmk           = $currentValueMmk - $purchaseValueMmk;  // +549,600

        // P&L percentage based on exchange rate change
        $pnlPercent = $purchaseRate > 0
            ? (($currentRate - $purchaseRate) / $purchaseRate) * 100
            : 0;

        // Native currency values (amount held doesn't change)
        $currentValueNative = $quantity;  // Still 800 USD
        $purchaseValueNative = $quantity; // Still 800 USD
        $pnlNative = 0;  // No P&L in native currency

        return [
            'id'                  => $asset->id,
            'name'                => $asset->name,
            'type'                => $asset->type,
            'quantity'            => $quantity,
            'purchase_price'      => $purchaseRate,
            'purchase_currency'   => $asset->purchase_currency,
            'purchase_date'       => $asset->purchase_date->format('Y-m-d'),
            'purchase_usd_rate'   => $asset->purchase_usd_rate ?? 4387,
            'purchase_sgd_rate'   => $asset->purchase_sgd_rate ?? 3408,
            // Purchase values
            'purchase_value_mmk'  => round($purchaseValueMmk, 2),
            'purchase_value_usd'  => round($purchaseValueMmk / $usdMmk, 2),
            'purchase_value_sgd'  => round($purchaseValueMmk / $sgdMmk, 2),
            // Current values
            'current_value'       => round($currentValueNative, 2),
            'current_value_mmk'   => round($currentValueMmk, 2),
            'current_value_usd'   => round($currentValueMmk / $usdMmk, 2),
            'current_value_sgd'   => round($currentValueMmk / $sgdMmk, 2),
            // P&L
            'pnl_original'        => round($pnlNative, 2),
            'pnl_mmk'             => round($pnlMmk, 2),
            'pnl_usd'             => round($pnlMmk / $usdMmk, 2),
            'pnl_sgd'             => round($pnlMmk / $sgdMmk, 2),
            'pnl_percent'         => round($pnlPercent, 2),
            'is_profit'           => $pnlMmk > 0,
            'has_market_price'    => true,
        ];
    }

    // ── Gold assets ─────────────────────────────────────────────────────────────
    private function calcGold($asset, $currentRates): array
    {
        $usdMmk = $currentRates['usd_mmk'] ?? 4387;
        $sgdMmk = $currentRates['sgd_mmk'] ?? 3408;

        $purchaseUsdRate = (float) ($asset->purchase_usd_rate ?? $usdMmk);
        $purchaseSgdRate = (float) ($asset->purchase_sgd_rate ?? $sgdMmk);

        $currentValueOriginal  = $this->goldCurrentValueInNativeCurrency($asset, $currentRates);
        $purchaseValueOriginal = (float) $asset->purchase_price * (float) $asset->quantity;

        $pnlOriginal = $currentValueOriginal - $purchaseValueOriginal;
        $pnlPercent  = $purchaseValueOriginal > 0
            ? ($pnlOriginal / $purchaseValueOriginal) * 100
            : 0;

        [$currentValueMmk, $purchaseValueMmk] = match ($asset->purchase_currency) {
            'USD'   => [
                $currentValueOriginal  * $usdMmk,
                $purchaseValueOriginal * $purchaseUsdRate,
            ],
            'SGD'   => [
                $currentValueOriginal  * $sgdMmk,
                $purchaseValueOriginal * $purchaseSgdRate,
            ],
            default => [
                $currentValueOriginal,
                $purchaseValueOriginal,
            ],
        };

        $pnlMmk = $currentValueMmk - $purchaseValueMmk;

        return [
            'id'                  => $asset->id,
            'name'                => $asset->name,
            'type'                => $asset->type,
            'quantity'            => (float) $asset->quantity,
            'purchase_price'      => (float) $asset->purchase_price,
            'purchase_currency'   => $asset->purchase_currency,
            'purchase_date'       => $asset->purchase_date->format('Y-m-d'),
            'purchase_usd_rate'   => $purchaseUsdRate,
            'purchase_sgd_rate'   => $purchaseSgdRate,
            'product_type'        => $asset->product_type,
            'weight_unit'         => $asset->weight_unit,
            'weight_in_grams'     => (float) ($asset->weight_in_grams ?? 0),
            'troy_ounces'         => (float) ($asset->troy_ounces ?? 0),
            'purchase_value_mmk'  => round($purchaseValueMmk, 2),
            'purchase_value_usd'  => round($purchaseValueMmk / $usdMmk, 2),
            'purchase_value_sgd'  => round($purchaseValueMmk / $sgdMmk, 2),
            'current_value'       => round($currentValueOriginal, 2),
            'current_value_mmk'   => round($currentValueMmk, 2),
            'current_value_usd'   => round($currentValueMmk / $usdMmk, 2),
            'current_value_sgd'   => round($currentValueMmk / $sgdMmk, 2),
            'pnl_original'        => round($pnlOriginal, 2),
            'pnl_mmk'             => round($pnlMmk, 2),
            'pnl_usd'             => round($pnlMmk / $usdMmk, 2),
            'pnl_sgd'             => round($pnlMmk / $sgdMmk, 2),
            'pnl_percent'         => round($pnlPercent, 2),
            'is_profit'           => $pnlOriginal > 0,
            'has_market_price'    => true,
        ];
    }

    // ── Static assets (property, car, jewelry, crypto, other) ──────────────────
    private function calcStatic($asset, $currentRates): array
    {
        $usdMmk = $currentRates['usd_mmk'] ?? 4387;
        $sgdMmk = $currentRates['sgd_mmk'] ?? 3408;

        $purchaseUsdRate = (float) ($asset->purchase_usd_rate ?? $usdMmk);
        $purchaseSgdRate = (float) ($asset->purchase_sgd_rate ?? $sgdMmk);

        $purchaseValueOriginal = (float) $asset->purchase_price * (float) $asset->quantity;

        // Convert to MMK using the HISTORICAL rate at purchase
        $purchaseValueMmk = match ($asset->purchase_currency) {
            'USD'   => $purchaseValueOriginal * $purchaseUsdRate,
            'SGD'   => $purchaseValueOriginal * $purchaseSgdRate,
            default => $purchaseValueOriginal,
        };

        return [
            'id'                  => $asset->id,
            'name'                => $asset->name,
            'type'                => $asset->type,
            'quantity'            => (float) $asset->quantity,
            'purchase_price'      => (float) $asset->purchase_price,
            'purchase_currency'   => $asset->purchase_currency,
            'purchase_date'       => $asset->purchase_date->format('Y-m-d'),
            'purchase_usd_rate'   => $purchaseUsdRate,
            'purchase_sgd_rate'   => $purchaseSgdRate,
            'purchase_value_mmk'  => round($purchaseValueMmk, 2),
            'purchase_value_usd'  => round($purchaseValueMmk / $usdMmk, 2),
            'purchase_value_sgd'  => round($purchaseValueMmk / $sgdMmk, 2),
            'current_value'       => round($purchaseValueOriginal, 2),
            'current_value_mmk'   => round($purchaseValueMmk, 2),
            'current_value_usd'   => round($purchaseValueMmk / $usdMmk, 2),
            'current_value_sgd'   => round($purchaseValueMmk / $sgdMmk, 2),
            'pnl_original'        => 0,
            'pnl_mmk'             => 0,
            'pnl_usd'             => 0,
            'pnl_sgd'             => 0,
            'pnl_percent'         => 0,
            'is_profit'           => false,
            'has_market_price'    => false,
        ];
    }

    // ── Gold price helper ────────────────────────────────────────────────────────
    private function goldCurrentValueInNativeCurrency($asset, $currentRates): float
    {
        $troyOz = (float) ($asset->troy_ounces ?? 0);

        if ($troyOz <= 0) {
            $troyOz = $this->troyOzFromProductType(
                (float) $asset->quantity,
                $asset->product_type
            );
        }

        if ($troyOz <= 0) {
            return (float) $asset->purchase_price * (float) $asset->quantity;
        }

        $usdMmk = $currentRates['usd_mmk'] ?? 4387;

        $pricePerTroyOz = match ($asset->purchase_currency) {
            'MMK'   => ($currentRates['gold_usd'] ?? 4703) * $usdMmk,
            'USD'   => $currentRates['gold_usd'] ?? 4703,
            'SGD'   => $currentRates['gold_sgd'] ?? 6050,
            default => $currentRates['gold_sgd'] ?? 6050,
        };

        return $troyOz * $pricePerTroyOz;
    }

    private function troyOzFromProductType(float $quantity, ?string $productType): float
    {
        $gramsPerTroyOz = 31.1035;

        return match ($productType) {
            '1oz'        => $quantity,
            '50g'        => $quantity * (50  / $gramsPerTroyOz),
            '100g'       => $quantity * (100 / $gramsPerTroyOz),
            '1kyatthar'  => $quantity * 0.525,
            '10kyatthar' => $quantity * 5.25,
            default      => 0.0,
        };
    }
}
