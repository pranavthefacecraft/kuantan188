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
        // Fix existing bookings that have incorrect ticket_id values
        // Event bookings (direct reservations) should have ticket_id = null
        // Only actual ticket purchases should have a valid ticket_id
        
        DB::statement("
            UPDATE bookings 
            SET ticket_id = NULL 
            WHERE ticket_id = 1 
            AND (
                -- These look like event bookings based on the booking reference pattern or test data
                booking_reference LIKE 'KB%' 
                OR customer_name = 'Test User'
                OR customer_email LIKE '%example.com'
                OR customer_email LIKE '%prasharpranav@gmail.com'
            )
        ");
        
        Log::info('Fixed existing booking types - set ticket_id to null for event bookings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes if needed
        DB::statement("
            UPDATE bookings 
            SET ticket_id = 1 
            WHERE ticket_id IS NULL
        ");
    }
};
