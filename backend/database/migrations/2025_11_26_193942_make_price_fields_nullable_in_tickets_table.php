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
            // Make price fields nullable since we use pivot table for pricing
            $table->decimal('base_price', 10, 2)->nullable()->change();
            $table->decimal('final_price', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Revert price fields back to not nullable
            $table->decimal('base_price', 10, 2)->nullable(false)->change();
            $table->decimal('final_price', 10, 2)->nullable(false)->change();
        });
    }
};
