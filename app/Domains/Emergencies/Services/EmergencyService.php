<?php

namespace App\Domains\Emergencies\Services;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Emergencies\Models\EmergencyType;
use App\Domains\Responders\Models\Hospital;
use App\Domains\Responders\Models\Responder;
use App\Domains\Users\Models\User;
use App\Services\ExpoPushService;
use App\Services\WebPushService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmergencyService
{
    /**
     * Full auto-dispatch flow: create the emergency, try to auto-assign the
     * nearest on-duty+available responder, otherwise broadcast to every
     * on-duty responder and fall back to routing the nearest (or preferred)
     * hospital. This is a straight extraction of the logic that used to live
     * inline in the web EmergencyController::trigger — kept byte-for-byte
     * equivalent so API and web dispatch behave identically. The web
     * controller's own copy is left in place untouched (zero risk to the
     * live app); a future pass should have it call this method too instead
     * of maintaining two copies.
     */
    public function dispatch(User $user, array $data): array
    {
        $typeId = EmergencyType::min('id')
            ?? EmergencyType::create([
                'name' => 'Medical',
                'icon' => 'medical-bag',
                'description' => 'Health emergencies requiring ambulance or doctors.',
            ])->id;

        $emergency = Emergency::create([
            'user_id' => $user->id,
            'emergency_type_id' => $typeId,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'status' => 'pending',
            'priority' => 5,
            'target_hospital_id' => $data['preferred_hospital_id'] ?? null,
            'triggered_via' => $data['triggered_via'] ?? 'app',
        ]);

        $nearestResponder = Responder::where('is_on_duty', true)
            ->where('is_available', true)
            ->select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(current_lat)) * cos(radians(current_lng) - radians(?)) + sin(radians(?)) * sin(radians(current_lat)))) AS distance',
                [$data['latitude'], $data['longitude'], $data['latitude']]
            )
            ->orderBy('distance')
            ->first();

        if ($nearestResponder) {
            $emergency->update([
                'assigned_responder_id' => $nearestResponder->id,
                'status' => 'dispatched',
                'eta_minutes' => ceil($nearestResponder->distance * 2) + 2,
            ]);

            $nearestResponder->update(['is_available' => false]);

            WebPushService::sendToUsers([$nearestResponder->user_id]);
            ExpoPushService::sendToUsers(
                [$nearestResponder->user_id],
                'New Emergency Assigned',
                'You have been dispatched to a new emergency.',
                ['uuid' => $emergency->uuid, 'type' => 'emergency_assigned']
            );

            return [
                'message' => 'Emergency alert received. ' . ucfirst($nearestResponder->responder_type) . ' unit dispatched.',
                'emergency' => $emergency->fresh(),
                'status' => 'dispatched',
                'responder' => [
                    'name' => $nearestResponder->user->name,
                    'type' => $nearestResponder->responder_type,
                    'phone' => $nearestResponder->user->phone,
                ],
                'hospital' => null,
                'no_responders' => false,
            ];
        }

        $onDutyUserIds = Responder::where('is_on_duty', true)->pluck('user_id')->toArray();
        WebPushService::sendToUsers($onDutyUserIds);
        ExpoPushService::sendToUsers(
            $onDutyUserIds,
            'Emergency Alert',
            'A new emergency needs a responder nearby.',
            ['uuid' => $emergency->uuid, 'type' => 'emergency_broadcast']
        );

        $nearestHospital = $emergency->target_hospital_id
            ? DB::table('hospitals')->where('id', $emergency->target_hospital_id)->first()
            : DB::table('hospitals')
                ->select('*')
                ->selectRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance',
                    [$data['latitude'], $data['longitude'], $data['latitude']]
                )
                ->orderBy('distance')
                ->first();

        if ($nearestHospital) {
            $emergency->update([
                'assigned_responder_id' => null,
                'status' => 'pending',
                'description' => 'Routing to ' . $nearestHospital->name,
                'target_hospital_id' => $nearestHospital->id,
            ]);

            try {
                $hospitalRecord = Hospital::find($nearestHospital->id);
                if ($hospitalRecord && $hospitalRecord->user) {
                    $hospitalRecord->user->notify(new \App\Notifications\NewEmergencyRoutedToHospital($emergency));
                    ExpoPushService::sendToUsers(
                        [$hospitalRecord->user_id],
                        'Incoming Patient',
                        'A patient is being routed to your facility.',
                        ['uuid' => $emergency->uuid, 'type' => 'hospital_routed']
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to send hospital emergency-routed notification: ' . $e->getMessage());
            }

            return [
                'message' => 'No mobile responders available. Routing to ' . $nearestHospital->name,
                'emergency' => $emergency->fresh(),
                'status' => 'pending',
                'responder' => null,
                'hospital' => $nearestHospital,
                'no_responders' => false,
            ];
        }

        return [
            'message' => 'Searching for available responders... Please stay calm.',
            'emergency' => $emergency->fresh(),
            'status' => 'pending',
            'responder' => null,
            'hospital' => null,
            'no_responders' => true,
        ];
    }

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
