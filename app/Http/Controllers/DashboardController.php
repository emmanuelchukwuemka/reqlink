<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        switch ($role) {
            case 'admin':
                $users = \App\Domains\Users\Models\User::orderBy('created_at', 'desc')->get();
                $activeEmergenciesCount = \App\Domains\Emergencies\Models\Emergency::whereNotIn('status', ['resolved', 'cancelled'])->count();
                $onDutyRespondersCount = \App\Domains\Responders\Models\Responder::where('is_on_duty', true)->count();
                $hospitalsCount = \App\Domains\Responders\Models\Hospital::count();
                $respondersCount = \App\Domains\Responders\Models\Responder::count();
                $resolvedTodayCount = \App\Domains\Emergencies\Models\Emergency::where('status', 'resolved')
                    ->whereDate('updated_at', today())->count();
                $totalEmergenciesCount = \App\Domains\Emergencies\Models\Emergency::count();
                return view('dashboards.admin', compact('users', 'activeEmergenciesCount', 'onDutyRespondersCount',
                    'hospitalsCount', 'respondersCount', 'resolvedTodayCount', 'totalEmergenciesCount'));
            case 'hospital':
                $hospital = \App\Domains\Responders\Models\Hospital::where('user_id', Auth::id())->first();
                $incomingEmergencies = \App\Domains\Emergencies\Models\Emergency::where('target_hospital_id', $hospital->id)
                    ->whereIn('status', ['pending', 'dispatched', 'enroute', 'arrived'])
                    ->whereNull('hospital_decline_reason')
                    ->with('user', 'emergencyType')
                    ->latest()
                    ->get();
                $hospitalEmergencies = $hospital->emergencies()->with('user', 'emergencyType')->latest()->get();
                $bedReservationHistory = $hospital->bedReservations()
                    ->with(['responder.user', 'emergency.user'])
                    ->latest()
                    ->limit(50)
                    ->get();
                return view('dashboards.hospital', compact('hospital', 'incomingEmergencies', 'hospitalEmergencies', 'bedReservationHistory'));
            case 'ambulance':
            case 'security':
            case 'fire':
                $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();
                $hospitals  = \App\Domains\Responders\Models\Hospital::all();
                $ambulances = \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->with('user')->get();
                $fireUnits  = \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->with('user')->get();
                $missionsDone = $responder
                    ? \App\Domains\Emergencies\Models\Emergency::where('assigned_responder_id', $responder->id)->where('status', 'resolved')->count()
                    : 0;
                $totalUnits = $responder
                    ? \App\Domains\Responders\Models\Responder::where('responder_type', $responder->responder_type)->count()
                    : 0;
                $activeEmergencyForBed = $responder
                    ? \App\Domains\Emergencies\Models\Emergency::where('assigned_responder_id', $responder->id)
                        ->whereIn('status', ['dispatched', 'enroute', 'arrived'])
                        ->latest()
                        ->first()
                    : null;
                return view('dashboards.responder', compact('responder', 'hospitals', 'ambulances', 'fireUnits', 'missionsDone', 'totalUnits', 'activeEmergencyForBed'));
            case 'doctor':
                $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();
                $hospitals  = \App\Domains\Responders\Models\Hospital::all();
                $ambulances = \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->with('user')->get();
                $fireUnits  = \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->with('user')->get();
                $missionsDone = $responder
                    ? \App\Domains\Emergencies\Models\Emergency::where('assigned_responder_id', $responder->id)->where('status', 'resolved')->count()
                    : 0;
                $totalUnits = \App\Domains\Responders\Models\Responder::where('responder_type', 'doctor')->count();
                $activeEmergencyForBed = $responder
                    ? \App\Domains\Emergencies\Models\Emergency::where('assigned_responder_id', $responder->id)
                        ->whereIn('status', ['dispatched', 'enroute', 'arrived'])
                        ->latest()
                        ->first()
                    : null;

                $triageEmergencies = \App\Domains\Emergencies\Models\Emergency::whereNotNull('triage_data')
                    ->with('user', 'emergencyType')
                    ->latest()
                    ->limit(30)
                    ->get();

                $consultRequests = \App\Domains\Emergencies\Models\Emergency::whereNotNull('doctor_consult_requested_at')
                    ->whereNull('consult_fee_paid_at')
                    ->with('user', 'emergencyType')
                    ->orderBy('doctor_consult_requested_at', 'desc')
                    ->get();

                $doctorReviews = collect();
                $avgRating = null;
                if ($responder) {
                    $doctorReviews = \App\Models\Review::where('responder_id', $responder->id)
                        ->with('user')
                        ->latest()
                        ->get();
                    $avgRating = $doctorReviews->count() ? round($doctorReviews->avg('rating'), 1) : null;
                }

                $walletTransactions = \App\Models\WalletTransaction::where('user_id', Auth::id())
                    ->where('status', 'success')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                return view('dashboards.doctor', compact(
                    'responder', 'hospitals', 'ambulances', 'fireUnits', 'missionsDone', 'totalUnits', 'activeEmergencyForBed',
                    'triageEmergencies', 'consultRequests', 'doctorReviews', 'avgRating', 'walletTransactions'
                ));
            default:
                $hospitals  = \App\Domains\Responders\Models\Hospital::all();
                $ambulances = \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->with('user')->get();
                $fireUnits  = \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->with('user')->get();

                $history = \App\Domains\Emergencies\Models\Emergency::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                $activeEmergency = \App\Domains\Emergencies\Models\Emergency::where('user_id', Auth::id())
                    ->where('status', '!=', 'resolved')
                    ->where('status', '!=', 'cancelled')
                    ->latest()
                    ->first();

                // Good Samaritan Missions
                $samaritanMissions = [];
                if (Auth::user()->is_good_samaritan && Auth::user()->samaritan_active) {
                    $samaritanMissions = \App\Domains\Emergencies\Models\Emergency::where('status', 'pending')
                        ->where('user_id', '!=', Auth::id()) // Don't respond to own emergency
                        ->latest()
                        ->limit(3)
                        ->get();
                }

                $walletTransactions = \App\Models\WalletTransaction::where('user_id', Auth::id())
                    ->where('status', 'success')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                return view('dashboard', compact('hospitals', 'ambulances', 'fireUnits', 'history', 'activeEmergency', 'samaritanMissions', 'walletTransactions'));
        }
    }
    public function adminDispatch(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'emergency_id' => 'required|integer',
            'responder_id' => 'required|integer',
        ]);

        $emergency = \App\Domains\Emergencies\Models\Emergency::findOrFail($request->emergency_id);
        $responder = \App\Domains\Responders\Models\Responder::with('user')->findOrFail($request->responder_id);

        // Free the previously assigned responder if there was one
        if ($emergency->assigned_responder_id && $emergency->assigned_responder_id !== $responder->id) {
            \App\Domains\Responders\Models\Responder::where('id', $emergency->assigned_responder_id)
                ->update(['is_available' => true]);
        }

        $emergency->update([
            'assigned_responder_id' => $responder->id,
            'status'                => 'dispatched',
        ]);
        $responder->update(['is_available' => false]);

        return response()->json([
            'success' => true,
            'message' => ($responder->user->name ?? 'Responder') . ' dispatched.',
        ]);
    }

    public function liveAdminData()
    {
        $activeEmergencies = \App\Domains\Emergencies\Models\Emergency::with('user')
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->latest()
            ->get();

        // Load assigned responder names in one query to avoid N+1
        $assignedIds = $activeEmergencies->pluck('assigned_responder_id')->filter()->unique();
        $responderNames = \App\Domains\Responders\Models\Responder::with('user')
            ->whereIn('id', $assignedIds)
            ->get()
            ->mapWithKeys(fn($r) => [$r->id => $r->user?->name ?? 'Unit #'.$r->id]);

        $emergencies = $activeEmergencies->map(fn($e) => [
            'id'               => $e->id,
            'uuid'             => $e->uuid,
            'status'           => $e->status,
            'subtype'          => $e->subtype,
            'lat'              => $e->latitude,
            'lng'              => $e->longitude,
            'latitude'         => $e->latitude,
            'longitude'        => $e->longitude,
            'user'             => $e->user ? $e->user->toArray() : null,
            'created_at'       => $e->created_at->toISOString(),
            'assigned_responder_id'   => $e->assigned_responder_id,
            'assigned_responder_name' => $e->assigned_responder_id ? ($responderNames[$e->assigned_responder_id] ?? null) : null,
            'evidence_file'    => $e->evidence_file,
        ]);

        $responders = \App\Domains\Responders\Models\Responder::with('user')
            ->where('is_on_duty', true)
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'type' => $r->responder_type,
                'name' => $r->user ? $r->user->name : ('Unit #' . $r->id),
                'lat'  => (float) $r->current_lat,
                'lng'  => (float) $r->current_lng,
            ]);

        return response()->json([
            'emergencies' => $emergencies,
            'responders'  => $responders,
        ]);
    }

    public function liveMapData()
    {
        $responders = \App\Domains\Responders\Models\Responder::with('user')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->where('is_on_duty', true)
            ->whereIn('responder_type', ['ambulance', 'fire'])
            ->get()
            ->map(fn($r) => [
                'id'        => $r->id,
                'type'      => $r->responder_type,
                'name'      => $r->user ? $r->user->name : ('Responder #' . $r->id),
                'lat'       => (float) $r->current_lat,
                'lng'       => (float) $r->current_lng,
                'available' => (bool) $r->is_available,
            ]);

        return response()->json(['responders' => $responders]);
    }

    public function commandCenter()
    {
        $emergencies = \App\Domains\Emergencies\Models\Emergency::with('user')
            ->where('status', '!=', 'resolved')
            ->latest()
            ->get()
            ->map(fn($e) => [
                'id'          => $e->id,
                'uuid'        => $e->uuid,
                'status'      => $e->status,
                'subtype'     => $e->subtype,
                'latitude'    => $e->latitude,
                'longitude'   => $e->longitude,
                'user'        => $e->user ? $e->user->toArray() : null,
                'created_at'  => $e->created_at->toISOString(),
                'assigned_responder_id' => $e->assigned_responder_id,
                'assigned_responder_name' => null,
                'evidence_file' => $e->evidence_file,
            ]);

        $responders = \App\Domains\Responders\Models\Responder::with('user')->where('is_on_duty', true)->get();

        return view('dashboards.admin_command', compact('emergencies', 'responders'));
    }

    public function toggleUserStatus($id)
    {
        $user = \App\Domains\Users\Models\User::findOrFail($id);
        
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot suspend your own account.');
        }

        $user->update([
            'is_suspended' => !$user->is_suspended
        ]);

        $status = $user->is_suspended ? 'suspended' : 're-activated';
        return redirect()->back()->with('success', "User {$user->name} has been {$status}.");
    }

    public function toggleSamaritan(Request $request)
    {
        $user = Auth::user();
        if (!$user->is_good_samaritan) {
            return response()->json(['error' => 'Not a Good Samaritan'], 403);
        }

        $user->update([
            'samaritan_active' => $request->input('active', false)
        ]);

        return response()->json(['success' => true]);
    }

    public function toggleMamaCare(Request $request)
    {
        $user = Auth::user();

        $user->update([
            'mama_care_active' => $request->input('active', false)
        ]);

        return response()->json(['success' => true]);
    }

    public function updateMamaCareProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'pregnancy_due_date' => 'nullable|date',
            'pregnancy_high_risk' => 'boolean',
            'preferred_maternity_hospital' => 'nullable|string|max:255',
            'obgyn_contact' => 'nullable|string|max:255'
        ]);

        $user->update([
            'pregnancy_due_date' => $request->pregnancy_due_date,
            'pregnancy_high_risk' => $request->boolean('pregnancy_high_risk'),
            'preferred_maternity_hospital' => $request->preferred_maternity_hospital,
            'obgyn_contact' => $request->obgyn_contact,
        ]);

        return redirect()->back()->with('success', 'Mama Care profile updated successfully.');
    }

    public function saveDoctorNotes(Request $request, $uuid)
    {
        $request->validate(['doctor_notes' => 'nullable|string|max:5000']);
        $emergency = \App\Domains\Emergencies\Models\Emergency::where('uuid', $uuid)->firstOrFail();
        $emergency->update(['doctor_notes' => $request->doctor_notes]);

        return response()->json(['success' => true]);
    }

    public function completeConsult($uuid)
    {
        $emergency = \App\Domains\Emergencies\Models\Emergency::where('uuid', $uuid)->firstOrFail();

        if ($emergency->consult_fee_paid_at) {
            return response()->json(['success' => false, 'message' => 'This consult has already been marked complete.'], 422);
        }

        $fee = 2000.00;
        $userId = Auth::id();

        \Illuminate\Support\Facades\DB::transaction(function () use ($emergency, $userId, $fee) {
            $user = \App\Domains\Users\Models\User::lockForUpdate()->find($userId);
            $newBalance = $user->wallet_balance + $fee;
            $user->wallet_balance = $newBalance;
            $user->save();

            \App\Models\WalletTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'credit',
                'amount'        => $fee,
                'balance_after' => $newBalance,
                'reference'     => 'consult_' . $emergency->uuid,
                'description'   => 'Consultation fee',
                'status'        => 'success',
            ]);

            $emergency->update(['consult_fee_paid_at' => now()]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Consult marked complete. ₦' . number_format($fee, 2) . ' credited to your wallet.',
        ]);
    }

    public function updateSpecialty(Request $request)
    {
        $request->validate(['specialty' => 'nullable|string|max:100']);
        $responder = \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->first();

        if (!$responder) {
            return response()->json(['success' => false, 'message' => 'No responder profile found.'], 404);
        }

        $responder->update(['specialty' => $request->specialty]);

        return response()->json(['success' => true]);
    }

    public function updateUserRole(Request $request, $id)
    {
        $request->validate(['role' => 'required|in:civilian,ambulance,fire,security,hospital,admin']);
        $user = \App\Domains\Users\Models\User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot change your own role.');
        }
        $user->update(['role' => $request->role]);
        return redirect()->back()->with('success', "Role for {$user->name} updated to {$request->role}.");
    }

    public function globalIncidents(Request $request)
    {
        $query = \App\Domains\Emergencies\Models\Emergency::with(['user', 'assignedResponder.user'])
            ->orderBy('created_at', 'desc');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $emergencies = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => \App\Domains\Emergencies\Models\Emergency::count(),
            'active'    => \App\Domains\Emergencies\Models\Emergency::whereNotIn('status', ['resolved', 'cancelled'])->count(),
            'pending'   => \App\Domains\Emergencies\Models\Emergency::where('status', 'pending')->count(),
            'resolved'  => \App\Domains\Emergencies\Models\Emergency::where('status', 'resolved')->count(),
            'today'     => \App\Domains\Emergencies\Models\Emergency::whereDate('created_at', today())->count(),
            'avg_response' => \App\Domains\Emergencies\Models\Emergency::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)/60) as avg')
                ->value('avg'),
        ];

        $responders = \App\Domains\Responders\Models\Responder::with('user')->get();

        return view('dashboards.admin_incidents', compact('emergencies', 'stats', 'responders'));
    }

    public function updateIncidentStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,dispatched,enroute,arrived,resolved,cancelled']);
        $emergency = \App\Domains\Emergencies\Models\Emergency::findOrFail($id);
        $emergency->update(['status' => $request->status]);
        if ($request->status === 'resolved' && !$emergency->resolved_at) {
            $emergency->update(['resolved_at' => now()]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Incident status updated.',
                'status' => $emergency->status,
                'id' => $emergency->id,
            ]);
        }

        return redirect()->back()->with('success', 'Incident status updated.');
    }

    public function agencyOversight()
    {
        $hospitals = \App\Domains\Responders\Models\Hospital::with('user')->get();
        $responders = \App\Domains\Responders\Models\Responder::with('user')->withCount([
            'assignedEmergencies',
            'assignedEmergencies as resolved_count' => fn($q) => $q->where('status', 'resolved'),
        ])->get();

        $stats = [
            'hospitals'       => \App\Domains\Responders\Models\Hospital::count(),
            'total_beds'      => \App\Domains\Responders\Models\Hospital::sum('total_beds'),
            'available_beds'  => \App\Domains\Responders\Models\Hospital::sum('available_beds'),
            'responders'      => \App\Domains\Responders\Models\Responder::count(),
            'on_duty'         => \App\Domains\Responders\Models\Responder::where('is_on_duty', true)->count(),
            'ambulances'      => \App\Domains\Responders\Models\Responder::where('responder_type', 'ambulance')->count(),
            'fire'            => \App\Domains\Responders\Models\Responder::where('responder_type', 'fire')->count(),
            'security'        => \App\Domains\Responders\Models\Responder::where('responder_type', 'security')->count(),
        ];

        return view('dashboards.admin_agencies', compact('hospitals', 'responders', 'stats'));
    }

    public function adminToggleResponderDuty(Request $request, $id)
    {
        $responder = \App\Domains\Responders\Models\Responder::findOrFail($id);
        $responder->update([
            'is_on_duty'   => !$responder->is_on_duty,
            'is_available' => !$responder->is_on_duty,
        ]);
        $status = $responder->is_on_duty ? 'set on duty' : 'set off duty';
        return redirect()->back()->with('success', "Responder {$status}.");
    }
}
