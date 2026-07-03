<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all ticket image_url paths from storage/tickets to uploads/tickets
        DB::table('tickets')
            ->where('image_url', 'like', 'storage/tickets/%')
            ->update([
                'image_url' => DB::raw("REPLACE(image_url, 'storage/tickets/', 'uploads/tickets/')")
            ]);

        // Also handle any variations with leading slash
        DB::table('tickets')
            ->where('image_url', 'like', '/storage/tickets/%')
            ->update([
                'image_url' => DB::raw("REPLACE(image_url, '/storage/tickets/', 'uploads/tickets/')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to storage/tickets if needed
        DB::table('tickets')
            ->where('image_url', 'like', 'uploads/tickets/%')
            ->update([
                'image_url' => DB::raw("REPLACE(image_url, 'uploads/tickets/', 'storage/tickets/')")
            ]);

        DB::table('tickets')
            ->where('image_url', 'like', '/uploads/tickets/%')
            ->update([
                'image_url' => DB::raw("REPLACE(image_url, '/uploads/tickets/', '/storage/tickets/')")
            ]);
    }
};
