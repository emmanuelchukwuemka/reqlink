<?php

namespace Database\Seeders;

use App\Domains\Responders\Models\Responder;
use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResponderSeeder extends Seeder
{
    public function run(): void
    {
        $responders = [
            [
                'name' => 'Ambulance 01 - Abuja',
                'email' => 'amb01@resqlink.com',
                'phone' => '+2348001112221',
                'type' => 'ambulance',
                'lat' => 9.0578,
                'lng' => 7.4950,
            ],
            [
                'name' => 'Police Rapid Response - Abuja',
                'email' => 'police01@resqlink.com',
                'phone' => '+2348001112222',
                'type' => 'police',
                'lat' => 9.0765,
                'lng' => 7.4985,
            ],
            [
                'name' => 'Fire Service - Garki',
                'email' => 'fire01@resqlink.com',
                'phone' => '+2348001112223',
                'type' => 'fire',
                'lat' => 9.0345,
                'lng' => 7.4820,
            ],
        ];

        foreach ($responders as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => 'password',
                'role' => 'responder',
                'is_verified' => true,
            ]);

            Responder::create([
                'user_id' => $user->id,
                'responder_type' => $data['type'],
                'vehicle_reg' => 'RSQ-' . strtoupper(substr($data['type'], 0, 3)) . '-001',
                'capacity' => 2,
                'current_lat' => $data['lat'],
                'current_lng' => $data['lng'],
                'is_available' => true,
                'last_ping' => now(),
            ]);
        }
    }
}
