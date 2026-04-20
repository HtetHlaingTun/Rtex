<?php

namespace App\Http\Controllers;

use App\Models\FuelPrice;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FuelPriceController extends Controller
{
    public function index()
    {
        return Inertia::render('Fuel/FuelPrice', [
            'currentPrices' => FuelPrice::latest()->first(),
            'history'       => FuelPrice::orderBy('created_at', 'desc')->take(7)->get(),
            'breadcrumbs'   => [
                ['label' => 'Home', 'route' => 'welcome'],
                ['label' => 'Fuel', 'route' => 'fuel-prices'],
                ['label' => 'Live price and History']
            ],
        ]);
    }

    public function indexApi()
    {
        $latest = DB::table('fuel_prices')->orderBy('created_at', 'desc')->first();

        return response()->json([
            'octane_92'      => $latest?->octane_92,
            'octane_95'      => $latest?->octane_95,
            'diesel'         => $latest?->diesel,
            'premium_diesel' => $latest?->premium_diesel,
        ]);
    }

    public function historyApi($region)
    {
        $prices = DB::table('fuel_prices')
            ->where('region', $region)
            ->orderBy('created_at', 'asc') // oldest first
            ->get()
            ->map(fn($p) => [
                'date' => date('Y-m-d', strtotime($p->created_at)),
                'time' => date('H:i', strtotime($p->created_at)),
                'octane_92' => (int)$p->octane_92,
                'octane_95' => (int)$p->octane_95,
                'diesel' => (int)$p->diesel,
                'premium_diesel' => (int)$p->premium_diesel,
            ]);

        return response()->json(['success' => true, 'region' => $region, 'history' => $prices]);
    }
    /**
     * Trend = how this record changed vs the NEXT NEWER record.
     * $previous here is actually the newer record (we iterate newest→oldest).
     */
}
