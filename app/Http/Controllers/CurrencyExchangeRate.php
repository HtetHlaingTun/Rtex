<?php


namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;

use App\Services\CBMRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Standard Facade

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CurrencyExchangeRate extends Controller
{
    protected $cbmService;

    public function __construct(CBMRateService $cbmService)
    {
        $this->cbmService = $cbmService;
    }

    public function guestIndex()
    {
        // Fetch only the latest verified rate for each currency
        $latestRateIds = \App\Models\ExchangeRate::selectRaw('MAX(id)')
            ->where('status', 'verified')
            ->groupBy('currency_id');

        $rates = \App\Models\ExchangeRate::with('currency')
            ->whereIn('id', $latestRateIds)
            ->get();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'rates' => $rates,
            // The 'gold' object should contain both prices from your snapshot
            'gold' => \App\Models\WorldGoldSnapshot::latest()->first(),
        ]);
    }

    public function getChartData($currencyId, Request $request)
    {
        try {
            $period = $request->query('period', 'month');

            // Define date ranges based on period
            $days = match ($period) {
                'week' => 7,
                'month' => 30,
                'quarter' => 90,
                'year' => 365,
                'all' => 730, // 2 years
                default => 30
            };

            $startDate = now()->subDays($days);

            // Fetch exchange rates for the currency
            $rates = ExchangeRate::where('currency_id', $currencyId)
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'asc')
                ->get(['buy_rate', 'sell_rate', 'created_at']);

            // If no data found, return empty array
            if ($rates->isEmpty()) {
                return response()->json([]);
            }

            // For longer periods, aggregate data to reduce points
            if (in_array($period, ['year', 'all']) && $rates->count() > 100) {
                $rates = $this->aggregateRates($rates, $period);
            }

            return response()->json($rates);
        } catch (\Exception $e) {
            Log::error('Chart data error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load chart data'], 500);
        }
    }

    private function aggregateRates($rates, $period)
    {
        // Group by day for year, or by month for all
        $groupBy = $period === 'all' ? 'Y-m' : 'Y-m-d';

        $grouped = $rates->groupBy(function ($rate) use ($groupBy) {
            return $rate->created_at->format($groupBy);
        });

        $aggregated = [];
        foreach ($grouped as $date => $group) {
            $aggregated[] = [
                'buy_rate' => $group->avg('buy_rate'),
                'sell_rate' => $group->avg('sell_rate'),
                'created_at' => $group->first()->created_at
            ];
        }

        return collect($aggregated);
    }


    public function index()
    {
        $cbmAvailable = $this->cbmService->isAvailable();
        $cbmRates = $cbmAvailable ? $this->cbmService->fetchCurrentRates() : [];

        $currencies = Currency::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $rates = [];

        foreach ($currencies as $currency) {
            // 1. TRY LIVE DATA FIRST
            if (isset($cbmRates[$currency->code])) {
                $cbmData = $cbmRates[$currency->code];
                $calculated = $this->calculateRatesFromCBM($currency, $cbmData['cbm_rate']);

                $rates[] = $this->transformRateData($currency, $calculated, $cbmData['cbm_rate'], true);
            }
            // 2. FALLBACK: USE LAST SAVED DATABASE RATE
            else {
                $lastRate = ExchangeRate::where('currency_id', $currency->id)
                    ->where('is_verified', true)
                    ->latest()
                    ->first();

                if ($lastRate) {
                    $rates[] = [
                        'id' => $currency->id,
                        'currency' => $currency->only(['id', 'code', 'name', 'symbol']),
                        'buy_rate' => $lastRate->buy_rate,
                        'sell_rate' => $lastRate->sell_rate,
                        'cbm_rate' => $lastRate->cbm_rate ?? 0,
                        'is_live' => false, // Add a flag to show it's cached
                        'last_updated' => $lastRate->updated_at,
                    ];
                }
            }
        }

        return Inertia::render('Currency/Index', [
            'rates' => $rates,
            'cbm_available' => $cbmAvailable,
            'last_fetch_time' => count($rates) > 0 ? $rates[0]['last_updated'] : now(),
            'userRole' => Auth::user()->role,
        ]);
    }

    public function refresh()
    {
        $this->cbmService->clearCache();
        $cbmRates = $this->cbmService->fetchCurrentRates();

        return response()->json([
            'success' => true,
            'rates' => $cbmRates,
            'fetched_at' => now(),
        ]);
    }

    public function history(Currency $currency)
    {
        // Show both active and archived rates (soft deleted)
        $history = ExchangeRate::withTrashed()
            ->where('currency_id', $currency->id)
            ->where('is_verified', true)
            ->latest('rate_date')
            ->latest('created_at')
            ->paginate(20);

        // Add additional info to show if rate is archived
        $history->getCollection()->transform(function ($rate) {
            $rate->is_archived = $rate->deleted_at !== null;
            $rate->archived_at = $rate->deleted_at;
            $rate->spread_type_applied = $rate->factors['spread_type'] ?? 'percentage';
            $rate->working_rate = $rate->factors['working_rate'] ?? null;
            return $rate;
        });

        return Inertia::render('Currency/History', [
            'currency' => $currency,
            'history' => $history
        ]); {
        }
    }

    public function factors()
    {
        // Get ALL active currencies, not just those with rates
        $currencies = Currency::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('code')
            ->get();

        // Log for debugging
        Log::info('Factors page currencies count: ' . $currencies->count());
        Log::info('Currencies: ' . $currencies->pluck('code')->implode(', '));

        // Get current CBM rates for preview
        $cbmRates = $this->cbmService->fetchCurrentRates();

        // Enhance currencies with CBM rate data
        foreach ($currencies as $currency) {
            if (isset($cbmRates[$currency->code])) {
                $currency->current_cbm_rate = $cbmRates[$currency->code]['cbm_rate'];
                $preview = $this->calculateRatesFromCBM($currency, $currency->current_cbm_rate);
                $currency->preview_buy_rate = $preview['buy_rate'];
                $currency->preview_sell_rate = $preview['sell_rate'];
            } else {
                // Set default values if currency not found in CBM rates
                $currency->current_cbm_rate = 0;
                $currency->preview_buy_rate = 0;
                $currency->preview_sell_rate = 0;
            }
        }

        return Inertia::render('Currency/Factors', [
            'currencies' => $currencies, // Make sure this is passed as an array
            'cbm_available' => $this->cbmService->isAvailable(),
            'default_factor' => config('cbm.default_factor', 1),
        ]);
    }

    public function updateFactor(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'cbm_conversion_factor' => 'nullable|numeric|min:0.000001|max:999999',
            'buy_spread_percentage' => 'nullable|numeric|min:0|max:100',
            'sell_spread_percentage' => 'nullable|numeric|min:0|max:100',
            'fixed_buy_margin' => 'nullable|numeric|min:0',
            'fixed_sell_margin' => 'nullable|numeric|min:0',
            'spread_type' => 'required|in:percentage,fixed',
            'use_cbm_auto_fetch' => 'boolean',
        ]);

        $currency->update([
            'cbm_conversion_factor' => $validated['cbm_conversion_factor'] ?? $currency->cbm_conversion_factor,
            'buy_spread_percentage' => $validated['buy_spread_percentage'] ?? $currency->buy_spread_percentage,
            'sell_spread_percentage' => $validated['sell_spread_percentage'] ?? $currency->sell_spread_percentage,
            'fixed_buy_margin' => $validated['fixed_buy_margin'] ?? $currency->fixed_buy_margin,
            'fixed_sell_margin' => $validated['fixed_sell_margin'] ?? $currency->fixed_sell_margin,
            'spread_type' => $validated['spread_type'],
            'use_cbm_auto_fetch' => $validated['use_cbm_auto_fetch'] ?? $currency->use_cbm_auto_fetch,
            'factor_last_updated' => now(),
            'factor_updated_by' => Auth::id(),
        ]);

        // Clear cache to apply new factors immediately
        $this->cbmService->clearCache();

        return redirect()->back()->with('success', "Factor updated for {$currency->code}");
    }

    public function pending()
    {
        $pendingRates = ExchangeRate::with(['currency', 'creator'])
            ->where('is_verified', false)
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Currency/Pending', [
            'rates' => $pendingRates
        ]);
    }

    public function verify(Request $request, $id)
    {
        $newRate = ExchangeRate::findOrFail($id);

        ExchangeRate::where('currency_id', $newRate->currency_id)
            ->where('rate_date', $newRate->rate_date)
            ->where('is_verified', true)
            ->where('id', '!=', $newRate->id)
            ->delete();

        $newRate->update([
            'is_verified' => true,
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('currencies.index')->with('success', 'Rate published successfully.');
    }

    public function settings()
    {
        return Inertia::render('Currency/Settings', [
            'currencies' => Currency::orderBy('display_order', 'asc')
                ->orderBy('code', 'asc')
                ->get()
        ]);
    }

    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4',
            'display_order' => 'nullable|integer',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        Currency::create($validated);

        return redirect()->back()->with('success', "{$validated['code']} has been added.");
    }

    // public function destroy(Currency $currency)
    // {
    //     if ($currency->rates()->count() > 0) {
    //         return redirect()->back()->with('error', 'Cannot delete currency. It has existing exchange rate records.');
    //     }

    //     $currency->delete();
    //     return redirect()->back()->with('success', 'Currency removed successfully.');
    // }
    // In CurrencyController.php
    public function destroy(Currency $currency)
    {
        // This will now set 'deleted_at' in the DB instead of erasing the row.
        // The foreign key constraint will NOT trigger because the record still exists.
        $currency->delete();

        return back()->with('success', 'Currency moved to trash. History preserved.');
    }
    public function destroyRate($id)
    {
        $rate = ExchangeRate::findOrFail($id);

        if (!$rate->is_verified) {
            $rate->forceDelete();
        }

        return redirect()->route('currencies.index')->with('success', 'Submission rejected.');
    }

    private function calculateRatesFromCBM($currency, $cbmRate)
    {
        // Get conversion factor (manual or default)
        $factor = $currency->cbm_conversion_factor ?? config('cbm.default_factor', 1);

        // Calculate working rate
        $workingRate = $cbmRate * $factor;

        // Apply spread based on currency configuration
        if ($currency->spread_type === 'percentage') {
            $buySpread = $currency->buy_spread_percentage ?? 0.5;
            $sellSpread = $currency->sell_spread_percentage ?? 0.5;

            $buyRate = $workingRate * (1 - ($buySpread / 100));
            $sellRate = $workingRate * (1 + ($sellSpread / 100));

            $buySpreadApplied = $buySpread;
            $sellSpreadApplied = $sellSpread;
        } else {
            // Fixed margin
            $buyMargin = $currency->fixed_buy_margin ?? 0;
            $sellMargin = $currency->fixed_sell_margin ?? 0;

            $buyRate = $workingRate - $buyMargin;
            $sellRate = $workingRate + $sellMargin;

            $buySpreadApplied = $buyMargin;
            $sellSpreadApplied = $sellMargin;
        }

        // Ensure rates are positive
        $buyRate = max(0, $buyRate);
        $sellRate = max(0, $sellRate);

        return [
            'buy_rate' => round($buyRate, $currency->decimal_places),
            'sell_rate' => round($sellRate, $currency->decimal_places),
            'mid_rate' => round(($buyRate + $sellRate) / 2, $currency->decimal_places),
            'working_rate' => round($workingRate, $currency->decimal_places),
            'cbm_conversion_factor' => $factor,
            'buy_spread_applied' => $buySpreadApplied,
            'sell_spread_applied' => $sellSpreadApplied,
        ];
    }

    /**
     * Standardizes the rate data for the Inertia frontend.
     */
    private function transformRateData($currency, $calculated, $cbmRate, $isLive)
    {
        // You can add your trend logic here or pass it in
        return [
            'id' => $currency->id,
            'currency' => [
                'id' => $currency->id,
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'decimal_places' => $currency->decimal_places,
            ],
            'buy_rate' => $calculated['buy_rate'],
            'sell_rate' => $calculated['sell_rate'],
            'mid_rate' => $calculated['mid_rate'],
            'cbm_rate' => $cbmRate,
            'cbm_conversion_factor' => $calculated['cbm_conversion_factor'],
            'working_rate' => $calculated['working_rate'],
            'buy_spread_applied' => $calculated['buy_spread_applied'],
            'sell_spread_applied' => $calculated['sell_spread_applied'],
            'spread_type' => $currency->spread_type,
            'market_trend' => 'stable', // Default or calculate trend here
            'is_live' => $isLive,
            'last_updated' => now(),
        ];
    }



    /**
     * Show historical rates for guests
     */
    public function guestHistory($currencyIdentifier)
    {
        // Find currency by ID or code
        if (is_numeric($currencyIdentifier)) {
            $currency = Currency::find($currencyIdentifier);
        } else {
            $currency = Currency::where('code', strtoupper($currencyIdentifier))->first();
        }

        if (!$currency) {
            abort(404, 'Currency not found');
        }

        // Get all historical rates for this currency
        $history = ExchangeRate::where('currency_id', $currency->id)
            ->where('is_verified', true)
            ->orderBy('rate_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Transform the data for the frontend
        $transformedHistory = $history->getCollection()->map(function ($rate) {
            $factors = is_array($rate->factors) ? $rate->factors : json_decode($rate->factors, true) ?? [];

            return [
                'id' => $rate->id,
                'buy_rate' => floatval($rate->buy_rate),
                'sell_rate' => floatval($rate->sell_rate),
                'cbm_rate' => $rate->cbm_rate ? floatval($rate->cbm_rate) : null,
                'rate_date' => $rate->rate_date,
                'created_at' => $rate->created_at->toISOString(),
                'source_name' => $rate->source_name ?? 'CBM Auto-Fetch',
                'change_percentage' => $rate->change_percentage ? floatval($rate->change_percentage) : null,
                'market_trend' => $rate->market_trend,
            ];
        });

        // Create a new paginator with transformed data
        $paginatedHistory = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformedHistory,
            $history->total(),
            $history->perPage(),
            $history->currentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return Inertia::render('History', [
            'currency' => [
                'id' => $currency->id,
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
            ],
            'history' => $paginatedHistory,
            'breadcrumbs' => [
                ['label' => 'Home', 'route' => 'welcome'],
                ['label' => $currency->code . ' History', 'route' => 'currencies.history', 'params' => ['currency' => $currency->id]], // Add the required parameter
            ],
        ]);
    }
}
