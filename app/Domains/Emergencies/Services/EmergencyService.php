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

    public function updateStatus(Emergency $emergency, string $status): bool
    {
        return $emergency->update(['status' => $status]);
    }
}
