<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Responders\Models\Responder;
use Illuminate\Support\Facades\Auth;

class ResponderController extends Controller
{
    public function toggleDuty(Request $request)
    {
        $responder = Responder::where('user_id', Auth::id())->first();
        
        if (!$responder) {
            return response()->json(['error' => 'Responder record not found'], 404);
        }

        $responder->update([
            'is_on_duty' => $request->is_on_duty
        ]);

        return response()->json([
            'success' => true,
            'is_on_duty' => $responder->is_on_duty
        ]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $responder = Responder::where('user_id', Auth::id())->first();
        
        if ($responder && $responder->is_on_duty) {
            $responder->update([
                'current_lat' => $request->latitude,
                'current_lng' => $request->longitude,
                'last_ping' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
