<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_name',
        'event_id',
        'total_quantity',
        'available_quantity',
        'description',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'ticket_country')
                    ->withPivot('adult_price', 'child_price')
                    ->withTimestamps();
    }

    // Keep for backward compatibility
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'ticket_id');
    }

    public function getBookingsViaEventCountryAttribute()
    {
        return Booking::where('event_id', $this->event_id)
                     ->where('country_id', $this->country_id)
                     ->get();
    }

    public function calculateFinalPriceForCountry($countryId): array
    {
        $country = $this->countries()->where('country_id', $countryId)->first();
        if ($country) {
            return [
                'adult_price' => $country->pivot->adult_price * $country->price_multiplier,
                'child_price' => $country->pivot->child_price * $country->price_multiplier
            ];
        }
        return ['adult_price' => 0, 'child_price' => 0];
    }

    public function calculateFinalPrice(): void
    {
        if ($this->country) {
            $this->final_price = $this->base_price * $this->country->price_multiplier;
        }
    }

    protected static function boot()
    {
        parent::boot();
        
        // Remove automatic price calculation since prices are now in pivot table
    }
}
