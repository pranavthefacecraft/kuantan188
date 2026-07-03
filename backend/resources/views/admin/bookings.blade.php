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
        <div class="card-body header-card-body">
            <div>
                <h2 class="page-title">Bookings Management</h2>
                <p class="page-subtitle">View and manage customer bookings</p>
            </div>
            <div class="header-actions">
                <!-- View Toggle -->
                <div class="view-toggle">
                    <button id="tableViewBtn" class="view-btn active" onclick="switchView('table')">
                        <span class="material-icons view-btn-icon">table_view</span>
                        Table View
                    </button>
                    <button id="calendarViewBtn" class="view-btn" onclick="switchView('calendar')">
                        <span class="material-icons view-btn-icon">calendar_view_month</span>
                        Calendar View
                    </button>
                </div>

                <!-- Filters -->
                <form method="GET" id="filtersForm" class="filters-form">
                    <!-- Search Box -->
                    <div class="search-container">
                        <input type="text" 
                               name="search" 
                               id="search_input" 
                               placeholder="Search bookings..." 
                               value="{{ request('search') }}" 
                               class="form-input search-input"
                               onkeyup="handleSearchKeyup(event)">
                        <span class="material-icons search-icon">search</span>
                    </div>

                    <select name="booking_type" id="booking_type_filter" class="btn btn-outline filter-select" onchange="updateFilters()">
                        <option value="">All Booking Types</option>
                        <option value="event" {{ request('booking_type') === 'event' ? 'selected' : '' }}>Event Bookings</option>
                        <option value="ticket" {{ request('booking_type') === 'ticket' ? 'selected' : '' }}>Ticket Bookings</option>
                    </select>
                    <select name="country_filter" id="country_filter" class="btn btn-outline filter-select" onchange="updateFilters()">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ request('country_filter') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status_filter" id="status_filter" class="btn btn-outline filter-select" onchange="updateFilters()">
                        <option value="">All Statuses</option>
                        <option value="confirmed" {{ request('status_filter') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status_filter') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status_filter') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @if(request()->hasAny(['search', 'booking_type', 'country_filter', 'status_filter']))
                        <a href="{{ route('admin.bookings') }}" class="btn btn-outline filter-select" onclick="clearFilters()">
                            <span class="material-icons btn-icon">clear</span>
                            Clear
                        </a>
                    @endif
                </form>
                <button class="btn btn-outline">
                    <span class="material-icons btn-icon">download</span>
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
                                    <div class="booking-reference">
                                        {{ $booking->booking_reference }}
                                    </div>
                                </td>
                                <td>
                                    @if(!empty($booking->ticket_id) && $booking->ticket_id !== null && $booking->ticket_id > 0)
                                        <span class="badge badge-info">
                                            <span class="material-icons badge-icon">confirmation_number</span>
                                            Ticket
                                        </span>
                                    @else
                                        <span class="badge badge-primary">
                                            <span class="material-icons badge-icon">event</span>
                                            Event
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <div class="customer-name">{{ $booking->customer_name }}</div>
                                        <div class="customer-detail">
                                            {{ $booking->customer_email }}
                                        </div>
                                        @if($booking->customer_phone)
                                            <div class="customer-detail">
                                                {{ $booking->customer_phone }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="event-title">{{ optional($booking->event)->title ?? $booking->event_title ?? 'N/A' }}</div>
                                        <div class="event-detail">
                                            {{ optional($booking->event)->event_date ? optional($booking->event)->event_date->format('M j, Y') : ($booking->event_date ?? 'N/A') }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="country-info">
                                        <span class="material-icons country-icon">public</span>
                                        {{ optional($booking->country)->name ?? ($booking->country ?? 'N/A') }}
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><strong>{{ $booking->quantity }}</strong> ticket(s)</div>
                                        <div class="quantity-detail" style="margin-top: 0.5rem; font-size: 0.875rem; color: #666;">
                                            @if($booking->adult_tickets > 0)
                                                <div>{{ $booking->adult_tickets }}× Adult</div>
                                            @endif
                                            @if($booking->teenager_tickets > 0)
                                                <div>{{ $booking->teenager_tickets }}× Teenagers From 13</div>
                                            @endif
                                            @if($booking->university_tickets > 0)
                                                <div>{{ $booking->university_tickets }}× University Students</div>
                                            @endif
                                            @if($booking->child_tickets > 0)
                                                <div>{{ $booking->child_tickets }}× Children Below 13</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="amount-main">
                                        RM {{ number_format($booking->total_amount, 2) }}
                                    </div>
                                    @if($booking->adult_tickets > 0 && $booking->child_tickets > 0)
                                        <div class="price-breakdown">
                                            A: RM{{ number_format(optional($booking->ticket)->adult_price ?? $booking->adult_price ?? 0, 2) }} × {{ $booking->adult_tickets }}<br>
                                            C: RM{{ number_format(optional($booking->ticket)->child_price ?? $booking->child_price ?? 0, 2) }} × {{ $booking->child_tickets }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status === 'confirmed')
                                        <span class="badge badge-success">
                                            <span class="material-icons badge-icon">check_circle</span>
                                            Confirmed
                                        </span>
                                    @elseif($booking->status === 'pending')
                                        <span class="badge badge-warning">
                                            <span class="material-icons badge-icon">schedule</span>
                                            Pending
                                        </span>
                                    @else
                                        <span class="badge badge-error">
                                            <span class="material-icons badge-icon">cancel</span>
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $booking->created_at->format('M j, Y') }}</div>
                                    <div class="date-detail">
                                        {{ $booking->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="actions-container">
                                        @if($booking->status === 'pending')
                                            <button class="btn btn-outline action-btn-success" 
                                                    onclick="updateBookingStatus('{{ $booking->id }}', 'confirmed')"
                                                    title="Confirm booking">
                                                <span class="material-icons action-icon">check</span>
                                            </button>
                                        @elseif($booking->status === 'confirmed')
                                            <button class="btn btn-outline action-btn-warning" 
                                                    onclick="updateBookingStatus('{{ $booking->id }}', 'pending')"
                                                    title="Set to pending">
                                                <span class="material-icons action-icon">schedule</span>
                                            </button>
                                        @endif
                                        <button class="btn btn-outline action-btn" 
                                                onclick="showBookingDetails('{{ $booking->id }}')"
                                                title="View details">
                                            <span class="material-icons action-icon">visibility</span>
                                        </button>
                                        <a href="#" class="btn btn-outline action-btn" title="Send email">
                                            <span class="material-icons action-icon">email</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="empty-state">
                                    <div class="empty-state-content">
                                        <span class="material-icons empty-state-icon">book_online</span>
                                        <div>
                                            <div class="empty-state-title">No bookings found</div>
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
                <div class="pagination-container">
                    {{ $bookings->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Calendar View -->
    <div id="calendarView" class="card calendar-view">
        <div class="card-body">
            <div id="calendar" class="calendar-container"></div>
            
            <!-- Calendar Stats -->
            <div class="calendar-stats">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="material-icons stat-icon stat-icon-primary">event</span>
                        <span class="stat-label">This Month</span>
                    </div>
                    <div id="monthlyBookings" class="stat-value">-</div>
                    <div class="stat-description">Total Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="material-icons stat-icon stat-icon-success">payments</span>
                        <span class="stat-label">This Month</span>
                    </div>
                    <div id="monthlyRevenue" class="stat-value">-</div>
                    <div class="stat-description">Total Revenue</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="material-icons stat-icon stat-icon-warning">schedule</span>
                        <span class="stat-label">Pending</span>
                    </div>
                    <div id="pendingBookings" class="stat-value">-</div>
                    <div class="stat-description">Need Review</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="material-icons stat-icon stat-icon-accent">today</span>
                        <span class="stat-label">Today</span>
                    </div>
                    <div id="todayBookings" class="stat-value">-</div>
                    <div class="stat-description">New Bookings</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div id="bookingDetailsModal" class="modal modal-hidden">
        <div class="modal-overlay" onclick="closeBookingDetailsModal()"></div>
        <div class="modal-container modal-container-large">
            <div class="modal-header">
                <h3 class="modal-title">Bookings for <span id="modalDate"></span></h3>
                <button type="button" class="modal-close" onclick="closeBookingDetailsModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <div class="modal-body">
                <div id="modalBookingsList" class="modal-bookings-list">
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

    <!-- Individual Booking Details Modal -->
    <div id="individualBookingModal" class="modal modal-hidden">
        <div class="modal-overlay" onclick="closeIndividualBookingModal()"></div>
        <div class="modal-container modal-container-medium">
            <div class="modal-header">
                <h3 class="modal-title">
                    <span class="material-icons modal-title-icon">receipt</span>
                    Booking Details
                </h3>
                <button type="button" class="modal-close" onclick="closeIndividualBookingModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <div class="modal-body">
                <div id="individualBookingContent">
                    <!-- Individual booking details will be loaded here -->
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="modal-footer-actions">
                    <button type="button" class="btn btn-outline" onclick="closeIndividualBookingModal()">
                        Close
                    </button>
                    <button type="button" class="btn btn-primary hidden" id="updateStatusBtn" onclick="updateBookingStatusFromModal()">
                        <span class="material-icons modal-btn-icon">check_circle</span>
                        Confirm Booking
                    </button>
                    <button type="button" class="btn btn-outline" onclick="sendBookingEmail()">
                        <span class="material-icons modal-btn-icon">email</span>
                        Send Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<style>
    /* Button Styles */
    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        background: transparent;
        color: var(--on-surface);
    }

    .btn-outline {
        background: var(--surface);
        color: var(--on-surface);
    }

    .btn-outline:hover {
        background: var(--surface-variant);
        color: var(--primary);
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        border: none !important;;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        /* border-color: var(--primary-dark); */
        color: white;
    }

    /* Header Styles */
    .header-card-body {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .page-subtitle {
        margin: 0.5rem 0 0 0;
        color: var(--on-surface-variant);
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    /* View Toggle Styles */
    .view-toggle {
        display: flex;
        background: var(--surface-variant);
        border-radius: 0.5rem;
        padding: 0.25rem;
    }

    .view-btn {
        padding: 0.5rem 1rem;
        border: none;
        background: none;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--on-surface);
        font-weight: 500;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .view-btn-icon {
        font-size: 18px;
        margin-right: 0.5rem;
    }

    .view-toggle .view-btn.active {
        background: var(--primary) !important;
        color: white !important;
    }

    .view-toggle .view-btn:hover {
        background: rgba(var(--primary-rgb), 0.1) !important;
    }

    /* Filters Styles */
    .filters-form {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-container {
        position: relative;
    }

    .search-input {
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        width: 250px;
        border: 1px solid var(--outline);
        border-radius: 0.5rem;
    }

    .search-icon {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--on-surface-variant);
        font-size: 18px;
        pointer-events: none;
    }

    .filter-select {
        padding: 0.5rem 1rem;
    }

    .btn-icon {
        font-size: 18px;
    }

    /* Form Input Styles */
    .form-input {
        display: block;
        width: 100%;
        padding: 0.5rem 1rem;
        border: 1px solid var(--border);
        border-radius: 0.375rem;
        background: var(--surface);
        color: var(--on-surface);
        font-size: 0.875rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }

    /* Table Styles */
    .booking-reference {
        font-weight: 600;
        color: var(--primary);
    }

    .badge-icon {
        font-size: 12px;
    }

    .customer-name {
        font-weight: 500;
    }

    .customer-detail {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    .event-title {
        font-weight: 500;
    }

    .event-detail {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    .country-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .country-icon {
        font-size: 16px;
        color: var(--accent);
    }

    .quantity-detail {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    .amount-main {
        font-weight: 600;
        color: var(--success);
    }

    .price-breakdown {
        font-size: 0.75rem;
        color: var(--on-surface-variant);
    }

    .date-detail {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    /* Actions Styles */
    .actions-container {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.25rem 0.5rem;
    }

    .action-btn-success {
        padding: 0.25rem 0.5rem;
        background: var(--success);
        color: white;
    }

    .action-btn-warning {
        padding: 0.25rem 0.5rem;
        background: var(--warning);
        color: white;
    }

    .action-icon {
        font-size: 16px;
    }

    /* Empty State Styles */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--on-surface-variant);
    }

    .empty-state-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .empty-state-icon {
        font-size: 48px;
        opacity: 0.3;
    }

    .empty-state-title {
        font-size: 1.125rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .pagination-container {
        margin-top: 1.5rem;
    }

    /* Calendar Styles */
    .calendar-view {
        display: none;
    }

    .calendar-container {
        height: 600px;
    }

    .calendar-stats {
        margin-top: 1.5rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .stat-card {
        background: var(--surface-variant);
        padding: 1rem;
        border-radius: 0.75rem;
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .stat-icon {
        font-size: 20px;
    }

    .stat-icon-primary {
        color: var(--primary);
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--on-surface);
    }

    .stat-description {
        font-size: 0.75rem;
        color: var(--on-surface-variant);
    }

    /* Modal Styles */
    .modal-hidden {
        display: none;
    }

    .modal-bookings-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .stat-icon-success {
        color: var(--success);
    }

    .stat-icon-warning {
        color: var(--warning);
    }

    .stat-icon-accent {
        color: var(--accent);
    }

    .modal-container-large {
        max-width: 800px;
    }

    .modal-container-medium {
        max-width: 700px;
    }

    .modal-title-icon {
        vertical-align: middle;
        margin-right: 0.5rem;
    }

    .modal-footer-actions {
        display: flex;
        gap: 1rem;
    }

    .modal-btn-icon {
        font-size: 16px;
        margin-right: 0.5rem;
    }

    .hidden {
        display: none;
    }

    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
    
    .badge-primary {
        background-color: #007bff;
        color: white;
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

    /* Individual Booking Modal Styles */
    #individualBookingModal .modal-container {
        max-width: 700px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .badge-success {
        background-color: rgba(34, 197, 94, 0.1);
        color: rgb(22, 163, 74);
    }

    .badge-warning {
        background-color: rgba(245, 158, 11, 0.1);
        color: rgb(217, 119, 6);
    }

    .badge-error {
        background-color: rgba(239, 68, 68, 0.1);
        color: rgb(220, 38, 38);
    }

    .badge-info {
        background-color: rgba(59, 130, 246, 0.1);
        color: rgb(37, 99, 235);
    }

    .badge-primary {
        background-color: rgba(99, 102, 241, 0.1);
        color: rgb(79, 70, 229);
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid var(--border);
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Clean pagination styling to match the design */
    .pagination {
        display: flex !important;
        justify-content: flex-start !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        margin: 20px 0 !important;
        padding: 0 !important;
        position: relative !important;
        z-index: 100 !important;
        background: transparent !important;
    }

    .pagination .page-item {
        margin: 0 !important;
    }

    .pagination .page-link {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 8px 12px !important;
        margin: 0 !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 4px !important;
        background: #fff !important;
        color: #007bff !important;
        text-decoration: none !important;
        min-width: auto !important;
        height: auto !important;
        font-weight: 400 !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        transition: all 0.15s ease-in-out !important;
    }

    .pagination .page-link:hover {
        background: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #0056b3 !important;
        text-decoration: none !important;
    }

    .pagination .page-item.active .page-link {
        background: #007bff !important;
        border-color: #007bff !important;
        color: #fff !important;
        z-index: 3 !important;
    }

    .pagination .page-item.disabled .page-link {
        background: #fff !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
        cursor: not-allowed !important;
        opacity: 0.65 !important;
    }

    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        border-radius: 4px !important;
    }

    /* Ensure proper spacing around pagination */
    .table-view .card-body {
        position: relative !important;
        z-index: 10 !important;
    }

    /* Prevent modal overlay from covering pagination */
    .modal {
        z-index: 1050 !important;
    }

    .modal-overlay {
        z-index: 1051 !important;
    }

    .modal-container {
        z-index: 1052 !important;
    }

    /* Modal State Classes */
    .modal-hidden {
        display: none;
    }

    .modal-bookings-list {
        max-height: 400px;
        overflow-y: auto;
    }

    /* JavaScript Generated Content Classes */
    .text-center-loading {
        text-align: center;
        padding: 2rem;
    }

    .no-bookings-message {
        text-align: center;
        padding: 2rem;
        color: var(--on-surface-variant);
    }

    .error-message {
        text-align: center;
        padding: 2rem;
        color: var(--error);
    }

    .booking-header-container {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.5rem;
    }

    .booking-customer-name {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .booking-customer-detail {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    .booking-actions-container {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .booking-reference {
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .booking-footer-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .booking-quantity {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    .booking-amount {
        font-weight: 600;
        color: var(--success);
    }

    /* View Visibility Classes */
    .hidden {
        display: none !important;
    }

    .visible {
        display: block !important;
    }

    /* Individual Booking Modal Classes */
    .spinner-centered {
        margin: 0 auto 1rem;
    }

    .error-icon-large {
        font-size: 48px;
        margin-bottom: 1rem;
    }

    .badge-icon {
        font-size: 12px;
    }

    .booking-details-grid {
        display: grid;
        gap: 1.5rem;
    }

    .booking-card-container {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: start;
        padding: 1rem;
        background: var(--surface-variant);
        border-radius: 0.75rem;
    }

    .booking-card-title {
        margin: 0 0 0.5rem 0;
        color: var(--primary);
        font-size: 1.25rem;
    }

    .booking-badges-container {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .booking-amount-section {
        text-align: right;
    }

    .booking-large-amount {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--success);
    }

    .booking-payment-method {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
    }

    .customer-details-title {
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .customer-icon {
        color: var(--primary);
    }

    /* Customer Details Classes */
    .customer-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .customer-label {
        font-size: 0.875rem;
        color: var(--on-surface-variant);
        margin-bottom: 0.25rem;
        display: block;
    }

    .customer-value {
        font-weight: 500;
    }

    .country-value-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .country-icon {
        font-size: 16px;
        color: var(--accent);
    }

    /* Ticket Details Classes */
    .ticket-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }

    .ticket-quantity-value {
        font-size: 1.25rem;
        font-weight: 600;
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
        status_filter: '{{ request('status_filter') ?? '' }}',
        search: '{{ request('search') ?? '' }}'
    };

    function initializePaginationLinks() {
        // Find all pagination links and add click handlers
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            if (!link.href.includes('javascript:')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the URL from the link
                    let url = new URL(this.href);
                    
                    // Add current filter values to the URL
                    const bookingType = document.getElementById('booking_type_filter').value;
                    const country = document.getElementById('country_filter').value;
                    const status = document.getElementById('status_filter').value;
                    const search = document.getElementById('search_input').value;
                    
                    if (bookingType) url.searchParams.set('booking_type', bookingType);
                    if (country) url.searchParams.set('country_filter', country);
                    if (status) url.searchParams.set('status_filter', status);
                    if (search) url.searchParams.set('search', search);
                    
                    // Navigate to the updated URL
                    window.location.href = url.toString();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize pagination links with filter preservation
        initializePaginationLinks();
        
        // Initialize search input value from URL parameter
        const searchInput = document.getElementById('search_input');
        if (searchInput && currentFilters.search) {
            searchInput.value = currentFilters.search;
        }
        
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
            if (tableView) {
                tableView.classList.remove('hidden');
                tableView.style.display = 'block';
            }
            if (calendarView) {
                calendarView.classList.add('hidden');
                calendarView.style.display = 'none';
            }
            if (tableBtn) tableBtn.classList.add('active');
            if (calendarBtn) calendarBtn.classList.remove('active');
        } else {
            if (tableView) {
                tableView.classList.add('hidden');
                tableView.style.display = 'none';
            }
            if (calendarView) {
                calendarView.classList.remove('hidden');
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
        const search = document.getElementById('search_input').value;

        currentFilters = {
            booking_type: bookingType,
            country_filter: country,
            status_filter: status,
            search: search
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

    // Handle search input with debouncing
    let searchTimeout;
    function handleSearchKeyup(event) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (event.key === 'Enter' || event.target.value.length === 0 || event.target.value.length >= 3) {
                updateFilters();
            }
        }, 500); // 500ms delay for debouncing
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
            eventClick: function(info) {
                // Extract booking ID from the event
                const bookingId = info.event.id || info.event.extendedProps.id;
                showBookingDetails(bookingId);
            },
            eventDidMount: function(info) {
                // Style event elements
                const booking = info.event.extendedProps;
                info.el.style.cursor = 'pointer';
                
                // Add tooltip
                info.el.title = `${booking.customer_name} - ${booking.status.toUpperCase()}`;
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

        bookingsList.innerHTML = '<div class="text-center-loading"><div class="loading"></div><p>Loading bookings...</p></div>';

        const params = new URLSearchParams({
            date: date,
            ...currentFilters
        });

        fetch(`/admin/api/bookings/date-details?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.bookings.length === 0) {
                        bookingsList.innerHTML = '<div class="no-bookings-message">No bookings found for this date.</div>';
                    } else {
                        bookingsList.innerHTML = data.bookings.map(booking => `
                            <div class="booking-item">
                                <div class="booking-header-container">
                                    <div>
                                        <div class="booking-customer-name">
                                            ${booking.booking_reference}
                                        </div>
                                        <div class="booking-customer-detail">
                                            ${booking.customer_name} • ${booking.customer_email}
                                        </div>
                                    </div>
                                    <div class="booking-actions-container">
                                        <span class="badge ${booking.type === 'ticket' ? 'badge-info' : 'badge-primary'}">
                                            ${booking.type === 'ticket' ? 'Ticket' : 'Event'}
                                        </span>
                                        <span class="badge ${getStatusBadgeClass(booking.status)}">
                                            ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                                        </span>
                                    </div>
                                </div>
                                <div class="booking-reference">
                                    <strong>${booking.event_title}</strong>
                                </div>
                                <div class="booking-footer-container">
                                    <div class="booking-customer-detail">
                                        ${booking.adult_quantity} Adults${booking.child_quantity > 0 ? `, ${booking.child_quantity} Children` : ''}
                                    </div>
                                    <div class="booking-amount">
                                        RM ${parseFloat(booking.total_amount).toFixed(2)}
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                } else {
                    bookingsList.innerHTML = '<div class="error-message">Error loading bookings for this date.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading bookings for date:', error);
                bookingsList.innerHTML = '<div class="error-message">Error loading bookings for this date.</div>';
            });

        modal.classList.remove('modal-hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeBookingDetailsModal() {
        const modal = document.getElementById('bookingDetailsModal');
        modal.classList.add('modal-hidden');
        modal.style.display = 'none';
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

    function showBookingDetails(event) {
        const booking = event.extendedProps;
        
        // Create modal content
        const modalContent = `
            <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Booking Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Reference:</strong> ${booking.booking_reference}</p>
                                    <p><strong>Type:</strong> ${booking.booking_type}</p>
                                    <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeColor(booking.status)}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span></p>
                                    <p><strong>Amount:</strong> RM ${parseFloat(booking.total_amount).toFixed(2)}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Customer:</strong> ${booking.customer_name}</p>
                                    <p><strong>Email:</strong> ${booking.customer_email}</p>
                                    <p><strong>Adults:</strong> ${booking.adult_quantity}</p>
                                    ${booking.child_quantity > 0 ? `<p><strong>Children:</strong> ${booking.child_quantity}</p>` : ''}
                                </div>
                            </div>
                            ${booking.booking_type === 'Event' ? `
                                <hr>
                                <p><strong>Event:</strong> ${booking.event_title}</p>
                                <p><strong>Event Date:</strong> ${booking.event_date}</p>
                            ` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('bookingDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body and show it
        document.body.insertAdjacentHTML('beforeend', modalContent);
        
        // Show modal using Bootstrap 5 syntax
        const modalElement = document.getElementById('bookingDetailsModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }

    function getStatusBadgeColor(status) {
        switch(status) {
            case 'confirmed': return 'success';
            case 'pending': return 'warning';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }

    function updateBookingStatus(bookingId, status) {
        if (confirm('Are you sure you want to update this booking status?')) {
            fetch(`/admin/api/bookings/${bookingId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
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

    // Individual Booking Modal Functions
    let currentBookingId = null;

    function showBookingDetails(bookingId) {
        currentBookingId = bookingId;
        const modal = document.getElementById('individualBookingModal');
        const content = document.getElementById('individualBookingContent');
        
        // Show loading state
        content.innerHTML = `
            <div class="text-center-loading">
                <div class="spinner spinner-centered"></div>
                <p>Loading booking details...</p>
            </div>
        `;
        
        // Show modal
        modal.style.display = 'flex';
        
        // Fetch booking details
        fetch(`/admin/api/bookings/${bookingId}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderBookingDetails(data.booking);
                } else {
                    content.innerHTML = `
                        <div class="error-message">
                            <span class="material-icons error-icon-large">error</span>
                            <p>Error loading booking details: ${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading booking details:', error);
                content.innerHTML = `
                    <div class="error-message">
                        <span class="material-icons error-icon-large">error</span>
                        <p>Error loading booking details</p>
                    </div>
                `;
            });
    }

    function renderBookingDetails(booking) {
        const content = document.getElementById('individualBookingContent');
        const updateBtn = document.getElementById('updateStatusBtn');
        
        // Show update button only for pending bookings
        if (booking.status === 'pending') {
            updateBtn.style.display = 'inline-flex';
        } else {
            updateBtn.style.display = 'none';
        }
        
        // Format dates
        const createdDate = new Date(booking.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const eventDate = booking.event_date ? new Date(booking.event_date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'N/A';

        // Get status badge
        let statusBadge = '';
        if (booking.status === 'confirmed') {
            statusBadge = '<span class="badge badge-success"><span class="material-icons badge-icon">check_circle</span> Confirmed</span>';
        } else if (booking.status === 'pending') {
            statusBadge = '<span class="badge badge-warning"><span class="material-icons badge-icon">schedule</span> Pending</span>';
        } else {
            statusBadge = '<span class="badge badge-error"><span class="material-icons badge-icon">cancel</span> Cancelled</span>';
        }

        // Get booking type
        const bookingType = booking.ticket_id && booking.ticket_id > 0 ? 
            '<span class="badge badge-info"><span class="material-icons badge-icon">confirmation_number</span> Ticket</span>' :
            '<span class="badge badge-primary"><span class="material-icons badge-icon">event</span> Event</span>';

        content.innerHTML = `
            <div class="booking-details-grid">
                <!-- Header Info -->
                <div class="booking-card-container">
                    <div>
                        <h4 class="booking-card-title">
                            ${booking.booking_reference}
                        </h4>
                        <div class="booking-badges-container">
                            ${bookingType}
                            ${statusBadge}
                        </div>
                    </div>
                    <div class="booking-amount-section">
                        <div class="booking-large-amount">
                            RM ${parseFloat(booking.total_amount).toFixed(2)}
                        </div>
                        <div class="booking-payment-method">
                            Total Amount
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div>
                    <h5 class="customer-details-title">
                        <span class="material-icons customer-icon">person</span>
                        Customer Information
                    </h5>
                    <div class="customer-details-grid">
                        <div>
                            <label class="customer-label">Name</label>
                            <div class="customer-value">${booking.customer_name}</div>
                        </div>
                        <div>
                            <label class="customer-label">Email</label>
                            <div>${booking.customer_email}</div>
                        </div>
                        <div>
                            <label class="customer-label">Phone</label>
                            <div>${booking.customer_phone || 'N/A'}</div>
                        </div>
                        <div>
                            <label class="customer-label">Country</label>
                            <div class="country-value-container">
                                <span class="material-icons country-icon">public</span>
                                ${booking.country_name || booking.country || 'N/A'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Information -->
                <div>
                    <h5 class="customer-details-title">
                        <span class="material-icons customer-icon">event</span>
                        Event Information
                    </h5>
                    <div class="customer-details-grid">
                        <div>
                            <label class="customer-label">Event/Ticket Name</label>
                            <div class="customer-value">${booking.event_title || booking.ticket_name || 'N/A'}</div>
                        </div>
                        <div>
                            <label class="customer-label">Event Date</label>
                            <div>${eventDate}</div>
                        </div>
                        <div>
                            <label class="customer-label">Booking Date</label>
                            <div>${createdDate}</div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Information -->
                <div>
                    <h5 class="customer-details-title">
                        <span class="material-icons customer-icon">confirmation_number</span>
                        Ticket Information
                    </h5>
                    <div class="ticket-details-grid">
                        <div>
                            <label class="customer-label">Adults</label>
                            <div class="ticket-quantity-value">${booking.adult_quantity || 0}</div>
                        </div>
                        <div>
                            <label class="customer-label">Children</label>
                            <div class="ticket-quantity-value">${booking.child_quantity || 0}</div>
                        </div>
                        <div>
                            <label class="customer-label">Adult Price</label>
                            <div>RM ${parseFloat(booking.adult_price || 0).toFixed(2)}</div>
                        </div>
                        <div>
                            <label class="customer-label">Child Price</label>
                            <div>RM ${parseFloat(booking.child_price || 0).toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function closeIndividualBookingModal() {
        const modal = document.getElementById('individualBookingModal');
        modal.style.display = 'none';
        currentBookingId = null;
    }

    function updateBookingStatusFromModal() {
        if (currentBookingId) {
            updateBookingStatus(currentBookingId, 'confirmed');
        }
    }

    function sendBookingEmail() {
        if (currentBookingId) {
            if (!confirm('Send confirmation email for this booking?')) return;

            fetch(`/api/bookings/${currentBookingId}/send-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Authorization': 'Bearer ' + (document.cookie.match(/token=([^;]+)/)?.[1] || '')
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
            })
            .catch(err => {
                alert('Failed to send email: ' + err.message);
            });
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeBookingDetailsModal();
            closeIndividualBookingModal();
        }
    });
</script>
@endsection