<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix the booking identification logic
        // Ticket bookings should be identified by specific patterns or event types
        
        // First, set all KB bookings back to ticket_id = 1 as default
        DB::statement("UPDATE bookings SET ticket_id = 1 WHERE booking_reference LIKE 'KB%'");
        
        // Then identify actual EVENT bookings (direct event reservations) and set ticket_id = NULL
        // These are typically for events like concerts, festivals that are direct reservations
        DB::statement("
            UPDATE bookings 
            SET ticket_id = NULL 
            WHERE (
                -- Events that are clearly direct event bookings (concerts, festivals)
                (event_title LIKE '%Concert%' OR event_title LIKE '%Festival%' OR event_title LIKE '%Heritage%')
                AND booking_reference LIKE 'KB%'
                AND (
                    customer_name = 'Test User' 
                    OR customer_email LIKE '%example.com'
                    OR customer_name LIKE '%prashra%'
                )
            )
            OR (
                -- Test bookings that are clearly for events
                customer_name = 'Test User' 
                AND customer_email LIKE '%example.com'
                AND event_title NOT LIKE '%General Admission%'
                AND event_title NOT LIKE '%Ticket%'
            )
        ");
        
        // Specifically ensure General Admission and similar ticket-like bookings remain as ticket bookings
        DB::statement("
            UPDATE bookings 
            SET ticket_id = 1 
            WHERE (
                event_title LIKE '%General Admission%' 
                OR event_title LIKE '%Ticket%'
                OR event_title LIKE '%Pass%'
            )
        ");
        
        Log::info('Fixed ticket vs event booking identification');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all bookings to ticket_id = 1
        DB::statement("UPDATE bookings SET ticket_id = 1 WHERE booking_reference LIKE 'KB%'");
    }
};
