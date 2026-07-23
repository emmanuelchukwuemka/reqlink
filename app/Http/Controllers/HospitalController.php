<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Emergencies\Models\Emergency;
use App\Models\HospitalPatient;
use App\Models\HospitalReservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HospitalController extends Controller
{
    public function update(Request $request)
    {
        $hospital = Hospital::where('user_id', Auth::id())->first();

        if (!$hospital) {
            return redirect()->back()->with('error', 'Hospital record not found.');
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
            ->map(fn($s) => trim($s))
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

        return redirect()->back()->with('success', 'Facility profile updated successfully.');
    }

    public function acceptPatient(Request $request, $uuid)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('target_hospital_id', $hospital->id)
            ->firstOrFail();

        $emergency->update([
            'hospital_accepted_at' => now(),
        ]);

        if ($hospital->available_beds > 0) {
            $hospital->decrement('available_beds');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Patient admission acknowledged. Emergency team notified.');
    }

    public function declinePatient(Request $request, $uuid)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('target_hospital_id', $hospital->id)
            ->firstOrFail();

        $emergency->update([
            'hospital_decline_reason' => $request->reason ?: 'No reason given',
        ]);

        return response()->json(['success' => true]);
    }

    public function dischargePatient($uuid)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $emergency = Emergency::where('uuid', $uuid)
            ->where('target_hospital_id', $hospital->id)
            ->firstOrFail();

        if ($emergency->admission_fee_paid_at) {
            return response()->json(['success' => false, 'message' => 'This patient has already been discharged.'], 422);
        }

        $fee = 5000.00;

        DB::transaction(function () use ($emergency, $hospital, $fee) {
            $emergency->update([
                'status' => 'resolved',
                'resolved_at' => $emergency->resolved_at ?? now(),
                'admission_fee_paid_at' => now(),
            ]);

            if ($hospital->available_beds < $hospital->total_beds) {
                $hospital->increment('available_beds');
            }

            $hospitalUser = \App\Domains\Users\Models\User::lockForUpdate()->find($hospital->user_id);
            $newBalance = $hospitalUser->wallet_balance + $fee;
            $hospitalUser->wallet_balance = $newBalance;
            $hospitalUser->save();

            \App\Models\WalletTransaction::create([
                'user_id'       => $hospitalUser->id,
                'type'          => 'credit',
                'amount'        => $fee,
                'balance_after' => $newBalance,
                'reference'     => 'admission_' . $emergency->uuid,
                'description'   => 'Admission fee',
                'status'        => 'success',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Patient discharged. ₦' . number_format($fee, 2) . ' credited to your wallet.',
        ]);
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
            ->filter(fn($e) => $e->assignedResponder && $e->assignedResponder->current_lat && $e->assignedResponder->current_lng)
            ->map(fn($e) => [
                'uuid' => $e->uuid,
                'lat'  => (float) $e->assignedResponder->current_lat,
                'lng'  => (float) $e->assignedResponder->current_lng,
                'type' => $e->assignedResponder->responder_type,
            ])
            ->values();

        return response()->json($locations);
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

        HospitalPatient::create([
            'hospital_id' => $hospital->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'reason' => $request->reason,
            'bed_type' => $request->bed_type,
            'notes' => $request->notes,
            'status' => 'admitted',
            'admitted_at' => now(),
        ]);

        if ($request->bed_type === 'icu') {
            if ($hospital->icu_beds > 0) $hospital->decrement('icu_beds');
        } else {
            if ($hospital->available_beds > 0) $hospital->decrement('available_beds');
        }

        return redirect()->back()->with('success', 'Patient added.');
    }

    public function dischargeManualPatient($id)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $patient = HospitalPatient::where('hospital_id', $hospital->id)->findOrFail($id);

        if ($patient->status === 'discharged') {
            return response()->json(['success' => false, 'message' => 'This patient has already been discharged.'], 422);
        }

        $patient->update(['status' => 'discharged', 'discharged_at' => now()]);

        if ($patient->bed_type === 'icu') {
            if ($hospital->icu_beds < $hospital->total_beds) $hospital->increment('icu_beds');
        } else {
            if ($hospital->available_beds < $hospital->total_beds) $hospital->increment('available_beds');
        }

        return response()->json(['success' => true, 'message' => 'Patient discharged.']);
    }

    public function destroyPatient($id)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $patient = HospitalPatient::where('hospital_id', $hospital->id)->findOrFail($id);

        if ($patient->status === 'admitted') {
            if ($patient->bed_type === 'icu') {
                if ($hospital->icu_beds < $hospital->total_beds) $hospital->increment('icu_beds');
            } else {
                if ($hospital->available_beds < $hospital->total_beds) $hospital->increment('available_beds');
            }
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

        HospitalReservation::create([
            'hospital_id' => $hospital->id,
            'patient_name' => $request->patient_name,
            'bed_type' => $request->bed_type,
            'expected_at' => $request->expected_at,
            'notes' => $request->notes,
            'status' => 'reserved',
        ]);

        if ($request->bed_type === 'icu') {
            if ($hospital->icu_beds > 0) $hospital->decrement('icu_beds');
        } else {
            if ($hospital->available_beds > 0) $hospital->decrement('available_beds');
        }

        return redirect()->back()->with('success', 'Bed reserved.');
    }

    public function cancelReservation($id)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();
        $reservation = HospitalReservation::where('hospital_id', $hospital->id)->findOrFail($id);

        if ($reservation->status !== 'reserved') {
            return response()->json(['success' => false, 'message' => 'This reservation can no longer be cancelled.'], 422);
        }

        $reservation->update(['status' => 'cancelled']);

        if ($reservation->bed_type === 'icu') {
            if ($hospital->icu_beds < $hospital->total_beds) $hospital->increment('icu_beds');
        } else {
            if ($hospital->available_beds < $hospital->total_beds) $hospital->increment('available_beds');
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
                'notes' => $reservation->notes,
                'status' => 'admitted',
                'admitted_at' => now(),
            ]);

            $reservation->update(['status' => 'admitted']);
        });

        return response()->json(['success' => true, 'message' => 'Reservation admitted as a patient.']);
    }

    public function exportAdmissions(Request $request)
    {
        $hospital = Hospital::where('user_id', Auth::id())->firstOrFail();

        $query = $hospital->emergencies()->whereNotNull('hospital_accepted_at')->with('user', 'emergencyType');

        if ($request->date_from) {
            $query->whereDate('hospital_accepted_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('hospital_accepted_at', '<=', $request->date_to);
        }

        $admissions = $query->orderBy('hospital_accepted_at', 'desc')->get();

        return response()->streamDownload(function () use ($admissions) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Patient', 'Emergency Type', 'Status', 'Admitted At', 'Discharged At']);
            foreach ($admissions as $e) {
                fputcsv($out, [
                    $e->user->name ?? 'Unknown',
                    $e->emergencyType->name ?? 'General',
                    strtoupper($e->status),
                    $e->hospital_accepted_at,
                    $e->status === 'resolved' ? $e->resolved_at : '',
                ]);
            }
            fclose($out);
        }, 'admissions.csv', ['Content-Type' => 'text/csv']);
    }
}
