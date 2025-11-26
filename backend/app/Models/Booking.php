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
        'country_id',
        'ticket_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'adult_tickets',
        'child_tickets',
        'adult_price',
        'child_price',
        'total_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_date',
        'status'
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_date' => 'datetime'
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
