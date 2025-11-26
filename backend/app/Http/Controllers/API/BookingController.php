<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Country;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['event', 'country']);

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'country_id' => 'required|exists:countries,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'adult_tickets' => 'required|integer|min:0',
            'child_tickets' => 'required|integer|min:0',
        ]);

        // Validate that at least one ticket is being booked
        if ($validated['adult_tickets'] == 0 && $validated['child_tickets'] == 0) {
            return response()->json([
                'message' => 'At least one ticket must be selected'
            ], 422);
        }

        return DB::transaction(function () use ($validated) {
            $event = Event::findOrFail($validated['event_id']);
            $country = Country::findOrFail($validated['country_id']);

            // Check if booking is open
            if (!$event->isBookingOpen()) {
                return response()->json([
                    'message' => 'Booking is not available for this event'
                ], 422);
            }

            $totalAmount = 0;
            $adultPrice = 0;
            $childPrice = 0;

            // Process adult tickets
            if ($validated['adult_tickets'] > 0) {
                $adultTicket = Ticket::where([
                    'event_id' => $validated['event_id'],
                    'country_id' => $validated['country_id'],
                    'ticket_type' => 'adult',
                    'is_active' => true
                ])->first();

                if (!$adultTicket) {
                    return response()->json([
                        'message' => 'Adult tickets not available for this country'
                    ], 422);
                }

                if ($adultTicket->available_quantity < $validated['adult_tickets']) {
                    return response()->json([
                        'message' => 'Not enough adult tickets available'
                    ], 422);
                }

                $adultPrice = $adultTicket->final_price;
                $totalAmount += $adultPrice * $validated['adult_tickets'];

                // Update available quantity
                $adultTicket->available_quantity -= $validated['adult_tickets'];
                $adultTicket->save();
            }

            // Process child tickets
            if ($validated['child_tickets'] > 0) {
                $childTicket = Ticket::where([
                    'event_id' => $validated['event_id'],
                    'country_id' => $validated['country_id'],
                    'ticket_type' => 'child',
                    'is_active' => true
                ])->first();

                if (!$childTicket) {
                    return response()->json([
                        'message' => 'Child tickets not available for this country'
                    ], 422);
                }

                if ($childTicket->available_quantity < $validated['child_tickets']) {
                    return response()->json([
                        'message' => 'Not enough child tickets available'
                    ], 422);
                }

                $childPrice = $childTicket->final_price;
                $totalAmount += $childPrice * $validated['child_tickets'];

                // Update available quantity
                $childTicket->available_quantity -= $validated['child_tickets'];
                $childTicket->save();
            }

            // Create booking
            $booking = Booking::create([
                'event_id' => $validated['event_id'],
                'country_id' => $validated['country_id'],
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'adult_tickets' => $validated['adult_tickets'],
                'child_tickets' => $validated['child_tickets'],
                'adult_price' => $adultPrice,
                'child_price' => $childPrice,
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'status' => 'pending'
            ]);

            $booking->load(['event', 'country']);
            return response()->json($booking, 201);
        });
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load(['event', 'country']);
        return response()->json($booking);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => 'in:pending,paid,failed,refunded',
            'payment_method' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'status' => 'in:pending,confirmed,cancelled'
        ]);

        if (isset($validated['payment_status']) && $validated['payment_status'] === 'paid') {
            $validated['payment_date'] = now();
        }

        $booking->update($validated);
        $booking->load(['event', 'country']);
        
        return response()->json($booking);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        return DB::transaction(function () use ($booking) {
            // Restore ticket quantities if booking is cancelled
            if ($booking->status !== 'cancelled') {
                if ($booking->adult_tickets > 0) {
                    $adultTicket = Ticket::where([
                        'event_id' => $booking->event_id,
                        'country_id' => $booking->country_id,
                        'ticket_type' => 'adult'
                    ])->first();
                    
                    if ($adultTicket) {
                        $adultTicket->available_quantity += $booking->adult_tickets;
                        $adultTicket->save();
                    }
                }

                if ($booking->child_tickets > 0) {
                    $childTicket = Ticket::where([
                        'event_id' => $booking->event_id,
                        'country_id' => $booking->country_id,
                        'ticket_type' => 'child'
                    ])->first();
                    
                    if ($childTicket) {
                        $childTicket->available_quantity += $booking->child_tickets;
                        $childTicket->save();
                    }
                }
            }

            $booking->delete();
            return response()->json(['message' => 'Booking deleted successfully']);
        });
    }

    public function getByReference(string $reference): JsonResponse
    {
        $booking = Booking::where('booking_reference', $reference)
            ->with(['event', 'country'])
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json($booking);
    }
}
