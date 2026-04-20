<?php
// app/Http/Controllers/Admin/FuelPriceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelCalibration;
use App\Models\FuelPrice;
use App\Services\FuelPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\Process\Process;

class AdminFuelPriceController extends Controller
{
    /**
     * Show fuel price dashboard (Vue page)
     */
    public function index()
    {
        $calibration = FuelCalibration::first();

        // Get latest prices
        $latestPrices = FuelPrice::whereDate('created_at', today())
            ->get()
            ->keyBy('region');

        if ($latestPrices->isEmpty()) {
            $latestPrices = FuelPrice::orderBy('created_at', 'desc')
                ->take(8)
                ->get()
                ->keyBy('region');
        }

        // Format prices for frontend
        $formattedPrices = [];
        $regions = ['yangon', 'mandalay', 'naypyidaw', 'ayeyarwady', 'bago', 'magway', 'sagaing', 'thanintharyi'];

        foreach ($regions as $region) {
            $price = $latestPrices[$region] ?? null;
            $formattedPrices[$region] = $price ? [
                'octane_92' => (int) $price->octane_92,
                'octane_95' => (int) $price->octane_95,
                'diesel' => (int) $price->diesel,
                'premium_diesel' => (int) $price->premium_diesel,
                'change_92' => (float) $price->change_percent_92,
                'updated_at' => $price->created_at?->toDateTimeString(),
            ] : null;
        }

        // Get USD rate
        $usd = DB::table('currencies')->where('code', 'USD')->first();
        $usdRate = $usd ? round($usd->avg_bank_rate * (1 + ($usd->bank_markup_percentage / 100)), 2) : 0;

        // Get stats
        $stats = $this->getStats();

        // Get history for chart (last 7 days)
        $history = FuelPrice::where('region', 'yangon')
            ->orderBy('created_at', 'desc')
            ->take(7)
            ->get()
            ->map(fn($p) => [
                'date' => $p->created_at->format('M d'),
                'price' => (int) $p->octane_92,
            ])
            ->reverse()
            ->values();

        return Inertia::render('Admin/Fuel/Index', [
            'calibration' => [
                'factor' => (float) ($calibration->calibration_factor ?? 1.4000),
                'reference_price' => (int) ($calibration->reference_price_92 ?? 0),
                'updated_at' => $calibration->updated_at?->toDateTimeString(),
                'notes' => $calibration->notes,
            ],
            'prices' => $formattedPrices,
            'usdRate' => $usdRate,
            'stats' => $stats,
            'history' => $history,
            'regions' => $regions,
        ]);
    }

    /**
     * Update calibration
     */
    // app/Http/Controllers/Admin/FuelPriceController.php

    public function updateCalibration(Request $request)
    {
        $request->validate([
            'action' => 'required|in:factor,market_price',
            'calibration_factor' => 'required_if:action,factor|numeric|min:0.5|max:5.0',
            'market_price' => 'required_if:action,market_price|integer|min:1000|max:10000',
            'notes' => 'nullable|string|max:255',
        ]);

        $calibration = FuelCalibration::firstOrCreate([]);

        if ($request->action === 'factor') {
            $calibration->update([
                'calibration_factor' => $request->calibration_factor,
                'notes' => $request->notes ?? 'Manual factor update',
            ]);
            $message = "Calibration factor updated to {$request->calibration_factor}";
        } else {
            $latest = FuelPrice::where('region', 'yangon')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latest) {
                return response()->json([
                    'success' => false,
                    'message' => 'No calculated price found.'
                ], 400);
            }

            $calculatedPrice = $latest->octane_92;
            $currentFactor = $calibration->calibration_factor ?? 1.4000;
            $newFactor = ($request->market_price / $calculatedPrice) * $currentFactor;

            $calibration->update([
                'calibration_factor' => round($newFactor, 4),
                'reference_price_92' => $request->market_price,
                'notes' => $request->notes ?? "Calibrated to market: {$request->market_price} MMK",
            ]);

            $message = "Calibrated to {$request->market_price} MMK. New factor: " . round($newFactor, 4);
        }

        // 🔥 AUTO-RUN UPDATE COMMAND
        try {
            Artisan::call('fuel:prices-update');
            $message .= " Prices updated automatically!";
        } catch (\Exception $e) {
            $message .= " Manual update may be required.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'calibration' => $calibration->fresh(),
            'auto_updated' => true,
        ]);
    }

    public function updateNow()
    {
        try {
            // Run in background (non-blocking)
            $process = new Process(['php', base_path('artisan'), 'fuel:prices-update']);
            $process->setTimeout(300); // 5 minutes max
            $process->start(); // Don't wait for result

            return response()->json([
                'success' => true,
                'message' => 'Fuel price update started in background. Page will refresh in 3 seconds.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start update: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * API health check
     */
    public function apiHealth(FuelPriceService $service)
    {
        return response()->json($service->checkApiHealth());
    }

    /**
     * Preview prices with factor
     */
    public function preview(Request $request, FuelPriceService $service)
    {
        $factor = $request->input('factor', FuelCalibration::getFactor());

        $usd = DB::table('currencies')->where('code', 'USD')->first();
        $usdRate = $usd->avg_bank_rate * (1 + ($usd->bank_markup_percentage / 100));

        $globalPrice = $service->fetchGlobalGasPrice();

        return response()->json(
            $service->calculatePrices($globalPrice, $usdRate, (float) $factor)
        );
    }

    private function getStats(): array
    {
        $lastWeek = FuelPrice::where('region', 'yangon')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();

        if ($lastWeek->count() < 2) {
            return [
                'trend' => 'stable',
                'change' => 0,
                'change_percent' => 0,
                'avg' => 0,
                'min' => 0,
                'max' => 0,
            ];
        }

        $latest = $lastWeek->first();
        $oldest = $lastWeek->last();
        $change = $latest->octane_92 - $oldest->octane_92;
        $changePercent = $oldest->octane_92 > 0 ? ($change / $oldest->octane_92) * 100 : 0;

        return [
            'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            'change' => round($change),
            'change_percent' => round($changePercent, 2),
            'avg' => round($lastWeek->avg('octane_92')),
            'min' => round($lastWeek->min('octane_92')),
            'max' => round($lastWeek->max('octane_92')),
        ];
    }
}
