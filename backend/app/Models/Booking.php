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
        // Payment Gateway Fields
        'payment_gateway',
        'payment_reference',
        'payment_url',
        'payment_status',
        'payment_completed_at',
        'payment_metadata',
        // Legacy fields for backward compatibility
        'country_id',
        'ticket_id',
        'customer_email',
        'customer_phone',
        'adult_tickets',
        'teenager_tickets',
        'university_tickets',
        'child_tickets',
        'adult_quantity',
        'child_quantity',
        'adult_price',
        'teenager_price',
        'university_price',
        'child_price',
        'payment_date',
        'status',
        'selected_time',
        'is_all_day_pass'
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'payment_completed_at' => 'datetime',
        'event_date' => 'date',
        'receive_updates' => 'boolean',
        'payment_metadata' => 'array',
        'is_all_day_pass' => 'boolean'
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

    // Payment Status Helper Methods
    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }

    public function isCancelled()
    {
        return $this->payment_status === 'cancelled';
    }

    public function isBillplzPayment()
    {
        return $this->payment_gateway === 'billplz';
    }

    public function isCashPayment()
    {
        return $this->payment_gateway === 'cash_on_delivery';
    }

    public function markAsPaid()
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_completed_at' => now(),
            'booking_status' => 'confirmed'
        ]);
    }

    public function markAsFailed()
    {
        $this->update([
            'payment_status' => 'failed',
            'booking_status' => 'cancelled'
        ]);
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
