@extends('layouts.admin')

@section('title', 'Tickets Management')

@section('content')
<div class="grid">
    <!-- Header Actions -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Tickets Management</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">Manage ticket prices and availability</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="openTicketModal()" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px;">add</span>
                    Add New Ticket
                </button>
                <button id="bulkDeleteBtn" onclick="bulkDeleteTickets()" class="btn btn-outline" style="display: none; color: var(--error); border-color: var(--error);">
                    <span class="material-icons" style="font-size: 18px;">delete_sweep</span>
                    Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="checkbox-input">
                            </th>
                            <th>Ticket Name</th>
                            <th>Event</th>
                            <th>Country</th>
                            <th>Adult Price</th>
                            <th>Child Price</th>
                            <th>Availability</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" class="ticket-checkbox" value="{{ $ticket->id }}" onchange="updateBulkDeleteButton()">
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: 600;">{{ $ticket->ticket_name ?? 'Unnamed Ticket' }}</div>
                                        <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                            {{ $ticket->description ? Str::limit($ticket->description, 50) : 'No description' }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($ticket->event)
                                        <div>
                                            <div style="font-weight: 600;">{{ $ticket->event->name }}</div>
                                            <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                                {{ $ticket->event->date->format('M j, Y') }}
                                            </div>
                                        </div>
                                    @else
                                        <span style="color: var(--on-surface-variant); font-style: italic;">
                                            <span class="material-icons" style="font-size: 14px;">event_busy</span>
                                            No specific event
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--accent);">public</span>
                                        <div>
                                            @if($ticket->countries->count() > 0)
                                                @foreach($ticket->countries as $country)
                                                    <div style="margin-bottom: 0.25rem;">
                                                        <div>{{ $country->name }}</div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span style="color: var(--on-surface-variant);">No countries assigned</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($ticket->countries->count() > 0)
                                        @foreach($ticket->countries as $country)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--primary); font-size: 0.875rem;">
                                                    {{ $country->name }}: RM {{ number_format($country->pivot->adult_price, 2) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <span style="color: var(--on-surface-variant);">No pricing set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->countries->count() > 0)
                                        @foreach($ticket->countries as $country)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--secondary); font-size: 0.875rem;">
                                                    {{ $country->name }}: RM {{ number_format($country->pivot->child_price, 2) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <span style="color: var(--on-surface-variant);">No pricing set</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $totalBookings = $ticket->bookings ? $ticket->bookings->where('status', 'confirmed')->count() : 0;
                                        $availableTickets = $ticket->total_quantity ? ($ticket->total_quantity - $totalBookings) : null;
                                        $availabilityPercentage = $ticket->total_quantity ? 
                                            (($ticket->total_quantity - $totalBookings) / $ticket->total_quantity * 100) : 100;
                                    @endphp
                                    @if($ticket->total_quantity)
                                        <div>
                                            <div style="font-size: 0.875rem; margin-bottom: 0.25rem;">
                                                {{ $availableTickets }} of {{ $ticket->total_quantity }}
                                            </div>
                                            <div style="width: 80px; height: 8px; background: var(--border); border-radius: 4px; overflow: hidden;">
                                                <div style="width: {{ $availabilityPercentage }}%; height: 100%; background: 
                                                    {{ $availabilityPercentage > 50 ? 'var(--success)' : ($availabilityPercentage > 25 ? 'var(--warning)' : 'var(--error)') }};
                                                    transition: width 0.3s ease;"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge badge-success">
                                            <span class="material-icons" style="font-size: 12px;">all_inclusive</span>
                                            Unlimited
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--accent);">book_online</span>
                                        {{ $totalBookings }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $revenue = $ticket->bookings ? $ticket->bookings->where('status', 'confirmed')->sum('total_amount') : 0;
                                    @endphp
                                    <div style="font-weight: 600; color: var(--success);">
                                        RM {{ number_format($revenue, 2) }}
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="openEditTicketModal({{ $ticket->id }})" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <span class="material-icons" style="font-size: 16px;">edit</span>
                                        </button>
                                        <a href="#" class="btn btn-outline" style="padding: 0.25rem 0.5rem; {{ $ticket->is_active ? '' : 'color: var(--error);' }}">
                                            <span class="material-icons" style="font-size: 16px; {{ $ticket->is_active ? 'color: var(--success);' : 'color: var(--error);' }}">
                                                {{ $ticket->is_active ? 'visibility' : 'visibility_off' }}
                                            </span>
                                        </a>
                                        @if($totalBookings == 0)
                                            <button class="btn btn-outline" 
                                                    style="padding: 0.25rem 0.5rem; color: var(--error);"
                                                    onclick="deleteTicket('{{ $ticket->id }}')">
                                                <span class="material-icons" style="font-size: 16px;">delete</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem; color: var(--on-surface-variant);">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                        <span class="material-icons" style="font-size: 48px; opacity: 0.3;">confirmation_number</span>
                                        <div>
                                            <div style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">No tickets found</div>
                                            <div>Create tickets for your events to start selling</div>
                                        </div>
                                        <button onclick="openTicketModal()" class="btn btn-primary">
                                            <span class="material-icons" style="font-size: 18px;">add</span>
                                            Create Ticket
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="pagination-container" style="margin-top: 1.5rem; display: flex; justify-content: center; align-items: center;">
                    <div class="pagination-wrapper">
                        {{ $tickets->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Ticket Modal -->
<div id="editTicketModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeEditTicketModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Edit Ticket</h3>
            <button type="button" class="modal-close" onclick="closeEditTicketModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
        
        <form id="editTicketForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_ticket_id" name="ticket_id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_ticket_name" class="form-label">Ticket Name *</label>
                        <input type="text" 
                               id="edit_ticket_name" 
                               name="ticket_name" 
                               class="form-input" 
                               placeholder="Enter ticket name"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="edit_event_id" class="form-label">Event (Optional)</label>
                        <select id="edit_event_id" name="event_id" class="form-input">
                            <option value="">No specific event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }} - {{ $event->event_date->format('M j, Y') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="edit_countries" class="form-label">Countries *</label>
                        <select id="edit_countries" name="countries[]" class="form-input" multiple required style="height: auto; min-height: 120px;">
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Hold Ctrl/Cmd to select multiple countries
                        </small>
                    </div>

                    <div class="form-group full-width" id="editCountryPricingSection" style="display: none;">
                        <label class="form-label">Country-Specific Pricing *</label>
                        <div id="editCountryPricingContainer">
                            <!-- Dynamic country pricing inputs will be added here -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_total_quantity" class="form-label">Total Quantity</label>
                        <input type="number" 
                               id="edit_total_quantity" 
                               name="total_quantity" 
                               class="form-input" 
                               min="1" 
                               placeholder="Leave empty for unlimited">
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Maximum number of tickets available (optional)
                        </small>
                    </div>

                    <div class="form-group full-width">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea id="edit_description" 
                                  name="description" 
                                  class="form-textarea" 
                                  rows="3"
                                  placeholder="Optional ticket description"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <div class="checkbox-group">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" 
                                   id="edit_is_active" 
                                   name="is_active" 
                                   class="checkbox-input" 
                                   value="1" 
                                   checked>
                            <label for="edit_is_active" class="checkbox-label">
                                Ticket is active and available for booking
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditTicketModal()">
                    Cancel
                </button>
                <button type="button" class="btn btn-secondary" onclick="debugFormData('editTicketForm')" style="margin-right: 0.5rem;">
                    <span class="material-icons" style="font-size: 18px;">bug_report</span>
                    Debug Form
                </button>
                <button type="button" class="btn btn-secondary" onclick="testFormFields()" style="margin-right: 0.5rem;">
                    <span class="material-icons" style="font-size: 18px;">search</span>
                    Test Fields
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px;">save</span>
                    Update Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Ticket Modal -->
<div id="ticketModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeTicketModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Add New Ticket</h3>
            <button type="button" class="modal-close" onclick="closeTicketModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
        
        <form id="ticketForm" method="POST" action="{{ route('admin.tickets.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="ticket_name" class="form-label">Ticket Name *</label>
                        <input type="text" 
                               id="ticket_name" 
                               name="ticket_name" 
                               class="form-input" 
                               placeholder="Enter ticket name"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="event_id" class="form-label">Event (Optional)</label>
                        <select id="event_id" name="event_id" class="form-input">
                            <option value="">No specific event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }} - {{ $event->event_date->format('M j, Y') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="countries" class="form-label">Countries *</label>
                        <select id="countries" name="countries[]" class="form-input" multiple required style="height: auto; min-height: 120px;">
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Hold Ctrl/Cmd to select multiple countries
                        </small>
                    </div>

                    <div class="form-group full-width" id="countryPricingSection" style="display: none;">
                        <label class="form-label">Country-Specific Pricing *</label>
                        <div id="countryPricingContainer">
                            <!-- Dynamic country pricing inputs will be added here -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="total_quantity" class="form-label">Total Quantity</label>
                        <input type="number" 
                               id="total_quantity" 
                               name="total_quantity" 
                               class="form-input" 
                               min="1" 
                               placeholder="Leave empty for unlimited">
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Maximum number of tickets available (optional)
                        </small>
                    </div>

                    <div class="form-group full-width">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea" 
                                  rows="3"
                                  placeholder="Optional ticket description"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <div class="checkbox-group">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   class="checkbox-input" 
                                   value="1" 
                                   checked>
                            <label for="is_active" class="checkbox-label">
                                Ticket is active and available for booking
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="debugFormData('ticketForm')" style="margin-right: 0.5rem;">
                    <span class="material-icons" style="font-size: 16px;">bug_report</span>
                    Debug Form
                </button>
                <button type="button" class="btn btn-secondary" onclick="testAddFormFields()" style="margin-right: 0.5rem;">
                    <span class="material-icons" style="font-size: 16px;">search</span>
                    Test Fields
                </button>
                <button type="button" class="btn btn-outline" onclick="closeTicketModal()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px;">confirmation_number</span>
                    Create Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
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
        right: 0;
        bottom: 0;
    }

    .modal-container {
        position: relative;
        background: var(--surface);
        border-radius: 1rem;
        box-shadow: var(--shadow-lg);
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        border: 1px solid var(--border);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
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
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: background 0.2s ease;
    }

    .modal-close:hover {
        background: var(--surface);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        background: var(--surface-variant);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--on-surface);
        margin-bottom: 0.5rem;
    }

    .form-input,
    .form-textarea {
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        background: var(--surface);
        color: var(--on-surface);
        font-size: 0.875rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .checkbox-input {
        width: 1.125rem;
        height: 1.125rem;
        border: 2px solid var(--border);
        border-radius: 0.25rem;
        background: var(--surface);
        cursor: pointer;
        position: relative;
        appearance: none;
    }

    .checkbox-input:checked {
        background: var(--primary);
        border-color: var(--primary);
    }

    .checkbox-input:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.875rem;
        font-weight: bold;
    }

    .checkbox-label {
        font-size: 0.875rem;
        color: var(--on-surface);
        cursor: pointer;
    }

    /* Pagination Styles */
    nav[role="navigation"] {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    .pagination {
        display: flex !important;
        justify-content: center;
        align-items: center;
        gap: 0.25rem;
        margin: 0;
        padding: 0;
        list-style: none;
        flex-wrap: nowrap;
    }

    .page-item {
        margin: 0;
        display: flex;
    }

    .page-link {
        display: flex !important;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0.5rem;
        margin: 0;
        color: var(--on-surface) !important;
        background: var(--surface) !important;
        border: 1px solid var(--outline) !important;
        border-radius: 0.5rem;
        text-decoration: none !important;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .page-link:hover {
        color: var(--primary) !important;
        background: var(--primary-container) !important;
        border-color: var(--primary) !important;
        text-decoration: none !important;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.2);
    }

    .page-item.active .page-link {
        color: white !important;
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
    }

    .page-item.disabled .page-link {
        color: var(--on-surface-variant) !important;
        background: var(--surface-variant) !important;
        border-color: var(--outline-variant) !important;
        cursor: not-allowed;
        opacity: 0.6;
        box-shadow: none;
    }

    .page-item.disabled .page-link:hover {
        color: var(--on-surface-variant) !important;
        background: var(--surface-variant) !important;
        border-color: var(--outline-variant) !important;
        box-shadow: none;
    }

    /* Style prev/next arrows */
    .page-link[aria-label*="Previous"]::before {
        content: '‹';
        font-size: 18px;
        font-weight: bold;
        line-height: 1;
    }

    .page-link[aria-label*="Next"]::before {
        content: '›';
        font-size: 18px;
        font-weight: bold;
        line-height: 1;
    }

    .page-link[aria-label*="Previous"],
    .page-link[aria-label*="Next"] {
        font-size: 0;
        padding: 0.5rem 0.75rem;
    }

    /* Hide default text in prev/next buttons */
    .page-link[aria-label*="Previous"] span,
    .page-link[aria-label*="Next"] span {
        display: none;
    }

    /* Ensure numbers are properly styled */
    .page-link:not([aria-label*="Previous"]):not([aria-label*="Next"]) {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        width: 100%;
        margin: 1rem 0;
    }

    .pagination-wrapper nav {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    /* Hide screen reader text */
    .pagination-wrapper .sr-only,
    .pagination-wrapper .hidden {
        display: none !important;
    }

    /* Override any Bootstrap or default pagination styles */
    .pagination li {
        display: flex !important;
        margin: 0 !important;
    }

    .pagination a, .pagination span {
        border-radius: 0.5rem !important;
        margin: 0 !important;
    }

    /* Ensure consistent button appearance */
    .page-link, .page-link:focus {
        outline: none !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    }

    .page-link:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
    }

    /* Force inline-flex for proper alignment */
    .pagination-container .pagination {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Override any inherited styles */
    .pagination-container * {
        box-sizing: border-box;
    }

    /* Ensure all pagination items are on same line */
    .pagination > li {
        float: none !important;
        display: inline-flex !important;
    }

    /* Responsive pagination */
    @media (max-width: 768px) {
        .pagination {
            gap: 0.25rem;
        }
        
        .page-link {
            min-width: 2rem;
            height: 2rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>

@endsection

@section('scripts')
<script>
console.log('Tickets script loaded successfully');

function openEditTicketModal(ticketId) {
    console.log('Opening edit modal for ticket ID:', ticketId);
    
    // Fetch ticket data
    fetch(`/admin/tickets/${ticketId}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Received data:', data);
        if (data.success) {
            const ticket = data.ticket;
            
            // Populate form fields
            document.getElementById('edit_ticket_id').value = ticket.id;
            document.getElementById('edit_ticket_name').value = ticket.ticket_name;
            document.getElementById('edit_event_id').value = ticket.event_id || '';
            document.getElementById('edit_total_quantity').value = ticket.total_quantity || '';
            document.getElementById('edit_description').value = ticket.description || '';
            document.getElementById('edit_is_active').checked = ticket.is_active;
            
            // Set selected countries
            const countriesSelect = document.getElementById('edit_countries');
            Array.from(countriesSelect.options).forEach(option => {
                option.selected = false;
            });
            
            // Create array to maintain order for proper indexing
            const selectedCountries = [];
            ticket.countries.forEach(country => {
                const option = countriesSelect.querySelector(`option[value="${country.id}"]`);
                if (option) {
                    option.selected = true;
                    selectedCountries.push(country);
                }
            });
            
            // Update country pricing section using the reliable method
            const countriesForForm = selectedCountries.map((country) => ({
                value: country.id,
                name: country.name,
                adultPrice: country.pivot.adult_price,
                childPrice: country.pivot.child_price
            }));
            
            const section = document.getElementById('editCountryPricingSection');
            section.style.display = 'block';
            rebuildCountryPricingFields(countriesForForm);
            
            // Set form action
            document.getElementById('editTicketForm').action = `/admin/tickets/${ticketId}`;
            
            // Show modal
            document.getElementById('editTicketModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            console.log('Edit modal should be visible now');
            
            // Add event listener for active status checkbox
            const activeCheckbox = document.getElementById('edit_is_active');
            if (activeCheckbox) {
                activeCheckbox.addEventListener('change', function() {
                    updateTicketStatusIcon(ticketId, this.checked);
                });
            }
        } else {
            console.error('Server error:', data.message);
            alert('Error loading ticket data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error loading ticket data: ' + error.message);
    });
}

window.openEditTicketModal = openEditTicketModal;

function closeEditTicketModal() {
    document.getElementById('editTicketModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('editTicketForm').reset();
    
    // Reset country pricing section
    const pricingSection = document.getElementById('editCountryPricingSection');
    const pricingContainer = document.getElementById('editCountryPricingContainer');
    pricingSection.style.display = 'none';
    pricingContainer.innerHTML = '';
}

function updateTicketStatusIcon(ticketId, isActive) {
    // Find the eye icon for this specific ticket in the table
    const editButton = document.querySelector(`button[onclick="openEditTicketModal(${ticketId})"]`);
    if (editButton && editButton.parentElement) {
        const eyeLink = editButton.parentElement.querySelector('a .material-icons');
        if (eyeLink) {
            if (isActive) {
                eyeLink.textContent = 'visibility';
                eyeLink.style.color = 'var(--success)';
                eyeLink.parentElement.style.color = '';
            } else {
                eyeLink.textContent = 'visibility_off';
                eyeLink.style.color = 'var(--error)';
                eyeLink.parentElement.style.color = 'var(--error)';
            }
        }
    }
}

function rebuildCountryPricingFields(countries) {
    console.log('=== REBUILDING COUNTRY PRICING FIELDS ===');
    console.log('Countries to build:', countries);
    
    const container = document.getElementById('editCountryPricingContainer');
    container.innerHTML = '';
    
    // Remove any existing hidden countries[] fields from this container
    const existingHiddenFields = container.querySelectorAll('input[name="countries[]"]');
    existingHiddenFields.forEach(field => field.remove());
    
    countries.forEach((country, index) => {
        console.log(`Building field for index ${index}:`, country);
        
        const countryDiv = document.createElement('div');
        countryDiv.className = 'country-pricing-item';
        countryDiv.style.cssText = `
            border: 1px solid var(--outline);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: var(--surface-variant);
        `;
        
        countryDiv.innerHTML = `
            <h4 style="margin: 0 0 1rem 0; color: var(--on-surface);">${country.name.trim()}</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Adult Price (RM):</label>
                    <input type="number" 
                           name="countries_data[${index}][adult_price]" 
                           value="${country.adultPrice || ''}" 
                           step="0.01" 
                           min="0" 
                           required 
                           class="form-control"
                           data-country-id="${country.value}">
                </div>
                <div class="form-group">
                    <label>Child Price (RM):</label>
                    <input type="number" 
                           name="countries_data[${index}][child_price]" 
                           value="${country.childPrice || ''}" 
                           step="0.01" 
                           min="0" 
                           required 
                           class="form-control"
                           data-country-id="${country.value}">
                </div>
            </div>
        `;
        
        container.appendChild(countryDiv);
    });
    
    console.log('Finished building country pricing fields');
}

// Test function for add form fields
function testAddFormFields() {
    console.log('=== ADD FORM FIELDS TEST ===');
    
    const form = document.getElementById('ticketForm');
    const container = document.getElementById('countryPricingContainer');
    
    if (!container) {
        console.log('Container not found');
        return;
    }
    
    const allInputs = container.querySelectorAll('input');
    console.log(`Found ${allInputs.length} input fields in add form container:`);
    
    allInputs.forEach((input, index) => {
        console.log(`  ${index}: name="${input.name}" value="${input.value}" type="${input.type}"`);
    });
    
    // Test FormData
    const formData = new FormData(form);
    const countriesData = formData.getAll('countries[]');
    console.log(`\nAdd FormData countries: [${countriesData.join(', ')}]`);
    
    for (let i = 0; i < countriesData.length; i++) {
        const adult = formData.get(`countries_data[${i}][adult_price]`);
        const child = formData.get(`countries_data[${i}][child_price]`);
        console.log(`  Index ${i}: adult="${adult}" child="${child}"`);
    }
    
    alert(`Found ${allInputs.length} fields in add form. Check console for details.`);
}

function openTicketModal() {
        document.getElementById('ticketModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        updateFinalPrice();
    }

    function closeTicketModal() {
        document.getElementById('ticketModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('ticketForm').reset();
        
        // Reset country pricing section
        const pricingSection = document.getElementById('countryPricingSection');
        const pricingContainer = document.getElementById('countryPricingContainer');
        pricingSection.style.display = 'none';
        pricingContainer.innerHTML = '';
    }

    function updateCountryPricing() {
        console.log('=== ADD FORM COUNTRY PRICING UPDATE ===');
        const countriesSelect = document.getElementById('countries');
        const pricingSection = document.getElementById('countryPricingSection');
        const pricingContainer = document.getElementById('countryPricingContainer');
        
        const selectedOptions = Array.from(countriesSelect.selectedOptions);
        console.log('Selected countries for add form:', selectedOptions.length);
        
        if (selectedOptions.length === 0) {
            console.log('No countries selected, hiding section');
            pricingSection.style.display = 'none';
            pricingContainer.innerHTML = '';
            return;
        }
        
        console.log('Countries selected, showing section and building fields');
        pricingSection.style.display = 'block';
        pricingContainer.innerHTML = '';
        
        selectedOptions.forEach((option, index) => {
            const countryId = option.value;
            const countryName = option.textContent.trim();
            
            const countryDiv = document.createElement('div');
            countryDiv.className = 'country-pricing-item';
            countryDiv.style.cssText = `
                border: 1px solid var(--outline);
                border-radius: 0.5rem;
                padding: 1rem;
                margin-bottom: 1rem;
                background: var(--surface-variant);
            `;
            
            countryDiv.innerHTML = `
                <h4 style="margin: 0 0 1rem 0; color: var(--on-surface); font-size: 1rem;">${countryName}</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label class="form-label">Adult Price (RM) *</label>
                        <input type="number" 
                               name="countries_data[${index}][adult_price]" 
                               class="form-input country-adult-price" 
                               step="0.01" 
                               min="0" 
                               placeholder="0.00"
                               data-country-id="${countryId}"
                               required>
                    </div>
                    <div>
                        <label class="form-label">Child Price (RM) *</label>
                        <input type="number" 
                               name="countries_data[${index}][child_price]" 
                               class="form-input country-child-price" 
                               step="0.01" 
                               min="0" 
                               placeholder="0.00"
                               data-country-id="${countryId}"
                               required>
                    </div>
                </div>
            `;
            
            pricingContainer.appendChild(countryDiv);
        });
        
        // Price calculation listeners removed - no multipliers needed
    }


    // Edit Ticket Modal Functions (COMMENTED OUT - USING WINDOW FUNCTION ABOVE)
    console.log('About to define openEditTicketModal function...');
    
    /*function openEditTicketModal(ticketId) {
        console.log('Opening edit modal for ticket ID:', ticketId);
        
        // Fetch ticket data
        fetch(`/admin/tickets/${ticketId}/edit`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            if (data.success) {
                const ticket = data.ticket;
                
                // Populate form fields
                document.getElementById('edit_ticket_id').value = ticket.id;
                document.getElementById('edit_ticket_name').value = ticket.ticket_name;
                document.getElementById('edit_event_id').value = ticket.event_id || '';
                document.getElementById('edit_total_quantity').value = ticket.total_quantity || '';
                document.getElementById('edit_description').value = ticket.description || '';
                document.getElementById('edit_is_active').checked = ticket.is_active;
                
                // Set selected countries
                const countriesSelect = document.getElementById('edit_countries');
                Array.from(countriesSelect.options).forEach(option => {
                    option.selected = false;
                });
                
                // Create array to maintain order for proper indexing
                const selectedCountries = [];
                ticket.countries.forEach(country => {
                    const option = countriesSelect.querySelector(`option[value="${country.id}"]`);
                    if (option) {
                        option.selected = true;
                        selectedCountries.push(country);
                    }
                });
                
                // Update country pricing section using the reliable method
                const countriesForForm = selectedCountries.map((country) => ({
                    value: country.id,
                    name: country.name,
                    adultPrice: country.pivot.adult_price,
                    childPrice: country.pivot.child_price
                }));
                
                const section = document.getElementById('editCountryPricingSection');
                section.style.display = 'block';
                rebuildCountryPricingFields(countriesForForm);
                
                // Set form action
                document.getElementById('editTicketForm').action = `/admin/tickets/${ticketId}`;
                
                // Show modal
                document.getElementById('editTicketModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
                console.log('Edit modal should be visible now');
            } else {
                console.error('Server error:', data.message);
                alert('Error loading ticket data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading ticket data: ' + error.message);
        });
    }*/

    function closeEditTicketModal() {
        document.getElementById('editTicketModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('editTicketForm').reset();
        
        // Reset country pricing section
        const pricingSection = document.getElementById('editCountryPricingSection');
        const pricingContainer = document.getElementById('editCountryPricingContainer');
        pricingSection.style.display = 'none';
        pricingContainer.innerHTML = '';
    }

    function updateEditCountryPricing(existingCountries = null) {
        const countriesSelect = document.getElementById('edit_countries');
        const pricingSection = document.getElementById('editCountryPricingSection');
        const pricingContainer = document.getElementById('editCountryPricingContainer');
        
        let selectedOptions = [];
        
        if (existingCountries) {
            // When loading existing data
            selectedOptions = existingCountries.map((country, index) => ({
                value: country.id,
                textContent: country.name,
                existing: country
            }));
        } else {
            // When user changes selection - get fresh selection and reset indices
            if (!countriesSelect) {
                console.error('Countries select element not found');
                return;
            }
            
            const selectedOptionsList = Array.from(countriesSelect.selectedOptions);
            console.log('Fresh selection from dropdown:', selectedOptionsList.length, 'countries');
            
            selectedOptions = selectedOptionsList.map((option, index) => ({
                value: option.value,
                textContent: option.textContent,
                existing: null
            }));
            
            console.log('Mapped selected options:', selectedOptions.map((opt, i) => `${i}: ${opt.value}`));
        }
        
        console.log('Selected options count:', selectedOptions.length);
        console.log('Selected options:', selectedOptions.map((opt, idx) => `${idx}: ${opt.value}`));
        
        if (selectedOptions.length === 0) {
            pricingSection.style.display = 'none';
            pricingContainer.innerHTML = '';
            return;
        }
        
        pricingSection.style.display = 'block';
        pricingContainer.innerHTML = ''; // Clear container before adding new fields
        pricingContainer.innerHTML = '';
        
        selectedOptions.forEach((option, index) => {
            const countryId = option.value;
            const countryName = option.textContent.trim();
            const existingData = option.existing;
            
            console.log(`Creating form fields for country at index ${index}: ID=${countryId}, Name=${countryName}`);
            
            const countryDiv = document.createElement('div');
            countryDiv.className = 'country-pricing-item';
            countryDiv.style.cssText = `
                border: 1px solid var(--outline);
                border-radius: 0.5rem;
                padding: 1rem;
                margin-bottom: 1rem;
                background: var(--surface-variant);
            `;
            
            // IMPORTANT: Use the exact index from the forEach loop (0, 1, 2, etc.)
            const formIndex = index;
            console.log(`Generating form fields with index ${formIndex} for country ${countryId}`);
            
            countryDiv.innerHTML = `
                <h4 style="margin: 0 0 1rem 0; color: var(--on-surface); font-size: 1rem;">${countryName}</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label class="form-label">Adult Price (RM) *</label>
                        <input type="number" 
                               name="countries_data[${formIndex}][adult_price]" 
                               class="form-input country-adult-price" 
                               step="0.01" 
                               min="0" 
                               placeholder="0.00"
                               data-country-id="${countryId}"
                               data-form-index="${formIndex}"
                               value="${existingData ? existingData.pivot.adult_price : ''}"
                               required>
                    </div>
                    <div>
                        <label class="form-label">Child Price (RM) *</label>
                        <input type="number" 
                               name="countries_data[${formIndex}][child_price]" 
                               class="form-input country-child-price" 
                               step="0.01" 
                               min="0" 
                               placeholder="0.00"
                               data-country-id="${countryId}"
                               data-form-index="${formIndex}"
                               value="${existingData ? existingData.pivot.child_price : ''}"
                               required>
                    </div>
                </div>
            `;
            
            pricingContainer.appendChild(countryDiv);
        });
        
        // Price calculation listeners removed - no multipliers needed
    }
    


    // Function to rebuild form fields with guaranteed sequential indexing
    function rebuildCountryPricingFields(countries) {
        console.log('=== REBUILD FUNCTION CALLED ===');
        console.log('Countries passed to rebuild:', countries);
        
        const container = document.getElementById('editCountryPricingContainer');
        if (!container) {
            console.error('Container not found!');
            return;
        }
        
        container.innerHTML = ''; // Clear everything
        console.log('Container cleared');
        
        countries.forEach((country, index) => {
            console.log(`Creating form fields for index ${index}:`);
            console.log(`  Country ID: ${country.value}`);
            console.log(`  Country Name: ${country.name}`);
            console.log(`  Adult Price: ${country.adultPrice}`);
            console.log(`  Child Price: ${country.childPrice}`);
            
            const adultFieldName = `countries_data[${index}][adult_price]`;
            const childFieldName = `countries_data[${index}][child_price]`;
            
            console.log(`  Generated field names: ${adultFieldName}, ${childFieldName}`);
            
            const div = document.createElement('div');
            div.style.cssText = `
                border: 1px solid var(--outline);
                border-radius: 0.5rem;
                padding: 1rem;
                margin-bottom: 1rem;
                background: var(--surface-variant);
            `;
            
            div.innerHTML = `
                <h4 style="margin: 0 0 1rem 0;">Country ${index + 1}: ${country.name}</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label>Adult Price (RM) *</label>
                        <input type="number" 
                               name="${adultFieldName}" 
                               class="form-input" 
                               step="0.01" 
                               min="0" 
                               value="${country.adultPrice || ''}"
                               data-debug-index="${index}"
                               data-debug-country="${country.value}"
                               required>
                    </div>
                    <div>
                        <label>Child Price (RM) *</label>
                        <input type="number" 
                               name="${childFieldName}" 
                               class="form-input" 
                               step="0.01" 
                               min="0" 
                               value="${country.childPrice || ''}"
                               data-debug-index="${index}"
                               data-debug-country="${country.value}"
                               required>
                    </div>
                </div>
            `;
            
            container.appendChild(div);
            
            // Verify the fields were created
            const adultField = container.querySelector(`input[name="${adultFieldName}"]`);
            const childField = container.querySelector(`input[name="${childFieldName}"]`);
            console.log(`  Adult field created: ${adultField ? 'YES' : 'NO'}`);
            console.log(`  Child field created: ${childField ? 'YES' : 'NO'}`);
        });
        
        console.log(`=== REBUILD COMPLETE: ${countries.length} countries, indices 0-${countries.length-1} ===`);
        
        // Final verification - list all form fields in container
        const allFields = container.querySelectorAll('input[name*="countries_data"]');
        console.log('All pricing fields created:');
        allFields.forEach(field => {
            console.log(`  ${field.name} = "${field.value}"`);
        });
    }

    // Debug function to check form data
    function debugFormData(formId) {
        const form = document.getElementById(formId);
        const formData = new FormData(form);
        
        console.log('=== FORM DEBUG ===');
        console.log('Form ID:', formId);
        
        const countries = formData.getAll('countries[]');
        console.log('Selected countries:', countries);
        console.log('Number of countries:', countries.length);
        
        console.log('\nAll form fields:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: "${value}"`);
        }
        
        console.log('\nCountry pricing validation:');
        countries.forEach((countryId, index) => {
            const adultPriceKey = `countries_data[${index}][adult_price]`;
            const childPriceKey = `countries_data[${index}][child_price]`;
            const adultPrice = formData.get(adultPriceKey);
            const childPrice = formData.get(childPriceKey);
            
            console.log(`Country at index ${index} (ID: ${countryId}):`);
            console.log(`  Looking for keys: "${adultPriceKey}", "${childPriceKey}"`);
            console.log(`  Adult Price: "${adultPrice}" (exists: ${adultPrice !== null})`);
            console.log(`  Child Price: "${childPrice}" (exists: ${childPrice !== null})`);
            console.log(`  Valid: ${adultPrice && childPrice && adultPrice !== '' && childPrice !== ''}`);
        });
        
        // Also show in alert for easier viewing
        const summary = `Countries: ${countries.length}\nForm fields: ${Array.from(formData.entries()).length}`;
        alert('Debug info logged to console.\n\n' + summary);
    }

    // Simple test to see what form fields exist right now
    function testFormFields() {
        console.log('=== CURRENT FORM FIELDS TEST ===');
        
        const form = document.getElementById('editTicketForm');
        const container = document.getElementById('editCountryPricingContainer');
        
        if (!container) {
            console.log('Container not found');
            return;
        }
        
        const allInputs = container.querySelectorAll('input');
        console.log(`Found ${allInputs.length} input fields in container:`);
        
        allInputs.forEach((input, index) => {
            console.log(`  ${index}: name="${input.name}" value="${input.value}" type="${input.type}"`);
        });
        
        // Test FormData
        const formData = new FormData(form);
        const countriesData = formData.getAll('countries[]');
        console.log(`\nFormData countries: [${countriesData.join(', ')}]`);
        
        for (let i = 0; i < countriesData.length; i++) {
            const adult = formData.get(`countries_data[${i}][adult_price]`);
            const child = formData.get(`countries_data[${i}][child_price]`);
            console.log(`  Index ${i}: adult="${adult}" child="${child}"`);
        }
        
        alert(`Found ${allInputs.length} fields. Check console for details.`);
    }

    // Add event listeners when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const countriesSelect = document.getElementById('countries');
        const editCountriesSelect = document.getElementById('edit_countries');
        
        if (countriesSelect) {
            countriesSelect.addEventListener('change', updateCountryPricing);
        }
        
        if (editCountriesSelect) {
            editCountriesSelect.addEventListener('change', function() {
                console.log('=== COUNTRY SELECTION CHANGED ===');
                console.log('Selected options count:', this.selectedOptions.length);
                
                const selected = Array.from(this.selectedOptions);
                console.log('Selected options:', selected.map(opt => `${opt.value}: ${opt.textContent}`));
                
                const countries = selected.map((option, mapIndex) => {
                    const country = {
                        value: option.value,
                        name: option.textContent.trim(),
                        adultPrice: '',
                        childPrice: ''
                    };
                    console.log(`Mapped country ${mapIndex}:`, country);
                    return country;
                });
                
                const section = document.getElementById('editCountryPricingSection');
                if (countries.length === 0) {
                    console.log('No countries selected, hiding section');
                    section.style.display = 'none';
                } else {
                    console.log(`${countries.length} countries selected, showing section`);
                    section.style.display = 'block';
                    rebuildCountryPricingFields(countries);
                }
            });
        }
        
        // Handle edit form submission
        const editForm = document.getElementById('editTicketForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = e.target.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="material-icons" style="font-size: 18px;">hourglass_empty</span> Updating...';
                
                console.log('=== FORM SUBMISSION DEBUG ===');
                
                const formData = new FormData(this);
                
                // Debug: Log form data
                console.log('Form data being sent:');
                const formEntries = [];
                const countriesEntries = [];
                const pricingEntries = [];
                
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: "${value}"`);
                    formEntries.push(`${key}: ${value}`);
                    
                    if (key === 'countries[]') {
                        countriesEntries.push(value);
                    } else if (key.includes('countries_data')) {
                        pricingEntries.push([key, value]);
                    }
                }
                
                console.log('\n=== SUMMARY ===');
                console.log('Countries array:', countriesEntries);
                console.log('Pricing data:', pricingEntries);
                console.log('Countries count:', countriesEntries.length);
                console.log('Pricing entries count:', pricingEntries.length);
                
                // Check if indices match
                for (let i = 0; i < countriesEntries.length; i++) {
                    const adultExists = pricingEntries.find(([key]) => key === `countries_data[${i}][adult_price]`);
                    const childExists = pricingEntries.find(([key]) => key === `countries_data[${i}][child_price]`);
                    console.log(`Index ${i}: adult=${adultExists ? 'EXISTS' : 'MISSING'}, child=${childExists ? 'EXISTS' : 'MISSING'}`);
                }
                
                // Validate that we have matching countries and pricing data
                const countries = formData.getAll('countries[]');
                console.log('Countries selected:', countries);
                
                // Get all form entries to see what's actually there
                const allEntries = Array.from(formData.entries());
                const allPricingEntries = allEntries.filter(([key]) => key.includes('countries_data'));
                console.log('Pricing entries found:', allPricingEntries);
                
                let hasValidPricing = true;
                let missingData = [];
                
                // Check each country against available pricing data
                console.log('=== VALIDATION CHECK ===');
                countries.forEach((countryId, expectedIndex) => {
                    console.log(`\n--- Validating Country ${expectedIndex} ---`);
                    console.log(`Country ID: ${countryId}`);
                    
                    // Try to find pricing data for this country at the expected index
                    const adultPriceKey = `countries_data[${expectedIndex}][adult_price]`;
                    const childPriceKey = `countries_data[${expectedIndex}][child_price]`;
                    
                    console.log(`Expected keys: ${adultPriceKey}, ${childPriceKey}`);
                    
                    const adultPrice = formData.get(adultPriceKey);
                    const childPrice = formData.get(childPriceKey);
                    
                    console.log(`Found values: adult="${adultPrice}", child="${childPrice}"`);
                    console.log(`Adult valid: ${adultPrice && adultPrice !== '' && parseFloat(adultPrice) >= 0}`);
                    console.log(`Child valid: ${childPrice && childPrice !== '' && parseFloat(childPrice) >= 0}`);
                    
                    if (!adultPrice || !childPrice || adultPrice === '' || childPrice === '' || parseFloat(adultPrice) < 0 || parseFloat(childPrice) < 0) {
                        hasValidPricing = false;
                        missingData.push(`Country at position ${expectedIndex + 1} (ID: ${countryId})`);
                        
                        console.log(`❌ VALIDATION FAILED for country ${expectedIndex}`);
                        
                        // Try to find if the data exists under a different index
                        const foundAdult = allPricingEntries.find(([key]) => key.includes('adult_price'));
                        const foundChild = allPricingEntries.find(([key]) => key.includes('child_price'));
                        console.log(`Available adult price fields:`, allPricingEntries.filter(([key]) => key.includes('adult_price')));
                        console.log(`Available child price fields:`, allPricingEntries.filter(([key]) => key.includes('child_price')));
                    } else {
                        console.log(`✅ VALIDATION PASSED for country ${expectedIndex}`);
                    }
                });
                
                console.log('=== VALIDATION COMPLETE ===');
                
                if (!hasValidPricing) {
                    alert(`Please ensure all countries have valid pricing data. Missing or invalid data for: ${missingData.join(', ')}`);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeEditTicketModal();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the ticket');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        }
    });

    function deleteTicket(ticketId) {
        if (confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
            fetch(`/admin/tickets/${ticketId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => {
                console.log('Delete response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Delete response data:', data);
                if (data.success) {
                    alert('Ticket deleted successfully');
                    location.reload();
                } else {
                    alert('Error deleting ticket: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('Error deleting ticket: ' + error.message);
            });
        }
    }

    // Bulk delete functionality
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const ticketCheckboxes = document.querySelectorAll('.ticket-checkbox');
        
        ticketCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        
        updateBulkDeleteButton();
    }

    function updateBulkDeleteButton() {
        const selectedCheckboxes = document.querySelectorAll('.ticket-checkbox:checked');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCount = document.getElementById('selectedCount');
        
        if (selectedCheckboxes.length > 0) {
            bulkDeleteBtn.style.display = 'flex';
            selectedCount.textContent = selectedCheckboxes.length;
        } else {
            bulkDeleteBtn.style.display = 'none';
        }
        
        // Update select all checkbox state
        const allCheckboxes = document.querySelectorAll('.ticket-checkbox');
        const selectAllCheckbox = document.getElementById('selectAll');
        
        if (selectedCheckboxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (selectedCheckboxes.length === allCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
        }
    }

    function bulkDeleteTickets() {
        const selectedCheckboxes = document.querySelectorAll('.ticket-checkbox:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Please select tickets to delete.');
            return;
        }
        
        const confirmMessage = `Are you sure you want to delete ${selectedIds.length} ticket(s)? This action cannot be undone.`;
        
        if (confirm(confirmMessage)) {
            fetch('/admin/tickets/bulk-delete', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    ticket_ids: selectedIds
                })
            })
            .then(response => {
                console.log('Bulk delete response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Bulk delete response data:', data);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error deleting tickets: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Bulk delete error:', error);
                alert('Error deleting tickets: ' + error.message);
            });
        }
    }
</script>
@endsection