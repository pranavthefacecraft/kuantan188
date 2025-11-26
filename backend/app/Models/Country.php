<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'currency_code',
        'currency_symbol',
        'price_multiplier',
        'is_active'
    ];

    protected $casts = [
        'price_multiplier' => 'decimal:4',
        'is_active' => 'boolean'
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketsMultiple(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_country')
                    ->withPivot('adult_price', 'child_price')
                    ->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
