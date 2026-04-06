<?php
// app/Http/Controllers/User/WatchlistController.php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WatchlistController extends Controller
{
    // In WatchlistController@index
    public function index()
    {
        $watchlist = Watchlist::where('user_id', Auth::id())
            ->with('currency')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get current rates
        $currentRates = [];
        foreach ($watchlist as $item) {
            $latestRate = ExchangeRate::where('currency_id', $item->currency_id)->latest()->first();
            if ($latestRate) {
                $currentRates[$item->currency_id] = [
                    'buy_rate' => $latestRate->buy_rate,
                    'sell_rate' => $latestRate->sell_rate,
                    'mid_rate' => ($latestRate->buy_rate + $latestRate->sell_rate) / 2,
                    'updated_at' => $latestRate->created_at,
                ];
            }
        }

        // IMPORTANT: Pass alerts to the view
        $alerts = Alert::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        $availableCurrencies = Currency::where('is_active', true)->orderBy('code')->get();

        return Inertia::render('Users/Watchlist/Index', [
            'watchlist' => $watchlist,
            'currentRates' => $currentRates,
            'availableCurrencies' => $availableCurrencies,
            'alerts' => $alerts, // Make sure this line exists
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
        ]);

        // Check if already in watchlist
        $exists = Watchlist::where('user_id', Auth::id())
            ->where('currency_id', $request->currency_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Currency already in your watchlist');
        }

        Watchlist::create([
            'user_id' => Auth::id(),
            'currency_id' => $request->currency_id,
        ]);

        return back()->with('success', 'Currency added to watchlist');
    }

    public function destroy($id)
    {
        $watchlist = Watchlist::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $watchlist->delete();

        return back()->with('success', 'Currency removed from watchlist');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:watchlists,id',
        ]);

        Watchlist::where('user_id', Auth::id())
            ->whereIn('id', $request->ids)
            ->delete();

        return back()->with('success', 'Selected currencies removed from watchlist');
    }
}
