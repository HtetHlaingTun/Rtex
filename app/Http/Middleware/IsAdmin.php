<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Logic: Check if user is logged in AND has an admin role/flag
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        // If not admin, send them back to the dashboard or home
        return redirect('/')->with('error', 'You do not have administrative access.');
    }
}
