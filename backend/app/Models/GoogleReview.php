<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoogleReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_review_id',
        'place_id',
        'author_name',
        'author_photo_url',
        'rating',
        'text',
        'review_time',
        'like_count',
        'reply_from_owner',
        'reply_time',
        'is_active'
    ];

    protected $casts = [
        'review_time' => 'datetime',
        'reply_time' => 'datetime',
        'is_active' => 'boolean',
        'rating' => 'integer',
        'like_count' => 'integer'
    ];

    // Scopes for common queries
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('review_time', '>=', now()->subDays($days));
    }

    // Helper methods
    public function getStarRatingAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getFormattedDateAttribute()
    {
        return $this->review_time->diffForHumans();
    }
}
