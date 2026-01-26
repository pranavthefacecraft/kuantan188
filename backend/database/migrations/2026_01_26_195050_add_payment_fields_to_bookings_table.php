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
            // Add payment gateway field
            $table->enum('payment_gateway', ['cash_on_delivery', 'billplz'])
                  ->default('cash_on_delivery')
                  ->after('payment_method');
            
            // Add payment reference (Billplz bill ID)
            $table->string('payment_reference')->nullable()->after('payment_gateway');
            
            // Add payment URL (redirect URL from Billplz)
            $table->text('payment_url')->nullable()->after('payment_reference');
            
            // Add payment status
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])
                  ->default('pending')
                  ->after('payment_url');
            
            // Add payment completed timestamp
            $table->timestamp('payment_completed_at')->nullable()->after('payment_status');
            
            // Add payment metadata (JSON field for storing additional payment info)
            $table->json('payment_metadata')->nullable()->after('payment_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'payment_reference', 
                'payment_url',
                'payment_status',
                'payment_completed_at',
                'payment_metadata'
            ]);
        });
    }
};
