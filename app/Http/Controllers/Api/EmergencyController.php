<?php

namespace App\Http\Controllers\Api;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Emergencies\Models\EmergencyType;
use App\Domains\Emergencies\Services\EmergencyService;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Responders\Models\Responder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmergencyController extends Controller
{
    protected EmergencyService $emergencyService;

    public function __construct(EmergencyService $emergencyService)
    {
        $this->emergencyService = $emergencyService;
    }

    public function types()
    {
        return response()->json(EmergencyType::all());
    }

    public function trigger(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'preferred_hospital_id' => 'nullable|integer|exists:hospitals,id',
        ]);

        $result = $this->emergencyService->dispatch(Auth::user(), [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'preferred_hospital_id' => $request->preferred_hospital_id,
            'triggered_via' => 'app',
        ]);

        return response()->json([
            'message' => $result['message'],
            'uuid' => $result['emergency']->uuid,
            'status' => $result['status'],
            'responder' => $result['responder'],
            'hospital' => $result['hospital'],
            'no_responders' => $result['no_responders'],
        ], 201);
    }

    public function getStatus($uuid)
    {
        $user = Auth::user();
        $emergency = Emergency::with('targetHospital')->where('uuid', $uuid)->firstOrFail();

        $isOwner = $emergency->user_id === $user->id;
        $isAssignedResponder = $emergency->assigned_responder_id && Responder::where('user_id', $user->id)
            ->where('id', $emergency->assigned_responder_id)->exists();

        if (!$isOwner && !$isAssignedResponder && $user->role !== 'admin') {
            abort(403);
        }

        $responderData = null;
        if ($emergency->assigned_responder_id) {
            $responder = Responder::with('user')->find($emergency->assigned_responder_id);
            if ($responder) {
                $responderData = [
                    'id' => $responder->id,
                    'name' => $responder->user->name,
                    'type' => $responder->responder_type,
                    'phone' => $responder->user->phone,
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
            ],
            'target_hospital' => $emergency->targetHospital,
            'hospital_choice_locked' => in_array($emergency->status, ['arrived', 'resolved', 'cancelled'], true),
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

    public function chooseHospital(Request $request, $uuid)
    {
        $request->validate(['hospital_id' => 'required|integer|exists:hospitals,id']);

        $user = Auth::user();
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();

        if ($emergency->user_id === $user->id) {
            if (in_array($emergency->status, ['arrived', 'resolved', 'cancelled'], true)) {
                return response()->json(['success' => false, 'message' => 'This emergency has already reached the pickup stage; the hospital can no longer be changed.'], 422);
            }
        } else {
            $responder = Responder::where('user_id', $user->id)->first();
            $isAssignedResponder = $responder && $emergency->assigned_responder_id === $responder->id;

            if (!$isAssignedResponder && $user->role !== 'admin') {
                abort(403);
            }

            if ($emergency->hospital_accepted_at || in_array($emergency->status, ['resolved', 'cancelled'], true)) {
                return response()->json(['success' => false, 'message' => 'The destination hospital has already accepted this patient and can no longer be changed.'], 422);
            }
        }

        $emergency->update(['target_hospital_id' => $request->hospital_id]);

        return response()->json([
            'success' => true,
            'hospital' => Hospital::find($request->hospital_id),
        ]);
    }

    public function acceptMission(Request $request, $uuid)
    {
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        $responder = Responder::where('user_id', Auth::id())->firstOrFail();

        $emergency->update([
            'assigned_responder_id' => $responder->id,
            'status' => 'enroute',
        ]);

        $responder->update(['is_available' => false]);

        return response()->json(['success' => true]);
    }

    public function declineMission(Request $request, $uuid)
    {
        $responder = Responder::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)->first();

        if (!$emergency) {
            return response()->json(['success' => true]);
        }

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
        $responder = Responder::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('assigned_responder_id', $responder->id)
            ->firstOrFail();

        $emergency->update(['status' => 'arrived']);

        return response()->json(['success' => true]);
    }

    public function saveResponderNotes(Request $request, $uuid)
    {
        $request->validate(['responder_notes' => 'nullable|string|max:2000']);

        $responder = Responder::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('assigned_responder_id', $responder->id)
            ->firstOrFail();

        $emergency->update(['responder_notes' => $request->responder_notes]);

        return response()->json(['success' => true]);
    }

    public function requestDoctorConsult(Request $request, $uuid)
    {
        $user = Auth::user();
        $query = Emergency::where('uuid', $uuid);

        if ($user->role === 'hospital') {
            $hospital = Hospital::where('user_id', $user->id)->firstOrFail();
            $query->where('target_hospital_id', $hospital->id);
        } elseif (in_array($user->role, ['ambulance', 'fire', 'security'], true)) {
            $responder = Responder::where('user_id', $user->id)->firstOrFail();
            $query->where('assigned_responder_id', $responder->id);
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $emergency = $query->firstOrFail();
        $emergency->update(['doctor_consult_requested_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function resolveEmergency(Request $request, $uuid)
    {
        $responder = Responder::where('user_id', Auth::id())->first();

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
        $responder = Responder::where('user_id', Auth::id())->first();

        $emergencies = Emergency::where(function ($q) use ($responder) {
            $q->whereNull('assigned_responder_id')->whereIn('status', ['pending', 'dispatched']);
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

    /**
     * The web version of this endpoint has no ownership check at all — anyone
     * with a valid emergency uuid could attach evidence to it. Tightened here
     * to owner/assigned-responder/admin, matching getStatus()'s rule.
     */
    public function uploadEvidence(Request $request, $uuid)
    {
        $request->validate(['evidence' => 'required|file|max:10240']);

        $user = Auth::user();
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        $this->authorizeEmergencyAccess($emergency, $user);

        $path = $request->file('evidence')->store('evidence', 'public');
        $emergency->update(['evidence_file' => $path]);

        return response()->json(['success' => true, 'path' => $path]);
    }

    /**
     * Same missing-ownership-check gap as uploadEvidence — tightened here.
     */
    public function updateTriage(Request $request, $uuid)
    {
        $request->validate(['triage_data' => 'required|array']);

        $user = Auth::user();
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();
        $this->authorizeEmergencyAccess($emergency, $user);

        $emergency->update(['triage_data' => $request->triage_data]);

        return response()->json(['success' => true]);
    }

    protected function authorizeEmergencyAccess(Emergency $emergency, $user): void
    {
        $isOwner = $emergency->user_id === $user->id;
        $isAssignedResponder = $emergency->assigned_responder_id && Responder::where('user_id', $user->id)
            ->where('id', $emergency->assigned_responder_id)->exists();

        if (!$isOwner && !$isAssignedResponder && $user->role !== 'admin') {
            abort(403);
        }
    }
}
