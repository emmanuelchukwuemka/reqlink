<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAnnouncementController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'target_role' => ['nullable', 'string', 'in:civilian,doctor,hospital,ambulance,security,fire'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $announcement = Announcement::create([
            'admin_id' => Auth::id(),
            'title' => $data['title'],
            'message' => $data['message'],
            'target_role' => $data['target_role'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        AdminActivityLog::record('announcement_sent', "Sent announcement \"{$announcement->title}\"" . ($announcement->target_role ? " to {$announcement->target_role} users" : ' to all users'), $announcement);

        return redirect()->back()->with('success', 'Announcement published.');
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $announcement = Announcement::findOrFail($id);
        $title = $announcement->title;
        $announcement->delete();

        AdminActivityLog::record('announcement_removed', "Removed announcement \"{$title}\"");

        return redirect()->back()->with('success', 'Announcement removed.');
    }
}
