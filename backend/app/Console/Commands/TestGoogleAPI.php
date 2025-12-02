<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestGoogleAPI extends Command
{
    protected $signature = 'google:test {--verbose}';
    protected $description = 'Test Google Places API configuration and connectivity';

    public function handle()
    {
        $this->info('=== Google Places API Test ===');
        
        // Step 1: Check environment variables
        $apiKey = config('services.google.places_api_key');
        $placeId = config('services.google.place_id');
        
        $this->info('Step 1: Configuration Check');
        $this->line("API Key: " . ($apiKey ? '✅ Present (' . substr($apiKey, 0, 10) . '...)' : '❌ Missing'));
        $this->line("Place ID: " . ($placeId ? '✅ Present (' . substr($placeId, 0, 20) . '...)' : '❌ Missing'));
        
        if (!$apiKey || !$placeId) {
            $this->error('Missing Google API configuration in .env file');
            return 1;
        }
        
        // Step 2: Test basic API connectivity
        $this->info("\nStep 2: API Connectivity Test");
        
        try {
            // First test - Simple place details
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'name,rating',
                'key' => $apiKey
            ]);
            
            if (!$response->successful()) {
                $this->error('❌ HTTP Error: ' . $response->status());
                $this->error('Response: ' . $response->body());
                return 1;
            }
            
            $data = $response->json();
            
            if ($this->option('verbose')) {
                $this->line('Raw API Response:');
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
            }
            
            // Check API response status
            if (!isset($data['status'])) {
                $this->error('❌ Invalid API response format');
                return 1;
            }
            
            switch ($data['status']) {
                case 'OK':
                    $placeName = $data['result']['name'] ?? 'Unknown';
                    $rating = $data['result']['rating'] ?? 'No rating';
                    $this->info("✅ API Working - Place: {$placeName}, Rating: {$rating}");
                    break;
                    
                case 'REQUEST_DENIED':
                    $this->error('❌ REQUEST_DENIED - API Key restrictions or billing issue');
                    $this->line('Solutions:');
                    $this->line('1. Check API key restrictions in Google Cloud Console');
                    $this->line('2. Ensure billing is enabled');
                    $this->line('3. Enable Places API');
                    return 1;
                    
                case 'INVALID_REQUEST':
                    $this->error('❌ INVALID_REQUEST - Check Place ID format');
                    return 1;
                    
                default:
                    $this->error('❌ API Error: ' . $data['status']);
                    if (isset($data['error_message'])) {
                        $this->line('Error message: ' . $data['error_message']);
                    }
                    return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            return 1;
        }
        
        // Step 3: Test reviews endpoint
        $this->info("\nStep 3: Reviews Endpoint Test");
        
        try {
            $response = Http::timeout(30)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'reviews,rating,user_ratings_total',
                'key' => $apiKey
            ]);
            
            if (!$response->successful()) {
                $this->error('❌ Reviews fetch failed: ' . $response->status());
                return 1;
            }
            
            $data = $response->json();
            
            if ($data['status'] === 'OK') {
                $reviews = $data['result']['reviews'] ?? [];
                $totalReviews = $data['result']['user_ratings_total'] ?? 0;
                
                $this->info("✅ Reviews API Working");
                $this->line("Total reviews on Google: {$totalReviews}");
                $this->line("Reviews returned by API: " . count($reviews));
                
                if ($this->option('verbose') && !empty($reviews)) {
                    $this->line("\nSample Review:");
                    $review = $reviews[0];
                    $this->line("Author: " . $review['author_name']);
                    $this->line("Rating: " . $review['rating']);
                    $this->line("Text: " . substr($review['text'], 0, 100) . '...');
                }
                
            } else {
                $this->error('❌ Reviews API Error: ' . $data['status']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Reviews test exception: ' . $e->getMessage());
            return 1;
        }
        
        $this->info("\n✅ All tests passed! Google Places API is working correctly.");
        $this->line("You can now run: php artisan reviews:sync --force");
        
        return 0;
    }
}