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
            // Drop the existing foreign key constraint
            $table->dropForeign(['event_id']);
            
            // Modify event_id to be nullable
            $table->unsignedBigInteger('event_id')->nullable()->change();
            
            // Add back the foreign key constraint with nullable support
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the nullable foreign key constraint
            $table->dropForeign(['event_id']);
            
            // Revert event_id to be required (non-nullable)
            $table->foreignId('event_id')->constrained()->onDelete('cascade')->change();
        });
    }
};
