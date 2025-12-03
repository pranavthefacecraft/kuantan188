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
                'ticket_id' => 1,
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
            \Log::info('Booking request received:', $request->all());
            \Log::info('Database columns available:', \Schema::getColumnListing('bookings'));
            
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|integer',
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
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            \Log::info('Validation passed, creating booking...');

            // Generate booking reference
            $bookingReference = 'KB' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            \Log::info('Generated booking reference:', ['reference' => $bookingReference]);

            // Only include fields that exist in the database schema
            $bookingData = [
                'booking_reference' => $bookingReference,
                'event_id' => $request->event_id,
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
                'ticket_id' => 1, // Default ticket ID
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

            \Log::info('Attempting to create booking with data:', $bookingData);

            $booking = Booking::create($bookingData);

            \Log::info('Booking created successfully:', ['id' => $booking->id, 'reference' => $booking->booking_reference]);

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
            \Log::error('Error file: ' . $e->getFile());
            \Log::error('Error line: ' . $e->getLine());
            
            return response()->json([
                'error' => 'Failed to create booking',
                'message' => 'An error occurred while processing your booking. Please try again.',
                'debug_message' => $e->getMessage(),
                'debug_file' => $e->getFile(),
                'debug_line' => $e->getLine(),
                'debug_trace' => $e->getTraceAsString()
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