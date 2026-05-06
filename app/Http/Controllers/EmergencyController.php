<?php

namespace App\Http\Controllers;

use App\Domains\Emergencies\Models\Emergency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmergencyController extends Controller
{
    public function trigger(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'type' => 'nullable|string',
        ]);

        // 1. Create the emergency record
        $emergency = Emergency::create([
            'user_id' => Auth::id(),
            'emergency_type_id' => 1, // Defaulting to Health for now
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'pending',
            'priority' => 5, // High priority for SOS
        ]);

        // 2. Find nearest hospital (Simple distance calculation)
        $nearestHospital = DB::table('hospitals')
            ->select('id', 'name', 'contact_phone', 'lat', 'lng')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance',
                [$request->latitude, $request->longitude, $request->latitude]
            )
            ->orderBy('distance')
            ->first();

        // 3. Mock assignment
        if ($nearestHospital) {
            $emergency->update([
                'assigned_responder_id' => $nearestHospital->id,
                'status' => 'dispatched',
                'eta_minutes' => rand(5, 15),
            ]);
        }

        return response()->json([
            'message' => 'Emergency alert received and responders dispatched.',
            'uuid' => $emergency->uuid,
            'emergency' => $emergency,
            'hospital' => $nearestHospital,
            'user_metadata' => [
                'blood_group' => Auth::user()->blood_group,
                'allergies' => Auth::user()->allergies,
                'medical_conditions' => Auth::user()->medical_conditions,
                'emergency_contact' => Auth::user()->emergency_contact_phone,
            ]
        ]);
    }

    public function getStatus($uuid)
    {
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        
        $responderData = null;
        if ($emergency->assigned_responder_id) {
            // Check if it's a Responder or Hospital
            $responder = \App\Domains\Responders\Models\Responder::with('user')->find($emergency->assigned_responder_id);
            if ($responder) {
                $responderData = [
                    'name' => $responder->user->name,
                    'type' => $responder->responder_type,
                    'lat' => $responder->current_lat,
                    'lng' => $responder->current_lng,
                ];
            }
        }

        return response()->json([
            'status' => $emergency->status,
            'eta' => $emergency->eta_minutes,
            'responder' => $responderData
        ]);
    }

    public function fetchAlerts()
    {
        // For now, we'll fetch pending emergencies assigned to this responder
        // In a real app, we'd use the responder's ID linked to the user
        $emergencies = Emergency::where('status', 'pending')
            ->orWhere('status', 'dispatched')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($emergencies);
    }
}
