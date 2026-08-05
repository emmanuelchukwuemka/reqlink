<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Mirrors the AppServiceProvider View::composer query that feeds the web
     * dashboards' announcement banners — no dedicated endpoint exists there
     * since Blade gets it injected directly.
     */
    public function index()
    {
        $user = Auth::user();

        $announcements = Announcement::active()
            ->where(fn ($q) => $q->whereNull('target_role')->orWhere('target_role', $user->role))
            ->latest()
            ->get();

        return response()->json($announcements);
    }
}
