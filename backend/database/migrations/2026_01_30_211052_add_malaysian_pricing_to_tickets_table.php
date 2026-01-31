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
            $table->decimal('malaysian_adult_price', 10, 2)->nullable()->after('available_for_non_malaysians');
            $table->decimal('malaysian_teen_price', 10, 2)->nullable()->after('malaysian_adult_price');
            $table->decimal('malaysian_university_price', 10, 2)->nullable()->after('malaysian_teen_price');
            $table->decimal('malaysian_child_price', 10, 2)->nullable()->after('malaysian_university_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'malaysian_adult_price',
                'malaysian_teen_price', 
                'malaysian_university_price',
                'malaysian_child_price'
            ]);
        });
    }
};
