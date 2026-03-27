<?php

namespace App\Models;

use App\Models\GoldType;
use App\Models\PriceHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class GoldPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gold_type_id',
        'price_date',
        'price',
        'currency_id',
        'opening_price',
        'closing_price',
        'high_price',
        'low_price',
        'previous_price',
        'change_percentage',
        'world_gold_usd',
        'usd_mmk_rate',
        'source_type',
        'source_name',
        'source_location',
        'trend',
        'market_notes',
        'market_factors',
        'status',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'price_date' => 'date',
        'price' => 'decimal:4',
        'is_verified' => 'boolean',
        'market_factors' => 'json',
        'verified_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        // 'created_by' is the foreign key on the gold_prices table
        return $this->belongsTo(User::class, 'created_by');
    }

    public function goldType(): BelongsTo
    {
        return $this->belongsTo(GoldType::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Polymorphic relationship to your price_histories table.
     */
    public function histories(): MorphMany
    {
        return $this->morphMany(PriceHistory::class, 'priceable');
    }

    /**
     * Polymorphic relationship to the verification_queue table.
     */
    public function queueEntries(): MorphMany
    {
        return $this->morphMany(VerificationQueue::class, 'verifiable');
    }

    // In App\Models\GoldPrice.php
    public function getMarketAnalysisAttribute()
    {
        $premiumFactor = 1.02041;
        $impliedRate = $this->usd_mmk_rate * $premiumFactor;

        return [
            'premium_display' => '+2.04%',
            'implied_rate' => round($impliedRate),
            'spread' => round($impliedRate - $this->usd_mmk_rate),
        ];
    }

    // Example logic: Automatically determine trend before saving


    protected static function booted()
    {
        static::creating(function ($goldPrice) {
            // empty() treats 0 as empty — use isset instead
            if (!array_key_exists('created_by', $goldPrice->getAttributes())) {
                $goldPrice->created_by = \Illuminate\Support\Facades\Auth::id();
            }

            if ($goldPrice->previous_price && $goldPrice->price) {
                if ($goldPrice->price > $goldPrice->previous_price)     $goldPrice->trend = 'up';
                elseif ($goldPrice->price < $goldPrice->previous_price) $goldPrice->trend = 'down';
                else                                                     $goldPrice->trend = 'stable';

                $goldPrice->change_percentage = (($goldPrice->price - $goldPrice->previous_price) / $goldPrice->previous_price) * 100;
            }
        });
    }
}
