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
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('available_for_malaysians')->default(true)->after('is_active');
            $table->boolean('available_for_non_malaysians')->default(true)->after('available_for_malaysians');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['available_for_malaysians', 'available_for_non_malaysians']);
        });
    }
};
