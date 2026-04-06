<?php

namespace App\Http\Middleware;

use App\Models\ExchangeRate;
use App\Models\Notification;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                // Ensure this is strictly null or an array
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'role' => $request->user()->role,
                    'is_admin' => $request->user()->is_admin,
                ] : null,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
            // Wrap these in a check so guests don't trigger admin queries
            'pendingCount' => function () use ($request) {
                return $request->user() ? ExchangeRate::where('is_verified', false)->count() : 0;
            },
            'pending_gold_count' => function () use ($request) {
                return $request->user() ? \App\Models\GoldPrice::where('status', 'pending')->count() : 0;
            },
            'breadcrumbs' => function () use ($request) {
                // Get the current path (e.g., "gold-history/new_system")
                $path = $request->path();
                $segments = explode('/', $path);
                $crumbs = [];
                $url = '';

                foreach ($segments as $segment) {
                    $url .= '/' . $segment;
                    $crumbs[] = [
                        'label' => $this->formatBreadcrumbLabel($segment),
                        'url' => $url,
                        'active' => $request->is(trim($url, '/')),
                    ];
                }
                return $crumbs;
            },
            'lastSync' => now()->toIso8601String(),
            'syncStatus' => 'success',
            // Notifications for the bell icon
            'notifications' => function () {
                if (Auth::check()) {
                    return [
                        'items' => Notification::where('user_id', Auth::id())
                            ->latest()
                            ->take(10)
                            ->get(),
                        'unread_count' => Notification::where('user_id', Auth::id())
                            ->where('is_read', false)
                            ->count(),
                    ];
                }
                return ['items' => [], 'unread_count' => 0];
            },
            // Watchlist count for the bell icon
            'watchlistCount' => function () {
                if (Auth::check()) {
                    return Watchlist::where('user_id', Auth::id())->count();
                }
                return 0;
            },

        ];
    }

    private function formatBreadcrumbLabel($slug)
    {
        $map = [
            'gold-history' => 'Gold Price',
            'new_system'   => 'New System',
            'traditional'  => 'Traditional',
            'world_oz'     => 'World Spot',
            'currency'     => 'Exchange Rates'
        ];

        return $map[$slug] ?? ucwords(str_replace(['-', '_'], ' ', $slug));
    }
}
