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

            \App\Services\WebPushService::sendToUsers([$nearestResponder->user_id]);

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

        // No mobile unit was free to auto-assign — this emergency stays unassigned
        // and broadcasts to every on-duty responder (see fetchAlerts()). Push all
        // of them too, since in-tab polling alone misses a backgrounded/locked phone.
        $onDutyUserIds = \App\Domains\Responders\Models\Responder::where('is_on_duty', true)
            ->pluck('user_id')->toArray();
        \App\Services\WebPushService::sendToUsers($onDutyUserIds);

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
                'target_hospital_id' => $nearestHospital->id,
            ]);

            // Email is a best-effort side notification — a mail/SMTP failure must never
            // break the core emergency dispatch flow (that would be far worse than a
            // hospital missing one email; they still see it live on their dashboard).
            try {
                $hospitalRecord = \App\Domains\Responders\Models\Hospital::find($nearestHospital->id);
                if ($hospitalRecord && $hospitalRecord->user) {
                    $hospitalRecord->user->notify(new \App\Notifications\NewEmergencyRoutedToHospital($emergency));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send hospital emergency-routed notification: ' . $e->getMessage());
            }

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

        // Mark unavailable immediately on accept — not just when the auto-dispatch
        // algorithm assigns someone. Without this, a responder who manually accepts
        // a broadcast (unassigned) alert could still be auto-routed a second,
        // unrelated emergency while already mid-mission.
        $responder->update(['is_available' => false]);

        return response()->json(['success' => true]);
    }

    public function declineMission(Request $request, $uuid)
    {
        $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)->first();

        if (!$emergency) {
            return response()->json(['success' => true]);
        }

        // Only actually change anything server-side if this emergency was
        // specifically assigned to this responder (auto-dispatch or admin
        // dispatch). If it's still an unassigned broadcast alert, every other
        // on-duty responder needs to keep seeing it — declining is purely a
        // local "stop alerting me about this one" action in that case.
        if ($emergency->assigned_responder_id === $responder->id) {
            $emergency->update([
                'assigned_responder_id' => null,
                'status' => 'pending',
            ]);
            $responder->update(['is_available' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function markArrived(Request $request, $uuid)
    {
        $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('assigned_responder_id', $responder->id)
            ->firstOrFail();

        $emergency->update(['status' => 'arrived']);

        return response()->json(['success' => true]);
    }

    public function saveResponderNotes(Request $request, $uuid)
    {
        $request->validate(['responder_notes' => 'nullable|string|max:2000']);
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        $emergency->update(['responder_notes' => $request->responder_notes]);

        return response()->json(['success' => true]);
    }

    public function requestDoctorConsult(Request $request, $uuid)
    {
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();

        $emergency->update([
            'doctor_consult_requested_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function resolveEmergency(Request $request, $uuid)
    {
        $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();

        $query = Emergency::where('uuid', $uuid);
        if ($responder) {
            $query->where('assigned_responder_id', $responder->id);
        }
        $emergency = $query->firstOrFail();

        $emergency->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
        $emergency->freeAssignedResponder();

        return response()->json(['success' => true]);
    }

    public function fetchAlerts()
    {
        $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();

        $emergencies = Emergency::where(function ($q) use ($responder) {
                // Unassigned broadcast alerts any on-duty responder can take
                $q->whereNull('assigned_responder_id')->whereIn('status', ['pending', 'dispatched']);
                // OR this responder's own mission at any stage up to resolution —
                // otherwise it drops out of the list the moment they accept it
                // ('enroute'/'arrived' aren't in the broadcast statuses above), and
                // they'd lose access to handoff notes, chat, and completion actions.
                if ($responder) {
                    $q->orWhere(function ($q2) use ($responder) {
                        $q2->where('assigned_responder_id', $responder->id)
                            ->whereIn('status', ['pending', 'dispatched', 'enroute', 'arrived']);
                    });
                }
            })
            ->with('user', 'targetHospital')
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
