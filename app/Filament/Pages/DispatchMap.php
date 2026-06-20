<?php

namespace App\Filament\Pages;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Responders\Models\Responder;
use BackedEnum;
use Filament\Pages\Page;

use App\Domains\Emergencies\Services\RoutingService;

class DispatchMap extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected string $view = 'filament.pages.dispatch-map';

    protected static ?string $navigationLabel = 'Live Dispatch Map';

    protected static ?string $title = 'Emergency Dispatch Center';

    protected static ?int $navigationSort = -1; // Keep it at the top

    protected static ?string $pollingInterval = '10s';

    public function refreshData()
    {
        $this->dispatch('map-data-updated', $this->getViewData());
    }

    public function assignResponder($emergencyId, $responderId)
    {
        $emergency = Emergency::find($emergencyId);
        $responder = Responder::find($responderId);

        if ($emergency && $responder) {
            $emergency->update([
                'assigned_responder_id' => $responder->id,
                'status' => 'dispatched',
            ]);

            $responder->update(['is_available' => false]);

            $this->dispatch('notify', ['message' => "Unit {$responder->user->name} dispatched to {$emergency->user->name}.", 'type' => 'success']);
            $this->refreshData();
        }
    }

    public function getViewData(): array
    {
        $emergencies = Emergency::whereNotIn('status', ['resolved', 'cancelled'])
            ->with(['user', 'emergencyType'])
            ->latest()
            ->get();

        $responders = Responder::where('is_on_duty', true)
            ->with('user')
            ->get();

        return [
            'emergencies' => $emergencies->map(function($e) {
                $recommendations = (new RoutingService())->findBestResponders($e, 3);
                
                return [
                    'id' => $e->id,
                    'uuid' => $e->uuid,
                    'lat' => $e->latitude,
                    'lng' => $e->longitude,
                    'type' => $e->emergencyType?->name ?? 'Unknown',
                    'priority' => $e->priority,
                    'status' => $e->status,
                    'caller' => $e->user?->name ?? 'Anonymous',
                    'phone' => $e->user?->phone ?? '',
                    'address' => $e->address ?? 'Location not specified',
                    'time_ago' => $e->created_at->diffForHumans(),
                    'evidence' => $e->evidence_file ? asset('storage/' . $e->evidence_file) : null,
                    'triage' => $e->triage_data,
                    'recommendations' => $recommendations->map(fn($r) => [
                        'id' => $r->id,
                        'name' => $r->user?->name,
                        'distance' => round($r->distance, 1),
                        'type' => $r->responder_type,
                    ]),
                ];
            }),
            'responders' => $responders->map(fn($r) => [
                'id' => $r->id,
                'lat' => $r->current_lat ?? 0,
                'lng' => $r->current_lng ?? 0,
                'type' => $r->responder_type,
                'name' => $r->user?->name ?? 'Responder',
                'status' => $r->is_available ? 'Available' : 'On Mission',
                'vehicle' => $r->vehicle_reg ?? 'N/A',
            ]),
            'hospitals' => Hospital::all()->map(fn($h) => [
                'id' => $h->id,
                'lat' => $h->lat,
                'lng' => $h->lng,
                'name' => $h->name,
                'beds' => $h->available_beds,
            ]),
            'stats' => [
                'pending' => $emergencies->where('status', 'pending')->count(),
                'active' => $emergencies->whereIn('status', ['dispatched', 'enroute', 'arrived'])->count(),
                'available_units' => $responders->where('is_available', true)->count(),
            ]
        ];
    }
}
