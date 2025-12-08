@extends('layouts.admin')

@section('title', 'Bookings Management')

@section('content')
<div class="grid">
    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif
    
    <!-- Header Actions -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Bookings Management</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">View and manage customer bookings</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <!-- View Toggle -->
                <div class="view-toggle" style="display: flex; background: var(--surface-variant); border-radius: 0.5rem; padding: 0.25rem;">
                    <button id="tableViewBtn" class="view-btn active" onclick="switchView('table')" style="padding: 0.5rem 1rem; border: none; background: none; border-radius: 0.25rem; cursor: pointer; transition: all 0.2s ease;">
                        <span class="material-icons" style="font-size: 18px; margin-right: 0.5rem;">table_view</span>
                        Table View
                    </button>
                    <button id="calendarViewBtn" class="view-btn" onclick="switchView('calendar')" style="padding: 0.5rem 1rem; border: none; background: none; border-radius: 0.25rem; cursor: pointer; transition: all 0.2s ease;">
                        <span class="material-icons" style="font-size: 18px; margin-right: 0.5rem;">calendar_view_month</span>
                        Calendar View
                    </button>
                </div>

                <!-- Filters -->
                <form method="GET" id="filtersForm" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <select name="booking_type" id="booking_type_filter" class="btn btn-outline" style="padding: 0.5rem 1rem;" onchange="updateFilters()">
                        <option value="">All Booking Types</option>
                        <option value="event" {{ request('booking_type') === 'event' ? 'selected' : '' }}>Event Bookings</option>
                        <option value="ticket" {{ request('booking_type') === 'ticket' ? 'selected' : '' }}>Ticket Bookings</option>
                    </select>
                    <select name="country_filter" id="country_filter" class="btn btn-outline" style="padding: 0.5rem 1rem;" onchange="updateFilters()">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ request('country_filter') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status_filter" id="status_filter" class="btn btn-outline" style="padding: 0.5rem 1rem;" onchange="updateFilters()">
                        <option value="">All Statuses</option>
                        <option value="confirmed" {{ request('status_filter') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status_filter') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status_filter') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @if(request()->hasAny(['booking_type', 'country_filter', 'status_filter']))
                        <a href="{{ route('admin.bookings') }}" class="btn btn-outline" style="padding: 0.5rem 1rem;" onclick="clearFilters()">
                            <span class="material-icons" style="font-size: 18px;">clear</span>
                            Clear
                        </a>
                    @endif
                </form>
                <button class="btn btn-outline">
                    <span class="material-icons" style="font-size: 18px;">download</span>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div id="tableView" class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking Reference</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Event</th>
                            <th>Country</th>
                            <th>Tickets</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--primary);">
                                        {{ $booking->booking_reference }}
                                    </div>
                                </td>
                                <td>
                                    @if(!empty($booking->ticket_id) && $booking->ticket_id !== null && $booking->ticket_id > 0)
                                        <span class="badge badge-info">
                                            <span class="material-icons" style="font-size: 12px;">confirmation_number</span>
                                            Ticket
                                        </span>
                                    @else
                                        <span class="badge badge-primary">
                                            <span class="material-icons" style="font-size: 12px;">event</span>
                                            Event
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: 500;">{{ $booking->customer_name }}</div>
                                        <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                            {{ $booking->customer_email }}
                                        </div>
                                        @if($booking->customer_phone)
                                            <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                                {{ $booking->customer_phone }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: 500;">{{ optional($booking->event)->title ?? $booking->event_title ?? 'N/A' }}</div>
                                        <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                            {{ optional($booking->event)->event_date ? optional($booking->event)->event_date->format('M j, Y') : ($booking->event_date ?? 'N/A') }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--accent);">public</span>
                                        {{ optional($booking->country)->name ?? ($booking->country ?? 'N/A') }}
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div>{{ $booking->adult_quantity }} Adults</div>
                                        @if($booking->child_quantity > 0)
                                            <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                                {{ $booking->child_quantity }} Children
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--success);">
                                        RM {{ number_format($booking->total_amount, 2) }}
                                    </div>
                                    @if($booking->adult_quantity > 0 && $booking->child_quantity > 0)
                                        <div style="font-size: 0.75rem; color: var(--on-surface-variant);">
                                            A: RM{{ number_format(optional($booking->ticket)->adult_price ?? $booking->adult_price ?? 0, 2) }} × {{ $booking->adult_quantity }}<br>
                                            C: RM{{ number_format(optional($booking->ticket)->child_price ?? $booking->child_price ?? 0, 2) }} × {{ $booking->child_quantity }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status === 'confirmed')
                                        <span class="badge badge-success">
                                            <span class="material-icons" style="font-size: 12px;">check_circle</span>
                                            Confirmed
                                        </span>
                                    @elseif($booking->status === 'pending')
                                        <span class="badge badge-warning">
                                            <span class="material-icons" style="font-size: 12px;">schedule</span>
                                            Pending
                                        </span>
                                    @else
                                        <span class="badge badge-error">
                                            <span class="material-icons" style="font-size: 12px;">cancel</span>
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $booking->created_at->format('M j, Y') }}</div>
                                    <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                        {{ $booking->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        @if($booking->status === 'pending')
                                            <button class="btn btn-outline" 
                                                    style="padding: 0.25rem 0.5rem; background: var(--success); color: white;"
                                                    onclick="updateBookingStatus('{{ $booking->id }}', 'confirmed')">
                                                <span class="material-icons" style="font-size: 16px;">check</span>
                                            </button>
                                        @endif
                                        <a href="#" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <span class="material-icons" style="font-size: 16px;">visibility</span>
                                        </a>
                                        <a href="#" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <span class="material-icons" style="font-size: 16px;">email</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 3rem; color: var(--on-surface-variant);">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                        <span class="material-icons" style="font-size: 48px; opacity: 0.3;">book_online</span>
                                        <div>
                                            <div style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">No bookings found</div>
                                            <div>Bookings will appear here when customers make purchases</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $bookings->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Calendar View -->
    <div id="calendarView" class="card" style="display: none;">
        <div class="card-body">
            <div id="calendar" style="height: 600px;"></div>
            
            <!-- Calendar Stats -->
            <div class="calendar-stats" style="margin-top: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="stat-card" style="background: var(--surface-variant); padding: 1rem; border-radius: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span class="material-icons" style="color: var(--primary); font-size: 20px;">event</span>
                        <span style="font-size: 0.875rem; color: var(--on-surface-variant);">This Month</span>
                    </div>
                    <div id="monthlyBookings" style="font-size: 1.5rem; font-weight: 600; color: var(--on-surface);">-</div>
                    <div style="font-size: 0.75rem; color: var(--on-surface-variant);">Total Bookings</div>
                </div>
                
                <div class="stat-card" style="background: var(--surface-variant); padding: 1rem; border-radius: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span class="material-icons" style="color: var(--success); font-size: 20px;">payments</span>
                        <span style="font-size: 0.875rem; color: var(--on-surface-variant);">This Month</span>
                    </div>
                    <div id="monthlyRevenue" style="font-size: 1.5rem; font-weight: 600; color: var(--on-surface);">-</div>
                    <div style="font-size: 0.75rem; color: var(--on-surface-variant);">Total Revenue</div>
                </div>
                
                <div class="stat-card" style="background: var(--surface-variant); padding: 1rem; border-radius: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span class="material-icons" style="color: var(--warning); font-size: 20px;">schedule</span>
                        <span style="font-size: 0.875rem; color: var(--on-surface-variant);">Pending</span>
                    </div>
                    <div id="pendingBookings" style="font-size: 1.5rem; font-weight: 600; color: var(--on-surface);">-</div>
                    <div style="font-size: 0.75rem; color: var(--on-surface-variant);">Need Review</div>
                </div>
                
                <div class="stat-card" style="background: var(--surface-variant); padding: 1rem; border-radius: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span class="material-icons" style="color: var(--accent); font-size: 20px;">today</span>
                        <span style="font-size: 0.875rem; color: var(--on-surface-variant);">Today</span>
                    </div>
                    <div id="todayBookings" style="font-size: 1.5rem; font-weight: 600; color: var(--on-surface);">-</div>
                    <div style="font-size: 0.75rem; color: var(--on-surface-variant);">New Bookings</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div id="bookingDetailsModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeBookingDetailsModal()"></div>
        <div class="modal-container" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">Bookings for <span id="modalDate"></span></h3>
                <button type="button" class="modal-close" onclick="closeBookingDetailsModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <div class="modal-body">
                <div id="modalBookingsList" style="max-height: 400px; overflow-y: auto;">
                    <!-- Bookings will be loaded here -->
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeBookingDetailsModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<style>
    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
    
    .badge-primary {
        background-color: #007bff;
        color: white;
    }

    /* View Toggle Styles */
    .view-toggle .view-btn.active {
        background: var(--primary) !important;
        color: white !important;
    }

    .view-toggle .view-btn:hover {
        background: rgba(var(--primary-rgb), 0.1) !important;
    }

    /* Calendar Styles */
    .fc {
        height: 100% !important;
    }

    .fc-toolbar {
        margin-bottom: 1.5rem !important;
    }

    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        color: var(--on-surface) !important;
    }

    .fc-button {
        background: var(--primary) !important;
        border: 1px solid var(--primary) !important;
        color: white !important;
        border-radius: 0.5rem !important;
        padding: 0.5rem 1rem !important;
        font-weight: 500 !important;
    }

    .fc-button:hover {
        background: var(--primary-dark) !important;
        border-color: var(--primary-dark) !important;
    }

    .fc-button:focus {
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.3) !important;
    }

    .fc-daygrid-day {
        position: relative;
    }

    .fc-daygrid-day-number {
        color: var(--on-surface) !important;
        font-weight: 500;
        padding: 0.5rem;
    }

    .fc-day-today {
        background-color: rgba(var(--primary-rgb), 0.1) !important;
    }

    .fc-day-today .fc-daygrid-day-number {
        background: var(--primary);
        color: white;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0.25rem auto;
    }

    /* Booking Dots */
    .booking-dots {
        position: absolute;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 2px;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 90%;
    }

    .booking-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .booking-dot.event {
        background-color: var(--primary);
    }

    .booking-dot.ticket {
        background-color: var(--accent);
    }

    .booking-dot.confirmed {
        background-color: var(--success);
    }

    .booking-dot.pending {
        background-color: var(--warning);
    }

    .booking-dot.cancelled {
        background-color: var(--error);
    }

    /* Day Cell Hover */
    .fc-daygrid-day:hover {
        background-color: rgba(var(--primary-rgb), 0.05);
        cursor: pointer;
    }

    .fc-daygrid-day.has-bookings:hover {
        background-color: rgba(var(--primary-rgb), 0.1);
    }

    /* Calendar Stats */
    .calendar-stats .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    /* Modal Styles for Calendar */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-container {
        position: relative;
        background: var(--surface);
        border-radius: 1rem;
        box-shadow: var(--shadow-lg);
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow: hidden;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface-variant);
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--on-surface);
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        color: var(--on-surface-variant);
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        background: var(--border);
        color: var(--on-surface);
    }

    .modal-body {
        padding: 2rem;
        max-height: 60vh;
        overflow-y: auto;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--border);
        background: var(--surface-variant);
    }

    /* Booking Item in Modal */
    .booking-item {
        padding: 1rem;
        border: 1px solid var(--border);
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        background: var(--surface-variant);
        transition: all 0.2s ease;
    }

    .booking-item:hover {
        background: var(--surface);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .view-toggle {
            flex-direction: column !important;
            width: 100%;
        }

        .calendar-stats {
            grid-template-columns: 1fr 1fr !important;
        }

        .fc-toolbar {
            flex-direction: column !important;
            gap: 1rem !important;
        }
    }

    @media (max-width: 480px) {
        .calendar-stats {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection

@section('scripts')
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<!-- Fallback CDN -->
<script>
    if (typeof FullCalendar === 'undefined') {
        console.log('Primary FullCalendar CDN failed, loading fallback...');
        document.write('<script src="https://unpkg.com/fullcalendar@5.11.5/main.min.js"><\/script>');
    }
</script>
<script>
    let calendar;
    let currentView = 'table';
    let currentFilters = {
        booking_type: '{{ request('booking_type') ?? '' }}',
        country_filter: '{{ request('country_filter') ?? '' }}',
        status_filter: '{{ request('status_filter') ?? '' }}'
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Wait for FullCalendar to load with multiple checks
        let checkCount = 0;
        const maxChecks = 10; // 5 seconds maximum
        
        function checkForFullCalendar() {
            checkCount++;
            
            if (typeof FullCalendar !== 'undefined') {
                initializeCalendar();
                updateCalendarStats();
            } else if (checkCount < maxChecks) {
                setTimeout(checkForFullCalendar, 500);
            } else {
                console.error('FullCalendar failed to load after ' + (maxChecks * 0.5) + ' seconds');
            }
        }
        
        // Start checking immediately
        checkForFullCalendar();
    });

    function switchView(view) {
        currentView = view;
        
        const tableView = document.getElementById('tableView');
        const calendarView = document.getElementById('calendarView');
        const tableBtn = document.getElementById('tableViewBtn');
        const calendarBtn = document.getElementById('calendarViewBtn');
        
        console.log('Elements found:', {
            tableView: !!tableView,
            calendarView: !!calendarView,
            tableBtn: !!tableBtn,
            calendarBtn: !!calendarBtn
        });
        
        if (view === 'table') {
            if (tableView) tableView.style.display = 'block';
            if (calendarView) calendarView.style.display = 'none';
            if (tableBtn) tableBtn.classList.add('active');
            if (calendarBtn) calendarBtn.classList.remove('active');
        } else {
            if (tableView) tableView.style.display = 'none';
            if (calendarView) {
                calendarView.style.display = 'block';
            }
            if (tableBtn) tableBtn.classList.remove('active');
            if (calendarBtn) calendarBtn.classList.add('active');
            
            // Initialize or refresh calendar
            if (!calendar) {
                initializeCalendar();
            } else {
                setTimeout(() => {
                    calendar.render();
                    calendar.refetchEvents();
                }, 100);
            }
        }
    }

    function updateFilters() {
        const bookingType = document.getElementById('booking_type_filter').value;
        const country = document.getElementById('country_filter').value;
        const status = document.getElementById('status_filter').value;

        currentFilters = {
            booking_type: bookingType,
            country_filter: country,
            status_filter: status
        };

        if (currentView === 'table') {
            // Submit form for table view
            document.getElementById('filtersForm').submit();
        } else {
            // Update calendar for calendar view
            if (calendar) {
                calendar.refetchEvents();
            }
            updateCalendarStats();
        }
    }

    function clearFilters() {
        currentFilters = {
            booking_type: '',
            country_filter: '',
            status_filter: ''
        };
        
        document.getElementById('booking_type_filter').value = '';
        document.getElementById('country_filter').value = '';
        document.getElementById('status_filter').value = '';

        if (currentView === 'calendar') {
            if (calendar) {
                calendar.refetchEvents();
            }
            updateCalendarStats();
        }
    }

    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        if (!calendarEl) {
            console.error('Calendar element not found!');
            return;
        }
        
        try {
            calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                const params = new URLSearchParams({
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr,
                    ...currentFilters
                });

                fetch(`/admin/api/bookings/calendar?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successCallback(data.events);
                        } else {
                            failureCallback(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading calendar events:', error);
                        failureCallback(error.message);
                    });
            },
            dayCellDidMount: function(info) {
                // Add booking dots to calendar days
                addBookingDots(info);
            },
            dateClick: function(info) {
                showBookingsForDate(info.dateStr);
            },
            eventDidMount: function(info) {
                // Hide default events as we show dots instead
                info.el.style.display = 'none';
            }
        });

        try {
            calendar.render();
        } catch (error) {
            console.error('Error rendering calendar:', error);
        }
        } catch (calendarError) {
            console.error('Error creating calendar:', calendarError);
        }
    }

    function addBookingDots(info) {
        const date = info.date.toISOString().split('T')[0];
        
        // Fetch booking summary for this date
        const params = new URLSearchParams({
            date: date,
            ...currentFilters
        });

        fetch(`/admin/api/bookings/date-summary?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.summary.total > 0) {
                    const cell = info.el;
                    cell.classList.add('has-bookings');
                    
                    let dotsContainer = cell.querySelector('.booking-dots');
                    if (!dotsContainer) {
                        dotsContainer = document.createElement('div');
                        dotsContainer.className = 'booking-dots';
                        cell.appendChild(dotsContainer);
                    }

                    dotsContainer.innerHTML = '';

                    const summary = data.summary;
                    const maxDots = 8; // Maximum dots to show
                    let dotsShown = 0;

                    // Add dots based on booking types and statuses
                    ['confirmed', 'pending', 'cancelled'].forEach(status => {
                        ['event', 'ticket'].forEach(type => {
                            const count = summary[`${type}_${status}`] || 0;
                            for (let i = 0; i < Math.min(count, maxDots - dotsShown); i++) {
                                if (dotsShown >= maxDots) break;
                                
                                const dot = document.createElement('span');
                                dot.className = `booking-dot ${type} ${status}`;
                                dot.title = `${type.charAt(0).toUpperCase() + type.slice(1)} booking (${status})`;
                                dotsContainer.appendChild(dot);
                                dotsShown++;
                            }
                        });
                    });

                    // Add overflow indicator if needed
                    if (summary.total > maxDots) {
                        const overflowDot = document.createElement('span');
                        overflowDot.className = 'booking-dot';
                        overflowDot.style.background = '#666';
                        overflowDot.textContent = '+';
                        overflowDot.style.fontSize = '8px';
                        overflowDot.style.color = 'white';
                        overflowDot.style.textAlign = 'center';
                        overflowDot.title = `${summary.total - maxDots} more bookings`;
                        dotsContainer.appendChild(overflowDot);
                    }
                }
            })
            .catch(error => {
                console.error('Error loading date summary:', error);
            });
    }

    function showBookingsForDate(date) {
        const modal = document.getElementById('bookingDetailsModal');
        const dateSpan = document.getElementById('modalDate');
        const bookingsList = document.getElementById('modalBookingsList');

        dateSpan.textContent = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        bookingsList.innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="loading"></div><p>Loading bookings...</p></div>';

        const params = new URLSearchParams({
            date: date,
            ...currentFilters
        });

        fetch(`/admin/api/bookings/date-details?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.bookings.length === 0) {
                        bookingsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--on-surface-variant);">No bookings found for this date.</div>';
                    } else {
                        bookingsList.innerHTML = data.bookings.map(booking => `
                            <div class="booking-item">
                                <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 0.5rem;">
                                    <div>
                                        <div style="font-weight: 600; color: var(--primary); margin-bottom: 0.25rem;">
                                            ${booking.booking_reference}
                                        </div>
                                        <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                            ${booking.customer_name} • ${booking.customer_email}
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <span class="badge ${booking.type === 'ticket' ? 'badge-info' : 'badge-primary'}">
                                            ${booking.type === 'ticket' ? 'Ticket' : 'Event'}
                                        </span>
                                        <span class="badge ${getStatusBadgeClass(booking.status)}">
                                            ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                                        </span>
                                    </div>
                                </div>
                                <div style="font-size: 0.875rem; margin-bottom: 0.5rem;">
                                    <strong>${booking.event_title}</strong>
                                </div>
                                <div style="display: flex; justify-content: between; align-items: center;">
                                    <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                        ${booking.adult_quantity} Adults${booking.child_quantity > 0 ? `, ${booking.child_quantity} Children` : ''}
                                    </div>
                                    <div style="font-weight: 600; color: var(--success);">
                                        RM ${parseFloat(booking.total_amount).toFixed(2)}
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                } else {
                    bookingsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--error);">Error loading bookings for this date.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading bookings for date:', error);
                bookingsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--error);">Error loading bookings for this date.</div>';
            });

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeBookingDetailsModal() {
        document.getElementById('bookingDetailsModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'confirmed': return 'badge-success';
            case 'pending': return 'badge-warning';
            case 'cancelled': return 'badge-error';
            default: return 'badge-secondary';
        }
    }

    function updateCalendarStats() {
        const params = new URLSearchParams(currentFilters);

        fetch(`/admin/api/bookings/stats?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    document.getElementById('monthlyBookings').textContent = stats.monthly_bookings || '0';
                    document.getElementById('monthlyRevenue').textContent = `RM ${parseFloat(stats.monthly_revenue || 0).toFixed(2)}`;
                    document.getElementById('pendingBookings').textContent = stats.pending_bookings || '0';
                    document.getElementById('todayBookings').textContent = stats.today_bookings || '0';
                }
            })
            .catch(error => {
                console.error('Error loading calendar stats:', error);
            });
    }

    function updateBookingStatus(bookingId, status) {
        if (confirm('Are you sure you want to update this booking status?')) {
            fetch(`/api/bookings/${bookingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (currentView === 'table') {
                        location.reload();
                    } else {
                        calendar.refetchEvents();
                        updateCalendarStats();
                    }
                } else {
                    alert('Error updating booking status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating booking status');
            });
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeBookingDetailsModal();
        }
    });
</script>
@endsection