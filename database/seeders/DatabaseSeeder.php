<?php

namespace Database\Seeders;

use App\Domains\Users\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'ResQLink Admin',
            'email' => 'admin@resqlink.com',
            'phone' => '+2348009990000',
            'password' => 'password',
            'role' => 'admin',
            'is_verified' => true,
        ]);

        $this->call([
            EmergencyTypeSeeder::class,
            HospitalSeeder::class,
            ResponderSeeder::class,
        ]);
    }
}
