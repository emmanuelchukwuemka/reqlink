<?php

namespace Database\Seeders;

use App\Domains\Responders\Models\Hospital;
use Illuminate\Database\Seeder;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        $hospitals = [
            [
                'name' => 'National Hospital Abuja',
                'lat' => 9.0480,
                'lng' => 7.4720,
                'total_beds' => 500,
                'available_beds' => 45,
                'icu_beds' => 10,
                'contact_phone' => '+2348000000001',
                'specialties' => json_encode(['Trauma', 'Cardiology', 'Surgery']),
            ],
            [
                'name' => 'Lagos University Teaching Hospital (LUTH)',
                'lat' => 6.5010,
                'lng' => 3.3590,
                'total_beds' => 760,
                'available_beds' => 20,
                'icu_beds' => 15,
                'contact_phone' => '+2348000000002',
                'specialties' => json_encode(['Maternity', 'Infectious Diseases', 'Pediatrics']),
            ],
        ];

        foreach ($hospitals as $hospital) {
            Hospital::create($hospital);
        }
    }
}
