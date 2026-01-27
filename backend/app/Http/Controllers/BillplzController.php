<?php

namespace App\Http\Controllers;

use App\Services\BillplzService;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BillplzController extends Controller
{
    private $billplzService;

    public function __construct(BillplzService $billplzService)
    {
        $this->billplzService = $billplzService;
    }

    /**
     * Create a new payment bill
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function createPayment(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'booking_id' => 'required|exists:bookings,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the booking
            $booking = Booking::findOrFail($request->booking_id);

            // Check if booking is already paid
            if ($booking->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is already paid'
                ], 400);
            }

            // Check if Billplz service is configured
            if (!$this->billplzService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment service is not configured properly'
                ], 500);
            }

            // Create bill in Billplz
            $billResult = $this->billplzService->createBill($booking);

            if (!$billResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment bill',
                    'error' => $billResult['error'] ?? 'Unknown error'
                ], 500);
            }

            $billData = $billResult['data'];
            
            // Fix Billplz URL format issue - comprehensive solution
            $paymentUrl = $billData['url'];
            
            // Handle various URL format issues that Billplz might return
            if (strpos($paymentUrl, '/bills/') !== false) {
                // Split URL at bill ID to reconstruct properly
                $urlParts = explode('/bills/', $paymentUrl);
                if (count($urlParts) === 2) {
                    $baseUrl = $urlParts[0] . '/bills/';
                    $pathAndQuery = $urlParts[1];
                    
                    // Check if there are query parameters improperly attached
                    if (strpos($pathAndQuery, '&') !== false) {
                        // Split bill ID from query parameters
                        $parts = explode('&', $pathAndQuery, 2);
                        $billId = $parts[0];
                        $queryString = $parts[1];
                        
                        // Reconstruct URL with proper format
                        $paymentUrl = $baseUrl . $billId . '?' . $queryString;
                        
                        Log::info('Fixed malformed Billplz URL', [
                            'original_url' => $billData['url'],
                            'fixed_url' => $paymentUrl,
                            'bill_id' => $billId
                        ]);
                    }
                }
            }
            
            // Legacy fix for specific redirect_url issue
            if (strpos($paymentUrl, '&redirect_url=') !== false) {
                $paymentUrl = str_replace('&redirect_url=', '?redirect_url=', $paymentUrl);
            }

            // Update booking with payment details
            $booking->update([
                'payment_gateway' => 'billplz',
                'payment_reference' => $billData['id'],
                'payment_url' => $paymentUrl, // Use fixed URL
                'payment_status' => 'pending',
                'payment_metadata' => $billData
            ]);

            Log::info('Payment bill created successfully', [
                'booking_id' => $booking->id,
                'bill_id' => $billData['id'],
                'original_url' => $billData['url'],
                'fixed_url' => $paymentUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment bill created successfully',
                'data' => [
                    'bill_id' => $billData['id'],
                    'payment_url' => $paymentUrl, // Return fixed URL
                    'amount' => $billData['amount'],
                    'booking_reference' => $booking->booking_reference
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating payment bill', [
                'error' => $e->getMessage(),
                'booking_id' => $request->booking_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Handle Billplz webhook callback
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handleCallback(Request $request): JsonResponse
    {
        try {
            // Get the X-Signature header
            $signature = $request->header('X-Signature');
            
            if (!$signature) {
                Log::warning('Billplz callback received without X-Signature header');
                return response()->json(['message' => 'Forbidden'], 403);
            }

            // Get callback data
            $data = $request->all();

            // Verify signature
            if (!$this->billplzService->verifyCallback($data, $signature)) {
                Log::warning('Billplz callback signature verification failed', [
                    'signature' => $signature,
                    'data' => $data
                ]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            Log::info('Billplz callback received', ['data' => $data]);

            // Process the callback
            $result = $this->billplzService->processCallback($data);

            if (!$result['success']) {
                Log::error('Failed to process Billplz callback', [
                    'error' => $result['error'],
                    'data' => $data
                ]);
                return response()->json(['message' => 'Processing failed'], 500);
            }

            Log::info('Billplz callback processed successfully', [
                'booking_id' => $result['booking']->id,
                'bill_id' => $data['id']
            ]);

            return response()->json(['message' => 'OK'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Billplz callback', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Check payment status
     * 
     * @param Request $request
     * @param string $billId
     * @return JsonResponse
     */
    public function checkPaymentStatus(Request $request, string $billId): JsonResponse
    {
        try {
            // Find booking by payment reference (bill ID)
            $booking = Booking::where('payment_reference', $billId)->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Get bill status from Billplz
            $billStatus = $this->billplzService->getBillStatus($billId);

            if (!$billStatus['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check payment status'
                ], 500);
            }

            $billData = $billStatus['data'];

            // Update local booking status if needed
            $paymentStatus = $billData['paid'] ? 'paid' : 'pending';
            
            if ($booking->payment_status !== $paymentStatus) {
                $booking->update([
                    'payment_status' => $paymentStatus,
                    'payment_completed_at' => $billData['paid'] ? now() : null,
                    'booking_status' => $billData['paid'] ? 'confirmed' : $booking->booking_status,
                    'payment_metadata' => $billData
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'bill_id' => $billData['id'],
                    'booking_reference' => $booking->booking_reference,
                    'payment_status' => $paymentStatus,
                    'paid' => $billData['paid'],
                    'amount' => $billData['amount'],
                    'paid_amount' => $billData['paid_amount'] ?? 0,
                    'payment_completed_at' => $booking->payment_completed_at?->toISOString(),
                    'state' => $billData['state'],
                    'due_at' => $billData['due_at'] ?? null,
                    'paid_at' => $billData['paid_at'] ?? null,
                    'payment_url' => $billData['url'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking payment status', [
                'error' => $e->getMessage(),
                'bill_id' => $billId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Test Billplz connection and configuration
     * 
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->billplzService->testConnection();

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed',
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Billplz connection successful',
                'configured' => $this->billplzService->isConfigured()
            ]);

        } catch (\Exception $e) {
            Log::error('Error testing Billplz connection', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint to test URL generation for payment issues
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function debugPaymentUrl(Request $request): JsonResponse
    {
        try {
            $bookingId = $request->input('booking_id', 1);
            
            // Find or create a test booking
            $booking = Booking::find($bookingId);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found for testing'
                ], 404);
            }

            // Test bill creation
            $billResult = $this->billplzService->createBill($booking);
            
            if (!$billResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create test bill',
                    'error' => $billResult['error'] ?? 'Unknown error'
                ], 500);
            }

            $billData = $billResult['data'];
            $originalUrl = $billData['url'];
            
            // Apply the same URL fixing logic
            $fixedUrl = $originalUrl;
            $fixApplied = false;
            
            if (strpos($fixedUrl, '/bills/') !== false) {
                $urlParts = explode('/bills/', $fixedUrl);
                if (count($urlParts) === 2) {
                    $baseUrl = $urlParts[0] . '/bills/';
                    $pathAndQuery = $urlParts[1];
                    
                    if (strpos($pathAndQuery, '&') !== false) {
                        $parts = explode('&', $pathAndQuery, 2);
                        $billId = $parts[0];
                        $queryString = $parts[1];
                        
                        $fixedUrl = $baseUrl . $billId . '?' . $queryString;
                        $fixApplied = true;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'debug_info' => [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'bill_id' => $billData['id'],
                    'original_url' => $originalUrl,
                    'fixed_url' => $fixedUrl,
                    'fix_applied' => $fixApplied,
                    'url_analysis' => [
                        'contains_bills_path' => strpos($originalUrl, '/bills/') !== false,
                        'contains_ampersand' => strpos($originalUrl, '&') !== false,
                        'contains_redirect_param' => strpos($originalUrl, 'redirect_url') !== false,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in payment URL debug', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Debug test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details for a booking
     * 
     * @param Request $request
     * @param int $bookingId
     * @return JsonResponse
     */
    public function getPaymentDetails(Request $request, int $bookingId): JsonResponse
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            $paymentDetails = [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'payment_gateway' => $booking->payment_gateway,
                'payment_reference' => $booking->payment_reference,
                'payment_status' => $booking->payment_status,
                'payment_url' => $booking->payment_url,
                'payment_completed_at' => $booking->payment_completed_at?->toISOString(),
                'total_amount' => $booking->total_amount,
                'payment_metadata' => $booking->payment_metadata
            ];

            return response()->json([
                'success' => true,
                'data' => $paymentDetails
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting payment details', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment details not found'
            ], 404);
        }
    }

    /**
     * Cancel a payment bill (if supported by Billplz)
     * 
     * @param Request $request
     * @param string $billId
     * @return JsonResponse
     */
    public function cancelPayment(Request $request, string $billId): JsonResponse
    {
        try {
            $booking = Booking::where('payment_reference', $billId)->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Update booking status
            $booking->update([
                'payment_status' => 'cancelled',
                'booking_status' => 'cancelled'
            ]);

            Log::info('Payment cancelled', [
                'booking_id' => $booking->id,
                'bill_id' => $billId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling payment', [
                'error' => $e->getMessage(),
                'bill_id' => $billId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payment'
            ], 500);
        }
    }
}