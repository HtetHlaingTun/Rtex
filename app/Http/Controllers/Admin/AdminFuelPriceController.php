<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelCalibration;
use App\Models\FuelPrice;
use App\Services\FuelPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class AdminFuelPriceController extends Controller
{
    /**
     * Get USD/MMK rate from exchange_rates table (same as main site)
     * This is the SINGLE SOURCE OF TRUTH for all currency rates
     */
    private function getUsdMmkRate(): float
    {
        // Get USD currency ID
        $usd = DB::table('currencies')->where('code', 'USD')->first();

        if (!$usd) {
            return 4157.42; // Fallback
        }

        // PRIMARY: Get latest verified rate from exchange_rates table
        $latestRate = DB::table('exchange_rates')
            ->where('currency_id', $usd->id)
            ->where('is_verified', 1)
            ->latest('rate_date')
            ->first();

        if ($latestRate) {
            // Use mid_rate if available
            if ($latestRate->mid_rate && $latestRate->mid_rate > 0) {
                return (float) $latestRate->mid_rate;
            }

            // Otherwise average of buy and sell
            if ($latestRate->buy_rate > 0 && $latestRate->sell_rate > 0) {
                return ($latestRate->buy_rate + $latestRate->sell_rate) / 2;
            }
        }

        // FALLBACK: Use currencies table calculation
        if ($usd->avg_bank_rate && $usd->avg_bank_rate > 0) {
            $markup = $usd->bank_markup_percentage ?? 14.00;
            return $usd->avg_bank_rate * (1 + ($markup / 100));
        }

        // ABSOLUTE FALLBACK
        return 4157.42;
    }

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
            ] : [
                'octane_92' => 0,
                'octane_95' => 210,
                'diesel' => -300,
                'premium_diesel' => -150,
                'change_92' => 0,
                'updated_at' => null,
            ];
        }

        // Get USD rate using unified method
        $usdRate = $this->getUsdMmkRate();

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
    public function updateCalibration(Request $request)
    {
        $request->validate([
            'action' => 'required|in:factor,market_price',
            'calibration_factor' => 'required_if:action,factor|numeric|min:0.5|max:5.0',
            'market_price' => 'required_if:action,market_price|integer|min:1000|max:10000',
            'notes' => 'nullable|string|max:255',
        ]);

        $calibration = FuelCalibration::firstOrCreate(
            [],
            ['calibration_factor' => 1.4000]
        );

        if ($request->action === 'factor') {
            // Direct factor update
            $calibration->update([
                'calibration_factor' => $request->calibration_factor,
                'notes' => $request->notes ?? 'Manual factor update',
            ]);

            $message = "Calibration factor updated to {$request->calibration_factor}";

            return response()->json([
                'success' => true,
                'message' => $message,
                'calibration' => $calibration->fresh(),
            ]);
        }

        // Calibrate from market price
        $latest = FuelPrice::where('region', 'yangon')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latest) {
            return response()->json([
                'success' => false,
                'message' => 'No calculated price found. Run Update Now first.'
            ], 400);
        }

        // Get values from latest record
        $globalPrice = $latest->global_usd_reference ?? 3.07;
        $usdRate = $this->getUsdMmkRate();

        // Calculate base import (MMK per liter before any markup)
        $baseImport = ($globalPrice / 3.785) * $usdRate;

        if ($baseImport <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid base import calculation.'
            ], 400);
        }

        // Calculate new factor directly from base import
        $newFactor = $request->market_price / $baseImport;

        $calibration->update([
            'calibration_factor' => round($newFactor, 4),
            'reference_price_92' => $request->market_price,
            'global_price_at_calibration' => $globalPrice,
            'usd_mmk_at_calibration' => round($usdRate, 2),
            'notes' => $request->notes ?? "Calibrated to market: {$request->market_price} MMK",
        ]);

        $message = "Calibrated to {$request->market_price} MMK. New factor: " . round($newFactor, 4);

        return response()->json([
            'success' => true,
            'message' => $message,
            'calibration' => $calibration->fresh(),
        ]);
    }

    /**
     * Manual trigger update
     */
    public function updateNow()
    {
        try {
            Artisan::call('fuel:prices-update');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Fuel prices updated successfully!',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
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
        $factor = (float) $request->input('factor', FuelCalibration::getFactor());
        $usdRate = $this->getUsdMmkRate();
        $globalPrice = $service->fetchGlobalGasPrice();

        return response()->json(
            $service->calculatePrices($globalPrice, $usdRate, $factor)
        );
    }

    /**
     * Get statistics for dashboard
     */
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

    // ============================================
    // API METHODS FOR GUEST FUEL PAGE
    // ============================================

    /**
     * API: Get current prices
     */
    // public function indexApi()
    // {
    //     $latest = DB::table('fuel_prices')
    //         ->where('region', 'yangon')
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     return response()->json([
    //         'octane_92'      => $latest->octane_92 ?? 0,
    //         'octane_95'      => $latest->octane_95 ?? 0,
    //         'diesel'         => $latest->diesel ?? 0,
    //         'premium_diesel' => $latest->premium_diesel ?? 0,
    //     ]);
    // }

    /**
     * API: History for specific region
     */
    // public function historyApi($region)
    // {
    //     $prices = DB::table('fuel_prices')
    //         ->where('region', $region)
    //         ->orderBy('created_at', 'desc')
    //         ->limit(30)
    //         ->get()
    //         ->map(fn($p) => [
    //             'date' => date('Y-m-d', strtotime($p->created_at)),
    //             'time' => date('H:i', strtotime($p->created_at)),
    //             'octane_92' => (int)$p->octane_92,
    //             'octane_95' => (int)$p->octane_95,
    //             'diesel' => (int)$p->diesel,
    //             'premium_diesel' => (int)$p->premium_diesel,
    //         ]);

    //     return response()->json([
    //         'success' => true,
    //         'region' => $region,
    //         'history' => $prices
    //     ]);
    // }

    // /**
    //  * Guest fuel page
    //  */
    // public function guestIndex()
    // {
    //     return Inertia::render('Fuel/FuelPrice', [
    //         'currentPrices' => FuelPrice::latest()->first(),
    //         'history' => FuelPrice::orderBy('created_at', 'desc')->take(7)->get(),
    //         'breadcrumbs' => [
    //             ['label' => 'Home', 'route' => 'welcome'],
    //             ['label' => 'Fuel', 'route' => 'fuel-prices'],
    //         ],
    //     ]);
    // }
}
