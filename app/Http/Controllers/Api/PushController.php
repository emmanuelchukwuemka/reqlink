<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'expo_push_token' => 'required|string',
            'platform' => 'required|string|in:ios,android',
        ]);

        DeviceToken::updateOrCreate(
            ['user_id' => Auth::id(), 'expo_push_token' => $request->expo_push_token],
            ['platform' => $request->platform]
        );

        return response()->json(['success' => true]);
    }

    public function unregister(Request $request)
    {
        $request->validate(['expo_push_token' => 'required|string']);

        DeviceToken::where('user_id', Auth::id())
            ->where('expo_push_token', $request->expo_push_token)
            ->delete();

        return response()->json(['success' => true]);
    }
}
