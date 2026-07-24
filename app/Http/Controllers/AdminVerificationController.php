<?php

namespace App\Http\Controllers;

use App\Domains\Users\Models\User;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminVerificationController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        $partnerRoles = ['doctor', 'hospital', 'ambulance', 'security', 'fire'];

        $pending = User::whereIn('role', $partnerRoles)
            ->where('is_verified', false)
            ->latest()
            ->get();

        $reviewed = User::whereIn('role', $partnerRoles)
            ->where(function ($q) {
                $q->where('is_verified', true)->orWhereNotNull('verification_rejected_reason');
            })
            ->whereNotNull('verification_reviewed_at')
            ->latest('verification_reviewed_at')
            ->limit(30)
            ->get();

        return view('dashboards.admin_verifications', compact('pending', 'reviewed'));
    }

    public function approve($id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        $user->update([
            'is_verified' => true,
            'verification_rejected_reason' => null,
            'verification_reviewed_at' => now(),
        ]);

        AdminActivityLog::record('verification_approved', "Approved verification for {$user->name} ({$user->role})", $user);

        return redirect()->back()->with('success', "{$user->name} has been verified.");
    }

    public function reject(Request $request, $id)
    {
        $this->ensureAdmin();

        $request->validate(['reason' => 'required|string|max:500']);

        $user = User::findOrFail($id);
        $user->update([
            'is_verified' => false,
            'verification_rejected_reason' => $request->reason,
            'verification_reviewed_at' => now(),
        ]);

        AdminActivityLog::record('verification_rejected', "Rejected verification for {$user->name} ({$user->role}): {$request->reason}", $user);

        return redirect()->back()->with('success', "{$user->name}'s verification was rejected.");
    }
}
