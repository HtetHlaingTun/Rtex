<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoldPrice;
use App\Models\GoldType;
use App\Models\WorldGoldSnapshot;
use App\Services\PriceVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class GoldPriceController extends Controller
{
    protected $verificationService;

    public function __construct(PriceVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    public function index()
    {
        // Get all gold types with their latest verified prices
        $goldTypes = GoldType::with(['latestVerifiedPrice', 'prices' => function ($q) {
            $q->latest('price_date')->limit(1);
        }])->get();

        // Count pending gold prices
        $pendingGoldCount = GoldPrice::where('status', 'pending')->count();

        // Get latest world gold snapshot
        $worldGoldSnapshot = WorldGoldSnapshot::latest('fetched_at')->first();

        // Get latest SGD rate from the WorldGoldSnapshot (since your command already fetches it)
        $sgdRate = null;
        if ($worldGoldSnapshot && $worldGoldSnapshot->usd_sgd_rate) {
            $sgdRate = [
                'usd_sgd_rate' => $worldGoldSnapshot->usd_sgd_rate,
                'sgd_price' => $worldGoldSnapshot->sgd_price,
                'change' => $worldGoldSnapshot->change,
                'change_percent' => $worldGoldSnapshot->change_percent,
                'fetched_at' => $worldGoldSnapshot->fetched_at,
            ];
        }

        return Inertia::render('Gold/Index', [
            'goldTypes' => $goldTypes,
            'pending_gold_count' => $pendingGoldCount,
            'worldGoldSnapshot' => $worldGoldSnapshot,
            'sgdRate' => $sgdRate,
        ]);
    }

    public function pending()
    {
        return Inertia::render('Gold/Pending', [
            'pendingPrices' => GoldPrice::with(['goldType', 'creator', 'currency'])
                ->where('status', 'pending')
                ->latest()
                ->paginate(10)
        ]);
    }

    public function approve(GoldPrice $goldPrice)
    {
        $goldPrice->update(['status' => 'verified']);

        return back()->with('success', "Price for {$goldPrice->goldType->name} has been verified.");
    }

    public function create()
    {
        return Inertia::render('Gold/Create', [
            'goldTypes'      => GoldType::where('is_active', true)->get(),
            'currencies'     => Currency::where('is_active', true)->get(),
            'previousPrices' => GoldPrice::where('status', 'verified')
                ->latest('price_date')
                ->get()
                ->groupBy('gold_type_id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'gold_type_id' => 'required|exists:gold_types,id',
            'currency_id'  => 'required|exists:currencies,id',
            'price'        => 'required|numeric',
            'price_date'   => 'required|date',
        ]);

        // ✅ Only delete the previous PENDING record — keeps verified history intact
        GoldPrice::where('gold_type_id', $request->gold_type_id)
            ->where('status', 'pending')
            ->delete();

        GoldPrice::create([
            'gold_type_id' => $request->gold_type_id,
            'currency_id'  => $request->currency_id,
            'price'        => $request->price,
            'price_date'   => $request->price_date,
            'status'       => 'pending',
            'created_by'   => Auth::id(),
            'market_notes' => $request->market_notes,
        ]);

        return redirect()->route('gold.index')->with('success', 'Rate updated successfully.');
    }

    public function history($id)
    {
        $goldType = GoldType::findOrFail($id);

        // Get paginated history
        $history = GoldPrice::where('gold_type_id', $id)
            ->orderBy('price_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        // Get chart data (last 30 days)
        $chartData = GoldPrice::where('gold_type_id', $id)
            ->where('status', 'verified')
            ->orderBy('price_date', 'asc')
            ->limit(30)
            ->get(['price_date as date', 'price'])
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'price' => (float) $item->price
                ];
            });

        // Get today's price
        $todayPrice = GoldPrice::where('gold_type_id', $id)
            ->whereDate('price_date', now()->toDateString())
            ->first();

        // Get yesterday's price
        $yesterdayPrice = GoldPrice::where('gold_type_id', $id)
            ->whereDate('price_date', now()->subDay()->toDateString())
            ->first();

        // Get latest USD rate
        $usdRate = ExchangeRate::whereHas('currency', function ($query) {
            $query->where('code', 'USD');
        })
            ->latest('rate_date')
            ->latest('id')
            ->first();

        // Get latest world gold snapshot (for SGD rate)
        $latestSnapshot = WorldGoldSnapshot::latest('fetched_at')->first();

        // Calculate volatility
        $prices = GoldPrice::where('gold_type_id', $id)
            ->where('status', 'verified')
            ->where('price_date', '>=', now()->subDays(30))
            ->orderBy('price_date', 'desc')
            ->pluck('price')
            ->toArray();

        $volatility = 0;
        if (count($prices) >= 2) {
            $changes = [];
            for ($i = 0; $i < count($prices) - 1; $i++) {
                $changes[] = abs(($prices[$i] - $prices[$i + 1]) / $prices[$i + 1]) * 100;
            }
            $volatility = round(array_sum($changes) / count($changes), 2);
        }

        return Inertia::render('Gold/History', [
            'goldType'       => $goldType,
            'history'        => $history,
            'chartData'      => $chartData,
            'todayPrice'     => $todayPrice,
            'yesterdayPrice' => $yesterdayPrice,
            'stats'          => [
                'highest'       => GoldPrice::where('gold_type_id', $id)->where('status', 'verified')->max('price'),
                'lowest'        => GoldPrice::where('gold_type_id', $id)->where('status', 'verified')->min('price'),
                'average'       => round(GoldPrice::where('gold_type_id', $id)->where('status', 'verified')->avg('price'), 2),
                'total_entries' => GoldPrice::where('gold_type_id', $id)->count(),
                'volatility'    => $volatility,
            ],
            'latestSnapshot' => $latestSnapshot,
            'usdRate'        => $usdRate,
        ]);
    }


    /**
     * Calculate price volatility for gold type
     */
    private function calculateVolatility($goldTypeId, $days = 30)
    {
        $prices = GoldPrice::where('gold_type_id', $goldTypeId)
            ->where('status', 'verified')
            ->orderBy('price_date', 'desc')
            ->limit($days)
            ->pluck('price')
            ->toArray();

        if (count($prices) < 2) {
            return 0;
        }

        // Calculate standard deviation
        $mean = array_sum($prices) / count($prices);
        $variance = 0;

        foreach ($prices as $price) {
            $variance += pow($price - $mean, 2);
        }

        $standardDeviation = sqrt($variance / count($prices));

        // Calculate coefficient of variation (volatility percentage)
        $volatility = ($standardDeviation / $mean) * 100;

        return round($volatility, 2);
    }



    public function getLiveRate()
    {
        // 1. Use the correct relationship name from your GoldType model
        // You have 'latestPrice' and 'latestVerifiedPrice' defined. 
        // 'latestVerifiedPrice' is better because it uses latestOfMany().
        $goldTypes = \App\Models\GoldType::with('latestVerifiedPrice')->get();

        $latest = \App\Models\WorldGoldSnapshot::latest('fetched_at')->first();

        if (!$latest) {
            return response()->json(['status' => 'error', 'usd_price' => null]);
        }

        return response()->json([
            'status'         => 'success',
            'usd_price'      => floatval($latest->usd_price),
            'change'         => floatval($latest->change),
            'change_percent' => floatval($latest->change_percent),
            'fetched_at'     => $latest->fetched_at,
            'mmk_price_new'  => $latest->mmk_price_new,
            'mmk_price_old'  => $latest->mmk_price_old,
            'usd_mmk_rate'   => $latest->usd_mmk_rate,
            'gold_types'     => $goldTypes, // This now contains 'latest_verified_price'
        ]);
    }

    public function getGoldHistory(Request $request)
    {
        $range = $request->get('range', '1d');

        $allowedRanges = ['1d', '5d', '1mo', '3mo', '6mo', '1y'];
        if (!in_array($range, $allowedRanges)) {
            $range = '1d';
        }

        $granularity = match ($range) {
            '1d'        => '5m',
            '5d'        => '15m',
            '1mo'       => '1d',
            '3mo', '6mo' => '1wk',
            '1y'        => '1mo',
            default     => '1d',
        };

        return Cache::remember("gold_history_{$range}", 60, function () use ($range, $granularity) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])->timeout(10)->get("https://query1.finance.yahoo.com/v8/finance/chart/GC=F", [
                    'range'       => $range,
                    'interval'    => $granularity,
                    'includePrePost' => false,
                ]);

                if (!$response->successful()) {
                    throw new \Exception('HTTP ' . $response->status());
                }

                $data       = $response->json();
                $meta       = $data['chart']['result'][0]['meta'];
                $timestamps = $data['chart']['result'][0]['timestamp'] ?? [];
                $closes     = $data['chart']['result'][0]['indicators']['quote'][0]['close'] ?? [];
                $highs      = $data['chart']['result'][0]['indicators']['quote'][0]['high'] ?? [];
                $lows       = $data['chart']['result'][0]['indicators']['quote'][0]['low'] ?? [];

                // Zip timestamps with prices, filter out nulls
                $points = [];
                foreach ($timestamps as $i => $ts) {
                    if (isset($closes[$i]) && $closes[$i] !== null) {
                        $points[] = [
                            'time'  => $ts * 1000, // ms for JS
                            'close' => round($closes[$i], 2),
                            'high'  => round($highs[$i] ?? $closes[$i], 2),
                            'low'   => round($lows[$i] ?? $closes[$i], 2),
                        ];
                    }
                }

                return [
                    'status'           => 'success',
                    'range'            => $range,
                    'current_price'    => floatval($meta['regularMarketPrice']),
                    'previous_close'   => floatval($meta['previousClose']),
                    'day_high'         => floatval($meta['regularMarketDayHigh']),
                    'day_low'          => floatval($meta['regularMarketDayLow']),
                    'fifty_two_week_high' => floatval($meta['fiftyTwoWeekHigh']),
                    'fifty_two_week_low'  => floatval($meta['fiftyTwoWeekLow']),
                    'change'           => round($meta['regularMarketPrice'] - $meta['previousClose'], 2),
                    'change_percent'   => round((($meta['regularMarketPrice'] - $meta['previousClose']) / $meta['previousClose']) * 100, 2),
                    'points'           => $points,
                ];
            } catch (\Exception $e) {
                Log::warning('Gold history fetch failed: ' . $e->getMessage());
                return ['status' => 'error', 'points' => []];
            }
        });
    }

    public function publicGoldHistory($type)
    {
        $validTypes = ['new_system', 'traditional', 'world_oz'];
        if (!in_array($type, $validTypes)) abort(404);

        $typeLabels = [
            'new_system' => 'New System',
            'traditional' => 'Traditional',
            'world_oz' => 'World Spot'
        ];

        $breadcrumbs = [
            ['label' => 'Home', 'route' => 'welcome'],
            ['label' => 'Gold History', 'route' => 'user.gold.history', 'params' => ['type' => 'new_system']],
            ['label' => $typeLabels[$type]]
        ];

        if ($type === 'world_oz') {
            $history = DB::table('world_gold_snapshots')
                ->select('id', 'fetched_at as created_at', 'usd_price as price', 'sgd_price')
                ->orderBy('fetched_at', 'desc')
                ->paginate(150);
        } else {
            $system = $type === 'new_system' ? 'new' : 'old';
            $goldTypeIds = GoldType::where('system', $system)->where('is_active', 1)->pluck('id');

            $history = GoldPrice::whereIn('gold_type_id', $goldTypeIds)
                ->where('status', 'verified')
                ->orderBy('price_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(150)
                ->withQueryString();
        }

        // Debug logging
        Log::info('Public gold history data', [
            'type' => $type,
            'total' => $history->total(),
            'first_record' => $history->first(),
        ]);

        $stats = null;
        $latestPrice = 0;
        $latestSgdPrice = 0; // Add this variable

        if ($type === 'world_oz') {
            $latest = DB::table('world_gold_snapshots')
                ->orderBy('fetched_at', 'desc')
                ->first();

            $yesterdayClose = DB::table('world_gold_snapshots')
                ->where('fetched_at', '<', now()->startOfDay())
                ->orderBy('fetched_at', 'desc')
                ->first();

            // Set latest price
            $latestPrice = $latest ? (float) $latest->usd_price : 0;
            $latestSgdPrice = $latest ? (float) ($latest->sgd_price ?? 0) : 0;

            if ($latest && $yesterdayClose) {
                $latestUsd = (float) $latest->usd_price;
                $yesterdayUsd = (float) $yesterdayClose->usd_price;
                $latestSgd = (float) ($latest->sgd_price ?? 0);
                $yesterdaySgd = (float) ($yesterdayClose->sgd_price ?? 0);

                $diff = $latestUsd - $yesterdayUsd;
                $diff_sgd = $latestSgd - $yesterdaySgd;
                $percent = $yesterdayUsd > 0 ? ($diff / $yesterdayUsd) * 100 : 0;
                $percent_sgd = $yesterdaySgd > 0 ? ($diff_sgd / $yesterdaySgd) * 100 : 0;
                $diffSgd = ($latestSgd > 0 && $yesterdaySgd > 0) ? ($latestSgd - $yesterdaySgd) : 0;

                $stats = [
                    'current' => $latestUsd,
                    'current_sgd' => $latestSgd,
                    'diff' => abs($diff),
                    'diff_sgd' => abs($diffSgd),
                    'percent' => round($percent, 2),
                    'percent_sgd' => round($percent_sgd, 2),
                    'trend' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
                    'compare_date' => \Carbon\Carbon::parse($yesterdayClose->fetched_at)->format('d M'),
                    'symbol' => '$',
                    'suffix' => '/ oz'
                ];
            }
        } else {
            // Local gold logic (MMK)
            $system = $type === 'new_system' ? 'new' : 'old';

            $latest = GoldPrice::whereHas('goldType', fn($q) => $q->where('system', $system))
                ->where('status', 'verified')
                ->orderBy('created_at', 'desc')
                ->first();

            $yesterdayClose = GoldPrice::whereHas('goldType', fn($q) => $q->where('system', $system))
                ->where('status', 'verified')
                ->where('price_date', '<', now()->toDateString())
                ->orderBy('price_date', 'desc')
                ->first();

            // Set latest price
            $latestPrice = $latest ? (float) $latest->price : 0;

            if ($latest && $yesterdayClose) {
                $diff = $latest->price - $yesterdayClose->price;
                $percent = ($diff / $yesterdayClose->price) * 100;

                $stats = [
                    'current' => (float) $latest->price,
                    'diff' => abs($diff),
                    'percent' => round($percent, 2),
                    'trend' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
                    'compare_date' => Carbon::parse($yesterdayClose->price_date)->format('d M'),
                    'symbol' => '',
                    'suffix' => ' MMK'
                ];
            }
        }

        return Inertia::render('Gold/PublicHistory', [
            'history' => $history,
            'selectedType' => $type,
            'breadcrumbs' => $breadcrumbs,
            'stats' => $stats,
            'latestPrice' => (float) $latestPrice,
            'latestSgdPrice' => (float) $latestSgdPrice, // Add this line
        ]);
    }


    public function publicGoldType($goldType)
    {
        $goldType = GoldType::findOrFail($goldType);

        $history = GoldPrice::where('gold_type_id', $goldType->id)
            ->orderBy('price_date', 'desc')
            ->paginate(30);

        // Get chart data for the last 30 days
        $chartData = GoldPrice::where('gold_type_id', $goldType->id)
            ->where('status', 'verified')
            ->orderBy('price_date', 'asc')
            ->limit(30)
            ->get(['price_date as date', 'price'])
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'price' => (float) $item->price
                ];
            });

        // Get latest world gold snapshot for reference
        $latestSnapshot = WorldGoldSnapshot::latest('fetched_at')->first();

        return Inertia::render('Public/GoldTypeHistory', [
            'goldType' => $goldType,
            'history' => $history,
            'chartData' => $chartData,
            'latestSnapshot' => $latestSnapshot,
        ]);
    }

    // chart data 
    public function chartData(Request $request, $id)
    {
        try {
            $period = $request->query('period', '7d');
            $type = $request->query('type');

            // Case 1: World Gold
            if ($type === 'world_oz' || $id === 'world') {
                return $this->getWorldGoldData($period);
            }

            // Case 2: Local Gold
            return $this->getLocalGoldData($id, $period);
        } catch (\Exception $e) {
            Log::error('Chart data error: ' . $e->getMessage(), [
                'id' => $id,

                'period' => $period
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getLocalGoldData($goldTypeId, $period)
    {
        try {
            // First, check if the gold_type exists
            $goldType = DB::table('gold_types')->find($goldTypeId);
            if (!$goldType) {
                Log::warning("Gold type not found: {$goldTypeId}");
                return response()->json([]);
            }

            // Build the query - remove status filter for now to see if we get data
            $query = GoldPrice::where('gold_type_id', $goldTypeId);

            // Temporarily remove status filter to debug
            // $query->where('status', 'verified');

            // Get date range based on period
            $days = match ($period) {
                '7d' => 7,
                '1m' => 30,
                '3m' => 90,
                '1y' => 365,
                'all' => 365 * 2, // 2 years max for performance
                default => 7
            };

            $startDate = now()->subDays($days);

            // Log the query for debugging
            Log::info('Local gold query', [
                'gold_type_id' => $goldTypeId,
                'period' => $period,
                'days' => $days,
                'start_date' => $startDate
            ]);

            // Apply date filter
            $query->where('created_at', '>=', $startDate);

            // Get count first to debug
            $totalCount = $query->count();
            Log::info("Found {$totalCount} records for gold_type_id: {$goldTypeId}");

            if ($totalCount === 0) {
                // Try without date filter to see if there's any data at all
                $anyData = GoldPrice::where('gold_type_id', $goldTypeId)->count();
                Log::info("Total records without date filter: {$anyData}");

                if ($anyData > 0) {
                    // There is data, but not in the date range, so return the oldest available
                    $oldestRecord = GoldPrice::where('gold_type_id', $goldTypeId)
                        ->orderBy('created_at', 'asc')
                        ->first();

                    if ($oldestRecord) {
                        Log::info("Oldest record date: {$oldestRecord->created_at}");
                    }
                }

                return response()->json([]);
            }

            // For 7-day period, return all data points
            if ($period === '7d') {
                return $query->select('created_at as recorded_at', 'price')
                    ->orderBy('created_at', 'asc')
                    ->get();
            }

            // For longer periods, aggregate by day to reduce data points
            // Use COALESCE to handle NULL prices
            return $query->select(
                DB::raw('DATE(created_at) as recorded_at'),
                DB::raw('AVG(COALESCE(price, 0)) as price')
            )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('recorded_at', 'asc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Local gold data error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    private function getWorldGoldData($period)
    {
        try {
            // 1. Basic Table Check
            if (!Schema::hasTable('world_gold_snapshots')) {
                Log::warning('world_gold_snapshots table does not exist');
                return response()->json([]);
            }

            // 2. Define Date Range
            $days = match ($period) {
                '7d' => 7,
                '1m' => 30,
                '3m' => 90,
                '1y' => 365,
                'all' => 365 * 2,
                default => 7
            };

            $startDate = now()->subDays($days);
            $dateColumn = 'fetched_at'; // Standardizing on your table column

            // 3. Query Logic
            $query = DB::table('world_gold_snapshots')
                ->where($dateColumn, '>=', $startDate);

            switch ($period) {
                case '7d':
                    /**
                     * For 7 days: Return all raw snapshots (likely hourly).
                     * This provides high-resolution data for the short-term chart.
                     */
                    return $query->select(
                        'usd_price as price',
                        'sgd_price',
                        "$dateColumn as recorded_at"
                    )
                        ->orderBy($dateColumn, 'asc')
                        ->get();

                case '1m':
                case '3m':
                case '1y':
                case 'all':
                    /**
                     * For longer periods: Aggregate by Day.
                     * We take the Average (AVG) of both USD and SGD for each day 
                     * to keep the chart clean and prevent thousands of points.
                     */
                    return $query->select(
                        DB::raw("AVG(usd_price) as price"),
                        DB::raw("AVG(sgd_price) as sgd_price"),
                        DB::raw("DATE($dateColumn) as recorded_at")
                    )
                        ->groupBy(DB::raw("DATE($dateColumn)"))
                        ->orderBy('recorded_at', 'asc')
                        ->get();

                default:
                    return $query->select('usd_price as price', 'sgd_price', "$dateColumn as recorded_at")
                        ->orderBy($dateColumn, 'asc')
                        ->get();
            }
        } catch (\Exception $e) {
            Log::error('World gold data error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    private function getPriceColumn($columns)
    {
        $possiblePriceColumns = ['usd_price', 'price', 'spot_price', 'gold_price', 'value'];

        foreach ($possiblePriceColumns as $col) {
            if (in_array($col, $columns)) {
                return $col;
            }
        }

        return null;
    }

    private function getDateColumn($columns)
    {
        $possibleDateColumns = ['fetched_at', 'created_at', 'recorded_at', 'timestamp', 'date'];

        foreach ($possibleDateColumns as $col) {
            if (in_array($col, $columns)) {
                return $col;
            }
        }

        return null;
    }
}
