<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::with(['event', 'country'])
            ->where('is_active', true);

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $tickets = $query->get();
        return response()->json($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'country_id' => 'required|exists:countries,id',
            'ticket_type' => 'required|in:adult,child',
            'base_price' => 'required|numeric|min:0',
            'total_quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Check if ticket already exists for this combination
        $existingTicket = Ticket::where([
            'event_id' => $validated['event_id'],
            'country_id' => $validated['country_id'],
            'ticket_type' => $validated['ticket_type']
        ])->first();

        if ($existingTicket) {
            return response()->json([
                'message' => 'Ticket already exists for this event, country, and type combination'
            ], 422);
        }

        $validated['available_quantity'] = $validated['total_quantity'];
        $ticket = Ticket::create($validated);
        $ticket->load(['event', 'country']);
        
        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load(['event', 'country']);
        return response()->json($ticket);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'exists:events,id',
            'country_id' => 'exists:countries,id',
            'ticket_type' => 'in:adult,child',
            'base_price' => 'numeric|min:0',
            'total_quantity' => 'integer|min:1',
            'available_quantity' => 'integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $ticket->update($validated);
        $ticket->load(['event', 'country']);
        
        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted successfully']);
    }

    public function getByEventAndCountry(Event $event, Country $country): JsonResponse
    {
        $tickets = Ticket::where('event_id', $event->id)
            ->where('country_id', $country->id)
            ->where('is_active', true)
            ->with(['event', 'country'])
            ->get();
            
        return response()->json($tickets);
    }
}
