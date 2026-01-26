<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Booking;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class BillplzService
{
    private $client;
    private $apiKey;
    private $collectionId;
    private $xSignatureKey;
    private $baseUrl;
    private $callbackUrl;
    private $redirectUrl;

    public function __construct()
    {
        $this->apiKey = Config::get('services.billplz.api_key');
        $this->collectionId = Config::get('services.billplz.collection_id');
        $this->xSignatureKey = Config::get('services.billplz.x_signature_key');
        $this->callbackUrl = Config::get('services.billplz.callback_url');
        $this->redirectUrl = Config::get('services.billplz.redirect_url');
        
        $environment = Config::get('services.billplz.environment', 'sandbox');
        $this->baseUrl = $environment === 'production' 
            ? Config::get('services.billplz.production_url')
            : Config::get('services.billplz.sandbox_url');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'auth' => [$this->apiKey, ''],
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Kuantan188/1.0 (Laravel)',
            ],
            'timeout' => 30,
            'verify' => true, // Verify SSL certificates
            'curl' => [
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
            ]
        ]);
    }

    /**
     * Create a new bill in Billplz
     *
     * @param Booking $booking
     * @return array
     */
    public function createBill(Booking $booking)
    {
        try {
            $billData = [
                'collection_id' => $this->collectionId,
                'description' => $this->generateBillDescription($booking),
                'email' => $booking->email,
                'name' => $booking->customer_name,
                'amount' => $this->convertToCents($booking->total_amount),
                'callback_url' => $this->callbackUrl,
                'redirect_url' => $this->redirectUrl . '?booking_id=' . $booking->id,
                'reference_1_label' => 'Booking Reference',
                'reference_1' => $booking->booking_reference,
                'reference_2_label' => 'Event',
                'reference_2' => $booking->event_title,
            ];

            Log::info('Creating Billplz bill', ['booking_id' => $booking->id, 'bill_data' => $billData]);

            $response = $this->client->post('bills', [
                'form_params' => $billData
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Billplz bill created successfully', ['booking_id' => $booking->id, 'bill_id' => $result['id']]);

            return [
                'success' => true,
                'data' => $result
            ];

        } catch (RequestException $e) {
            Log::error('Failed to create Billplz bill', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create payment bill',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Get bill status from Billplz
     *
     * @param string $billId
     * @return array
     */
    public function getBillStatus($billId)
    {
        try {
            $response = $this->client->get("bills/{$billId}");
            $result = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $result
            ];

        } catch (RequestException $e) {
            Log::error('Failed to get Billplz bill status', [
                'bill_id' => $billId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to get payment status'
            ];
        }
    }

    /**
     * Verify webhook callback signature
     *
     * @param array $data
     * @param string $signature
     * @return bool
     */
    public function verifyCallback($data, $signature)
    {
        $calculatedSignature = hash_hmac('sha256', 
            http_build_query($data, '', '&', PHP_QUERY_RFC3986), 
            $this->xSignatureKey
        );

        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Process webhook callback data
     *
     * @param array $data
     * @return array
     */
    public function processCallback($data)
    {
        try {
            // Find booking by reference or bill ID
            $booking = Booking::where('payment_reference', $data['id'])
                ->orWhere('booking_reference', $data['reference_1'] ?? '')
                ->first();

            if (!$booking) {
                Log::warning('Booking not found for Billplz callback', ['bill_id' => $data['id']]);
                return [
                    'success' => false,
                    'error' => 'Booking not found'
                ];
            }

            // Update booking payment status
            $paymentStatus = $data['paid'] ? 'paid' : 'failed';
            
            $booking->update([
                'payment_status' => $paymentStatus,
                'payment_completed_at' => $data['paid'] ? now() : null,
                'booking_status' => $data['paid'] ? 'confirmed' : 'cancelled',
                'payment_metadata' => $data
            ]);

            Log::info('Booking payment status updated', [
                'booking_id' => $booking->id,
                'payment_status' => $paymentStatus,
                'bill_id' => $data['id']
            ]);

            return [
                'success' => true,
                'booking' => $booking
            ];

        } catch (\Exception $e) {
            Log::error('Failed to process Billplz callback', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process payment callback'
            ];
        }
    }

    /**
     * Generate bill description
     *
     * @param Booking $booking
     * @return string
     */
    private function generateBillDescription(Booking $booking)
    {
        return sprintf(
            'Ticket booking for %s - %s (Ref: %s)',
            $booking->event_title,
            $booking->customer_name,
            $booking->booking_reference
        );
    }

    /**
     * Convert amount to cents (Billplz expects amount in sen/cents)
     *
     * @param float $amount
     * @return int
     */
    private function convertToCents($amount)
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert amount from cents back to ringgit
     *
     * @param int $cents
     * @return float
     */
    private function convertFromCents($cents)
    {
        return $cents / 100;
    }

    /**
     * Check if the service is configured properly
     *
     * @return bool
     */
    public function isConfigured()
    {
        return !empty($this->apiKey) 
            && !empty($this->collectionId) 
            && !empty($this->xSignatureKey)
            && !empty($this->baseUrl);
    }

    /**
     * Test connection to Billplz API
     *
     * @return array
     */
    public function testConnection()
    {
        try {
            Log::info('Testing Billplz connection', [
                'base_uri' => $this->baseUrl,
                'api_key_length' => strlen($this->apiKey),
                'collection_id' => $this->collectionId,
                'full_url_should_be' => $this->baseUrl . '/collections'
            ]);

            $response = $this->client->get('collections');
            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Billplz connection successful', ['collections_count' => count($result['collections'] ?? [])]);

            return [
                'success' => true,
                'message' => 'Connection successful',
                'collections' => $result
            ];

        } catch (RequestException $e) {
            $errorDetails = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response',
                'request_uri' => $e->getRequest()->getUri()->__toString()
            ];

            Log::error('Billplz connection failed', $errorDetails);

            return [
                'success' => false,
                'error' => 'Connection failed',
                'details' => $errorDetails
            ];
        }
    }
}