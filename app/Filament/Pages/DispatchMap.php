<?php

namespace App\Filament\Pages;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Responders\Models\Responder;
use BackedEnum;
use Filament\Pages\Page;

class DispatchMap extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected string $view = 'filament.pages.dispatch-map';

    protected static ?string $navigationLabel = 'Live Dispatch Map';

    protected static ?string $title = 'Emergency Dispatch Center';

    protected static ?int $navigationSort = -1; // Keep it at the top

    protected static ?string $pollingInterval = '15s';

    public function getViewData(): array
    {
        return [
            'emergencies' => Emergency::whereNotIn('status', ['resolved', 'cancelled'])
                ->with(['user', 'emergencyType'])
                ->get()
                ->map(fn($e) => [
                    'id' => $e->id,
                    'lat' => $e->latitude,
                    'lng' => $e->longitude,
                    'type' => $e->emergencyType?->name ?? 'Unknown',
                    'priority' => $e->priority,
                    'status' => $e->status,
                    'caller' => $e->user?->name ?? 'Anonymous',
                    'phone' => $e->user?->phone ?? '',
                ]),
            'responders' => Responder::where('is_available', true)
                ->with('user')
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'lat' => $r->current_lat ?? 0,
                    'lng' => $r->current_lng ?? 0,
                    'type' => $r->responder_type,
                    'name' => $r->user?->name ?? 'Responder',
                ]),
            'hospitals' => Hospital::all()->map(fn($h) => [
                'id' => $h->id,
                'lat' => $h->lat,
                'lng' => $h->lng,
                'name' => $h->name,
                'beds' => $h->available_beds,
            ]),
        ];
    }
}
