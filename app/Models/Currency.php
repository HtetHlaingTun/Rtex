<?php

namespace App\Models;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'flag_icon',
        'decimal_places',
        'min_buy_rate',
        'max_buy_rate',
        'min_sell_rate',
        'max_sell_rate',
        'notes',
        'is_active',
        'is_base_currency',
        'display_order',
        'created_by',
        'updated_by',
        'cbm_conversion_factor',
        'buy_spread_percentage',
        'sell_spread_percentage',
        'fixed_buy_margin',
        'fixed_sell_margin',
        'spread_type',
        'use_cbm_auto_fetch',
        'factor_last_updated',
        'factor_updated_by',
        'cbm_rate',


        // Logic & Sync Fields
        'source_mode',
        'manual_base_rate',
        'avg_bank_rate',
        'bank_markup_percentage',
        'banks_last_synced_at'
    ];

    protected $casts = [
        'cbm_conversion_factor' => 'decimal:6',
        'buy_spread_percentage' => 'decimal:4',
        'sell_spread_percentage' => 'decimal:4',
        'fixed_buy_margin' => 'decimal:2',
        'fixed_sell_margin' => 'decimal:2',
        'use_cbm_auto_fetch' => 'boolean',
        'is_active' => 'boolean',
        'is_base_currency' => 'boolean',
        'factor_last_updated' => 'datetime',
        'banks_last_synced_at' => 'datetime',
        'bank_markup_percentage' => 'float',
        'avg_bank_rate' => 'float',
        'manual_base_rate' => 'float',
    ];



    protected $attributes = [
        'source_mode' => 'bank_avg',
        'bank_markup_percentage' => 0,
        'buy_spread_percentage' => 0.5,
        'sell_spread_percentage' => 0.5,
        'spread_type' => 'percentage',
        'cbm_conversion_factor' => 1,
        'use_cbm_auto_fetch' => true,
        'is_active' => true,
        'decimal_places' => 2,

    ];

    /**
     * Auto-fill audit fields (Fixed for Console/Artisan commands)
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Default to ID 1 if no user is logged in (Command Line/Cron)
            $model->created_by = Auth::id() ?? 1;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id() ?? 1;
        });
    }

    /**
     * Business Logic: Calculation Engine
     */
    public function calculateWorkingRate($cbmRateFromApi = null)
    {
        return match ($this->source_mode) {
            'manual'   => (float) ($this->manual_base_rate ?? 0),
            'bank_avg' => (float) ($this->avg_bank_rate ?? 0) * (1 + (($this->bank_markup_percentage ?? 0) / 100)),
            'cbm'      => (float) ($cbmRateFromApi ?? 0) * (float) ($this->cbm_conversion_factor ?? 1),
            default    => (float) ($cbmRateFromApi ?? 0),
        };
    }

    // public function calculateRatesFromCBM($cbmRateFromApi)
    // {
    //     $workingRate = $this->calculateWorkingRate($cbmRateFromApi);
    //     $decimal = $this->decimal_places ?? 2;

    //     if ($this->spread_type === 'percentage') {
    //         $buyRate = $workingRate * (1 - (($this->buy_spread_percentage ?? 0.5) / 100));
    //         $sellRate = $workingRate * (1 + (($this->sell_spread_percentage ?? 0.5) / 100));
    //     } else {
    //         $buyRate = $workingRate - ($this->fixed_buy_margin ?? 0);
    //         $sellRate = $workingRate + ($this->fixed_sell_margin ?? 0);
    //     }

    //     return [
    //         'buy_rate'     => round(max(0, $buyRate), $decimal),
    //         'sell_rate'    => round(max(0, $sellRate), $decimal),
    //         'working_rate' => round($workingRate, $decimal),
    //         'active_mode'  => $this->source_mode,
    //     ];
    // }

    // Add these methods to your Currency model if not already present

    public function calculateRatesFromCBM(float $cbmRate): array
    {
        $factor = $this->cbm_conversion_factor ?? 1;
        $workingRate = $cbmRate * $factor;

        if ($this->spread_type === 'percentage') {
            $buySpread = $this->buy_spread_percentage ?? 0.5;
            $sellSpread = $this->sell_spread_percentage ?? 0.5;

            return [
                'buy_rate' => round($workingRate * (1 - $buySpread / 100), $this->decimal_places ?? 2),
                'sell_rate' => round($workingRate * (1 + $sellSpread / 100), $this->decimal_places ?? 2),
                'working_rate' => round($workingRate, $this->decimal_places ?? 2),
            ];
        } else {
            return [
                'buy_rate' => round(max(0, $workingRate - ($this->fixed_buy_margin ?? 0)), $this->decimal_places ?? 2),
                'sell_rate' => round($workingRate + ($this->fixed_sell_margin ?? 0), $this->decimal_places ?? 2),
                'working_rate' => round($workingRate, $this->decimal_places ?? 2),
            ];
        }
    }

    /**
     * Relationships
     */
    public function rates()
    {
        return $this->hasMany(ExchangeRate::class);
    }

    public function latestRate()
    {
        return $this->hasOne(ExchangeRate::class)
            ->where('is_verified', true)
            ->latest('rate_date'); // Order by actual rate date
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order');
    }
}
