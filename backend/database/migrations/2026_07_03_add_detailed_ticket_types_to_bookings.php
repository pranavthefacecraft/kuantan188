<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add detailed ticket type columns
            $table->integer('teenager_tickets')->default(0)->after('adult_tickets');
            $table->integer('university_tickets')->default(0)->after('teenager_tickets');
            
            // Add price columns for new ticket types
            $table->decimal('teenager_price', 10, 2)->default(0)->after('child_price');
            $table->decimal('university_price', 10, 2)->default(0)->after('teenager_price');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['teenager_tickets', 'university_tickets', 'teenager_price', 'university_price']);
        });
    }
};
