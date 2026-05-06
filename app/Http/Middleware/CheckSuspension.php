<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspension
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_suspended) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->withErrors([
                'phone' => 'Your account has been suspended by an administrator.',
            ]);
        }

        return $next($request);
    }
}
