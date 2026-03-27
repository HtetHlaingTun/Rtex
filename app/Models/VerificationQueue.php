<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VerificationQueue extends Model
{
    protected $table = 'verification_queue';

    protected $fillable = [
        'verifiable_id',
        'verifiable_type',
        'status',
        'submitted_data',
        'submission_notes',
        'priority',
        'due_date',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'review_action',
        'submitted_by'
    ];

    protected $casts = [
        'submitted_data' => 'json',
        'reviewed_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    /**
     * The polymorphic relationship. 
     * This will return either an ExchangeRate or a GoldPrice model.
     */
    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
