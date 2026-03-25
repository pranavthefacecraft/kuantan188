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
        'country_id',
        'ticket_type',
        'base_price',
        'final_price',
        'total_quantity',
        'available_quantity',
        'description',
        'image_url',
        'is_active',
        'available_for_malaysians',
        'available_for_non_malaysians',
        'malaysian_adult_price',
        'malaysian_teen_price',
        'malaysian_university_price',
        'malaysian_child_price',
        'non_malaysian_adult_price',
        'non_malaysian_teen_price',
        'non_malaysian_university_price',
        'non_malaysian_child_price'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'is_active' => 'boolean',
        'available_for_malaysians' => 'boolean',
        'available_for_non_malaysians' => 'boolean',
        'malaysian_adult_price' => 'decimal:2',
        'malaysian_teen_price' => 'decimal:2',
        'malaysian_university_price' => 'decimal:2',
        'malaysian_child_price' => 'decimal:2',
        'non_malaysian_adult_price' => 'decimal:2',
        'non_malaysian_teen_price' => 'decimal:2',
        'non_malaysian_university_price' => 'decimal:2',
        'non_malaysian_child_price' => 'decimal:2'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'ticket_id');
    }
}
