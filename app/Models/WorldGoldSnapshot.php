<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorldGoldSnapshot extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'usd_price',
        'sgd_price',
        'usd_sgd_rate',
        'change',
        'change_percent',
        'day_high',
        'day_low',
        'previous_close',
        'usd_mmk_rate',
        'mmk_price',
        'mmk_price_new',
        'mmk_price_old',
        'fetched_at',
    ];

    protected $casts = [
        'usd_price' => 'float',
        'sgd_price' => 'float',
        'usd_sgd_rate' => 'float',
        'change' => 'float',
        'change_percent' => 'float',
        'day_high' => 'float',
        'day_low' => 'float',
        'previous_close' => 'float',
        'usd_mmk_rate' => 'float',
        'mmk_price' => 'float',
        'mmk_price_new' => 'float',
        'mmk_price_old' => 'float',
        'fetched_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function asDateTime($value)
    {
        return parent::asDateTime($value)->timezone('Asia/Singapore');
    }

    /**
     * Get the latest USD/MMK rate from exchange_rates (bank_avg rate with markup)
     * This is the REAL market rate, not CBM reference
     */
    public static function getUsdMmkRate(): ?float
    {
        // Get the latest USD exchange rate from bank_avg mode
        $usdCurrency = Currency::where('code', 'USD')->first();

        if (!$usdCurrency) {
            return null;
        }

        $rate = ExchangeRate::where('currency_id', $usdCurrency->id)
            ->where('status', 'verified')
            ->where('is_verified', true)
            ->latest('created_at')
            ->first();

        // Return the mid rate (average of buy and sell) for gold calculation
        if ($rate) {
            return floatval(($rate->buy_rate + $rate->sell_rate) / 2);
        }

        // Fallback: use stored avg_bank_rate from currency if available
        if ($usdCurrency->avg_bank_rate > 0) {
            return floatval($usdCurrency->avg_bank_rate);
        }

        return null;
    }

    /**
     * New system conversion (16.329g per Kyat Thar)
     * Formula: (USD Gold Price × Purity Factor × USD-MMK Rate) ÷ Kyat Thar Weight in Ounces
     * 
     * @param float $usdPrice USD gold price per ounce
     * @param float $mmkRate USD-MMK exchange rate (mid rate)
     * @return float Gold price in MMK per Kyat Thar
     */
    public static function convertToMmkNew(float $usdPrice, float $mmkRate): float
    {
        // 1 Kyat Thar = 16.329g = 0.5247 oz (16.329 / 31.1035)
        // So formula: USD Price × 1.02041 (purity) × USD-MMK Rate × 0.5247 (oz per Kyat)
        // Or simplified: (USD Price × 1.02041 × USD-MMK Rate) ÷ (31.1035 / 16.329)
        // 31.1035 / 16.329 = 1.9048
        return round(($usdPrice * 1.02041 * $mmkRate) / 1.9048);
    }

    /**
     * Old system conversion (16.606g per Kyat Thar)
     * Formula: (USD Gold Price × Purity Factor × USD-MMK Rate) ÷ Kyat Thar Weight in Ounces
     * 
     * @param float $usdPrice USD gold price per ounce
     * @param float $mmkRate USD-MMK exchange rate (mid rate)
     * @return float Gold price in MMK per Kyat Thar
     */
    public static function convertToMmkOld(float $usdPrice, float $mmkRate): float
    {
        // 1 Kyat Thar = 16.606g = 0.5338 oz (16.606 / 31.1035)
        // 31.1035 / 16.606 = 1.8731
        return round(($usdPrice * 1.02041 * $mmkRate) / 1.8731);
    }

    /**
     * Generic conversion (1.875 Kyat Thar per ounce - for reference)
     * @deprecated Use convertToMmkNew or convertToMmkOld instead
     */
    public static function convertToMmk(float $usdPrice, float $mmkRate): float
    {
        return round(($usdPrice * 1.02041 * $mmkRate) / 1.875);
    }

    /**
     * Convert SGD gold price to USD equivalent
     * 
     * @param float $sgdPrice Gold price in SGD per ounce
     * @param float $usdSgdRate USD/SGD exchange rate
     * @return float USD equivalent price per ounce
     */
    public static function convertSgdToUsd(float $sgdPrice, float $usdSgdRate): float
    {
        return $sgdPrice / $usdSgdRate;
    }

    /**
     * Calculate the final validated MMK price by comparing USD and SGD channels
     * Returns the most accurate price based on cross-market validation
     * 
     * @param float $usdPrice USD gold price per ounce
     * @param float $sgdPrice SGD gold price per ounce
     * @param float $usdSgdRate USD/SGD exchange rate
     * @param float $usdMmkRate USD/MMK exchange rate
     * @param string $system 'new' or 'old' for Kyat Thar system
     * @return float Gold price in MMK per Kyat Thar
     */
    public static function calculateValidatedPrice(float $usdPrice, float $sgdPrice, float $usdSgdRate, float $usdMmkRate, string $system = 'new'): float
    {
        // Calculate price from USD channel
        $priceFromUsd = $system === 'new'
            ? self::convertToMmkNew($usdPrice, $usdMmkRate)
            : self::convertToMmkOld($usdPrice, $usdMmkRate);

        // Convert SGD gold to USD equivalent
        $usdEquivalent = self::convertSgdToUsd($sgdPrice, $usdSgdRate);

        // Calculate price from SGD channel
        $priceFromSgd = $system === 'new'
            ? self::convertToMmkNew($usdEquivalent, $usdMmkRate)
            : self::convertToMmkOld($usdEquivalent, $usdMmkRate);

        // Calculate discrepancy percentage
        $discrepancy = abs($priceFromUsd - $priceFromSgd) / max($priceFromUsd, $priceFromSgd) * 100;

        // If discrepancy is within 2%, use average (both channels agree)
        if ($discrepancy <= 2) {
            return round(($priceFromUsd + $priceFromSgd) / 2);
        }

        // If discrepancy is high, use weighted average
        // USD channel has higher liquidity in Myanmar market, so weight it 70%
        $weightedPrice = ($priceFromUsd * 0.7) + ($priceFromSgd * 0.3);

        return round($weightedPrice);
    }

    /**
     * Get the current market rate from exchange_rates (buy rate)
     */
    public static function getCurrentBuyRate(): ?float
    {
        $usdCurrency = Currency::where('code', 'USD')->first();

        if (!$usdCurrency) {
            return null;
        }

        $rate = ExchangeRate::where('currency_id', $usdCurrency->id)
            ->where('status', 'verified')
            ->latest('created_at')
            ->first();

        return $rate ? floatval($rate->buy_rate) : null;
    }

    /**
     * Get the current market rate from exchange_rates (sell rate)
     */
    public static function getCurrentSellRate(): ?float
    {
        $usdCurrency = Currency::where('code', 'USD')->first();

        if (!$usdCurrency) {
            return null;
        }

        $rate = ExchangeRate::where('currency_id', $usdCurrency->id)
            ->where('status', 'verified')
            ->latest('created_at')
            ->first();

        return $rate ? floatval($rate->sell_rate) : null;
    }

    /**
     * Calculate trend based on price change
     */
    public function getIsTrendingUpAttribute(): bool
    {
        return $this->change >= 0;
    }

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::creating(function ($snapshot) {
            // Auto-calculate MMK prices if USD rate is available
            if ($snapshot->usd_mmk_rate && $snapshot->usd_price) {
                // Check if both USD and SGD channels are available for validation
                if ($snapshot->sgd_price && $snapshot->usd_sgd_rate) {
                    // DUAL VALIDATION: Use both USD and SGD channels for more accurate pricing
                    if (is_null($snapshot->mmk_price_new)) {
                        $snapshot->mmk_price_new = self::calculateValidatedPrice(
                            $snapshot->usd_price,
                            $snapshot->sgd_price,
                            $snapshot->usd_sgd_rate,
                            $snapshot->usd_mmk_rate,
                            'new'
                        );
                    }

                    if (is_null($snapshot->mmk_price_old)) {
                        $snapshot->mmk_price_old = self::calculateValidatedPrice(
                            $snapshot->usd_price,
                            $snapshot->sgd_price,
                            $snapshot->usd_sgd_rate,
                            $snapshot->usd_mmk_rate,
                            'old'
                        );
                    }
                }
                // FALLBACK: Only USD channel available
                else {
                    if (is_null($snapshot->mmk_price)) {
                        $snapshot->mmk_price = self::convertToMmk($snapshot->usd_price, $snapshot->usd_mmk_rate);
                    }
                    if (is_null($snapshot->mmk_price_new)) {
                        $snapshot->mmk_price_new = self::convertToMmkNew($snapshot->usd_price, $snapshot->usd_mmk_rate);
                    }
                    if (is_null($snapshot->mmk_price_old)) {
                        $snapshot->mmk_price_old = self::convertToMmkOld($snapshot->usd_price, $snapshot->usd_mmk_rate);
                    }
                }
            }
        });
    }
}
