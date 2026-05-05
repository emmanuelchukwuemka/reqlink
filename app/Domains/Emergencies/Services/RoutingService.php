<?php

namespace App\Domains\Emergencies\Services;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Responder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoutingService
{
    /**
     * Find the best responders for an emergency.
     * Phase 1: Rule-based (Distance + Availability)
     */
    public function findBestResponders(Emergency $emergency, int $limit = 3): Collection
    {
        // Simple Haversine approximation or MySQL Spatial if available
        // For Phase 1, we'll use a basic distance calculation
        
        return Responder::where('is_available', true)
            ->where('responder_type', $this->mapEmergencyToResponderType($emergency->emergency_type_id))
            ->select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(current_lat)) * cos(radians(current_lng) - radians(?)) + sin(radians(?)) * sin(radians(current_lat)))) AS distance',
                [$emergency->latitude, $emergency->longitude, $emergency->latitude]
            )
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    private function mapEmergencyToResponderType(int $typeId): string
    {
        // Simple mapping for now
        return match ($typeId) {
            1 => 'ambulance',
            2 => 'police',
            3 => 'fire',
            default => 'ambulance',
        };
    }
}
