<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $countries = Country::where('is_active', true)->get();
        return response()->json($countries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:3|unique:countries',
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:5',
            'price_multiplier' => 'required|numeric|min:0.0001|max:999.9999',
            'is_active' => 'boolean'
        ]);

        $country = Country::create($validated);
        return response()->json($country, 201);
    }

    public function show(Country $country): JsonResponse
    {
        return response()->json($country);
    }

    public function update(Request $request, Country $country): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => ['string', 'max:3', Rule::unique('countries')->ignore($country->id)],
            'currency_code' => 'string|max:3',
            'currency_symbol' => 'string|max:5',
            'price_multiplier' => 'numeric|min:0.0001|max:999.9999',
            'is_active' => 'boolean'
        ]);

        $country->update($validated);
        return response()->json($country);
    }

    public function destroy(Country $country): JsonResponse
    {
        $country->delete();
        return response()->json(['message' => 'Country deleted successfully']);
    }
}
