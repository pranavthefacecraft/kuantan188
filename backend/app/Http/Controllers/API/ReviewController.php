<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\GoogleReview;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    /**
     * Get reviews from database (for frontend)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $rating = $request->get('rating'); // Filter by rating
            $recent = $request->get('recent', false); // Show only recent reviews
            
            $query = GoogleReview::active()
                ->orderBy('review_time', 'desc');
            
            if ($rating) {
                $query->byRating($rating);
            }
            
            if ($recent) {
                $query->recent(30); // Last 30 days
            }
            
            $reviews = $query->paginate($perPage);
            
            // Get summary statistics
            $stats = Cache::remember('review_stats', 3600, function () {
                return [
                    'total_reviews' => GoogleReview::active()->count(),
                    'average_rating' => round(GoogleReview::active()->avg('rating'), 1),
                    'rating_breakdown' => [
                        5 => GoogleReview::active()->byRating(5)->count(),
                        4 => GoogleReview::active()->byRating(4)->count(),
                        3 => GoogleReview::active()->byRating(3)->count(),
                        2 => GoogleReview::active()->byRating(2)->count(),
                        1 => GoogleReview::active()->byRating(1)->count(),
                    ]
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $reviews->items(),
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviews'
            ], 500);
        }
    }

    /**
     * Fetch reviews from Google Places API and store in database
     */
    public function fetchFromGoogle(): JsonResponse
    {
        try {
            $apiKey = config('services.google.places_api_key');
            $placeId = config('services.google.place_id');
            
            if (!$apiKey || !$placeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google API key or Place ID not configured'
                ], 400);
            }
            
            // Fetch place details with reviews from Google Places API
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'reviews,rating,user_ratings_total',
                'key' => $apiKey
            ]);
            
            if (!$response->successful()) {
                Log::error('Google Places API error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch from Google Places API'
                ], 500);
            }
            
            $data = $response->json();
            
            if ($data['status'] !== 'OK') {
                Log::error('Google Places API status error: ' . $data['status']);
                return response()->json([
                    'success' => false,
                    'message' => 'Google Places API error: ' . $data['status']
                ], 500);
            }
            
            $reviews = $data['result']['reviews'] ?? [];
            $newReviews = 0;
            $updatedReviews = 0;
            
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
            }
            
            // Clear cache after updating
            Cache::forget('review_stats');
            
            return response()->json([
                'success' => true,
                'message' => "Sync completed: {$newReviews} new reviews, {$updatedReviews} updated",
                'data' => [
                    'new_reviews' => $newReviews,
                    'updated_reviews' => $updatedReviews,
                    'total_reviews' => count($reviews)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching Google reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviews from Google'
            ], 500);
        }
    }

    /**
     * Get review statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = Cache::remember('review_stats', 3600, function () {
                return [
                    'total_reviews' => GoogleReview::active()->count(),
                    'average_rating' => round(GoogleReview::active()->avg('rating'), 1),
                    'rating_breakdown' => [
                        5 => GoogleReview::active()->byRating(5)->count(),
                        4 => GoogleReview::active()->byRating(4)->count(),
                        3 => GoogleReview::active()->byRating(3)->count(),
                        2 => GoogleReview::active()->byRating(2)->count(),
                        1 => GoogleReview::active()->byRating(1)->count(),
                    ],
                    'recent_reviews' => GoogleReview::active()->recent(30)->count(),
                    'last_updated' => GoogleReview::latest('updated_at')->first()?->updated_at?->toISOString()
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching review stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch review statistics'
            ], 500);
        }
    }

    /**
     * Toggle review active status (for admin)
     */
    public function toggleStatus(Request $request, GoogleReview $review): JsonResponse
    {
        try {
            $review->update(['is_active' => !$review->is_active]);
            
            // Clear cache after status change
            Cache::forget('review_stats');
            
            return response()->json([
                'success' => true,
                'message' => $review->is_active ? 'Review activated' : 'Review hidden',
                'data' => $review
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling review status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review status'
            ], 500);
        }
    }
}
