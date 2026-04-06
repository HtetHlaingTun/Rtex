<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::where('user_id', Auth::id())
            ->with('currency')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get current rates for each alert
        $currentRates = [];
        foreach ($alerts as $alert) {
            $latestRate = ExchangeRate::where('currency_id', $alert->currency_id)
                ->latest()
                ->first();

            if ($latestRate) {
                $currentRates[$alert->currency_id] = [
                    'buy_rate' => $latestRate->buy_rate,
                    'sell_rate' => $latestRate->sell_rate,
                    'mid_rate' => ($latestRate->buy_rate + $latestRate->sell_rate) / 2,
                ];
            }
        }

        // Get currencies for creating new alerts
        $currencies = Currency::where('is_active', true)
            ->orderBy('code')
            ->get();

        return Inertia::render('Users/Alerts/Index', [
            'alerts' => $alerts,
            'currentRates' => $currentRates,
            'currencies' => $currencies,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'type' => 'required|in:above,below',
            'target_rate' => 'required|numeric|min:0',
        ]);

        // Check for exact duplicate (including inactive)
        $existing = Alert::where('user_id', Auth::id())
            ->where('currency_id', $request->currency_id)
            ->where('type', $request->type)
            ->where('target_rate', $request->target_rate)
            ->first();

        if ($existing) {
            if ($existing->is_active) {
                return back()->with('error', 'You already have an identical active alert for this currency.');
            } else {
                // Reactivate the inactive alert
                $existing->update([
                    'is_active' => true,
                    'triggered_at' => null,
                ]);
                return back()->with('success', 'Alert reactivated successfully.');
            }
        }

        // Create new alert
        Alert::create([
            'user_id' => Auth::id(),
            'currency_id' => $request->currency_id,
            'type' => $request->type,
            'target_rate' => $request->target_rate,
            'is_active' => true,
        ]);

        return back()->with('success', 'Alert created successfully');
    }

    public function update(Request $request, $id)
    {
        $alert = Alert::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'type' => 'sometimes|in:above,below',
            'target_rate' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $alert->update($request->only(['type', 'target_rate', 'is_active']));

        return back()->with('success', 'Alert updated successfully');
    }

    public function destroy($id)
    {
        $alert = Alert::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $alert->delete();

        return back()->with('success', 'Alert deleted successfully');
    }

    public function toggle($id)
    {
        $alert = Alert::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $alert->update(['is_active' => !$alert->is_active]);

        $status = $alert->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Alert {$status} successfully");
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:alerts,id',
        ]);

        Alert::where('user_id', Auth::id())
            ->whereIn('id', $request->ids)
            ->delete();

        return back()->with('success', 'Selected alerts deleted successfully');
    }
}
