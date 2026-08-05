<?php

namespace App\Http\Controllers\Api;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Models\HospitalPatient;
use App\Models\HospitalReservation;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HospitalController extends Controller
{
    /**
     * List for the civilian hospital-picker and the ambulance
     * bed-reservation target picker.
     */
    public function index()
    {
        return response()->json(Hospital::all());
    }

    public function update(Request $request)
    {
        $hospital = Hospital::where('user_id', Auth::id())->first();

        if (!$hospital) {
            return response()->json(['success' => false, 'message' => 'Hospital record not found.'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'available_beds' => 'required|integer|min:0',
            'icu_beds' => 'required|integer|min:0',
            'specialties' => 'nullable|string|max:500',
            'resources' => 'nullable|string|max:1000',
        ]);

        $specialties = collect(explode(',', (string) $request->specialties))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        $resources = [];
        foreach (explode("\n", (string) $request->resources) as $line) {
            if (!str_contains($line, ':')) continue;
            [$key, $value] = explode(':', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key !== '') {
                $resources[$key] = $value;
            }
        }

        $hospital->update([
            'name' => $request->name,
            'contact_phone' => $request->contact_phone,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'available_beds' => $request->available_beds,
            'icu_beds' => $request->icu_beds,
            'specialties' => $specialties,
            'resources' => $resources,
        ]);

        return response()->json(['success' => true, 'hospital' => $hospital]);
    }

    public function acceptPatient(Request $request, $uuid)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('target_hospital_id', $hospital->id)
            ->firstOrFail();

        $emergency->update(['hospital_accepted_at' => now()]);

        if ($hospital->available_beds > 0) {
            $hospital->decrement('available_beds');
        }

        return response()->json(['success' => true]);
    }

    public function declinePatient(Request $request, $uuid)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('target_hospital_id', $hospital->id)
            ->firstOrFail();

        $emergency->update(['hospital_decline_reason' => $request->reason ?: 'No reason given']);

        return response()->json(['success' => true]);
    }

    public function dischargePatient($uuid)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('target_hospital_id', $hospital->id)
            ->firstOrFail();

        if ($emergency->status === 'resolved') {
            return response()->json(['success' => false, 'message' => 'This patient has already been discharged.'], 422);
        }

        $fee = 5000.00;

        $feeCollected = DB::transaction(function () use ($emergency, $hospital, $fee) {
            $emergency->update([
                'status' => 'resolved',
                'resolved_at' => $emergency->resolved_at ?? now(),
            ]);
            $emergency->freeAssignedResponder();

            if ($hospital->available_beds < $hospital->total_beds) {
                $hospital->increment('available_beds');
            }

            $patient = User::lockForUpdate()->find($emergency->user_id);
            if (!$patient || $patient->wallet_balance < $fee) {
                return false;
            }

            $patientBalance = $patient->wallet_balance - $fee;
            $patient->wallet_balance = $patientBalance;
            $patient->save();

            WalletTransaction::create([
                'user_id' => $patient->id,
                'type' => 'debit',
                'amount' => $fee,
                'balance_after' => $patientBalance,
                'reference' => 'admission_fee_' . $emergency->uuid,
                'description' => 'Hospital admission fee',
                'status' => 'success',
            ]);

            $hospitalUser = User::lockForUpdate()->find($hospital->user_id);
            $newBalance = $hospitalUser->wallet_balance + $fee;
            $hospitalUser->wallet_balance = $newBalance;
            $hospitalUser->save();

            WalletTransaction::create([
                'user_id' => $hospitalUser->id,
                'type' => 'credit',
                'amount' => $fee,
                'balance_after' => $newBalance,
                'reference' => 'admission_' . $emergency->uuid,
                'description' => 'Admission fee',
                'status' => 'success',
            ]);

            $emergency->update(['admission_fee_paid_at' => now()]);

            return true;
        });

        return response()->json([
            'success' => true,
            'message' => $feeCollected
                ? 'Patient discharged. ₦' . number_format($fee, 2) . ' credited to your wallet.'
                : 'Patient discharged. The ₦' . number_format($fee, 2) . ' admission fee could not be collected (patient has insufficient wallet balance).',
        ]);
    }

    /**
     * Emergencies routed to this hospital that are still active — the list a
     * hospital dashboard needs to drive accept/decline/discharge actions.
     * (incomingLocations() below only returns bare lat/lng for the map.)
     */
    public function incoming()
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();

        $emergencies = Emergency::where('target_hospital_id', $hospital->id)
            ->whereIn('status', ['pending', 'dispatched', 'enroute', 'arrived'])
            ->with('user', 'assignedResponder.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($emergencies);
    }

    public function incomingLocations()
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();

        $emergencies = Emergency::where('target_hospital_id', $hospital->id)
            ->whereIn('status', ['dispatched', 'enroute', 'arrived'])
            ->whereNotNull('assigned_responder_id')
            ->with('assignedResponder')
            ->get();

        $locations = $emergencies
            ->filter(fn ($e) => $e->assignedResponder && $e->assignedResponder->current_lat && $e->assignedResponder->current_lng)
            ->map(fn ($e) => [
                'uuid' => $e->uuid,
                'lat' => (float) $e->assignedResponder->current_lat,
                'lng' => (float) $e->assignedResponder->current_lng,
                'type' => $e->assignedResponder->responder_type,
            ])
            ->values();

        return response()->json($locations);
    }

    /**
     * JSON equivalent of the web CSV export — a mobile admissions history
     * screen needs a list to render, not a file download.
     */
    public function admissions(Request $request)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();

        $query = $hospital->emergencies()->whereNotNull('hospital_accepted_at')->with('user', 'emergencyType');

        if ($request->date_from) {
            $query->whereDate('hospital_accepted_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('hospital_accepted_at', '<=', $request->date_to);
        }

        return response()->json($query->orderBy('hospital_accepted_at', 'desc')->get());
    }

    // ── Manually added (walk-in) patients ──────────────────────────

    public function storePatient(Request $request)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'reason' => 'nullable|string|max:255',
            'bed_type' => 'required|in:general,icu',
            'notes' => 'nullable|string|max:1000',
        ]);

        $bedField = $request->bed_type === 'icu' ? 'icu_beds' : 'available_beds';
        $deducted = $hospital->{$bedField} > 0;

        $patient = HospitalPatient::create([
            'hospital_id' => $hospital->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'reason' => $request->reason,
            'bed_type' => $request->bed_type,
            'bed_deducted' => $deducted,
            'notes' => $request->notes,
            'status' => 'admitted',
            'admitted_at' => now(),
        ]);

        if ($deducted) {
            $hospital->decrement($bedField);
        }

        return response()->json(['success' => true, 'patient' => $patient], 201);
    }

    public function dischargeManualPatient($id)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $patient = HospitalPatient::where('hospital_id', $hospital->id)->findOrFail($id);

        if ($patient->status === 'discharged') {
            return response()->json(['success' => false, 'message' => 'This patient has already been discharged.'], 422);
        }

        $patient->update(['status' => 'discharged', 'discharged_at' => now()]);

        if ($patient->bed_deducted) {
            $hospital->increment($patient->bed_type === 'icu' ? 'icu_beds' : 'available_beds');
        }

        return response()->json(['success' => true, 'message' => 'Patient discharged.']);
    }

    public function destroyPatient($id)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $patient = HospitalPatient::where('hospital_id', $hospital->id)->findOrFail($id);

        if ($patient->status === 'admitted' && $patient->bed_deducted) {
            $hospital->increment($patient->bed_type === 'icu' ? 'icu_beds' : 'available_beds');
        }

        $patient->delete();

        return response()->json(['success' => true]);
    }

    // ── Manually added bed reservations ──────────────────────────

    public function storeReservation(Request $request)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'patient_name' => 'required|string|max:255',
            'bed_type' => 'required|in:general,icu',
            'expected_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $bedField = $request->bed_type === 'icu' ? 'icu_beds' : 'available_beds';
        $deducted = $hospital->{$bedField} > 0;

        $reservation = HospitalReservation::create([
            'hospital_id' => $hospital->id,
            'patient_name' => $request->patient_name,
            'bed_type' => $request->bed_type,
            'bed_deducted' => $deducted,
            'expected_at' => $request->expected_at,
            'notes' => $request->notes,
            'status' => 'reserved',
        ]);

        if ($deducted) {
            $hospital->decrement($bedField);
        }

        return response()->json(['success' => true, 'reservation' => $reservation], 201);
    }

    public function cancelReservation($id)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $reservation = HospitalReservation::where('hospital_id', $hospital->id)->findOrFail($id);

        if ($reservation->status !== 'reserved') {
            return response()->json(['success' => false, 'message' => 'This reservation can no longer be cancelled.'], 422);
        }

        $reservation->update(['status' => 'cancelled']);

        if ($reservation->bed_deducted) {
            $hospital->increment($reservation->bed_type === 'icu' ? 'icu_beds' : 'available_beds');
        }

        return response()->json(['success' => true]);
    }

    public function admitReservation($id)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $reservation = HospitalReservation::where('hospital_id', $hospital->id)->findOrFail($id);

        if ($reservation->status !== 'reserved') {
            return response()->json(['success' => false, 'message' => 'This reservation can no longer be admitted.'], 422);
        }

        DB::transaction(function () use ($reservation) {
            HospitalPatient::create([
                'hospital_id' => $reservation->hospital_id,
                'name' => $reservation->patient_name,
                'bed_type' => $reservation->bed_type,
                'bed_deducted' => $reservation->bed_deducted,
                'notes' => $reservation->notes,
                'status' => 'admitted',
                'admitted_at' => now(),
            ]);

            $reservation->update(['status' => 'admitted', 'bed_deducted' => false]);
        });

        return response()->json(['success' => true, 'message' => 'Reservation admitted as a patient.']);
    }
}
