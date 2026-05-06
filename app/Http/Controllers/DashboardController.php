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
                return view('dashboards.hospital', compact('hospital'));
            case 'ambulance':
            case 'security':
            case 'fire':
                $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();
                return view('dashboards.responder', compact('responder'));
            default:
                $hospitals = \App\Domains\Responders\Models\Hospital::all();
                $history = \App\Domains\Emergencies\Models\Emergency::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                $activeEmergency = \App\Domains\Emergencies\Models\Emergency::where('user_id', Auth::id())
                    ->where('status', '!=', 'resolved')
                    ->where('status', '!=', 'cancelled')
                    ->latest()
                    ->first();

                return view('dashboard', compact('hospitals', 'history', 'activeEmergency')); // Civilian/User dashboard
        }
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
}
