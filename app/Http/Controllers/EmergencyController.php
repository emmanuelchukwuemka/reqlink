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

        // 1. Resolve emergency type — seed row if seeder never ran on this environment
        $typeId = \App\Domains\Emergencies\Models\EmergencyType::min('id')
            ?? \App\Domains\Emergencies\Models\EmergencyType::create([
                'name' => 'Medical',
                'icon' => 'medical-bag',
                'description' => 'Health emergencies requiring ambulance or doctors.',
            ])->id;

        // 2. Create the emergency record
        $emergency = Emergency::create([
            'user_id' => Auth::id(),
            'emergency_type_id' => $typeId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'pending',
            'priority' => 5,
        ]);

        // 2. Search for nearest ON-DUTY and AVAILABLE Responders
        $nearestResponder = \App\Domains\Responders\Models\Responder::where('is_on_duty', true)
            ->where('is_available', true)
            ->select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(current_lat)) * cos(radians(current_lng) - radians(?)) + sin(radians(?)) * sin(radians(current_lat)))) AS distance',
                [$request->latitude, $request->longitude, $request->latitude]
            )
            ->orderBy('distance')
            ->first();

        if ($nearestResponder) {
            $emergency->update([
                'assigned_responder_id' => $nearestResponder->id,
                'status' => 'dispatched',
                'eta_minutes' => ceil($nearestResponder->distance * 2) + 2, // Realistic calculation: ~2 mins per km + 2 mins prep
            ]);

            $nearestResponder->update(['is_available' => false]);

            return response()->json([
                'message' => 'Emergency alert received. ' . ucfirst($nearestResponder->responder_type) . ' unit dispatched.',
                'uuid' => $emergency->uuid,
                'status' => 'dispatched',
                'responder' => [
                    'name' => $nearestResponder->user->name,
                    'type' => $nearestResponder->responder_type,
                ]
            ]);
        }

        // 3. Fallback to nearest Hospital if no active mobile responders
        $nearestHospital = DB::table('hospitals')
            ->select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance',
                [$request->latitude, $request->longitude, $request->latitude]
            )
            ->orderBy('distance')
            ->first();

        if ($nearestHospital) {
            $emergency->update([
                'assigned_responder_id' => null, // Not a mobile responder yet
                'status' => 'pending',
                'description' => 'Routing to ' . $nearestHospital->name,
            ]);

            return response()->json([
                'message' => 'No mobile responders available. Routing to ' . $nearestHospital->name,
                'uuid' => $emergency->uuid,
                'status' => 'pending',
                'hospital' => $nearestHospital
            ]);
        }

        return response()->json([
            'message' => 'Searching for available responders... Please stay calm.',
            'uuid' => $emergency->uuid,
            'status' => 'pending',
            'no_responders' => true
        ]);
    }

    public function updateUserLocation(Request $request, $uuid)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $emergency = Emergency::where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'dispatched', 'enroute'])
            ->firstOrFail();

        $emergency->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['success' => true]);
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
            'responder' => $responderData,
            'user_location' => [
                'lat' => $emergency->latitude,
                'lng' => $emergency->longitude,
            ]
        ]);
    }

    public function acceptMission(Request $request, $uuid)
    {
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        
        // Find the responder record for the current user
        $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->firstOrFail();

        $emergency->update([
            'assigned_responder_id' => $responder->id,
            'status' => 'enroute',
        ]);

        return response()->json(['success' => true]);
    }

    public function resolveEmergency(Request $request, $uuid)
    {
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        
        $emergency->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function fetchAlerts()
    {
        $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();

        $emergencies = Emergency::where(function ($q) use ($responder) {
                // Unassigned emergencies (any on-duty responder can take)
                $q->whereNull('assigned_responder_id');
                // OR assigned specifically to this responder by admin
                if ($responder) {
                    $q->orWhere('assigned_responder_id', $responder->id);
                }
            })
            ->whereIn('status', ['pending', 'dispatched'])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($emergencies);
    }

    public function uploadEvidence(Request $request, $uuid)
    {
        $request->validate([
            'evidence' => 'required|file|max:10240', // 10MB max
        ]);

        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        
        $path = $request->file('evidence')->store('evidence', 'public');
        
        $emergency->update([
            'evidence_file' => $path,
        ]);

        return response()->json(['success' => true, 'path' => $path]);
    }

    public function updateTriage(Request $request, $uuid)
    {
        $request->validate([
            'triage_data' => 'required|array',
        ]);

        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        
        $emergency->update([
            'triage_data' => $request->triage_data,
        ]);

        return response()->json(['success' => true]);
    }
}
