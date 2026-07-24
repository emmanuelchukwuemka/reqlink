<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\BackupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupRequestController extends Controller
{
    protected function currentResponder()
    {
        return \App\Domains\Responders\Models\Responder::where('user_id', Auth::id())->firstOrFail();
    }

    public function store(Request $request)
    {
        $request->validate([
            'emergency_uuid' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'message' => 'nullable|string|max:255',
        ]);

        $responder = $this->currentResponder();

        $emergency = $request->emergency_uuid
            ? \App\Domains\Emergencies\Models\Emergency::where('uuid', $request->emergency_uuid)->first()
            : null;

        $backup = BackupRequest::create([
            'responder_id' => $responder->id,
            'emergency_id' => $emergency?->id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        AdminActivityLog::record(
            'backup_requested',
            ucfirst($responder->responder_type) . " unit ({$responder->user->name}) requested backup" . ($request->message ? ": {$request->message}" : '')
        );

        return response()->json(['success' => true, 'id' => $backup->id]);
    }

    // Poll: other on-duty responders of the same type see pending requests, excluding their own
    public function index()
    {
        $responder = $this->currentResponder();

        $requests = BackupRequest::with('responder.user', 'emergency')
            ->where('status', 'pending')
            ->where('responder_id', '!=', $responder->id)
            ->whereHas('responder', fn ($q) => $q->where('responder_type', $responder->responder_type))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'unit_name' => $b->responder->user->name ?? 'Unit',
                'message' => $b->message,
                'lat' => $b->lat,
                'lng' => $b->lng,
                'emergency_uuid' => $b->emergency->uuid ?? null,
                'created_at' => $b->created_at->diffForHumans(),
            ]);

        return response()->json($requests);
    }

    // My own sent requests (any status), for the Backup Requests tab
    public function mine()
    {
        $responder = $this->currentResponder();

        $requests = BackupRequest::where('responder_id', $responder->id)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'message' => $b->message,
                'status' => $b->status,
                'created_at' => $b->created_at->diffForHumans(),
            ]);

        return response()->json($requests);
    }

    public function acknowledge($id)
    {
        $backup = BackupRequest::findOrFail($id);
        $backup->update(['status' => 'acknowledged']);

        return response()->json(['success' => true]);
    }

    public function resolve($id)
    {
        $responder = $this->currentResponder();
        $backup = BackupRequest::where('responder_id', $responder->id)->findOrFail($id);
        $backup->update(['status' => 'resolved']);

        return response()->json(['success' => true]);
    }
}
