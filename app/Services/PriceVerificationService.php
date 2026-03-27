<?php

namespace App\Services;

use App\Models\GoldPrice;
use App\Models\PriceHistory;
use App\Models\VerificationQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PriceVerificationService
{
    /**
     * Submit a new price record into the verification queue.
     * Works for both ExchangeRate and GoldPrice models.
     */
    public function submitToQueue(GoldPrice $goldPrice, array $data, $notes)
    {
        // Get the previous price for comparison
        $previous = GoldPrice::where('gold_type_id', $data['gold_type_id'])
            ->where('id', '!=', $goldPrice->id)
            ->latest('price_date')
            ->first();

        if ($previous) {
            $diff = abs($data['price'] - $previous->price);
            $percentChange = ($diff / $previous->price) * 100;

            // If price change is less than 5%, auto-verify it
            if ($percentChange < 5) {
                $goldPrice->update(['status' => 'verified']);
                return;
            }
        }

        // Otherwise, it stays 'pending' for admin eyes
        $goldPrice->update(['status' => 'pending']);
    }

    /**
     * Approve a pending request. 
     * Updates the original record, marks queue as approved, and logs history.
     */
    public function approve(VerificationQueue $queue, string $reviewNotes = null)
    {
        return DB::transaction(function () use ($queue, $reviewNotes) {
            $model = $queue->verifiable;

            // 1. Update the actual GoldPrice or ExchangeRate record
            $model->update(array_merge($queue->submitted_data, [
                'status'        => 'verified',
                'is_verified'   => true,
                'verified_at'   => now(),
                'verified_by'   => Auth::id(),
            ]));

            // 2. Mark the Queue entry as approved
            $queue->update([
                'status'        => 'approved',
                'reviewed_by'   => Auth::id(),
                'reviewed_at'   => now(),
                'review_notes'  => $reviewNotes,
                'review_action' => 'approved'
            ]);

            // 3. Create Audit Log
            return $this->logHistory($model, 'verified', "Approved: " . $reviewNotes);
        });
    }

    /**
     * Reject a pending request.
     */
    public function reject(VerificationQueue $queue, string $reason)
    {
        return DB::transaction(function () use ($queue, $reason) {
            $model = $queue->verifiable;

            // Update original record status
            $model->update(['status' => 'rejected']);

            // Update queue entry
            $queue->update([
                'status'        => 'rejected',
                'reviewed_by'   => Auth::id(),
                'reviewed_at'   => now(),
                'review_notes'  => $reason,
                'review_action' => 'rejected'
            ]);

            return $this->logHistory($model, 'rejected', $reason);
        });
    }

    /**
     * Internal helper to log changes to the price_histories table.
     */
    protected function logHistory($model, string $action, string $reason)
    {
        return PriceHistory::create([
            'priceable_id'   => $model->id,
            'priceable_type' => get_class($model),
            'action'         => $action,
            'reason'         => $reason,
            'user_id'        => Auth::id(),
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            // You can also store old_data vs new_data here if needed
        ]);
    }
}
