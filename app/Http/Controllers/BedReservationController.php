<?php

namespace App\Http\Controllers;

use App\Models\BedReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Responders\Models\Responder;

class BedReservationController extends Controller
{
    // Ambulance requests a bed at a hospital
    // Route: POST /bed/reserve/{hospitalUuid}  — body: { emergency_uuid, eta_minutes }
    public function reserve(Request $request, $hospitalUuid)
    {
        $request->validate(['emergency_uuid' => 'required|string', 'eta_minutes' => 'nullable|integer']);

        $hospital  = Hospital::findOrFail($hospitalUuid);
        $emergency = Emergency::where('uuid', $request->emergency_uuid)->firstOrFail();
        $responder = Responder::where('user_id', Auth::id())->firstOrFail();

        // Cancel any previous pending reservation for this emergency
        BedReservation::where('emergency_id', $emergency->id)
            ->where('responder_id', $responder->id)
            ->whereIn('status', ['pending'])
            ->update(['status' => 'cancelled']);

        $reservation = BedReservation::create([
            'hospital_id'  => $hospital->id,
            'emergency_id' => $emergency->id,
            'responder_id' => $responder->id,
            'status'       => 'pending',
            'eta_minutes'  => $request->eta_minutes ?? 10,
        ]);

        return response()->json(['success' => true, 'reservation_id' => $reservation->id]);
    }

    // Hospital confirms or declines a reservation
    public function respond(Request $request, $id)
    {
        $request->validate(['action' => 'required|in:confirmed,declined']);

        $hospital     = Hospital::where('user_id', Auth::id())->firstOrFail();
        $reservation  = BedReservation::where('id', $id)->where('hospital_id', $hospital->id)->firstOrFail();

        $reservation->update([
            'status'       => $request->action,
            'confirmed_at' => $request->action === 'confirmed' ? now() : null,
        ]);

        // Decrement available beds when confirmed
        if ($request->action === 'confirmed' && $hospital->available_beds > 0) {
            $hospital->decrement('available_beds');
        }

        return response()->json(['success' => true]);
    }

    // Ambulance marks arrived — hospital increments used beds
    public function arrived($id)
    {
        $responder   = Responder::where('user_id', Auth::id())->firstOrFail();
        $reservation = BedReservation::where('id', $id)->where('responder_id', $responder->id)->firstOrFail();
        $reservation->update(['status' => 'arrived', 'arrived_at' => now()]);

        return response()->json(['success' => true]);
    }

    // Hospital polls for pending reservations
    public function pending()
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();

        $reservations = BedReservation::where('hospital_id', $hospital->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['responder.user', 'emergency.user'])
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'status'       => $r->status,
                'eta_minutes'  => $r->eta_minutes,
                'responder'    => $r->responder->user->name ?? 'Unit',
                'patient'      => $r->emergency->user->name ?? 'Unknown',
                'created_at'   => $r->created_at->toISOString(),
            ]);

        return response()->json($reservations);
    }
}
