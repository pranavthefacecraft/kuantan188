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
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('ticket_country', 'university_price')) {
                $table->decimal('university_price', 10, 2)->after('teen_price')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_country', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_country', 'university_price')) {
                $table->dropColumn('university_price');
            }
        });
    }
};
