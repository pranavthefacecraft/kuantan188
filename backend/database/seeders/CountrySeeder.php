<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Malaysia',
                'code' => 'MY',
                'currency_code' => 'MYR',
                'currency_symbol' => 'RM',
                'price_multiplier' => 1.0000,
                'is_active' => true
            ],
            [
                'name' => 'United States',
                'code' => 'US',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'price_multiplier' => 0.2400,
                'is_active' => true
            ],
            [
                'name' => 'Singapore',
                'code' => 'SG',
                'currency_code' => 'SGD',
                'currency_symbol' => 'S$',
                'price_multiplier' => 0.3200,
                'is_active' => true
            ],
            [
                'name' => 'United Kingdom',
                'code' => 'GB',
                'currency_code' => 'GBP',
                'currency_symbol' => '£',
                'price_multiplier' => 0.1900,
                'is_active' => true
            ],
            [
                'name' => 'India',
                'code' => 'IN',
                'currency_code' => 'INR',
                'currency_symbol' => '₹',
                'price_multiplier' => 20.0000,
                'is_active' => true
            ],
            [
                'name' => 'Australia',
                'code' => 'AU',
                'currency_code' => 'AUD',
                'currency_symbol' => 'A$',
                'price_multiplier' => 0.3600,
                'is_active' => true
            ]
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
