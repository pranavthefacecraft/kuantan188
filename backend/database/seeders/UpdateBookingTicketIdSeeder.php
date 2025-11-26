<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class UpdateBookingTicketIdSeeder extends Seeder
{
    public function run(): void
    {
        // Update existing bookings to link them to the appropriate tickets
        $bookings = Booking::whereNull('ticket_id')->get();
        
        foreach ($bookings as $booking) {
            // Find the appropriate ticket for this booking based on event and country
            $ticket = Ticket::where('event_id', $booking->event_id)
                           ->where('country_id', $booking->country_id)
                           ->first();
            
            if ($ticket) {
                $booking->update(['ticket_id' => $ticket->id]);
                echo "Updated booking {$booking->booking_reference} to use ticket {$ticket->id}\n";
            } else {
                echo "No matching ticket found for booking {$booking->booking_reference}\n";
            }
        }
    }
}