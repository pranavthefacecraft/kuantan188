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
            $table->decimal('non_malaysian_adult_price', 10, 2)->nullable();
            $table->decimal('non_malaysian_teen_price', 10, 2)->nullable();
            $table->decimal('non_malaysian_university_price', 10, 2)->nullable();
            $table->decimal('non_malaysian_child_price', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'non_malaysian_adult_price',
                'non_malaysian_teen_price', 
                'non_malaysian_university_price',
                'non_malaysian_child_price'
            ]);
        });
    }
};
