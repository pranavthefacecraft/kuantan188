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
            foreach ($countries as $country) {
                // Create adult ticket
                Ticket::create([
                    'event_id' => $event->id,
                    'country_id' => $country->id,
                    'ticket_type' => 'adult',
                    'base_price' => $basePrices[$event->id]['adult'],
                    'final_price' => $basePrices[$event->id]['adult'] * $country->price_multiplier,
                    'total_quantity' => 100,
                    'available_quantity' => 100,
                    'description' => 'Adult ticket for ' . $event->title,
                    'is_active' => true
                ]);

                // Create child ticket
                Ticket::create([
                    'event_id' => $event->id,
                    'country_id' => $country->id,
                    'ticket_type' => 'child',
                    'base_price' => $basePrices[$event->id]['child'],
                    'final_price' => $basePrices[$event->id]['child'] * $country->price_multiplier,
                    'total_quantity' => 50,
                    'available_quantity' => 50,
                    'description' => 'Child ticket (under 12) for ' . $event->title,
                    'is_active' => true
                ]);
            }
        }
    }
}
