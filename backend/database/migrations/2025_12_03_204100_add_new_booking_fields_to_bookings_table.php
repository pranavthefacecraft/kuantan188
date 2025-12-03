<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('event_title')->nullable()->after('event_id');
            $table->string('email')->nullable()->after('customer_email');
            $table->string('mobile_phone')->nullable()->after('customer_phone');
            $table->string('country')->nullable()->after('country_id');
            $table->string('postal_code')->nullable()->after('country');
            $table->integer('quantity')->nullable()->after('child_tickets');
            $table->date('event_date')->nullable()->after('quantity');
            $table->decimal('total_amount', 10, 2)->nullable()->after('event_date');
            $table->string('payment_method')->nullable()->after('total_amount');
            $table->boolean('receive_updates')->default(false)->after('payment_method');
            $table->string('booking_status')->default('pending')->after('receive_updates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'event_title',
                'email',
                'mobile_phone',
                'country',
                'postal_code',
                'quantity',
                'event_date',
                'total_amount',
                'payment_method',
                'receive_updates',
                'booking_status'
            ]);
        });
    }
};
