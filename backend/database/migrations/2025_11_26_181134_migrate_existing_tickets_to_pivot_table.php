<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check what columns exist in tickets table
        $ticketsWithCountry = DB::table('tickets')
            ->whereNotNull('country_id')
            ->get();

        foreach ($ticketsWithCountry as $ticket) {
            // Check if pivot record already exists
            $exists = DB::table('ticket_country')
                ->where('ticket_id', $ticket->id)
                ->where('country_id', $ticket->country_id)
                ->exists();

            if (!$exists) {
                // Use base_price as fallback if adult/child prices don't exist
                $adultPrice = $ticket->base_price ?? 50.00; // Default fallback
                $childPrice = $ticket->base_price ?? 30.00; // Default fallback

                DB::table('ticket_country')->insert([
                    'ticket_id' => $ticket->id,
                    'country_id' => $ticket->country_id,
                    'adult_price' => $adultPrice,
                    'child_price' => $childPrice,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear pivot table
        DB::table('ticket_country')->truncate();
    }
};
