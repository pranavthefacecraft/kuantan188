<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@kuantan188.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]
        );

        // Create Yusri admin user
        User::firstOrCreate(
            ['email' => 'yusri@thefacecraft.com'],
            [
                'name' => 'Yusri - TheFacecraft',
                'password' => Hash::make('Ysr@TFC2026!#SecureAdmin'),
                'email_verified_at' => now()
            ]
        );
    }
}