<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\Country;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::all();
        $countries = Country::all();

        // Base prices in MYR for different events
        $basePrices = [
            1 => ['adult' => 50.00, 'child' => 25.00], // Food Festival
            2 => ['adult' => 80.00, 'child' => 40.00], // Cultural Concert
            3 => ['adult' => 150.00, 'child' => 75.00], // Beach Festival
        ];

        foreach ($events as $event) {
            $prices = $basePrices[$event->id] ?? ['adult' => 60.00, 'child' => 30.00];

            foreach ($countries as $country) {
                // Create adult ticket (skip if exists)
                Ticket::firstOrCreate(
                    [
                        'event_id' => $event->id,
                        'country_id' => $country->id,
                        'ticket_type' => 'adult',
                    ],
                    [
                        'base_price' => $prices['adult'],
                        'final_price' => $prices['adult'] * $country->price_multiplier,
                        'total_quantity' => 100,
                        'available_quantity' => 100,
                        'description' => 'Adult ticket for ' . $event->title,
                        'is_active' => true
                    ]
                );

                // Create child ticket (skip if exists)
                Ticket::firstOrCreate(
                    [
                        'event_id' => $event->id,
                        'country_id' => $country->id,
                        'ticket_type' => 'child',
                    ],
                    [
                        'base_price' => $prices['child'],
                        'final_price' => $prices['child'] * $country->price_multiplier,
                        'total_quantity' => 50,
                        'available_quantity' => 50,
                        'description' => 'Child ticket (under 12) for ' . $event->title,
                        'is_active' => true
                    ]
                );
            }
        }

        // --- All Day Pass sample tickets ---
        $allDayPassTickets = [
            [
                'ticket_name' => 'Sky Deck - All Day Pass',
                'description' => 'Enjoy unlimited access to the Sky Deck for the entire day. No time slot required.',
                'is_all_day_pass' => true,
                'is_active' => true,
                'available_for_malaysians' => true,
                'available_for_non_malaysians' => true,
                'total_quantity' => 50,
                'available_quantity' => 50,
                'base_price' => 120.00,
                'malaysian_adult_price' => 120.00,
                'malaysian_teen_price' => 100.00,
                'malaysian_university_price' => 95.00,
                'malaysian_child_price' => 70.00,
                'non_malaysian_adult_price' => 150.00,
                'non_malaysian_teen_price' => 130.00,
                'non_malaysian_university_price' => 125.00,
                'non_malaysian_child_price' => 90.00,
                'malaysian_all_day_adult_price' => 120.00,
                'malaysian_all_day_teen_price' => 100.00,
                'malaysian_all_day_university_price' => 95.00,
                'malaysian_all_day_child_price' => 70.00,
                'non_malaysian_all_day_adult_price' => 150.00,
                'non_malaysian_all_day_teen_price' => 130.00,
                'non_malaysian_all_day_university_price' => 125.00,
                'non_malaysian_all_day_child_price' => 90.00,
            ],
            [
                'ticket_name' => 'Observation Deck - All Day Pass',
                'description' => 'Full-day access to the Observation Deck. Come and go as you please.',
                'is_all_day_pass' => true,
                'is_active' => true,
                'available_for_malaysians' => true,
                'available_for_non_malaysians' => true,
                'total_quantity' => 30,
                'available_quantity' => 30,
                'base_price' => 100.00,
                'malaysian_adult_price' => 100.00,
                'malaysian_teen_price' => 85.00,
                'malaysian_university_price' => 80.00,
                'malaysian_child_price' => 55.00,
                'non_malaysian_adult_price' => 130.00,
                'non_malaysian_teen_price' => 110.00,
                'non_malaysian_university_price' => 105.00,
                'non_malaysian_child_price' => 75.00,
                'malaysian_all_day_adult_price' => 100.00,
                'malaysian_all_day_teen_price' => 85.00,
                'malaysian_all_day_university_price' => 80.00,
                'malaysian_all_day_child_price' => 55.00,
                'non_malaysian_all_day_adult_price' => 130.00,
                'non_malaysian_all_day_teen_price' => 110.00,
                'non_malaysian_all_day_university_price' => 105.00,
                'non_malaysian_all_day_child_price' => 75.00,
            ],
        ];

        foreach ($allDayPassTickets as $ticketData) {
            Ticket::firstOrCreate(
                ['ticket_name' => $ticketData['ticket_name']],
                $ticketData
            );
        }
    }
}
