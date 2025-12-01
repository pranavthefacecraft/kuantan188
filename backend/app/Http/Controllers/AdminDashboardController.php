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
        
        // Get recent bookings
        $recentBookings = Booking::with(['ticket.event', 'ticket.country'])
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
        $events = Event::with(['tickets.country', 'tickets.bookings'])->paginate(15);
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
    public function bookings()
    {
        $bookings = Booking::with(['ticket.event', 'ticket.country'])
            ->latest()
            ->paginate(15);
        return view('admin.bookings', compact('bookings'));
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
            // Create the base ticket
            $ticket = Ticket::create([
                'ticket_name' => $request->ticket_name,
                'event_id' => $request->event_id,
                'total_quantity' => $request->total_quantity,
                'available_quantity' => $request->total_quantity, // Initially all tickets are available
                'description' => $request->description,
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

            // Update the base ticket
            $ticket->update([
                'ticket_name' => $request->ticket_name,
                'event_id' => $request->event_id,
                'total_quantity' => $request->total_quantity,
                'available_quantity' => $request->total_quantity, // Update available quantity if total changed
                'description' => $request->description,
                'is_active' => $request->is_active === '1'
            ]);

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
        $countries = Country::with(['tickets.bookings'])->paginate(15);
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
            \Artisan::call('reviews:sync', ['--force' => true]);
            $output = \Artisan::output();
            
            return redirect()->route('admin.reviews')
                ->with('success', 'Google Reviews sync completed successfully!')
                ->with('sync_output', $output);
                
        } catch (\Exception $e) {
            return redirect()->route('admin.reviews')
                ->with('error', 'Sync failed: ' . $e->getMessage());
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
}