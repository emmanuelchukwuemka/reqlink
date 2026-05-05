<?php

namespace Database\Seeders;

use App\Domains\Emergencies\Models\EmergencyType;
use Illuminate\Database\Seeder;

class EmergencyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Medical',
                'icon' => 'medical-bag',
                'description' => 'Health emergencies requiring ambulance or doctors.'
            ],
            [
                'name' => 'Security',
                'icon' => 'shield-alert',
                'description' => 'Security threats requiring police intervention.'
            ],
            [
                'name' => 'Fire',
                'icon' => 'fire',
                'description' => 'Fire outbreaks requiring fire services.'
            ],
            [
                'name' => 'Disaster',
                'icon' => 'alert-decagram',
                'description' => 'Natural or man-made disasters.'
            ],
            [
                'name' => 'Accident',
                'icon' => 'car-crash',
                'description' => 'Road traffic accidents.'
            ],
        ];

        foreach ($types as $type) {
            EmergencyType::create($type);
        }
    }
}
