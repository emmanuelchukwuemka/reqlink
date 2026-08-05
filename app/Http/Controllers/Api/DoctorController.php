<?php

namespace App\Http\Controllers\Api;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /**
     * Emergencies with an open consult request — the list a mobile doctor
     * Home screen needs (no such listing exists on the web side either,
     * since Blade just queries this inline via DashboardController::index()).
     */
    public function consultQueue()
    {
        if (Auth::user()->role !== 'doctor') {
            abort(403);
        }

        $emergencies = Emergency::whereNotNull('doctor_consult_requested_at')
            ->whereNull('consult_fee_paid_at')
            ->with('user', 'targetHospital', 'assignedResponder.user')
            ->orderBy('doctor_consult_requested_at', 'desc')
            ->get();

        return response()->json($emergencies);
    }

    public function saveNotes(Request $request, $uuid)
    {
        if (Auth::user()->role !== 'doctor') {
            abort(403);
        }

        $request->validate(['doctor_notes' => 'nullable|string|max:5000']);
        $emergency = Emergency::where('uuid', $uuid)
            ->whereNotNull('doctor_consult_requested_at')
            ->firstOrFail();
        $emergency->update(['doctor_notes' => $request->doctor_notes]);

        return response()->json(['success' => true]);
    }

    public function completeConsult($uuid)
    {
        if (Auth::user()->role !== 'doctor') {
            abort(403);
        }

        $emergency = Emergency::where('uuid', $uuid)->firstOrFail();

        if (!$emergency->doctor_consult_requested_at) {
            return response()->json(['success' => false, 'message' => 'No consult was requested for this emergency.'], 422);
        }

        if ($emergency->consult_fee_paid_at) {
            return response()->json(['success' => false, 'message' => 'This consult has already been marked complete.'], 422);
        }

        $fee = 2000.00;
        $doctorId = Auth::id();

        $result = DB::transaction(function () use ($emergency, $doctorId, $fee) {
            $patient = User::lockForUpdate()->find($emergency->user_id);

            if ($patient->wallet_balance < $fee) {
                return ['success' => false, 'message' => 'Patient has insufficient wallet balance to cover the ₦' . number_format($fee, 2) . ' consultation fee.'];
            }

            $patientBalance = $patient->wallet_balance - $fee;
            $patient->wallet_balance = $patientBalance;
            $patient->save();

            WalletTransaction::create([
                'user_id' => $patient->id,
                'type' => 'debit',
                'amount' => $fee,
                'balance_after' => $patientBalance,
                'reference' => 'consult_' . $emergency->uuid,
                'description' => 'Doctor consultation fee',
                'status' => 'success',
            ]);

            $doctor = User::lockForUpdate()->find($doctorId);
            $doctorBalance = $doctor->wallet_balance + $fee;
            $doctor->wallet_balance = $doctorBalance;
            $doctor->save();

            WalletTransaction::create([
                'user_id' => $doctor->id,
                'type' => 'credit',
                'amount' => $fee,
                'balance_after' => $doctorBalance,
                'reference' => 'consult_earn_' . $emergency->uuid,
                'description' => 'Consultation fee',
                'status' => 'success',
            ]);

            $emergency->update(['consult_fee_paid_at' => now()]);

            return ['success' => true];
        });

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Consult marked complete. ₦' . number_format($fee, 2) . ' credited to your wallet.',
        ]);
    }
}
