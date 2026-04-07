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
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has admin role (super_admin or admin)
        $user = Auth::user();
        $allowedRoles = ['super_admin', 'admin'];

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
