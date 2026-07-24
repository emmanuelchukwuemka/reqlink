<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function vapidPublicKey()
    {
        return response()->json(['key' => config('services.vapid.public_key')]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint'   => 'required|string',
            'p256dh_key' => 'required|string',
            'auth_token' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => Auth::id(), 'endpoint' => $request->endpoint],
            ['p256dh_key' => $request->p256dh_key, 'auth_token' => $request->auth_token]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json(['success' => true]);
    }

    // Send a real Web Push (VAPID) notification to every subscriber with the given role
    public static function sendToRole(string $role): void
    {
        $userIds = \App\Domains\Users\Models\User::where('role', $role)->pluck('id')->toArray();
        WebPushService::sendToUsers($userIds);
    }
}
