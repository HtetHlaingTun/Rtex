<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PriceHistory extends Model
{
    protected $table = 'price_histories';

    protected $fillable = [
        'priceable_id',
        'priceable_type',
        'old_data',
        'new_data',
        'field_changed',
        'old_value',
        'new_value',
        'action',
        'reason',
        'ip_address',
        'user_agent',
        'user_id'
    ];

    protected $casts = [
        'old_data' => 'json',
        'new_data' => 'json',
        'old_value' => 'decimal:4',
        'new_value' => 'decimal:4',
    ];

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
