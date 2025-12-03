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
            // Only add columns that don't exist
            if (!Schema::hasColumn('bookings', 'event_title')) {
                $table->string('event_title')->nullable()->after('event_id');
            }
            if (!Schema::hasColumn('bookings', 'email')) {
                $table->string('email')->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('bookings', 'mobile_phone')) {
                $table->string('mobile_phone')->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('bookings', 'country')) {
                $table->string('country')->nullable()->after('country_id');
            }
            if (!Schema::hasColumn('bookings', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }
            if (!Schema::hasColumn('bookings', 'quantity')) {
                $table->integer('quantity')->nullable()->after('child_tickets');
            }
            if (!Schema::hasColumn('bookings', 'event_date')) {
                $table->date('event_date')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('bookings', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('bookings', 'receive_updates')) {
                $table->boolean('receive_updates')->default(false)->after('payment_method');
            }
            if (!Schema::hasColumn('bookings', 'booking_status')) {
                $table->string('booking_status')->default('pending')->after('receive_updates');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $columns = ['event_title', 'email', 'mobile_phone', 'country', 'postal_code', 'quantity', 'event_date', 'payment_method', 'receive_updates', 'booking_status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};