<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    /**
     * Get all active events for public display
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::where('is_active', true)
                     ->orderBy('event_date', 'asc');

        // Add search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Add category filtering if needed (you can extend this)
        if ($request->has('category')) {
            // Implement category filtering if you have categories
        }

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'event_date' => $event->event_date->format('Y-m-d H:i:s'),
                'event_date_formatted' => $event->event_date->format('F j, Y'),
                'event_time_formatted' => $event->event_date->format('g:i A'),
                'image_url' => $event->image_url 
                    ? (str_starts_with($event->image_url, 'http') 
                        ? $event->image_url 
                        : asset($event->image_url))
                    : 'https://via.placeholder.com/400x250/6c63ff/ffffff?text=' . urlencode($event->title),
                'price' => $event->price ?? 'From RM50',
                'price_display' => $event->price ? "From RM{$event->price}" : 'From RM50',
                'category' => $this->determineCategory($event->title), // Simple category detection
                'is_booking_open' => $event->isBookingOpen(),
                'slug' => str_replace(' ', '-', strtolower($event->title))
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $events,
            'total' => $events->count()
        ]);
    }

    /**
     * Get featured events for homepage
     */
    public function featured(): JsonResponse
    {
        $events = Event::where('is_active', true)
                      ->orderBy('event_date', 'asc')
                      ->limit(6) // Get top 6 events for homepage
                      ->get()
                      ->map(function ($event) {
                          return [
                              'id' => $event->id,
                              'title' => $event->title,
                              'description' => strlen($event->description) > 150 
                                             ? substr($event->description, 0, 150) . '...' 
                                             : $event->description,
                              'location' => $event->location,
                              'event_date' => $event->event_date->format('Y-m-d H:i:s'),
                              'event_date_formatted' => $event->event_date->format('F j, Y'),
                              'event_time_formatted' => $event->event_date->format('g:i A'),
                              'image_url' => $event->image_url 
                                  ? (str_starts_with($event->image_url, 'http') 
                                      ? $event->image_url 
                                      : asset($event->image_url))
                                  : 'https://picsum.photos/400/250?random=' . $event->id,
                              'price' => $event->price ?? 'From RM50',
                              'price_display' => $event->price ? "From RM{$event->price}" : 'From RM50',
                              'category' => $this->determineCategory($event->title),
                              'is_booking_open' => $event->isBookingOpen(),
                              'slug' => str_replace(' ', '-', strtolower($event->title))
                          ];
                      });

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Get single event details
     */
    public function show($id): JsonResponse
    {
        $event = Event::where('is_active', true)->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'event_date' => $event->event_date->format('Y-m-d H:i:s'),
                'event_date_formatted' => $event->event_date->format('F j, Y'),
                'event_time_formatted' => $event->event_date->format('g:i A'),
                'image_url' => $event->image_url 
                    ? (str_starts_with($event->image_url, 'http') 
                        ? $event->image_url 
                        : asset($event->image_url))
                    : 'https://picsum.photos/400/250?random=' . $event->id,
                'price' => $event->price ?? 'From RM50',
                'price_display' => $event->price ? "From RM{$event->price}" : 'From RM50',
                'category' => $this->determineCategory($event->title),
                'is_booking_open' => $event->isBookingOpen(),
                'slug' => str_replace(' ', '-', strtolower($event->title))
            ]
        ]);
    }

    /**
     * Simple category determination based on title keywords
     */
    private function determineCategory(string $title): string
    {
        $title = strtolower($title);
        
        if (str_contains($title, 'music') || str_contains($title, 'concert') || str_contains($title, 'festival')) {
            return 'Music';
        }
        if (str_contains($title, 'food') || str_contains($title, 'culinary')) {
            return 'Food';
        }
        if (str_contains($title, 'cultural') || str_contains($title, 'heritage') || str_contains($title, 'traditional')) {
            return 'Culture';
        }
        if (str_contains($title, 'art') || str_contains($title, 'exhibition')) {
            return 'Art';
        }
        if (str_contains($title, 'tech') || str_contains($title, 'technology') || str_contains($title, 'conference')) {
            return 'Technology';
        }
        
        return 'Events';
    }

    /**
     * Get all bookings/tickets for public display
     */
    public function getTickets(): JsonResponse
    {
        $bookings = \App\Models\Booking::with(['event', 'ticket'])
                                     ->where('status', '!=', 'cancelled')
                                     ->orderBy('created_at', 'desc')
                                     ->get()
                                     ->map(function ($booking) {
            return [
                'id' => $booking->booking_reference ?: 'TKT' . str_pad($booking->id, 3, '0', STR_PAD_LEFT),
                'eventTitle' => $booking->event->title ?? 'Unknown Event',
                'eventDate' => $booking->event ? $booking->event->event_date->format('F j, Y \a\t g:i A') : 'TBD',
                'location' => $booking->event->location ?? 'TBD',
                'quantity' => ($booking->adult_tickets ?? 0) + ($booking->child_tickets ?? 0),
                'totalAmount' => number_format($booking->total_amount ?? 0, 2),
                'bookingDate' => $booking->created_at->format('M j, Y'),
                'status' => ucfirst($booking->status ?? 'pending'),
                'customerName' => $booking->customer_name,
                'customerEmail' => $booking->customer_email,
                'paymentStatus' => ucfirst($booking->payment_status ?? 'pending')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'total' => $bookings->count()
        ]);
    }
}