<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportMessageController extends Controller
{
    /**
     * Guest-friendly like the web version — not behind auth:sanctum, but
     * still auto-fills name/email when a bearer token is present, since
     * Sanctum's guard resolves the token on demand even without the
     * auth:sanctum middleware having run.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $user = Auth::guard('sanctum')->user();

        SupportMessage::create([
            'user_id' => $user?->id,
            'name' => $validated['name'] ?? $user?->name,
            'email' => $validated['email'] ?? $user?->email,
            'message' => $validated['message'],
        ]);

        return response()->json(['message' => 'Your message has been sent to the admin. We will get back to you shortly!'], 201);
    }

    public function mine()
    {
        return response()->json(
            SupportMessage::where('user_id', Auth::id())->latest()->get()
        );
    }
}
