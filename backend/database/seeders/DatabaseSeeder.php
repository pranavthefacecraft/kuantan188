<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@kuantan188.com',
            'password' => bcrypt('password123')
        ]);

        // Run seeders
        $this->call([
            CountrySeeder::class,
            EventSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
