<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domains\Emergencies\Models\Emergency;

class ChatController extends Controller
{
    public function messages($uuid)
    {
        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();

        $messages = ChatMessage::where('emergency_uuid', $uuid)
            ->with('sender:id,name,role')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'message'     => $m->message,
                'sender_name' => $m->sender->name ?? 'Unknown',
                'sender_role' => $m->sender_role,
                'mine'        => $m->sender_id === Auth::id(),
                'time'        => $m->created_at->format('H:i'),
            ]);

        // Mark unread as read
        ChatMessage::where('emergency_uuid', $uuid)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function send(Request $request, $uuid)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        Emergency::where('uuid', $uuid)->firstOrFail();

        $user = Auth::user();
        $role = match(true) {
            $user->role === 'admin'    => 'admin',
            in_array($user->role, ['ambulance','fire','security']) => 'responder',
            default                    => 'user',
        };

        $msg = ChatMessage::create([
            'emergency_uuid' => $uuid,
            'sender_id'      => $user->id,
            'sender_role'    => $role,
            'message'        => $request->message,
        ]);

        return response()->json([
            'id'          => $msg->id,
            'message'     => $msg->message,
            'sender_name' => $user->name,
            'sender_role' => $role,
            'mine'        => true,
            'time'        => $msg->created_at->format('H:i'),
        ]);
    }

    public function unreadCount($uuid)
    {
        $count = ChatMessage::where('emergency_uuid', $uuid)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
