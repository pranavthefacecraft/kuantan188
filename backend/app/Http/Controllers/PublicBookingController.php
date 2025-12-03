<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PublicBookingController extends Controller
{
    /**
     * Test endpoint for debugging
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'status' => 'API is working',
            'timestamp' => now(),
            'columns' => \Schema::getColumnListing('bookings')
        ]);
    }

    /**
     * Store a new booking
     */
    public function store(Request $request): JsonResponse
    {
        try {
            \Log::info('Booking request received:', $request->all());
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|integer',
                'event_title' => 'required|string|max:255',
                'customer_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'mobile_phone' => 'nullable|string|max:20',
                'country' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
                'quantity' => 'required|integer|min:1',
                'event_date' => 'required|date',
                'total_amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string|max:50',
                'receive_updates' => 'boolean',
                'booking_status' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            // Generate booking reference
            $bookingReference = 'KB' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $booking = Booking::create([
                'booking_reference' => $bookingReference,
                'event_id' => $request->event_id,
                'event_title' => $request->event_title,
                'customer_name' => $request->customer_name,
                'email' => $request->email,
                'mobile_phone' => $request->mobile_phone,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'quantity' => $request->quantity,
                'event_date' => $request->event_date,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'receive_updates' => $request->receive_updates ?? false,
                'booking_status' => $request->booking_status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'customer_name' => $booking->customer_name,
                    'email' => $booking->email,
                    'event_title' => $booking->event_title,
                    'event_date' => $booking->event_date,
                    'quantity' => $booking->quantity,
                    'total_amount' => $booking->total_amount,
                    'payment_method' => $booking->payment_method,
                    'booking_status' => $booking->booking_status,
                    'created_at' => $booking->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Booking creation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to create booking',
                'message' => 'An error occurred while processing your booking. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get bookings by email (for customer lookup)
     */
    public function getBookingsByEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Invalid email format'
                ], 422);
            }

            $bookings = Booking::where('email', $request->email)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'booking_reference', 'event_title', 'event_date', 'quantity', 'total_amount', 'booking_status', 'created_at']);

            return response()->json([
                'success' => true,
                'bookings' => $bookings
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve bookings: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to retrieve bookings'
            ], 500);
        }
    }
}