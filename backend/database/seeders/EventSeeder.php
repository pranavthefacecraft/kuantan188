<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'Kuantan International Food Festival 2025',
                'description' => 'A celebration of culinary delights from around the world, featuring local and international cuisine, cooking competitions, and cultural performances.',
                'location' => 'Kuantan City Centre, Pahang, Malaysia',
                'event_date' => Carbon::create(2025, 3, 15, 10, 0, 0),
                'booking_start_date' => Carbon::now(),
                'booking_end_date' => Carbon::create(2025, 3, 14, 23, 59, 59),
                'image_url' => 'https://example.com/food-festival.jpg',
                'is_active' => true
            ],
            [
                'title' => 'Pahang Cultural Heritage Concert',
                'description' => 'An evening of traditional Malaysian music and dance performances showcasing the rich cultural heritage of Pahang state.',
                'location' => 'Dewan Jubli Perak, Kuantan',
                'event_date' => Carbon::create(2025, 4, 20, 19, 30, 0),
                'booking_start_date' => Carbon::now(),
                'booking_end_date' => Carbon::create(2025, 4, 19, 18, 0, 0),
                'image_url' => 'https://example.com/cultural-concert.jpg',
                'is_active' => true
            ],
            [
                'title' => 'Kuantan Beach Music Festival',
                'description' => 'A three-day music festival featuring local and international artists performing by the beautiful beaches of Kuantan.',
                'location' => 'Teluk Cempedak Beach, Kuantan',
                'event_date' => Carbon::create(2025, 6, 1, 16, 0, 0),
                'booking_start_date' => Carbon::now(),
                'booking_end_date' => Carbon::create(2025, 5, 30, 12, 0, 0),
                'image_url' => 'https://example.com/beach-festival.jpg',
                'is_active' => true
            ]
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
