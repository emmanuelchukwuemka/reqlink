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
                $hospitals = \App\Domains\Responders\Models\Hospital::all();
                $ambulances = \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->with('user')->get();
                $securityUnits = \App\Domains\Responders\Models\Responder::where('responder_type', 'security')->with('user')->get();
                $fireUnits = \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->with('user')->get();
                return view('dashboards.responder', compact('responder', 'hospitals', 'ambulances', 'securityUnits', 'fireUnits'));
            default:
                $hospitals = \App\Domains\Responders\Models\Hospital::all();
                $ambulances = \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->with('user')->get();
                $securityUnits = \App\Domains\Responders\Models\Responder::where('responder_type', 'security')->with('user')->get();
                $fireUnits = \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->with('user')->get();
                
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

                return view('dashboard', compact('hospitals', 'ambulances', 'securityUnits', 'fireUnits', 'history', 'activeEmergency', 'samaritanMissions', 'walletTransactions'));
        }
    }
    public function liveMapData()
    {
        $responders = \App\Domains\Responders\Models\Responder::with('user')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->where('is_on_duty', true)
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
        $emergencies = \App\Domains\Emergencies\Models\Emergency::where('status', '!=', 'resolved')->get();
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
