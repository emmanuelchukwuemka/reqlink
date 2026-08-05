<?php

namespace App\Http\Controllers\Api;

use App\Domains\Responders\Models\Responder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * No self-service path to BECOME a Good Samaritan exists server-side —
     * is_good_samaritan is admin-granted only. This just flips the
     * already-granted active/inactive switch.
     */
    public function toggleSamaritan(Request $request)
    {
        $user = Auth::user();
        if (!$user->is_good_samaritan) {
            return response()->json(['error' => 'Not a Good Samaritan'], 403);
        }

        $user->update(['samaritan_active' => $request->boolean('active')]);

        return response()->json(['success' => true, 'samaritan_active' => $user->samaritan_active]);
    }

    public function toggleMamaCare(Request $request)
    {
        $user = Auth::user();
        $user->update(['mama_care_active' => $request->boolean('active')]);

        return response()->json(['success' => true, 'mama_care_active' => $user->mama_care_active]);
    }

    public function updateMamaCareProfile(Request $request)
    {
        $request->validate([
            'pregnancy_due_date' => 'nullable|date',
            'pregnancy_high_risk' => 'boolean',
            'preferred_maternity_hospital' => 'nullable|string|max:255',
            'obgyn_contact' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->update([
            'pregnancy_due_date' => $request->pregnancy_due_date,
            'pregnancy_high_risk' => $request->boolean('pregnancy_high_risk'),
            'preferred_maternity_hospital' => $request->preferred_maternity_hospital,
            'obgyn_contact' => $request->obgyn_contact,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateSpecialty(Request $request)
    {
        $request->validate(['specialty' => 'nullable|string|max:100']);

        $responder = Responder::where('user_id', Auth::id())->first();
        if (!$responder) {
            return response()->json(['success' => false, 'message' => 'No responder profile found.'], 404);
        }

        $responder->update(['specialty' => $request->specialty]);

        return response()->json(['success' => true]);
    }
}
