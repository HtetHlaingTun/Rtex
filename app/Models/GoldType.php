<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoldType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'purity',
        'unit',
        'gram_conversion',
        'description',
        'min_price',
        'max_price',
        'color_code',
        'icon',
        'is_active',
        'show_on_dashboard',
        'display_order',
        'metadata',
        'system',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_dashboard' => 'boolean',
        'metadata' => 'json',
        'gram_conversion' => 'decimal:4',
        'display_order' => 'integer',
    ];

    /**
     * Relationship to all price entries.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(GoldPrice::class)->orderBy('price_date', 'desc');
    }

    /**
     * Helper to get the most recent verified price.
     */
    public function latestPrice(): HasOne
    {
        return $this->hasOne(GoldPrice::class)
            ->where('status', 'verified')
            ->latest('price_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function latestVerifiedPrice()
    {
        return $this->hasOne(GoldPrice::class)->where('status', 'verified')->latestOfMany();
    }
}
