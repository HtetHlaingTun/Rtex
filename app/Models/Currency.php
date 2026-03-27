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
        'factor_updated_by'
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
    ];

    // Auto-fill audit fields
    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->created_by = Auth::id());
        static::updating(fn($model) => $model->updated_by = Auth::id());
    }

    /**
     * Relationships
     */
    public function rates()
    {
        return $this->hasMany(ExchangeRate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope to get only active currencies for the dropdowns
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order');
    }



    public function latestRate()
    {
        return $this->hasOne(ExchangeRate::class)
            ->where('is_verified', true)
            ->where('status', 'verified')
            ->latest('rate_date');
    }



    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function factorUpdater()
    {
        return $this->belongsTo(User::class, 'factor_updated_by');
    }

    public function getEffectiveFactorAttribute()
    {
        return $this->cbm_conversion_factor ?? config('cbm.default_factor', 1);
    }

    public function calculateWorkingRate($cbmRate)
    {
        return $cbmRate * $this->effective_factor;
    }

    public function calculateRatesFromCBM($cbmRate)
    {
        // Get conversion factor (manual or default)
        $factor = $this->cbm_conversion_factor ?? config('cbm.default_factor', 1);

        // Calculate working rate
        $workingRate = $cbmRate * $factor;

        // Apply spread based on currency configuration
        if ($this->spread_type === 'percentage') {
            $buySpread = $this->buy_spread_percentage ?? 0.5;
            $sellSpread = $this->sell_spread_percentage ?? 0.5;

            $buyRate = $workingRate * (1 - ($buySpread / 100));
            $sellRate = $workingRate * (1 + ($sellSpread / 100));

            $buySpreadApplied = $buySpread;
            $sellSpreadApplied = $sellSpread;
        } else {
            // Fixed margin
            $buyMargin = $this->fixed_buy_margin ?? 0;
            $sellMargin = $this->fixed_sell_margin ?? 0;

            $buyRate = $workingRate - $buyMargin;
            $sellRate = $workingRate + $sellMargin;

            $buySpreadApplied = $buyMargin;
            $sellSpreadApplied = $sellMargin;
        }

        // Ensure rates are positive
        $buyRate = max(0, $buyRate);
        $sellRate = max(0, $sellRate);

        return [
            'buy_rate' => round($buyRate, $this->decimal_places),
            'sell_rate' => round($sellRate, $this->decimal_places),
            'mid_rate' => round(($buyRate + $sellRate) / 2, $this->decimal_places),
            'working_rate' => round($workingRate, $this->decimal_places),
            'cbm_conversion_factor' => $factor,
            'buy_spread_applied' => $buySpreadApplied,
            'sell_spread_applied' => $sellSpreadApplied,
            'spread_type_applied' => $this->spread_type,
        ];
    }

    public function hasValidFactor()
    {
        return $this->cbm_conversion_factor !== null && $this->cbm_conversion_factor > 0;
    }
}
