<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'location',
        'event_date',
        'booking_start_date',
        'booking_end_date',
        'image_url',
        'price',
        'child_price',
        'is_active'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'booking_start_date' => 'datetime',
        'booking_end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isBookingOpen(): bool
    {
        $now = Carbon::now();
        return $this->is_active &&
               (!$this->booking_start_date || $now->gte($this->booking_start_date)) &&
               (!$this->booking_end_date || $now->lte($this->booking_end_date));
    }

    /**
     * Date accessor for backward compatibility
     */
    public function getDateAttribute()
    {
        return $this->event_date;
    }

    /**
     * Name accessor for backward compatibility
     */
    public function getNameAttribute()
    {
        return $this->title;
    }
}
