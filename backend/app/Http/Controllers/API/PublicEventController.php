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
     * Updated: 2025-12-04 - Deployment test version 5.3 - Testing newest deployment workflow updates
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
                        : 'https://admin.tfcmockup.com/' . str_replace(' ', '%20', $event->image_url))
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
                                      : 'https://admin.tfcmockup.com/' . $event->image_url)
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
                'name' => $event->title, // Add name field for compatibility
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'event_date' => $event->event_date->format('Y-m-d'),
                'event_time' => $event->event_date->format('H:i:s'),
                'event_date_formatted' => $event->event_date->format('F j, Y'),
                'event_time_formatted' => $event->event_date->format('g:i A'),
                'image_url' => $event->image_url 
                    ? (str_starts_with($event->image_url, 'http') 
                        ? $event->image_url 
                        : 'https://admin.tfcmockup.com/' . $event->image_url)
                    : 'https://picsum.photos/400/250?random=' . $event->id,
                'price' => is_numeric($event->price) ? $event->price : 50,
                'price_display' => $event->price ? "From RM{$event->price}" : 'From RM50',
                'capacity' => $event->capacity ?? 100,
                'current_bookings' => $event->bookings()->count(),
                'status' => $event->is_active ? 'active' : 'inactive',
                'category' => $this->determineCategory($event->title),
                'is_booking_open' => $event->isBookingOpen(),
                'slug' => str_replace(' ', '-', strtolower($event->title)),
                'created_at' => $event->created_at->toISOString()
            ]
        ]);
    }

    /**
     * Get events for Book Now section with specific category filtering
     */
    public function bookNow(Request $request): JsonResponse
    {
        $query = Event::where('is_active', true)
                     ->where('event_date', '>', now()) // Only future events
                     ->orderBy('event_date', 'asc');

        // Filter by specific Book Now categories if requested
        if ($request->has('category') && $request->get('category') !== 'all') {
            $category = $request->get('category');
            $query->where(function($q) use ($category) {
                switch(strtolower($category)) {
                    case 'sky wedding':
                    case 'sky_wedding':
                        $q->where('title', 'like', '%wedding%')
                          ->orWhere('title', 'like', '%sky wedding%')
                          ->orWhere('description', 'like', '%wedding%')
                          ->orWhere('description', 'like', '%ceremony%');
                        break;
                    case 'school event':
                    case 'school_event':
                        $q->where('title', 'like', '%school%')
                          ->orWhere('title', 'like', '%education%')
                          ->orWhere('title', 'like', '%student%')
                          ->orWhere('description', 'like', '%school%')
                          ->orWhere('description', 'like', '%educational%');
                        break;
                    case 'sky yoga':
                    case 'sky_yoga':
                        $q->where('title', 'like', '%yoga%')
                          ->orWhere('title', 'like', '%sky yoga%')
                          ->orWhere('title', 'like', '%meditation%')
                          ->orWhere('description', 'like', '%yoga%')
                          ->orWhere('description', 'like', '%wellness%');
                        break;
                }
            });
        }

        $events = $query->get()->map(function ($event) {
            // Get ticket pricing information
            $ticketPrices = $this->getTicketPricing($event);
            
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
                        : 'https://admin.tfcmockup.com/' . str_replace(' ', '%20', $event->image_url))
                    : 'https://via.placeholder.com/400x250/6c63ff/ffffff?text=' . urlencode($event->title),
                'price' => $event->price ?? $ticketPrices['base_price'] ?? 'From RM50',
                'price_display' => $this->formatPriceDisplay($event, $ticketPrices),
                'category' => $this->determineBookNowCategory($event->title),
                'is_booking_open' => $event->isBookingOpen(),
                'slug' => str_replace(' ', '-', strtolower($event->title)),
                'ticket_pricing' => $ticketPrices
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $events,
            'total' => $events->count(),
            'available_categories' => ['Sky Wedding', 'School Event', 'Sky Yoga'],
            'deployment_test' => [
                'version' => '3.0',
                'timestamp' => now()->format('Y-m-d H:i:s T'),
                'message' => 'Auto-deployment test successful!'
            ]
        ]);
    }

    /**
     * Get ticket pricing information for an event
     */
    private function getTicketPricing($event): array
    {
        $tickets = $event->tickets()->with('countries')->get();
        
        if ($tickets->isEmpty()) {
            return [
                'base_price' => $event->price ?? 50.00,
                'adult_price_range' => null,
                'child_price_range' => null,
                'countries_available' => []
            ];
        }

        $adultPrices = [];
        $childPrices = [];
        $countries = [];

        foreach ($tickets as $ticket) {
            foreach ($ticket->countries as $country) {
                $adultPrice = $country->pivot->adult_price ?? 0;
                $childPrice = $country->pivot->child_price ?? 0;
                
                if ($adultPrice > 0) $adultPrices[] = $adultPrice;
                if ($childPrice > 0) $childPrices[] = $childPrice;
                
                $countries[$country->id] = [
                    'name' => $country->name,
                    'code' => $country->code,
                    'currency_symbol' => $country->currency_symbol,
                    'adult_price' => $adultPrice,
                    'child_price' => $childPrice
                ];
            }
        }

        return [
            'base_price' => $event->price ?? (!empty($adultPrices) ? min($adultPrices) : 50.00),
            'adult_price_range' => !empty($adultPrices) ? [
                'min' => min($adultPrices),
                'max' => max($adultPrices)
            ] : null,
            'child_price_range' => !empty($childPrices) ? [
                'min' => min($childPrices),
                'max' => max($childPrices)
            ] : null,
            'countries_available' => array_values($countries)
        ];
    }

    /**
     * Format price display for Book Now events
     */
    private function formatPriceDisplay($event, $ticketPrices): string
    {
        if ($event->price) {
            return "From RM{$event->price}";
        }
        
        if (isset($ticketPrices['adult_price_range'])) {
            $minPrice = $ticketPrices['adult_price_range']['min'];
            return "From RM{$minPrice}";
        }
        
        return 'From RM' . ($ticketPrices['base_price'] ?? 50);
    }

    /**
     * Determine category specifically for Book Now section
     */
    private function determineBookNowCategory(string $title): string
    {
        $title = strtolower($title);
        
        // Sky Wedding category
        if (str_contains($title, 'wedding') || str_contains($title, 'sky wedding') || 
            str_contains($title, 'ceremony') || str_contains($title, 'marriage')) {
            return 'Sky Wedding';
        }
        
        // School Event category
        if (str_contains($title, 'school') || str_contains($title, 'education') || 
            str_contains($title, 'student') || str_contains($title, 'academic')) {
            return 'School Event';
        }
        
        // Sky Yoga category
        if (str_contains($title, 'yoga') || str_contains($title, 'sky yoga') || 
            str_contains($title, 'meditation') || str_contains($title, 'wellness')) {
            return 'Sky Yoga';
        }
        
        // Fallback - determine from general categories (prioritize specific content over format)
        if (str_contains($title, 'food') || str_contains($title, 'culinary')) {
            return 'Food';
        }
        if (str_contains($title, 'cultural') || str_contains($title, 'heritage') || str_contains($title, 'traditional')) {
            return 'Culture';
        }
        if (str_contains($title, 'music') || str_contains($title, 'concert') || str_contains($title, 'festival')) {
            return 'Music';
        }
        
        return 'Events';
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
        $tickets = \App\Models\Ticket::with(['event', 'country'])
                                   ->where('is_active', true)
                                   ->get()
                                   ->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'title' => $ticket->title ?? $ticket->name ?? 'Untitled Ticket',
                'name' => $ticket->name ?? $ticket->title ?? 'Untitled Ticket',
                'description' => $ticket->description,
                'adult_price' => $ticket->adult_price,
                'child_price' => $ticket->child_price,
                'price' => $ticket->adult_price ?? $ticket->price ?? null,
                'image_url' => $ticket->image_url 
                    ? (str_starts_with($ticket->image_url, 'http') 
                        ? $ticket->image_url 
                        : 'https://admin.tfcmockup.com/' . $ticket->image_url)
                    : null,
                'event_id' => $ticket->event_id,
                'country_id' => $ticket->country_id,
                'is_active' => $ticket->is_active,
                'event' => $ticket->event ? [
                    'id' => $ticket->event->id,
                    'title' => $ticket->event->title,
                    'location' => $ticket->event->location
                ] : null,
                'country' => $ticket->country ? [
                    'id' => $ticket->country->id,
                    'name' => $ticket->country->name,
                    'currency_symbol' => $ticket->country->currency_symbol
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'total' => $tickets->count()
        ]);
    }

    /**
     * Debug endpoint to check raw event data
     */
    public function debug(Request $request): JsonResponse
    {
        try {
            // Get all events without filtering
            $allEvents = Event::all();
            
            // Get active events
            $activeEvents = Event::where('is_active', true)->get();
            
            // Get events with images
            $eventsWithImages = Event::whereNotNull('image_url')->get();
            
            return response()->json([
                'success' => true,
                'debug_info' => [
                    'total_events_in_db' => $allEvents->count(),
                    'active_events' => $activeEvents->count(),
                    'events_with_images' => $eventsWithImages->count(),
                    'all_events' => $allEvents->map(function($event) {
                        return [
                            'id' => $event->id,
                            'title' => $event->title,
                            'is_active' => $event->is_active,
                            'image_url' => $event->image_url,
                            'created_at' => $event->created_at,
                        ];
                    })
                ],
                'deployment_test' => [
                    'version' => '5.3',
                    'timestamp' => now()->toISOString(),
                    'message' => 'Debug endpoint for event image troubleshooting'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'debug_info' => [
                    'database_connection' => 'Failed to connect or query database'
                ]
            ], 500);
        }
    }
}