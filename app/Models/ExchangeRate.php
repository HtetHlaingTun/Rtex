<?php
// app/Models/ExchangeRate.php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ExchangeRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'currency_id',
        'rate_date',
        'buy_rate',
        'sell_rate',
        'cbm_rate',
        'previous_buy_rate',
        'previous_sell_rate',
        'change_percentage',
        'market_trend',
        'market_analysis',
        'factors',
        'source_name',
        'source_reference',
        'status',
        'is_verified',
        'verified_at',
        'verified_by',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'rate_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'factors' => 'array',
        'buy_rate' => 'decimal:4',
        'sell_rate' => 'decimal:4',
        'cbm_rate' => 'decimal:4',
        'previous_buy_rate' => 'decimal:4',
        'previous_sell_rate' => 'decimal:4',
        'change_percentage' => 'decimal:4',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Check if running from command line or queue
            if (app()->runningInConsole() || app()->runningUnitTests()) {
                // Default system user ID (create a system user first)
                $model->created_by = 1; // System user ID
                $model->updated_by = 1;
            } else {
                // Normal web request - use authenticated user
                $model->created_by = Auth::id() ?? 1;
                $model->updated_by = Auth::id() ?? 1;
            }
        });

        static::updating(function ($model) {
            if (app()->runningInConsole() || app()->runningUnitTests()) {
                $model->updated_by = 1;
            } else {
                $model->updated_by = Auth::id() ?? 1;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
