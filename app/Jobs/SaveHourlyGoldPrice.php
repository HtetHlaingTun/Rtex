<?php

namespace App\Jobs;

use App\Models\Currency;
use App\Models\GoldPrice;
use App\Models\GoldType;
use App\Models\WorldGoldSnapshot;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SaveHourlyGoldPrice
{
    use Queueable;

    public function handle(): void
    {
        try {
            // 1. Get the most recent snapshot (Global Price)
            $latest = WorldGoldSnapshot::latest('fetched_at')->first();

            if (!$latest || !$latest->usd_mmk_rate) {
                Log::warning('SaveHourlyGoldPrice: No snapshot or MMK rate available');
                return;
            }

            // 2. Ensure MMK currency exists
            $mmkCurrencyId = Currency::where('code', 'MMK')->value('id');
            if (!$mmkCurrencyId) {
                Log::warning('SaveHourlyGoldPrice: MMK currency not found');
                return;
            }

            // 3. Process each active Myanmar gold type
            $activeTypes = GoldType::where('is_active', true)
                ->where('category', 'myanmar')
                ->get();

            foreach ($activeTypes as $type) {
                // Determine which formula to use based on the GoldType system
                $currentPrice = ($type->system === 'old')
                    ? $latest->mmk_price_old
                    : $latest->mmk_price_new;

                if (!$currentPrice) continue;

                // Find the existing record for today (to update) or the most recent one (for change calc)
                $todayDate = Carbon::now('Asia/Singapore')->toDateString();

                $previousRecord = GoldPrice::where('gold_type_id', $type->id)
                    ->where('source_type', 'auto')
                    ->where('status', 'verified')
                    ->latest('created_at')
                    ->first();

                // Calculate Change Percentage
                $previousPrice = $previousRecord?->price ?? $currentPrice;
                $changePercent = $previousPrice > 0
                    ? round((($currentPrice - $previousPrice) / $previousPrice) * 100, 4)
                    : 0;

                // 4. Save or Update today's record
                GoldPrice::updateOrCreate(
                    [
                        'gold_type_id' => $type->id,
                        'price_date'   => $todayDate,
                        'source_type'  => 'auto',
                    ],
                    [
                        'currency_id'       => $mmkCurrencyId,
                        'price'             => $currentPrice,
                        'previous_price'    => $previousPrice,
                        'change_percentage' => $changePercent,
                        'world_gold_usd'    => $latest->usd_price,
                        'usd_mmk_rate'      => $latest->usd_mmk_rate,
                        'status'            => 'verified',
                        'market_notes'      => "Auto hourly update: World \${$latest->usd_price} @ {$latest->usd_mmk_rate} MMK",
                        'created_by'        => 1, // System/Admin ID
                        'updated_at'        => Carbon::now('Asia/Singapore'),
                    ]
                );
            }

            Log::info("Hourly GoldPrice sync completed for {$activeTypes->count()} types.");

            // 5. Run the history cleanup
            $this->cleanupOldRecords();
        } catch (\Exception $e) {
            Log::error('SaveHourlyGoldPrice Error: ' . $e->getMessage());
        }
    }

    /**
     * Collapses old history into 1 record per day to save space.
     */
    private function cleanupOldRecords(): void
    {
        $cutoff = Carbon::now('Asia/Singapore')->subDays(10)->startOfDay();

        $activeTypeIds = GoldType::where('is_active', true)
            ->where('category', 'myanmar')
            ->pluck('id');

        foreach ($activeTypeIds as $typeId) {
            // Find days older than 10 days that have multiple "auto" entries
            $oldDates = GoldPrice::where('gold_type_id', $typeId)
                ->where('source_type', 'auto')
                ->where('price_date', '<', $cutoff)
                ->selectRaw('price_date')
                ->groupBy('price_date')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('price_date');

            foreach ($oldDates as $date) {
                // Keep the very last update of that day
                $keeperId = GoldPrice::where('gold_type_id', $typeId)
                    ->where('source_type', 'auto')
                    ->where('price_date', $date)
                    ->latest('created_at')
                    ->value('id');

                // Delete the others
                GoldPrice::where('gold_type_id', $typeId)
                    ->where('source_type', 'auto')
                    ->where('price_date', $date)
                    ->where('id', '!=', $keeperId)
                    ->delete();
            }
        }

        Log::info('GoldPrice history cleanup: Old records collapsed to daily snapshots.');
    }
}
