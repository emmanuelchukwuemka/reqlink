<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', 'en');
        if (in_array($locale, ['en', 'yo', 'ha', 'ig'])) {
            app()->setLocale($locale);
        }
        return $next($request);
    }
}
