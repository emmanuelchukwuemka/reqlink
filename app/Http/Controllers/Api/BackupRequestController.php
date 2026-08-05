<?php

namespace App\Http\Controllers\Api;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Responder;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\BackupRequest;
use App\Services\ExpoPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupRequestController extends Controller
{
    protected function currentResponder(): Responder
    {
        return Responder::where('user_id', Auth::id())->firstOrFail();
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
            ? Emergency::where('uuid', $request->emergency_uuid)->first()
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

        $peerUserIds = Responder::where('responder_type', $responder->responder_type)
            ->where('is_on_duty', true)
            ->where('id', '!=', $responder->id)
            ->pluck('user_id')
            ->toArray();
        ExpoPushService::sendToUsers(
            $peerUserIds,
            'Backup Requested',
            ucfirst($responder->responder_type) . ' unit ' . $responder->user->name . ' needs backup.',
            ['backup_request_id' => $backup->id, 'type' => 'backup_requested']
        );

        return response()->json(['success' => true, 'id' => $backup->id]);
    }

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
        $responder = $this->currentResponder();

        $backup = BackupRequest::where('id', $id)
            ->where('responder_id', '!=', $responder->id)
            ->whereHas('responder', fn ($q) => $q->where('responder_type', $responder->responder_type))
            ->firstOrFail();

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
