<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('is_all_day_pass')->default(false)->after('is_active');
            $table->decimal('malaysian_all_day_adult_price', 10, 2)->nullable()->after('malaysian_child_price');
            $table->decimal('malaysian_all_day_teen_price', 10, 2)->nullable()->after('malaysian_all_day_adult_price');
            $table->decimal('malaysian_all_day_university_price', 10, 2)->nullable()->after('malaysian_all_day_teen_price');
            $table->decimal('malaysian_all_day_child_price', 10, 2)->nullable()->after('malaysian_all_day_university_price');
            $table->decimal('non_malaysian_all_day_adult_price', 10, 2)->nullable()->after('non_malaysian_child_price');
            $table->decimal('non_malaysian_all_day_teen_price', 10, 2)->nullable()->after('non_malaysian_all_day_adult_price');
            $table->decimal('non_malaysian_all_day_university_price', 10, 2)->nullable()->after('non_malaysian_all_day_teen_price');
            $table->decimal('non_malaysian_all_day_child_price', 10, 2)->nullable()->after('non_malaysian_all_day_university_price');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'is_all_day_pass',
                'malaysian_all_day_adult_price',
                'malaysian_all_day_teen_price',
                'malaysian_all_day_university_price',
                'malaysian_all_day_child_price',
                'non_malaysian_all_day_adult_price',
                'non_malaysian_all_day_teen_price',
                'non_malaysian_all_day_university_price',
                'non_malaysian_all_day_child_price',
            ]);
        });
    }
};
