<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $timeout = session('timeout', 120);

            if (time() - session('last_activity', time()) > $timeout * 60) {
                Auth::logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect('/login')->with('message', 'Session expired');
            }

            session(['last_activity' => time()]);
        }


        return $next($request)->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Referrer-Policy', 'no-referrer-when-downgrade')
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
