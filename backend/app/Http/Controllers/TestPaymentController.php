<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BillplzService;
use Illuminate\Http\JsonResponse;

class TestPaymentController extends Controller
{
    protected $billplzService;

    public function __construct(BillplzService $billplzService)
    {
        $this->billplzService = $billplzService;
    }

    public function testPaymentCreation(Request $request): JsonResponse
    {
        try {
            // Test payment creation without booking validation
            $paymentData = [
                'amount' => 50.00,
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'description' => 'Test Payment - Event Ticket'
            ];

            $bill = $this->billplzService->createBill($paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Test payment bill created successfully',
                'bill' => $bill
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
