<?php

namespace App\Http\Controllers\Api;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Responders\Models\Responder;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected const RESPONDER_ROLES = ['ambulance', 'security', 'fire'];

    /**
     * Per-role home-screen aggregate, JSON port of the web
     * DashboardController::index(). Admin is intentionally not special-cased
     * (out of mobile scope — falls through to the civilian shape, which is
     * harmless since the web admin panel remains the only real admin UI).
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;

        if (in_array($role, self::RESPONDER_ROLES, true)) {
            return response()->json($this->responderHome($user));
        }

        if ($role === 'doctor') {
            return response()->json($this->doctorHome($user));
        }

        if ($role === 'hospital') {
            return response()->json($this->hospitalHome($user));
        }

        return response()->json($this->civilianHome($user));
    }

    protected function responderHome($user): array
    {
        $responder = Responder::where('user_id', $user->id)->first();

        $missionsDone = $responder
            ? Emergency::where('assigned_responder_id', $responder->id)->where('status', 'resolved')->count()
            : 0;

        $activeMission = $responder
            ? Emergency::where('assigned_responder_id', $responder->id)
                ->whereIn('status', ['dispatched', 'enroute', 'arrived'])
                ->with('user', 'targetHospital')
                ->latest()
                ->first()
            : null;

        $reviews = $responder ? Review::where('responder_id', $responder->id)->get() : collect();

        return [
            'role' => $user->role,
            'responder' => $responder,
            'missions_done' => $missionsDone,
            'active_mission' => $activeMission,
            'average_rating' => $reviews->count() ? round($reviews->avg('rating'), 1) : null,
            'reviews_count' => $reviews->count(),
        ];
    }

    protected function doctorHome($user): array
    {
        $responder = Responder::where('user_id', $user->id)->first();

        $missionsDone = $responder
            ? Emergency::where('assigned_responder_id', $responder->id)->where('status', 'resolved')->count()
            : 0;

        $reviews = $responder ? Review::where('responder_id', $responder->id)->get() : collect();

        return [
            'role' => 'doctor',
            'responder' => $responder,
            'missions_done' => $missionsDone,
            'average_rating' => $reviews->count() ? round($reviews->avg('rating'), 1) : null,
            'reviews_count' => $reviews->count(),
        ];
    }

    protected function hospitalHome($user): array
    {
        $hospital = Hospital::where('user_id', $user->id)->first();

        $incomingCount = $hospital
            ? Emergency::where('target_hospital_id', $hospital->id)
                ->whereIn('status', ['pending', 'dispatched', 'enroute', 'arrived'])
                ->count()
            : 0;

        return [
            'role' => 'hospital',
            'hospital' => $hospital,
            'incoming_count' => $incomingCount,
        ];
    }

    protected function civilianHome($user): array
    {
        $history = Emergency::where('user_id', $user->id)
            ->with('targetHospital', 'emergencyType')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $activeEmergency = Emergency::where('user_id', $user->id)
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->latest()
            ->first();

        return [
            'role' => $user->role,
            'history' => $history,
            'active_emergency' => $activeEmergency ? [
                'uuid' => $activeEmergency->uuid,
                'status' => $activeEmergency->status,
            ] : null,
        ];
    }

    /**
     * Nearby on-duty ambulance/fire positions — mobile "nearby responders" map.
     */
    public function liveMapData()
    {
        $responders = Responder::with('user')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->where('is_on_duty', true)
            ->whereIn('responder_type', ['ambulance', 'fire'])
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'type' => $r->responder_type,
                'name' => $r->user ? $r->user->name : ('Responder #' . $r->id),
                'lat' => (float) $r->current_lat,
                'lng' => (float) $r->current_lng,
                'available' => (bool) $r->is_available,
            ]);

        return response()->json(['responders' => $responders]);
    }
}
