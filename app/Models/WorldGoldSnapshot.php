<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorldGoldSnapshot extends Model
{

    use SoftDeletes; // 2. Add this


    protected $fillable = [
        'usd_price',
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


    // usdPrice * 1.02041: Adjusts the global price for local purity/margin.

    // * mmkRate: Converts the currency from USD to Myanmar Kyat.

    // / 1.9048 (or 1.8731): Converts the mass from 1 Ounce down to 1 Kyat Thar.
    // New system: 1 Kyatthar = 16.329g
    public static function convertToMmkNew(float $usdPrice, float $mmkRate): float
    {
        return round(($usdPrice * 1.02041 * $mmkRate) / 1.9048);
    }

    // Old system: 1 Kyatthar = 16.606g
    public static function convertToMmkOld(float $usdPrice, float $mmkRate): float
    {
        return round(($usdPrice * 1.02041 * $mmkRate) / 1.8731);
    }


    public function getIsTrendingUpAttribute(): bool
    {
        return $this->change >= 0;
    }

    // Helper: get latest USD/MMK rate from exchange_rates
    public static function getUsdMmkRate(): ?float
    {
        $rate = ExchangeRate::where('currency_id', 1) // USD
            ->where('status', 'verified')
            ->whereNotNull('mid_rate')
            ->latest('rate_date')
            ->first();

        return $rate ? floatval($rate->mid_rate) : null;
    }

    // Formula: (usd_per_oz * 1.02041 * usd_mmk) / 1.875
    public static function convertToMmk(float $usdPrice, float $mmkRate): float
    {
        return round(($usdPrice * 1.02041 * $mmkRate) / 1.875);
    }

    protected static function booted()
    {
        static::creating(function ($snapshot) {
            // Ensure all MMK prices are calculated if rate exists but fields are empty
            if ($snapshot->usd_mmk_rate && $snapshot->usd_price) {
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
        });
    }
}
