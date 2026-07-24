<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminSupportController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    public function markRead($id)
    {
        $this->ensureAdmin();

        $message = SupportMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Message marked as read.');
    }

    public function reply(Request $request, $id)
    {
        $this->ensureAdmin();

        $request->validate(['reply' => 'required|string|max:2000']);

        $message = SupportMessage::findOrFail($id);
        $message->update([
            'admin_reply' => $request->reply,
            'replied_at' => now(),
            'is_read' => true,
        ]);

        if ($message->email) {
            try {
                Mail::raw($request->reply, function ($mail) use ($message) {
                    $mail->to($message->email)
                        ->subject('Re: Your message to ResQLink Support');
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to email support reply: ' . $e->getMessage());
            }
        }

        AdminActivityLog::record('support_replied', "Replied to support message from {$message->name}", $message);

        return redirect()->back()->with('success', 'Reply sent.');
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $message = SupportMessage::findOrFail($id);
        $message->delete();

        return redirect()->back()->with('success', 'Message deleted.');
    }
}
