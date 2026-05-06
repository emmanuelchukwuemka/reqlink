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
                return view('dashboards.admin', compact('users'));
            case 'hospital':
            case 'ambulance':
            case 'security':
            case 'fire':
                return view('dashboards.responder');
            default:
                $hospitals = \App\Domains\Users\Models\User::where('role', 'hospital')->get();
                $history = \App\Domains\Emergencies\Models\Emergency::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                return view('dashboard', compact('hospitals', 'history')); // Civilian/User dashboard
        }
    }
}
