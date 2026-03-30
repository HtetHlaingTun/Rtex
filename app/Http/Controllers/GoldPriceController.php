<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\GoldPrice;
use App\Models\GoldType;
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
        // Fetch the latest snapshot once
        $snapshot = \App\Models\WorldGoldSnapshot::latest('fetched_at')->first();

        return Inertia::render('Gold/Index', [
            'goldTypes' => GoldType::with(['latestPrice' => function ($query) {
                $query->orderBy('price_date', 'desc')->orderBy('created_at', 'desc');
            }])->get(),
            'pending_gold_count' => GoldPrice::where('status', 'pending')->count(),

            // Use the $snapshot variable we just created
            'worldGoldSnapshot' => $snapshot,
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

        $cutoff = now()->subDays(10)->startOfDay();

        $baseQuery = GoldPrice::where('gold_type_id', $id)
            ->where('gold_prices.status', 'verified')
            // Join snapshots where the fetched_at is closest to the gold_price creation
            ->leftJoin('world_gold_snapshots', function ($join) {
                $join->on(
                    'world_gold_snapshots.created_at',
                    '=',
                    DB::raw("(SELECT created_at FROM world_gold_snapshots 
                          WHERE created_at <= gold_prices.created_at 
                          ORDER BY created_at DESC LIMIT 1)")
                );
            })
            ->select([
                'gold_prices.*',
                'world_gold_snapshots.usd_price as world_gold_usd',
                'world_gold_snapshots.change as world_gold_change',
                'world_gold_snapshots.day_high as world_gold_high',
                'world_gold_snapshots.change_percent as world_gold_change_percent',
            ]);

        // Within 10 days — all hourly records (up to 240)
        $recentRecords = (clone $baseQuery)
            ->where('gold_prices.created_at', '>=', $cutoff)
            ->orderBy('gold_prices.created_at', 'desc')
            ->get();

        // Beyond 10 days — 1 record per day (latest of that day)
        $olderRecords = (clone $baseQuery)
            ->where('gold_prices.created_at', '<', $cutoff)
            ->orderBy('gold_prices.price_date', 'desc')
            ->orderBy('gold_prices.created_at', 'desc')
            ->get()
            ->unique('price_date')
            ->values();

        // Merge for paginated display
        $allRecords = $recentRecords->concat($olderRecords);

        // Manual pagination
        $page    = request()->get('page', 1);
        $perPage = 20;
        $total   = $allRecords->count();
        $items   = $allRecords->forPage($page, $perPage);

        $history = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        if (!$history) {
            // Redirect back to home with a flash message
            return redirect('/')->with('error', 'History record not found.');
        }


        // Chart data — last 30 days, 1 point per day
        $chartData = GoldPrice::where('gold_type_id', $id)
            ->where('status', 'verified')
            ->where('price_date', '>=', now()->subDays(30))
            ->orderBy('price_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('price_date')
            ->values()
            ->map(fn($p) => [
                'date'  => $p->price_date->format('d M'), // ← "19 Mar" not "Mar 19"
                'price' => floatval($p->price),
            ]);

        // ✅ Fallback: if less than 2 days, use today's hourly records
        if ($chartData->count() < 2) {
            $chartData = GoldPrice::where('gold_type_id', $id)
                ->where('status', 'verified')
                ->whereDate('price_date', today())
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($p) => [
                    'date'  => $p->created_at->format('H:i'), // ← was H:i, keep short
                    'price' => floatval($p->price),
                ]);
        }


        $todayPrice = GoldPrice::where('gold_type_id', $id)
            ->orderBy('price_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $yesterdayPrice = GoldPrice::where('gold_type_id', $id)
            ->where('status', 'verified')
            ->whereDate('price_date', today()->subDay())
            ->latest('created_at')
            ->first();

        return Inertia::render('Gold/History', [
            'goldType'       => $goldType,
            'history'        => $history,
            'chartData'      => $chartData,
            'todayPrice'     => $todayPrice,
            'yesterdayPrice' => $yesterdayPrice,
            'stats'          => [
                'highest'       => GoldPrice::where('gold_type_id', $id)->where('status', 'verified')->max('price'),
                'lowest'        => GoldPrice::where('gold_type_id', $id)->where('status', 'verified')->min('price'),
                'total_entries' => GoldPrice::where('gold_type_id', $id)->count(),
            ],
            'latestSnapshot' => \App\Models\WorldGoldSnapshot::latest('fetched_at')->first(),
            'usdRate' => ExchangeRate::whereHas('currency', function ($query) {
                $query->where('code', 'USD');
            })
                ->latest('rate_date')
                ->latest('id') // Ensures you get the most recent entry if multiple exist for one date
                ->first(),
        ]);
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
            ['label' => 'Gold History', 'route' => 'public.gold.history', 'params' => ['type' => 'new_system']],
            ['label' => $typeLabels[$type]]
        ];

        if ($type === 'world_oz') {
            $history = DB::table('world_gold_snapshots')
                ->select('id', 'fetched_at as created_at', 'usd_price as price')
                ->orderBy('fetched_at', 'desc')
                ->paginate(150); // Increase limit to see more days
        } else {
            $system = $type === 'new_system' ? 'new' : 'old';
            $goldTypeIds = GoldType::where('system', $system)->where('is_active', 1)->pluck('id');

            // REMOVE the '>= $today' restriction to allow older history to flow in
            $history = GoldPrice::whereIn('gold_type_id', $goldTypeIds)
                ->where('status', 'verified')
                // We order by date so Page 1 contains the most recent days
                ->orderBy('price_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(150) // High number to ensure we get at least 7-30 days of data
                ->withQueryString();
        }

        // Debug: Log to see if data is being passed
        Log::info('Public gold history data', [
            'type' => $type,
            'total' => $history->total(),
            'first_record' => $history->first(),
            'has_links' => isset($history->links)
        ]);

        // TEMPORARY DEBUG - Remove after testing
        Log::info('History data being sent:', [
            'total' => $history->total(),
            'count' => $history->count(),
            'first_item' => $history->first(),
            'has_data' => $history->isNotEmpty()
        ]);


        $stats = null;

        if ($type === 'world_oz') {
            // --- WORLD GOLD LOGIC ---
            $latest = DB::table('world_gold_snapshots')
                ->orderBy('fetched_at', 'desc')
                ->first();

            $yesterdayClose = DB::table('world_gold_snapshots')
                ->where('fetched_at', '<', now()->startOfDay())
                ->orderBy('fetched_at', 'desc')
                ->first();

            if ($latest && $yesterdayClose) {
                $diff = $latest->usd_price - $yesterdayClose->usd_price;
                $percent = ($diff / $yesterdayClose->usd_price) * 100;

                $stats = [
                    'current' => (float) $latest->usd_price,
                    'diff' => abs($diff),
                    'percent' => round($percent, 2),
                    'trend' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
                    'compare_date' => Carbon::parse($yesterdayClose->fetched_at)->format('d M'),
                    'symbol' => '$',
                    'suffix' => '/ oz'
                ];
            }
        } else {
            // --- LOCAL GOLD LOGIC (MMK) ---
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

        if ($type === 'world_oz') {
            // Get the single most recent snapshot from the world table
            $latestRecord = DB::table('world_gold_snapshots')
                ->orderBy('fetched_at', 'desc')
                ->first();
            $latestPrice = $latestRecord ? $latestRecord->usd_price : 0;
        } else {
            // Get the single most recent verified price for the local system
            $system = $type === 'new_system' ? 'new' : 'old';
            $latestRecord = GoldPrice::whereHas('goldType', fn($q) => $q->where('system', $system))
                ->where('status', 'verified')
                ->orderBy('created_at', 'desc')
                ->first();
            $latestPrice = $latestRecord ? $latestRecord->price : 0;
        }

        return Inertia::render('Gold/PublicHistory', [
            'history' => $history,
            'selectedType' => $type,
            'breadcrumbs' => $breadcrumbs,
            'stats' => $stats,
            'latestPrice' => (float)$latestPrice,
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
            // Check if table exists
            if (!Schema::hasTable('world_gold_snapshots')) {
                Log::warning('world_gold_snapshots table does not exist');
                return response()->json([]);
            }

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

            // Check available columns
            $columns = Schema::getColumnListing('world_gold_snapshots');

            // Determine price column
            $priceColumn = $this->getPriceColumn($columns);
            if (!$priceColumn) {
                Log::error('No price column found. Available: ' . implode(', ', $columns));
                return response()->json([]);
            }

            // Determine date column
            $dateColumn = $this->getDateColumn($columns);
            if (!$dateColumn) {
                Log::error('No date column found. Available: ' . implode(', ', $columns));
                return response()->json([]);
            }

            // Count total records in range
            $totalRecords = DB::table('world_gold_snapshots')
                ->where($dateColumn, '>=', $startDate)
                ->count();

            Log::info("World gold query for period {$period}: found {$totalRecords} records");

            // Apply different aggregation strategies based on period
            switch ($period) {
                case '7d':
                    // For 7 days, return all points (hourly data) - this gives ~168 points
                    return DB::table('world_gold_snapshots')
                        ->where($dateColumn, '>=', $startDate)
                        ->select("$priceColumn as price", "$dateColumn as recorded_at")
                        ->orderBy($dateColumn, 'asc')
                        ->get();

                case '1m':
                case '3m':
                case '1y':
                case 'all':
                    // For all longer periods, use daily aggregation
                    // This will give 1 point per day instead of 24 points per day
                    return DB::table('world_gold_snapshots')
                        ->where($dateColumn, '>=', $startDate)
                        ->select(
                            DB::raw("AVG($priceColumn) as price"),
                            DB::raw("DATE($dateColumn) as recorded_at")
                        )
                        ->groupBy(DB::raw("DATE($dateColumn)"))
                        ->orderBy('recorded_at', 'asc')
                        ->get();

                default:
                    return DB::table('world_gold_snapshots')
                        ->where($dateColumn, '>=', $startDate)
                        ->select("$priceColumn as price", "$dateColumn as recorded_at")
                        ->orderBy($dateColumn, 'asc')
                        ->get();
            }
        } catch (\Exception $e) {
            Log::error('World gold data error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
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
