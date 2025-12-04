<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

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
     * Test booking creation with sample data
     */
    public function testCreate(): JsonResponse
    {
        try {
            $sampleData = [
                'event_id' => 1,
                'event_title' => 'Test Event',
                'customer_name' => 'Test User',
                'email' => 'test@example.com',
                'mobile_phone' => '1234567890',
                'country' => 'Malaysia',
                'postal_code' => '12345',
                'quantity' => 1,
                'event_date' => '2025-12-10',
                'total_amount' => 100.00,
                'payment_method' => 'cash_on_delivery',
                'receive_updates' => false,
                'booking_status' => 'confirmed'
            ];

            \Log::info('Test booking creation started with data:', $sampleData);

            $bookingReference = 'KB' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Basic required fields that should exist
            $bookingData = [
                'booking_reference' => $bookingReference,
                'event_id' => $sampleData['event_id'],
                'country_id' => 1,
                'postal_code' => $sampleData['postal_code'],
                'customer_name' => $sampleData['customer_name'],
                'customer_email' => $sampleData['email'],
                'customer_phone' => $sampleData['mobile_phone'],
                'adult_tickets' => 0,
                'child_tickets' => 0,
                'quantity' => $sampleData['quantity'],
                'event_date' => $sampleData['event_date'],
                'adult_price' => 0,
                'child_price' => 0,
                'total_amount' => $sampleData['total_amount'],
                'payment_status' => 'pending',
                'payment_method' => $sampleData['payment_method'],
                'payment_reference' => null,
                'payment_date' => null,
                'status' => $sampleData['booking_status'],
                'ticket_id' => null, // Test bookings are event bookings, not ticket bookings
            ];

            // Add optional fields only if columns exist
            if (Schema::hasColumn('bookings', 'event_title')) {
                $bookingData['event_title'] = $sampleData['event_title'];
            }
            if (Schema::hasColumn('bookings', 'email')) {
                $bookingData['email'] = $sampleData['email'];
            }
            if (Schema::hasColumn('bookings', 'mobile_phone')) {
                $bookingData['mobile_phone'] = $sampleData['mobile_phone'];
            }
            if (Schema::hasColumn('bookings', 'country')) {
                $bookingData['country'] = $sampleData['country'];
            }

            \Log::info('Attempting test booking creation with:', $bookingData);

            $booking = Booking::create($bookingData);

            \Log::info('Test booking created successfully:', ['id' => $booking->id]);

            return response()->json([
                'success' => true,
                'message' => 'Test booking created successfully',
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'data_used' => $bookingData
            ]);

        } catch (\Exception $e) {
            \Log::error('Test booking creation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Store a new booking
     */
    public function store(Request $request): JsonResponse
    {
        try {
            \Log::info('[BOOKING_API] ===== NEW BOOKING REQUEST STARTED =====');
            \Log::info('[BOOKING_API] Request method:', [$request->method()]);
            \Log::info('[BOOKING_API] Request URL:', [$request->url()]);
            \Log::info('[BOOKING_API] Request headers:', $request->headers->all());
            \Log::info('[BOOKING_API] Request body (raw):', [$request->getContent()]);
            \Log::info('[BOOKING_API] Request data (parsed):', $request->all());
            \Log::info('[BOOKING_API] Database columns available:', \Schema::getColumnListing('bookings'));
            
            $validator = Validator::make($request->all(), [
                'event_id' => 'nullable|integer', // Made optional for ticket bookings
                'ticket_id' => 'nullable|integer', // Added ticket_id validation
                'event_title' => 'nullable|string|max:255',
                'customer_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'mobile_phone' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'required|string|max:20',
                'quantity' => 'required|integer|min:1',
                'event_date' => 'required|date',
                'total_amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string|max:50',
                'booking_status' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                \Log::error('[BOOKING_API] Validation failed:', $validator->errors()->toArray());
                \Log::error('[BOOKING_API] Failed validation rules for data:', $request->all());
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'messages' => $validator->errors(),
                    'received_data' => $request->all()
                ], 422);
            }

            \Log::info('[BOOKING_API] Validation passed, proceeding to create booking...');

            // Handle ticket_id and event_id relationship
            $finalEventId = $request->event_id;
            $finalTicketId = $request->ticket_id;
            
            if ($request->ticket_id) {
                \Log::info('[BOOKING_API] This is a ticket booking, ticket_id:', ['ticket_id' => $request->ticket_id]);
                // If ticket_id is provided, try to get the event_id from the ticket
                try {
                    $ticket = \App\Models\Ticket::find($request->ticket_id);
                    if ($ticket && $ticket->event_id) {
                        $finalEventId = $ticket->event_id;
                        \Log::info('[BOOKING_API] Found event_id from ticket:', ['event_id' => $finalEventId]);
                    } else {
                        \Log::info('[BOOKING_API] Ticket has no event_id or ticket not found, making event_id null for standalone ticket');
                        $finalEventId = null;
                    }
                } catch (\Exception $e) {
                    \Log::warning('[BOOKING_API] Could not fetch ticket details:', ['error' => $e->getMessage()]);
                    $finalEventId = null;
                }
            } elseif ($request->event_id) {
                \Log::info('[BOOKING_API] This is a direct event booking, event_id:', ['event_id' => $request->event_id]);
                $finalTicketId = null; // Direct event bookings don't have ticket_id
            }

            // Generate booking reference
            $bookingReference = 'KB' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            \Log::info('[BOOKING_API] Generated booking reference:', ['reference' => $bookingReference]);

            // Only include fields that exist in the database schema
            $bookingData = [
                'booking_reference' => $bookingReference,
                'event_id' => $finalEventId, // Use resolved event_id (could be null for standalone tickets)
                'ticket_id' => $finalTicketId, // Use resolved ticket_id (could be null for direct events)
                'country_id' => 1, // Default country ID
                'postal_code' => $request->postal_code,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->mobile_phone,
                'adult_tickets' => $request->adult_tickets ?? 0,
                'child_tickets' => $request->child_tickets ?? 0,
                'quantity' => $request->quantity,
                'event_date' => $request->event_date,
                'adult_price' => $request->adult_price ?? 0,
                'child_price' => $request->child_price ?? 0,
                'total_amount' => $request->total_amount,
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_reference' => null,
                'payment_date' => null,
                'status' => $request->booking_status ?? 'confirmed',
            ];

            // Add optional fields only if they exist in database
            if (Schema::hasColumn('bookings', 'event_title')) {
                $bookingData['event_title'] = $request->event_title;
            }
            if (Schema::hasColumn('bookings', 'email')) {
                $bookingData['email'] = $request->email;
            }
            if (Schema::hasColumn('bookings', 'mobile_phone')) {
                $bookingData['mobile_phone'] = $request->mobile_phone;
            }
            if (Schema::hasColumn('bookings', 'country')) {
                $bookingData['country'] = $request->country;
            }

            \Log::info('[BOOKING_API] Final booking data to be inserted:', $bookingData);
            \Log::info('[BOOKING_API] Attempting to create booking in database...');

            $booking = Booking::create($bookingData);

            \Log::info('[BOOKING_API] Booking created successfully in database');
            \Log::info('[BOOKING_API] Created booking details:', [
                'id' => $booking->id, 
                'reference' => $booking->booking_reference,
                'customer_name' => $booking->customer_name,
                'email' => $booking->email ?? $booking->customer_email,
                'total_amount' => $booking->total_amount
            ]);

            \Log::info('[BOOKING_API] ===== BOOKING REQUEST COMPLETED SUCCESSFULLY =====');

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
            \Log::error('[BOOKING_API] ===== BOOKING REQUEST FAILED =====');
            \Log::error('[BOOKING_API] Exception type: ' . get_class($e));
            \Log::error('[BOOKING_API] Error message: ' . $e->getMessage());
            \Log::error('[BOOKING_API] Error file: ' . $e->getFile());
            \Log::error('[BOOKING_API] Error line: ' . $e->getLine());
            \Log::error('[BOOKING_API] Stack trace: ' . $e->getTraceAsString());
            \Log::error('[BOOKING_API] Request data that caused error:', $request->all());
            \Log::error('[BOOKING_API] Database connection status: ' . (\DB::connection()->getPdo() ? 'Connected' : 'Not Connected'));
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create booking',
                'message' => 'An error occurred while processing your booking. Please check the logs for details.',
                'debug_info' => [
                    'exception_type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
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