<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GoogleReview;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SyncGoogleReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:sync {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Google Reviews from Google Places API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Google Reviews sync...');
        
        // Check if we should skip sync (rate limiting)
        if (!$this->option('force')) {
            $lastSync = Cache::get('last_review_sync');
            if ($lastSync && now()->diffInHours($lastSync) < 6) {
                $this->info('Reviews were synced recently. Use --force to override.');
                return 0;
            }
        }
        
        try {
            $apiKey = config('services.google.places_api_key');
            $placeId = config('services.google.place_id');
            
            if (!$apiKey || !$placeId) {
                $this->error('Google API key or Place ID not configured in .env');
                return 1;
            }
            
            $this->info("Fetching reviews for Place ID: {$placeId}");
            
            // Fetch place details with reviews from Google Places API
            $response = Http::timeout(30)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'reviews,rating,user_ratings_total',
                'key' => $apiKey
            ]);
            
            if (!$response->successful()) {
                $this->error('Failed to fetch from Google Places API: ' . $response->status());
                $this->error('Response body: ' . $response->body());
                Log::error('Google Places API HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                return 1;
            }
            
            $data = $response->json();
            
            if (!isset($data['status'])) {
                $this->error('Invalid API response format');
                Log::error('Invalid Google Places API response', ['response' => $data]);
                return 1;
            }
            
            if ($data['status'] !== 'OK') {
                $errorMessage = $this->getGoogleApiErrorMessage($data['status'], $data);
                $this->error('Google Places API error: ' . $data['status']);
                $this->error($errorMessage);
                
                if (isset($data['error_message'])) {
                    $this->error('Google error message: ' . $data['error_message']);
                }
                
                Log::error('Google Places API status error', [
                    'status' => $data['status'],
                    'error_message' => $data['error_message'] ?? null,
                    'full_response' => $data
                ]);
                return 1;
            }
            
            $reviews = $data['result']['reviews'] ?? [];
            $newReviews = 0;
            $updatedReviews = 0;
            
            $this->info("Processing " . count($reviews) . " reviews...");
            
            $progressBar = $this->output->createProgressBar(count($reviews));
            $progressBar->start();
            
            foreach ($reviews as $review) {
                $googleReviewId = $review['author_name'] . '_' . $review['time']; // Create unique ID
                
                $existingReview = GoogleReview::where('google_review_id', $googleReviewId)->first();
                
                $reviewData = [
                    'google_review_id' => $googleReviewId,
                    'place_id' => $placeId,
                    'author_name' => $review['author_name'],
                    'author_photo_url' => $review['profile_photo_url'] ?? null,
                    'rating' => $review['rating'],
                    'text' => $review['text'] ?? null,
                    'review_time' => date('Y-m-d H:i:s', $review['time']),
                    'like_count' => 0, // Google API doesn't provide like count
                    'is_active' => true
                ];
                
                if ($existingReview) {
                    $existingReview->update($reviewData);
                    $updatedReviews++;
                } else {
                    GoogleReview::create($reviewData);
                    $newReviews++;
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            
            // Clear cache after updating
            Cache::forget('review_stats');
            Cache::put('last_review_sync', now());
            
            $this->info("✅ Sync completed successfully!");
            $this->info("📊 Results:");
            $this->info("   • New reviews: {$newReviews}");
            $this->info("   • Updated reviews: {$updatedReviews}");
            $this->info("   • Total processed: " . count($reviews));
            
            // Show current stats
            $totalActive = GoogleReview::active()->count();
            $avgRating = round(GoogleReview::active()->avg('rating'), 1);
            $this->info("📈 Current stats: {$totalActive} active reviews, {$avgRating} avg rating");
            
            Log::info("Google Reviews sync completed: {$newReviews} new, {$updatedReviews} updated");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error syncing Google reviews: ' . $e->getMessage());
            Log::error('Error syncing Google reviews: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Get human-readable error message for Google API status codes
     */
    private function getGoogleApiErrorMessage($status, $data)
    {
        switch ($status) {
            case 'REQUEST_DENIED':
                return "API request denied. Check:\n" .
                       "1. API key restrictions in Google Cloud Console\n" .
                       "2. Places API is enabled\n" .
                       "3. Billing is enabled\n" .
                       "4. API key has proper permissions";
                       
            case 'INVALID_REQUEST':
                return "Invalid request. Check:\n" .
                       "1. Place ID format is correct\n" .
                       "2. Required parameters are provided";
                       
            case 'NOT_FOUND':
                return "Place not found. Check Place ID is correct";
                
            case 'ZERO_RESULTS':
                return "No reviews found for this place";
                
            case 'OVER_QUERY_LIMIT':
                return "API quota exceeded. Check daily/per-second limits";
                
            case 'UNKNOWN_ERROR':
                return "Unknown server error. Try again later";
                
            default:
                return "Unexpected error status: {$status}";
        }
    }
}
