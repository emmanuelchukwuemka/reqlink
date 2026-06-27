<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
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

    // Send FCM push to all subscribers of a given role
    public static function sendToRole(string $role, string $title, string $body, array $data = []): void
    {
        $serverKey = config('services.firebase.server_key');
        if (!$serverKey) return;

        $userIds = \App\Domains\Users\Models\User::where('role', $role)->pluck('id');
        $tokens = PushSubscription::whereIn('user_id', $userIds)->pluck('endpoint')->toArray();
        if (empty($tokens)) return;

        $payload = json_encode([
            'registration_ids' => $tokens,
            'notification' => ['title' => $title, 'body' => $body, 'icon' => '/images/logo.png'],
            'data' => $data,
        ]);

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: key=' . $serverKey,
                'Content-Type: application/json',
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
