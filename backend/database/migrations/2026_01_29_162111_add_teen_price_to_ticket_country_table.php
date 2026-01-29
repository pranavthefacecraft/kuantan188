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
        Schema::table('ticket_country', function (Blueprint $table) {
            $table->decimal('teen_price', 10, 2)->after('child_price')->nullable();
            $table->decimal('university_price', 10, 2)->after('teen_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_country', function (Blueprint $table) {
            $table->dropColumn(['teen_price', 'university_price']);
        });
    }
};
