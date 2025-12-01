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
        Schema::create('google_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('google_review_id')->unique(); // Google's unique review ID
            $table->string('place_id'); // Google Place ID for our business
            $table->string('author_name');
            $table->text('author_photo_url')->nullable();
            $table->integer('rating'); // 1-5 stars
            $table->text('text')->nullable(); // Review content
            $table->timestamp('review_time'); // When review was created on Google
            $table->integer('like_count')->default(0);
            $table->text('reply_from_owner')->nullable(); // Business owner reply
            $table->timestamp('reply_time')->nullable();
            $table->boolean('is_active')->default(true); // For hiding inappropriate reviews
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['place_id', 'rating']);
            $table->index(['review_time']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_reviews');
    }
};
