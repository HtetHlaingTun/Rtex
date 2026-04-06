<?php

namespace App\Models;

use App\Models\User;
use App\Helpers\GoldCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAsset extends Model
{
    protected $table = 'user_assets';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'quantity',
        'weight_unit',
        'kyatthar_type',
        'product_type',
        'weight_in_grams',
        'troy_ounces',
        'purchase_price',
        'purchase_currency',
        'purchase_usd_rate',
        'purchase_sgd_rate',
        'purchase_mmk_rate',
        'purchase_date',
        'description',
        'metadata',
        'is_active'
    ];

    protected $casts = [
        'metadata' => 'array',
        'purchase_date' => 'date',
        'quantity' => 'decimal:4',
        'purchase_price' => 'decimal:2',
        'purchase_usd_rate' => 'decimal:4',
        'purchase_sgd_rate' => 'decimal:4',
        'purchase_mmk_rate' => 'decimal:4',
        'weight_in_grams' => 'decimal:4',
        'troy_ounces' => 'decimal:6',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get current market value in MMK
     */
    public function getCurrentValueMmk($currentRates)
    {
        // For gold assets with weight, use weight-based calculation
        if ($this->type === 'gold' && $this->troy_ounces) {
            $currentValue = GoldCalculator::getCurrentValue($this, $currentRates);
            return $currentValue;
        }

        // For other assets or gold without weight
        $currentPrice = $this->getCurrentMarketPrice($currentRates);
        return $currentPrice * $this->quantity;
    }

    /**
     * Get current market price based on asset type
     */
    public function getCurrentMarketPrice($currentRates)
    {
        // For gold assets with product type, use GoldCalculator
        if ($this->type === 'gold' && $this->product_type) {
            return GoldCalculator::getCurrentMarketPrice($this, $currentRates);
        }

        switch ($this->type) {
            case 'gold':
                if ($this->purchase_currency === 'USD') {
                    return $currentRates['gold_usd'] ?? 0;
                } elseif ($this->purchase_currency === 'SGD') {
                    return $currentRates['gold_sgd'] ?? 0;
                }
                return $currentRates['gold_mmk'] ?? 0;

            case 'property':
            case 'car':
            default:
                return $this->getCurrentPriceFromRate($currentRates);
        }
    }

    /**
     * Convert purchase price to current value based on exchange rates
     */
    public function getCurrentPriceFromRate($currentRates)
    {
        if ($this->purchase_currency === 'MMK') {
            return $this->purchase_price;
        }

        $currentRate = $this->purchase_currency === 'USD'
            ? ($currentRates['usd_mmk'] ?? 1)
            : ($currentRates['sgd_mmk'] ?? 1);

        $purchaseRate = $this->purchase_currency === 'USD'
            ? $this->purchase_usd_rate
            : $this->purchase_sgd_rate;

        // Convert purchase price from original currency to MMK at current rate
        if ($purchaseRate && $purchaseRate > 0) {
            return ($this->purchase_price / $purchaseRate) * $currentRate;
        }

        return $this->purchase_price;
    }

    /**
     * Get purchase value in MMK
     */
    public function getPurchaseValueInMmk()
    {
        if ($this->purchase_currency === 'MMK') {
            return $this->purchase_price * $this->quantity;
        }

        $rate = $this->purchase_currency === 'USD'
            ? $this->purchase_usd_rate
            : $this->purchase_sgd_rate;

        if ($rate && $rate > 0) {
            return ($this->purchase_price / $rate) * $this->quantity;
        }

        return $this->purchase_price * $this->quantity;
    }

    /**
     * Get purchase value in original currency
     */
    public function getPurchaseValueOriginal()
    {
        return $this->purchase_price * $this->quantity;
    }

    /**
     * Get current value in original currency (for gold with weight)
     */
    public function getCurrentValueOriginal($currentRates)
    {
        if ($this->type === 'gold' && $this->troy_ounces) {
            $pricePerTroyOz = 0;

            switch ($this->purchase_currency) {
                case 'USD':
                    $pricePerTroyOz = $currentRates['gold_usd'] ?? 0;
                    break;
                case 'SGD':
                    $pricePerTroyOz = $currentRates['gold_sgd'] ?? 0;
                    break;
                case 'MMK':
                    // For MMK, convert from USD
                    $usdPrice = $currentRates['gold_usd'] ?? 0;
                    $usdToMmk = $currentRates['usd_mmk'] ?? 1;
                    $pricePerTroyOz = $usdPrice * $usdToMmk;
                    break;
            }

            return $this->troy_ounces * $pricePerTroyOz;
        }

        return $this->purchase_price * $this->quantity;
    }

    /**
     * Calculate P&L with multi-currency support
     */
    public function calculatePnL($currentRates)
    {
        // Get values in MMK for base calculation
        $currentValueMmk = $this->getCurrentValueMmk($currentRates);
        $purchaseValueMmk = $this->getPurchaseValueInMmk();

        $pnlMmk = $currentValueMmk - $purchaseValueMmk;
        $pnlPercent = $purchaseValueMmk > 0 ? ($pnlMmk / $purchaseValueMmk) * 100 : 0;

        // Get values in original currency
        $currentValueOriginal = $this->getCurrentValueOriginal($currentRates);
        $purchaseValueOriginal = $this->getPurchaseValueOriginal();
        $pnlOriginal = $currentValueOriginal - $purchaseValueOriginal;

        // Convert to other currencies
        $usdRate = $currentRates['usd_mmk'] ?? 1;
        $sgdRate = $currentRates['sgd_mmk'] ?? 1;

        $pnlUsd = $pnlMmk / $usdRate;
        $pnlSgd = $pnlMmk / $sgdRate;

        return [
            // MMK values
            'current_value_mmk' => round($currentValueMmk, 2),
            'purchase_value_mmk' => round($purchaseValueMmk, 2),
            'pnl_mmk' => round($pnlMmk, 2),

            // Original currency values
            'current_value_original' => round($currentValueOriginal, 2),
            'purchase_value_original' => round($purchaseValueOriginal, 2),
            'pnl_original' => round($pnlOriginal, 2),

            // Other currencies
            'pnl_usd' => round($pnlUsd, 2),
            'pnl_sgd' => round($pnlSgd, 2),
            'pnl_percent' => round($pnlPercent, 2),
            'is_profit' => $pnlMmk > 0,
        ];
    }

    /**
     * Get product label for display
     */
    public function getProductLabelAttribute()
    {
        if ($this->type === 'gold' && $this->product_type) {
            return GoldCalculator::getProductLabel($this->product_type);
        }
        return null;
    }

    /**
     * Get weight display string
     */
    public function getWeightDisplayAttribute()
    {
        if (!$this->troy_ounces) return null;

        if ($this->product_type === '1oz') {
            return "{$this->quantity} oz";
        } elseif (in_array($this->product_type, ['50g', '100g', 'custom'])) {
            return "{$this->weight_in_grams} grams";
        } elseif (in_array($this->product_type, ['1kyatthar', '10kyatthar'])) {
            $kyattharCount = $this->quantity * ($this->product_type === '1kyatthar' ? 1 : 10);
            return "{$kyattharCount} Kyatthar";
        }

        return null;
    }
}
