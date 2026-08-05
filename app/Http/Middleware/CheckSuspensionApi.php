<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sanctum-token equivalent of CheckSuspension. That middleware only runs in
 * the `web` (session) group, so a suspended user's bearer token would
 * otherwise keep working indefinitely against the API.
 */
class CheckSuspensionApi
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_suspended) {
            $user->currentAccessToken()?->delete();

            // `suspended: true` lets the mobile client distinguish this from an
            // ordinary ownership/role 403 (e.g. viewing someone else's
            // emergency) — only this one should force a logout.
            return response()->json([
                'message' => 'Your account has been suspended by an administrator.',
                'suspended' => true,
            ], 403);
        }

        return $next($request);
    }
}
