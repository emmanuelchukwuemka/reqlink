<?php

namespace App\Domains\Emergencies\Services;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\DB;

class EmergencyService
{
    public function createEmergency(User $user, array $data): Emergency
    {
        return DB::transaction(function () use ($user, $data) {
            $emergency = Emergency::create([
                'user_id' => $user->id,
                'emergency_type_id' => $data['emergency_type_id'],
                'subtype' => $data['subtype'] ?? null,
                'description' => $data['description'] ?? null,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $data['address'] ?? null,
                'status' => 'pending',
                'priority' => $data['priority'] ?? 1,
            ]);

            // TODO: Trigger Routing Engine
            // TODO: Broadcast via Reverb
            
            return $emergency;
        });
    }

    public function findNearbySamaritans(float $lat, float $lng, float $radiusKm = 2.0)
    {
        // Simple Haversine approximation or just a box search for demo
        return User::where('is_good_samaritan', true)
            ->where('samaritan_active', true)
            ->whereNotNull('last_known_lat')
            ->get()
            ->filter(function($user) use ($lat, $lng, $radiusKm) {
                // Approximate distance
                $dist = sqrt(pow($user->last_known_lat - $lat, 2) + pow($user->last_known_lng - $lng, 2)) * 111;
                return $dist <= $radiusKm;
            });
    }

    public function updateStatus(Emergency $emergency, string $status): bool
    {
        return $emergency->update(['status' => $status]);
    }
}
