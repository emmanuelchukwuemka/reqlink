<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('hospitals')->insert([
            [
                'name' => 'General Medical Center',
                'lat' => 6.5244,
                'lng' => 3.3792,
                'total_beds' => 100,
                'available_beds' => 20,
                'icu_beds' => 5,
                'contact_phone' => '+2348011223344',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'St. Nicholas Hospital',
                'lat' => 6.4531,
                'lng' => 3.3958,
                'total_beds' => 50,
                'available_beds' => 10,
                'icu_beds' => 2,
                'contact_phone' => '+2348022334455',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lagos University Teaching Hospital (LUTH)',
                'lat' => 6.5173,
                'lng' => 3.3601,
                'total_beds' => 500,
                'available_beds' => 45,
                'icu_beds' => 15,
                'contact_phone' => '+2348033445566',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('emergency_types')->insert([
            ['name' => 'Health', 'icon' => 'heart-pulse', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Security', 'icon' => 'shield-alert', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fire', 'icon' => 'flame', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
