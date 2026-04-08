<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetMetaTags
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        view()->share([
            'metaTitle' => 'MMRatePro - Live Exchange Rates & Gold Prices',
            'metaDescription' => 'Real-time USD, SGD, EUR, THB exchange rates to MMK. Live gold prices in Myanmar kyat.',
            'metaImage' => url('/default-og-image.jpg'),
        ]);


        return $next($request);
    }
}
