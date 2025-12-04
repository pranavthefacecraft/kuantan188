<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'booking_reference',
        'event_id',
        'event_title',
        'customer_name',
        'email',
        'mobile_phone',
        'country',
        'postal_code',
        'quantity',
        'event_date',
        'total_amount',
        'payment_method',
        'receive_updates',
        'booking_status',
        // Legacy fields for backward compatibility
        'country_id',
        'ticket_id',
        'customer_email',
        'customer_phone',
        'adult_tickets',
        'child_tickets',
        'adult_quantity',
        'child_quantity',
        'adult_price',
        'child_price',
        'payment_status',
        'payment_reference',
        'payment_date',
        'status'
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'event_date' => 'date',
        'receive_updates' => 'boolean'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Accessor methods to handle different field names
    public function getAdultQuantityAttribute()
    {
        return $this->adult_tickets ?? 0;
    }

    public function getChildQuantityAttribute()
    {
        return $this->child_tickets ?? 0;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (!$booking->booking_reference) {
                $booking->booking_reference = 'BK-' . strtoupper(Str::random(8));
            }
        });
    }
}
