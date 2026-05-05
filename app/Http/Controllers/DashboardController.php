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
                return view('dashboards.admin');
            case 'hospital':
            case 'ambulance':
            case 'security':
            case 'fire':
                return view('dashboards.responder');
            default:
                return view('dashboard'); // Civilian/User dashboard
        }
    }
}
