<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent bookings with safer relationships
        $recentBookings = Booking::with(['event', 'country'])
            ->latest()
            ->take(10)
            ->get();

        // Get booking trends (last 30 days)
        $bookingTrends = $this->getBookingTrends();
        
        // Get event distribution
        $eventDistribution = $this->getEventDistribution();
        
        // Get revenue trends (last 12 months)
        $revenueTrends = $this->getRevenueTrends();

        return view('admin.dashboard', compact(
            'stats', 
            'recentBookings', 
            'bookingTrends', 
            'eventDistribution', 
            'revenueTrends'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        return [
            'totalBookings' => Booking::count(),
            'totalRevenue' => Booking::where('status', 'confirmed')->sum('total_amount'),
            'activeEvents' => Event::where('event_date', '>=', now())->count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'totalCustomers' => Booking::distinct('customer_email')->count(),
            'totalTickets' => Ticket::count(),
            'totalCountries' => Country::count(),
            'todayBookings' => Booking::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Get booking trends for the last 30 days
     */
    private function getBookingTrends()
    {
        $trends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'bookings' => Booking::whereDate('created_at', $date)->count(),
                'revenue' => Booking::whereDate('created_at', $date)
                    ->where('status', 'confirmed')
                    ->sum('total_amount')
            ];
        }
        return $trends;
    }

    /**
     * Get event distribution by country
     */
    private function getEventDistribution()
    {
        return DB::table('events')
            ->join('tickets', 'events.id', '=', 'tickets.event_id')
            ->join('countries', 'tickets.country_id', '=', 'countries.id')
            ->select('countries.name', DB::raw('count(*) as count'))
            ->groupBy('countries.id', 'countries.name')
            ->get();
    }

    /**
     * Get revenue trends for the last 12 months
     */
    private function getRevenueTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $trends[] = [
                'month' => $date->format('M Y'),
                'revenue' => Booking::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('status', 'confirmed')
                    ->sum('total_amount')
            ];
        }
        return $trends;
    }

    /**
     * Show events management
     */
    public function events()
    {
        $events = Event::with(['tickets.countries', 'tickets.bookings'])->paginate(15);
        return view('admin.events', compact('events'));
    }

    /**
     * Store a new event
     */
    public function storeEvent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'event_date' => 'required|date|after:now',
            'booking_start_date' => 'nullable|date',
            'booking_end_date' => 'nullable|date|after:booking_start_date',
            'event_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'is_active' => 'in:0,1'
        ]);

        try {
            $imageUrl = null;
            
            // Handle image upload
            if ($request->hasFile('event_image')) {
                $image = $request->file('event_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                
                // Create events directory if it doesn't exist
                $uploadPath = public_path('uploads/events');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Move uploaded file
                $image->move($uploadPath, $imageName);
                $imageUrl = 'uploads/events/' . $imageName;
                
                // Sync image to storage after upload
                $this->syncImageToStorage($imageName);
            }

            $event = Event::create([
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'adult_price' => $request->adult_price,
                'child_price' => $request->child_price,
                'event_date' => $request->event_date,
                'booking_start_date' => $request->booking_start_date,
                'booking_end_date' => $request->booking_end_date,
                'image_url' => $imageUrl,
                'is_active' => $request->is_active === '1'
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event created successfully!',
                    'event' => $event
                ]);
            }

            return redirect()->route('admin.events')->with('success', 'Event created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating event: ' . $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Error creating event: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show bookings management
     */
    public function bookings(Request $request)
    {
        try {
            // Use simpler query without problematic relationships initially
            $query = Booking::query();
            
            // Filter by booking type
            if ($request->filled('booking_type')) {
                if ($request->booking_type === 'event') {
                    $query->where(function($q) {
                        $q->whereNull('ticket_id')
                          ->orWhere('ticket_id', 0)
                          ->orWhere('ticket_id', '');
                    });
                } elseif ($request->booking_type === 'ticket') {
                    $query->whereNotNull('ticket_id')
                          ->where('ticket_id', '>', 0);
                }
            }
            
            // Filter by country
            if ($request->filled('country_filter')) {
                $query->where('country_id', $request->country_filter);
            }
            
            // Filter by status
            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            }
            
            // Get bookings without relationships first to isolate the issue
            $bookings = $query->latest()->paginate(20);
            
            // Try to load relationships safely
            try {
                $bookings->load(['event', 'country', 'ticket']);
            } catch (\Exception $relationError) {
                \Log::warning('Could not load booking relationships: ' . $relationError->getMessage());
                // Continue without relationships
            }
            
            // Get all countries for filter dropdown
            $countries = Country::orderBy('name')->get();
            
            return view('admin.bookings', compact('bookings', 'countries'));
        } catch (\Exception $e) {
            \Log::error('Error in admin bookings page: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return empty data to prevent 500 error
            $bookings = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $countries = collect([]);
            
            return view('admin.bookings', compact('bookings', 'countries'))
                ->with('error', 'There was an error loading bookings. Please check the logs.');
        }
    }

    /**
     * Get calendar events for bookings
     */
    public function calendarEvents(Request $request)
    {
        try {
            $start = Carbon::parse($request->start)->startOfDay();
            $end = Carbon::parse($request->end)->endOfDay();
            
            // Build query to get all bookings that should appear in the calendar date range
            // Priority: event_date > related_event.event_date > created_at
            $query = Booking::where(function($q) use ($start, $end) {
                // Primary: Bookings with event_date in range
                $q->where(function($subQ) use ($start, $end) {
                    $subQ->whereNotNull('event_date')
                         ->whereBetween('event_date', [$start, $end]);
                });
                
                // Secondary: Bookings with related event dates in range
                $q->orWhereHas('event', function($eventQ) use ($start, $end) {
                    $eventQ->whereBetween('event_date', [$start, $end]);
                });
                
                // Tertiary: Bookings created in range (fallback for tickets without event dates)
                $q->orWhere(function($subQ) use ($start, $end) {
                    $subQ->whereNull('event_date')
                         ->whereDoesntHave('event')
                         ->whereBetween('created_at', [$start, $end]);
                });
            });
            
            // Apply filters
            if ($request->filled('booking_type')) {
                if ($request->booking_type === 'event') {
                    $query->where(function($q) {
                        $q->whereNull('ticket_id')
                          ->orWhere('ticket_id', 0)
                          ->orWhere('ticket_id', '');
                    });
                } elseif ($request->booking_type === 'ticket') {
                    $query->whereNotNull('ticket_id')
                          ->where('ticket_id', '>', 0);
                }
            }
            
            if ($request->filled('country_filter')) {
                $query->where('country_id', $request->country_filter);
            }
            
            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            }
            
            // Load relationships for better data access
            $bookings = $query->with(['event', 'ticket', 'country'])->get();
            
            // Create calendar events - show bookings on their event date (preferred) or booking date (fallback)
            $events = [];
            
            foreach ($bookings as $booking) {
                $isTicketBooking = !empty($booking->ticket_id) && $booking->ticket_id > 0;
                
                // Determine the date to display the booking on calendar
                $displayDate = null;
                
                // Priority 1: Use event_date from booking record
                if ($booking->event_date) {
                    $displayDate = Carbon::parse($booking->event_date)->format('Y-m-d');
                }
                // Priority 2: Use event_date from related event
                elseif ($booking->event && $booking->event->event_date) {
                    $displayDate = Carbon::parse($booking->event->event_date)->format('Y-m-d');
                }
                // Priority 3: Fallback to booking creation date (for tickets without specific event dates)
                else {
                    $displayDate = $booking->created_at->format('Y-m-d');
                }
                
                $bookingDate = $displayDate;
                
                // Determine title based on booking type
                if ($isTicketBooking) {
                    $title = $booking->booking_reference . ' (Ticket)';
                    if ($booking->ticket && is_object($booking->ticket) && isset($booking->ticket->title)) {
                        $title = $booking->ticket->title . ' - ' . $booking->booking_reference;
                    } elseif ($booking->ticket && is_object($booking->ticket) && isset($booking->ticket->name)) {
                        $title = $booking->ticket->name . ' - ' . $booking->booking_reference;
                    }
                    $backgroundColor = $this->getTicketStatusColor($booking->status);
                } else {
                    $title = $booking->booking_reference . ' (Event)';
                    if ($booking->event_title) {
                        $title = $booking->event_title . ' - ' . $booking->booking_reference;
                    } elseif ($booking->event && is_object($booking->event) && isset($booking->event->title)) {
                        $title = $booking->event->title . ' - ' . $booking->booking_reference;
                    }
                    $backgroundColor = $this->getEventStatusColor($booking->status);
                }
                
                // Add booking event
                $events[] = [
                    'id' => $booking->id,
                    'title' => $title,
                    'start' => $bookingDate,
                    'allDay' => true,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $backgroundColor,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'customer_name' => $booking->customer_name,
                        'customer_email' => $booking->customer_email ?? $booking->email,
                        'status' => $booking->status ?? $booking->booking_status,
                        'total_amount' => $booking->total_amount,
                        'adult_quantity' => $booking->adult_quantity ?? $booking->quantity,
                        'child_quantity' => $booking->child_quantity ?? 0,
                        'booking_type' => $isTicketBooking ? 'Ticket' : 'Event',
                        'event_title' => $booking->event_title ?? ($booking->event ? $booking->event->title : 'N/A'),
                        'event_date' => $booking->event_date ? Carbon::parse($booking->event_date)->format('M j, Y') : 
                                      ($booking->event ? Carbon::parse($booking->event->event_date)->format('M j, Y') : 'N/A'),
                        'country' => $this->getCountryName($booking)
                    ]
                ];
                
                // For event bookings, also show on the actual event date if different
                if (!$isTicketBooking) {
                    $eventDate = null;
                    if ($booking->event_date) {
                        $eventDate = Carbon::parse($booking->event_date)->format('Y-m-d');
                    } elseif ($booking->event && $booking->event->event_date) {
                        $eventDate = Carbon::parse($booking->event->event_date)->format('Y-m-d');
                    }
                    
                    // Add event date entry if different from booking date
                    if ($eventDate && $eventDate !== $bookingDate) {
                        $eventTitle = ($booking->event_title ?? ($booking->event ? $booking->event->title : 'Event')) . ' (Event Day)';
                        
                        $events[] = [
                            'id' => $booking->id . '_event',
                            'title' => $eventTitle,
                            'start' => $eventDate,
                            'allDay' => true,
                            'backgroundColor' => '#17a2b8', // Different color for event day
                            'borderColor' => '#17a2b8',
                            'textColor' => '#ffffff',
                            'extendedProps' => array_merge($events[count($events) - 1]['extendedProps'], [
                                'is_event_day' => true
                            ])
                        ];
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'events' => $events,
                'total_bookings' => $bookings->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading calendar events: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error loading calendar events: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Safely get country name from booking
     */
    private function getCountryName($booking)
    {
        try {
            if ($booking->country && is_object($booking->country) && isset($booking->country->name)) {
                return $booking->country->name;
            } elseif (is_string($booking->country) && !empty($booking->country)) {
                return $booking->country;
            } else {
                return 'N/A';
            }
        } catch (\Exception $e) {
            \Log::warning('Error getting country name for booking ' . $booking->id . ': ' . $e->getMessage());
            return 'N/A';
        }
    }

    /**
     * Get color for event bookings based on status
     */
    private function getEventStatusColor($status)
    {
        switch ($status) {
            case 'confirmed':
                return '#28a745'; // Green for confirmed events
            case 'pending':
                return '#ffc107'; // Yellow for pending events
            case 'cancelled':
                return '#dc3545'; // Red for cancelled events
            default:
                return '#6c757d'; // Gray for unknown status
        }
    }

    /**
     * Get color for ticket bookings based on status
     */
    private function getTicketStatusColor($status)
    {
        switch ($status) {
            case 'confirmed':
                return '#007bff'; // Blue for confirmed tickets
            case 'pending':
                return '#fd7e14'; // Orange for pending tickets
            case 'cancelled':
                return '#6f42c1'; // Purple for cancelled tickets
            default:
                return '#6c757d'; // Gray for unknown status
        }
    }

    /**
     * Get booking summary for a specific date
     */
    public function dateSummary(Request $request)
    {
        try {
            $date = Carbon::parse($request->date);
            $startOfDay = $date->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            // Query for bookings that should appear on this date (prioritize event_date over created_at)
            $query = Booking::where(function($q) use ($startOfDay, $endOfDay) {
                // Primary: Bookings with event_date on this date
                $q->where(function($subQ) use ($startOfDay, $endOfDay) {
                    $subQ->whereNotNull('event_date')
                         ->whereBetween('event_date', [$startOfDay, $endOfDay]);
                });
                
                // Secondary: Bookings with related event dates on this date  
                $q->orWhereHas('event', function($eventQ) use ($startOfDay, $endOfDay) {
                    $eventQ->whereBetween('event_date', [$startOfDay, $endOfDay]);
                });
                
                // Tertiary: Bookings created on this date (fallback for tickets without event dates)
                $q->orWhere(function($subQ) use ($startOfDay, $endOfDay) {
                    $subQ->whereNull('event_date')
                         ->whereDoesntHave('event')  
                         ->whereBetween('created_at', [$startOfDay, $endOfDay]);
                });
            });
            
            // Apply filters
            if ($request->filled('booking_type')) {
                if ($request->booking_type === 'event') {
                    $query->where(function($q) {
                        $q->whereNull('ticket_id')
                          ->orWhere('ticket_id', 0)
                          ->orWhere('ticket_id', '');
                    });
                } elseif ($request->booking_type === 'ticket') {
                    $query->whereNotNull('ticket_id')
                          ->where('ticket_id', '>', 0);
                }
            }
            
            if ($request->filled('country_filter')) {
                $query->where('country_id', $request->country_filter);
            }
            
            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            }
            
            $bookings = $query->get();
            
            $summary = [
                'total' => $bookings->count(),
                'event_confirmed' => 0,
                'event_pending' => 0,
                'event_cancelled' => 0,
                'ticket_confirmed' => 0,
                'ticket_pending' => 0,
                'ticket_cancelled' => 0,
            ];
            
            foreach ($bookings as $booking) {
                $type = (!empty($booking->ticket_id) && $booking->ticket_id !== null && $booking->ticket_id > 0) ? 'ticket' : 'event';
                $status = $booking->status ?? $booking->booking_status ?? 'pending';
                
                $key = $type . '_' . $status;
                if (isset($summary[$key])) {
                    $summary[$key]++;
                }
            }
            
            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading date summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading date summary'
            ]);
        }
    }

    /**
     * Get detailed bookings for a specific date
     */
    public function dateDetails(Request $request)
    {
        try {
            $date = Carbon::parse($request->date);
            $startOfDay = $date->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            // Get all bookings that should appear on this date (prioritize event_date)
            $query = Booking::with(['event', 'country', 'ticket'])
                ->where(function($q) use ($startOfDay, $endOfDay) {
                    // Primary: Bookings with event_date on this date
                    $q->where(function($subQ) use ($startOfDay, $endOfDay) {
                        $subQ->whereNotNull('event_date')
                             ->whereBetween('event_date', [$startOfDay, $endOfDay]);
                    });
                    
                    // Secondary: Bookings with related event dates on this date
                    $q->orWhereHas('event', function($eventQ) use ($startOfDay, $endOfDay) {
                        $eventQ->whereBetween('event_date', [$startOfDay, $endOfDay]);
                    });
                    
                    // Tertiary: Bookings created on this date (fallback for tickets without event dates)
                    $q->orWhere(function($subQ) use ($startOfDay, $endOfDay) {
                        $subQ->whereNull('event_date')
                             ->whereDoesntHave('event')
                             ->whereBetween('created_at', [$startOfDay, $endOfDay]);
                    });
                });
            
            // Apply filters
            if ($request->filled('booking_type')) {
                if ($request->booking_type === 'event') {
                    $query->where(function($q) {
                        $q->whereNull('ticket_id')
                          ->orWhere('ticket_id', 0)
                          ->orWhere('ticket_id', '');
                    });
                } elseif ($request->booking_type === 'ticket') {
                    $query->whereNotNull('ticket_id')
                          ->where('ticket_id', '>', 0);
                }
            }
            
            if ($request->filled('country_filter')) {
                $query->where('country_id', $request->country_filter);
            }
            
            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            }
            
            $bookings = $query->orderBy('created_at', 'desc')->get();
            
            $bookingData = $bookings->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'customer_name' => $booking->customer_name,
                    'customer_email' => $booking->customer_email ?? $booking->email,
                    'customer_phone' => $booking->customer_phone ?? $booking->mobile_phone,
                    'event_title' => optional($booking->event)->title ?? $booking->event_title ?? 'N/A',
                    'adult_quantity' => $booking->adult_quantity ?? $booking->quantity ?? 0,
                    'child_quantity' => $booking->child_quantity ?? 0,
                    'total_amount' => $booking->total_amount,
                    'status' => $booking->status ?? $booking->booking_status,
                    'type' => (!empty($booking->ticket_id) && $booking->ticket_id !== null && $booking->ticket_id > 0) ? 'Ticket' : 'Event',
                    'country' => $this->getCountryName($booking),
                    'created_at' => $booking->created_at->format('H:i'),
                    'event_date' => $booking->event_date ? Carbon::parse($booking->event_date)->format('M j, Y') : 
                                   (optional($booking->event)->event_date ? Carbon::parse($booking->event->event_date)->format('M j, Y') : 'N/A'),
                ];
            });
            
            return response()->json([
                'success' => true,
                'bookings' => $bookingData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading date details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading date details'
            ]);
        }
    }

    /**
     * Get calendar statistics
     */
    public function calendarStats(Request $request)
    {
        try {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();
            $today = $now->copy()->startOfDay();
            
            // Base queries with filters
            $baseQuery = Booking::query();
            
            if ($request->filled('booking_type')) {
                if ($request->booking_type === 'event') {
                    $baseQuery->where(function($q) {
                        $q->whereNull('ticket_id')
                          ->orWhere('ticket_id', 0)
                          ->orWhere('ticket_id', '');
                    });
                } elseif ($request->booking_type === 'ticket') {
                    $baseQuery->whereNotNull('ticket_id')
                          ->where('ticket_id', '>', 0);
                }
            }
            
            if ($request->filled('country_filter')) {
                $baseQuery->where('country_id', $request->country_filter);
            }
            
            if ($request->filled('status_filter')) {
                $baseQuery->where('status', $request->status_filter);
            }
            
            $stats = [
                'monthly_bookings' => (clone $baseQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'monthly_revenue' => (clone $baseQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->where('status', 'confirmed')->sum('total_amount'),
                'pending_bookings' => (clone $baseQuery)->where('status', 'pending')->count(),
                'today_bookings' => (clone $baseQuery)->whereDate('created_at', $today)->count()
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading calendar stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading calendar stats'
            ]);
        }
    }

    /**
     * Show tickets management
     */
    public function tickets()
    {
        $tickets = Ticket::with(['event', 'countries', 'bookings'])->orderBy('created_at', 'desc')->paginate(15);
        $events = Event::where('is_active', true)->orderBy('event_date')->get();
        $countries = Country::orderBy('name')->get();
        
        return view('admin.tickets', compact('tickets', 'events', 'countries'));
    }

    /**
     * Store a new ticket
     */
    public function storeTicket(Request $request)
    {
        $request->validate([
            'ticket_name' => 'required|string|max:255',
            'event_id' => 'nullable|exists:events,id',
            'countries' => 'required|array|min:1',
            'countries.*' => 'exists:countries,id',
            'countries_data' => 'required|array',
            'countries_data.*.adult_price' => 'required|numeric|min:0|max:999999.99',
            'countries_data.*.child_price' => 'required|numeric|min:0|max:999999.99',
            'total_quantity' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'in:0,1'
        ]);

        try {
            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('ticket_image')) {
                $image = $request->file('ticket_image');
                $imageName = 'ticket_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('storage/tickets'), $imageName);
                $imageUrl = 'storage/tickets/' . $imageName;
            }

            // Create the base ticket
            $ticket = Ticket::create([
                'ticket_name' => $request->ticket_name,
                'event_id' => $request->event_id,
                'total_quantity' => $request->total_quantity,
                'available_quantity' => $request->total_quantity, // Initially all tickets are available
                'description' => $request->description,
                'image_url' => $imageUrl,
                'is_active' => $request->is_active === '1'
            ]);

            // Attach countries with their respective prices
            $countriesData = [];
            foreach ($request->countries as $index => $countryId) {
                $countriesData[$countryId] = [
                    'adult_price' => $request->countries_data[$index]['adult_price'],
                    'child_price' => $request->countries_data[$index]['child_price']
                ];
            }
            
            $ticket->countries()->attach($countriesData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket created successfully!',
                    'ticket' => $ticket
                ]);
            }

            return redirect()->route('admin.tickets')->with('success', 'Ticket created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating ticket: ' . $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Error creating ticket: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Get ticket data for editing
     */
    public function editTicket(Ticket $ticket)
    {
        try {
            $ticket->load(['countries', 'event']);
            return response()->json([
                'success' => true,
                'ticket' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading ticket: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing ticket
     */
    public function updateTicket(Request $request, Ticket $ticket)
    {
        $request->validate([
            'ticket_name' => 'required|string|max:255',
            'event_id' => 'nullable|exists:events,id',
            'countries' => 'required|array|min:1',
            'countries.*' => 'exists:countries,id',
            'countries_data' => 'required|array',
            'countries_data.*.adult_price' => 'required|numeric|min:0|max:999999.99',
            'countries_data.*.child_price' => 'required|numeric|min:0|max:999999.99',
            'total_quantity' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'in:0,1'
        ]);

        try {
            // Log the incoming data for debugging
            \Log::info('Update ticket request data:', [
                'countries' => $request->countries,
                'countries_data' => $request->countries_data
            ]);

            // Handle image upload
            $updateData = [
                'ticket_name' => $request->ticket_name,
                'event_id' => $request->event_id,
                'total_quantity' => $request->total_quantity,
                'available_quantity' => $request->total_quantity, // Update available quantity if total changed
                'description' => $request->description,
                'is_active' => $request->is_active === '1'
            ];

            // Process image upload if provided
            if ($request->hasFile('ticket_image')) {
                // Delete old image if it exists
                if ($ticket->image_url) {
                    $oldImagePath = public_path($ticket->image_url);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Upload new image
                $image = $request->file('ticket_image');
                $imageName = 'ticket_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('storage/tickets'), $imageName);
                $updateData['image_url'] = 'storage/tickets/' . $imageName;
            }

            // Update the base ticket
            $ticket->update($updateData);

            // Sync countries with their respective prices
            $countriesData = [];
            $countriesArray = $request->countries ?? [];
            $countriesDataArray = $request->countries_data ?? [];
            
            // Ensure both arrays exist and are arrays
            if (!is_array($countriesArray)) {
                throw new \Exception('Countries must be an array');
            }
            
            if (!is_array($countriesDataArray)) {
                throw new \Exception('Countries data must be an array');
            }

            // Reindex both arrays to ensure sequential indexing
            $countriesArray = array_values($countriesArray);
            $countriesDataArray = array_values($countriesDataArray);

            // Validate that we have the same number of countries and pricing data
            if (count($countriesArray) !== count($countriesDataArray)) {
                throw new \Exception('Mismatch between number of countries and pricing data');
            }

            foreach ($countriesArray as $index => $countryId) {
                // Ensure the index exists in countries_data
                if (isset($countriesDataArray[$index]) && 
                    isset($countriesDataArray[$index]['adult_price']) && 
                    isset($countriesDataArray[$index]['child_price'])) {
                    
                    $countriesData[$countryId] = [
                        'adult_price' => $countriesDataArray[$index]['adult_price'],
                        'child_price' => $countriesDataArray[$index]['child_price']
                    ];
                } else {
                    throw new \Exception("Missing price data for country at index {$index}. Available keys: " . implode(', ', array_keys($countriesDataArray[$index] ?? [])));
                }
            }
            
            \Log::info('Processed countries data:', $countriesData);
            $ticket->countries()->sync($countriesData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket updated successfully!',
                    'ticket' => $ticket->load(['countries', 'event'])
                ]);
            }

            return redirect()->route('admin.tickets')->with('success', 'Ticket updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating ticket: ' . $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Error updating ticket: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show countries management
     */
    public function countries()
    {
        $countries = Country::with('tickets')->paginate(15);
        return view('admin.countries', compact('countries'));
    }

    /**
     * Get event data for editing
     */
    public function editEvent(Event $event)
    {
        try {
            return response()->json([
                'success' => true,
                'event' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading event: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing event
     */
    public function updateEvent(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'event_date' => 'required|date',
            'booking_start_date' => 'nullable|date',
            'booking_end_date' => 'nullable|date|after:booking_start_date',
            'event_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'is_active' => 'boolean'
        ]);

        try {
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'price' => $request->price,
                'event_date' => $request->event_date,
                'booking_start_date' => $request->booking_start_date,
                'booking_end_date' => $request->booking_end_date,
                'is_active' => $request->is_active === '1'
            ];

            // Handle image upload if new image is provided
            if ($request->hasFile('event_image')) {
                // Delete old image if it exists
                if ($event->image_url && file_exists(public_path($event->image_url))) {
                    unlink(public_path($event->image_url));
                }

                $image = $request->file('event_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                
                // Create events directory if it doesn't exist
                $uploadPath = public_path('uploads/events');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Move uploaded file
                $image->move($uploadPath, $imageName);
                $updateData['image_url'] = 'uploads/events/' . $imageName;
                
                // Sync image to storage after upload
                $this->syncImageToStorage($imageName);
            }

            $event->update($updateData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event updated successfully!',
                    'event' => $event->fresh()
                ]);
            }

            return redirect()->route('admin.events')->with('success', 'Event updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating event: ' . $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Error updating event: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle event status (active/inactive)
     */
    public function toggleEventStatus(Event $event)
    {
        try {
            $event->is_active = !$event->is_active;
            $event->save();

            $status = $event->is_active ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "Event {$status} successfully!",
                'is_active' => $event->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling event status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a ticket
     */
    public function destroyTicket(Ticket $ticket)
    {
        try {
            // Check if there are any bookings for this ticket
            $bookingCount = $ticket->bookings()->count();
            
            if ($bookingCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete ticket. It has {$bookingCount} existing bookings."
                ], 400);
            }
            
            // Delete the ticket (this will also delete pivot table entries due to cascading)
            $ticket->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Ticket deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete tickets
     */
    public function bulkDeleteTickets(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:tickets,id'
        ]);

        try {
            $ticketIds = $request->ticket_ids;
            $tickets = Ticket::whereIn('id', $ticketIds)->get();
            
            // Check for tickets with bookings
            $ticketsWithBookings = [];
            $ticketsToDelete = [];
            
            foreach ($tickets as $ticket) {
                $bookingCount = $ticket->bookings()->count();
                if ($bookingCount > 0) {
                    $ticketsWithBookings[] = [
                        'name' => $ticket->ticket_name,
                        'bookings' => $bookingCount
                    ];
                } else {
                    $ticketsToDelete[] = $ticket->id;
                }
            }
            
            // Delete tickets without bookings
            $deletedCount = 0;
            if (!empty($ticketsToDelete)) {
                $deletedCount = Ticket::whereIn('id', $ticketsToDelete)->delete();
            }
            
            // Prepare response message
            $message = '';
            if ($deletedCount > 0) {
                $message .= "{$deletedCount} ticket(s) deleted successfully. ";
            }
            
            if (!empty($ticketsWithBookings)) {
                $skippedNames = array_column($ticketsWithBookings, 'name');
                $message .= count($ticketsWithBookings) . " ticket(s) could not be deleted because they have existing bookings: " . implode(', ', $skippedNames);
            }
            
            return response()->json([
                'success' => true,
                'message' => trim($message),
                'deleted_count' => $deletedCount,
                'skipped_count' => count($ticketsWithBookings)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync uploaded image to storage directory
     */
    private function syncImageToStorage($filename)
    {
        try {
            $sourceFile = public_path('uploads/events/' . $filename);
            $storagePath = storage_path('app/public/uploads/events');
            $destinationFile = $storagePath . '/' . $filename;
            
            // Ensure storage directory exists
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            // Copy file to storage
            if (file_exists($sourceFile)) {
                copy($sourceFile, $destinationFile);
                \Log::info('Image synced to storage: ' . $filename);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync image to storage: ' . $e->getMessage());
        }
    }

    /**
     * Show Google Reviews management page
     */
    public function reviews()
    {
        $reviews = \App\Models\GoogleReview::latest()->paginate(20);
        $stats = [
            'total_reviews' => \App\Models\GoogleReview::count(),
            'active_reviews' => \App\Models\GoogleReview::where('is_active', true)->count(),
            'average_rating' => \App\Models\GoogleReview::where('is_active', true)->avg('rating'),
            'latest_sync' => \App\Models\GoogleReview::latest()->first()?->created_at,
        ];
        
        return view('admin.reviews', compact('reviews', 'stats'));
    }

    /**
     * Sync Google Reviews from API
     */
    public function syncGoogleReviews()
    {
        try {
            // Call the artisan command to sync reviews
            $exitCode = \Artisan::call('reviews:sync', ['--force' => true]);
            $output = \Artisan::output();
            
            if ($exitCode === 0) {
                return redirect()->route('admin.reviews')
                    ->with('success', 'Google Reviews sync completed successfully!')
                    ->with('sync_output', $output);
            } else {
                return redirect()->route('admin.reviews')
                    ->with('error', 'Google Reviews sync failed. Check the output below for details.')
                    ->with('sync_output', $output);
            }
                
        } catch (\Exception $e) {
            \Log::error('Admin sync error: ' . $e->getMessage());
            
            return redirect()->route('admin.reviews')
                ->with('error', 'Sync failed with exception: ' . $e->getMessage())
                ->with('sync_output', 'Exception occurred: ' . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString());
        }
    }

    /**
     * Toggle review status (active/inactive)
     */
    public function toggleReviewStatus(\App\Models\GoogleReview $review)
    {
        $review->is_active = !$review->is_active;
        $review->save();

        $status = $review->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.reviews')
            ->with('success', "Review by {$review->author_name} has been {$status}.");
    }

    /**
     * Get detailed information for a specific booking
     */
    public function getBookingDetails($bookingId)
    {
        try {
            $booking = Booking::with(['event', 'ticket', 'country'])
                ->findOrFail($bookingId);

            // Get country name safely
            $countryName = null;
            if ($booking->country) {
                $countryName = is_object($booking->country) ? $booking->country->name : $booking->country;
            }

            // Get event/ticket information
            $eventTitle = null;
            $ticketName = null;
            $eventDate = null;

            if ($booking->event) {
                $eventTitle = $booking->event->title;
                $eventDate = $booking->event->event_date;
            }

            if ($booking->ticket) {
                $ticketName = is_object($booking->ticket) ? $booking->ticket->title : $booking->ticket;
            }

            // Use event_date from booking if available, otherwise from event
            if ($booking->event_date) {
                $eventDate = $booking->event_date;
            }

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'customer_name' => $booking->customer_name,
                    'customer_email' => $booking->customer_email,
                    'customer_phone' => $booking->customer_phone,
                    'event_title' => $eventTitle ?: $booking->event_title,
                    'ticket_name' => $ticketName,
                    'event_date' => $eventDate,
                    'adult_quantity' => $booking->adult_quantity,
                    'child_quantity' => $booking->child_quantity,
                    'adult_price' => $booking->adult_price,
                    'child_price' => $booking->child_price,
                    'total_amount' => $booking->total_amount,
                    'status' => $booking->status,
                    'country_name' => $countryName,
                    'country' => $booking->country,
                    'ticket_id' => $booking->ticket_id,
                    'event_id' => $booking->event_id,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading booking details: ' . $e->getMessage()
            ]);
        }
    }
}