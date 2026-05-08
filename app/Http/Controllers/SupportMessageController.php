<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        \App\Models\SupportMessage::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'] ?? (auth()->check() ? auth()->user()->name : null),
            'email' => $validated['email'] ?? (auth()->check() ? auth()->user()->email : null),
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Your message has been sent to the admin. We will get back to you shortly!');
    }
}
