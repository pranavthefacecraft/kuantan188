<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::with(['tickets.country'])
            ->where('is_active', true)
            ->orderBy('event_date', 'asc')
            ->get();
        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date|after:now',
            'booking_start_date' => 'nullable|date|before:event_date',
            'booking_end_date' => 'nullable|date|before:event_date|after:booking_start_date',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        $event = Event::create($validated);
        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        $event->load(['tickets.country', 'bookings']);
        return response()->json($event);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'date|after:now',
            'booking_start_date' => 'nullable|date|before:event_date',
            'booking_end_date' => 'nullable|date|before:event_date|after:booking_start_date',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        $event->update($validated);
        return response()->json($event);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();
        return response()->json(['message' => 'Event deleted successfully']);
    }
}
