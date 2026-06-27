<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        switch ($role) {
            case 'admin':
                $users = \App\Domains\Users\Models\User::orderBy('created_at', 'desc')->get();
                $activeEmergenciesCount = \App\Domains\Emergencies\Models\Emergency::where('status', '!=', 'resolved')->count();
                $onDutyRespondersCount = \App\Domains\Responders\Models\Responder::where('is_on_duty', true)->count();
                return view('dashboards.admin', compact('users', 'activeEmergenciesCount', 'onDutyRespondersCount'));
            case 'hospital':
                $hospital = \App\Domains\Responders\Models\Hospital::where('user_id', Auth::id())->first();
                $incomingEmergencies = \App\Domains\Emergencies\Models\Emergency::where('target_hospital_id', $hospital->id)
                    ->whereIn('status', ['pending', 'dispatched', 'enroute', 'arrived'])
                    ->with('user', 'emergencyType')
                    ->latest()
                    ->get();
                return view('dashboards.hospital', compact('hospital', 'incomingEmergencies'));
            case 'ambulance':
            case 'security':
            case 'fire':
                $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();
                $hospitals  = \App\Domains\Responders\Models\Hospital::all();
                $ambulances = \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->with('user')->get();
                $fireUnits  = \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->with('user')->get();
                $missionsDone = $responder
                    ? \App\Domains\Emergencies\Models\Emergency::where('assigned_responder_id', $responder->id)->where('status', 'resolved')->count()
                    : 0;
                $totalUnits = $responder
                    ? \App\Domains\Responders\Models\Responder::where('responder_type', $responder->responder_type)->count()
                    : 0;
                $activeEmergencyForBed = $responder
                    ? \App\Domains\Emergencies\Models\Emergency::where('assigned_responder_id', $responder->id)
                        ->whereIn('status', ['dispatched', 'enroute', 'arrived'])
                        ->latest()
                        ->first()
                    : null;
                return view('dashboards.responder', compact('responder', 'hospitals', 'ambulances', 'fireUnits', 'missionsDone', 'totalUnits', 'activeEmergencyForBed'));
            default:
                $hospitals  = \App\Domains\Responders\Models\Hospital::all();
                $ambulances = \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->with('user')->get();
                $fireUnits  = \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->with('user')->get();

                $history = \App\Domains\Emergencies\Models\Emergency::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                $activeEmergency = \App\Domains\Emergencies\Models\Emergency::where('user_id', Auth::id())
                    ->where('status', '!=', 'resolved')
                    ->where('status', '!=', 'cancelled')
                    ->latest()
                    ->first();

                // Good Samaritan Missions
                $samaritanMissions = [];
                if (Auth::user()->is_good_samaritan && Auth::user()->samaritan_active) {
                    $samaritanMissions = \App\Domains\Emergencies\Models\Emergency::where('status', 'pending')
                        ->where('user_id', '!=', Auth::id()) // Don't respond to own emergency
                        ->latest()
                        ->limit(3)
                        ->get();
                }

                $walletTransactions = \App\Models\WalletTransaction::where('user_id', Auth::id())
                    ->where('status', 'success')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                return view('dashboard', compact('hospitals', 'ambulances', 'fireUnits', 'history', 'activeEmergency', 'samaritanMissions', 'walletTransactions'));
        }
    }
    public function adminDispatch(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'emergency_id' => 'required|integer',
            'responder_id' => 'required|integer',
        ]);

        $emergency = \App\Domains\Emergencies\Models\Emergency::findOrFail($request->emergency_id);
        $responder = \App\Domains\Responders\Models\Responder::with('user')->findOrFail($request->responder_id);

        // Free the previously assigned responder if there was one
        if ($emergency->assigned_responder_id && $emergency->assigned_responder_id !== $responder->id) {
            \App\Domains\Responders\Models\Responder::where('id', $emergency->assigned_responder_id)
                ->update(['is_available' => true]);
        }

        $emergency->update([
            'assigned_responder_id' => $responder->id,
            'status'                => 'dispatched',
        ]);
        $responder->update(['is_available' => false]);

        return response()->json([
            'success' => true,
            'message' => ($responder->user->name ?? 'Responder') . ' dispatched.',
        ]);
    }

    public function liveAdminData()
    {
        $activeEmergencies = \App\Domains\Emergencies\Models\Emergency::with('user')
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->latest()
            ->get();

        // Load assigned responder names in one query to avoid N+1
        $assignedIds = $activeEmergencies->pluck('assigned_responder_id')->filter()->unique();
        $responderNames = \App\Domains\Responders\Models\Responder::with('user')
            ->whereIn('id', $assignedIds)
            ->get()
            ->mapWithKeys(fn($r) => [$r->id => $r->user?->name ?? 'Unit #'.$r->id]);

        $emergencies = $activeEmergencies->map(fn($e) => [
            'id'               => $e->id,
            'uuid'             => $e->uuid,
            'status'           => $e->status,
            'lat'              => $e->latitude,
            'lng'              => $e->longitude,
            'latitude'         => $e->latitude,
            'longitude'        => $e->longitude,
            'user'             => $e->user ? ['name' => $e->user->name] : null,
            'created_at'       => $e->created_at->toISOString(),
            'assigned_responder_id'   => $e->assigned_responder_id,
            'assigned_responder_name' => $e->assigned_responder_id ? ($responderNames[$e->assigned_responder_id] ?? null) : null,
            'evidence_file'    => $e->evidence_file,
        ]);

        $responders = \App\Domains\Responders\Models\Responder::with('user')
            ->where('is_on_duty', true)
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'type' => $r->responder_type,
                'name' => $r->user ? $r->user->name : ('Unit #' . $r->id),
                'lat'  => (float) $r->current_lat,
                'lng'  => (float) $r->current_lng,
            ]);

        return response()->json([
            'emergencies' => $emergencies,
            'responders'  => $responders,
        ]);
    }

    public function liveMapData()
    {
        $responders = \App\Domains\Responders\Models\Responder::with('user')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->where('is_on_duty', true)
            ->whereIn('responder_type', ['ambulance', 'fire'])
            ->get()
            ->map(fn($r) => [
                'id'        => $r->id,
                'type'      => $r->responder_type,
                'name'      => $r->user ? $r->user->name : ('Responder #' . $r->id),
                'lat'       => (float) $r->current_lat,
                'lng'       => (float) $r->current_lng,
                'available' => (bool) $r->is_available,
            ]);

        return response()->json(['responders' => $responders]);
    }

    public function commandCenter()
    {
        $emergencies = \App\Domains\Emergencies\Models\Emergency::with('user')
            ->where('status', '!=', 'resolved')
            ->latest()
            ->get()
            ->map(fn($e) => [
                'id'          => $e->id,
                'uuid'        => $e->uuid,
                'status'      => $e->status,
                'latitude'    => $e->latitude,
                'longitude'   => $e->longitude,
                'user'        => $e->user ? ['name' => $e->user->name] : null,
                'created_at'  => $e->created_at->toISOString(),
                'assigned_responder_id' => $e->assigned_responder_id,
                'assigned_responder_name' => null,
                'evidence_file' => $e->evidence_file,
            ]);

        $responders = \App\Domains\Responders\Models\Responder::with('user')->where('is_on_duty', true)->get();

        return view('dashboards.admin_command', compact('emergencies', 'responders'));
    }

    public function toggleUserStatus($id)
    {
        $user = \App\Domains\Users\Models\User::findOrFail($id);
        
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot suspend your own account.');
        }

        $user->update([
            'is_suspended' => !$user->is_suspended
        ]);

        $status = $user->is_suspended ? 'suspended' : 're-activated';
        return redirect()->back()->with('success', "User {$user->name} has been {$status}.");
    }

    public function toggleSamaritan(Request $request)
    {
        $user = Auth::user();
        if (!$user->is_good_samaritan) {
            return response()->json(['error' => 'Not a Good Samaritan'], 403);
        }

        $user->update([
            'samaritan_active' => $request->input('active', false)
        ]);

        return response()->json(['success' => true]);
    }
}
