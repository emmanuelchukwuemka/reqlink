<?php

namespace App\Http\Controllers;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UssdBridgeController extends Controller
{
    /**
     * Handle incoming SMS alerts (e.g., from Twilio or Africa's Talking)
     * Simulation: POST /api/bridge/sms?from=+234...&message=SOS
     */
    public function handleSms(Request $request)
    {
        $phone = $request->input('from');
        $message = strtoupper($request->input('message'));

        if (!Str::contains($message, 'SOS')) {
            return response()->json(['status' => 'ignored']);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        // Create Emergency Alert
        $emergency = Emergency::create([
            'user_id' => $user->id,
            'emergency_type_id' => 1, // General Emergency
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'status' => 'pending',
            'triggered_via' => 'sms'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Emergency triggered via SMS',
            'uuid' => $emergency->uuid
        ]);
    }

    /**
     * Handle USSD Sessions (e.g., *700*1#)
     * Simulation: POST /api/bridge/ussd?phoneNumber=+234...&text=1
     */
    public function handleUssd(Request $request)
    {
        $phone = $request->input('phoneNumber');
        $text = $request->input('text'); // USSD menu input

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return "CON Welcome to ResQLink.\nYour phone is not registered.\nPlease register on the app.";
        }

        if ($text == "") {
            return "CON ResQLink Emergency\n1. Trigger SOS\n2. Report Accident\n3. Medical ID";
        }

        if ($text == "1") {
            $emergency = Emergency::create([
                'user_id' => $user->id,
                'emergency_type_id' => 1, // General
                'latitude' => 6.5244,
                'longitude' => 3.3792,
                'status' => 'pending',
                'triggered_via' => 'ussd'
            ]);
            return "END Emergency Alert Sent!\nResponders are being dispatched to your registered location.";
        }

        return "END Invalid Option.";
    }
}
