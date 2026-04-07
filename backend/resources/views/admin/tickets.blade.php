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
                            <th>Ticket Name</th>
                            <th>Event</th>
                            <th>Target Audience</th>
                            <th>Adult Price</th>
                            <th>Teenagers From 13</th>
                            <th>University Students</th>
                            <th>Children Below 13</th>
                            <th>Availability</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr data-ticket-id="{{ $ticket->id }}">
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
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        @if($ticket->available_for_malaysians)
                                            <span class="badge badge-success" style="background: var(--primary); color: white; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem;">
                                                <span class="material-icons" style="font-size: 12px;">flag</span>
                                                Malaysian
                                            </span>
                                        @endif
                                        @if($ticket->available_for_non_malaysians)
                                            <span class="badge badge-info" style="background: #FFE6ED; color: #66001D; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem;">
                                                <span class="material-icons" style="font-size: 12px;">public</span>
                                                Non-Malaysian
                                            </span>
                                        @endif
                                        @if(!$ticket->available_for_malaysians && !$ticket->available_for_non_malaysians)
                                            <span style="color: var(--error); font-style: italic; font-size: 0.875rem;">
                                                <span class="material-icons" style="font-size: 14px;">block</span>
                                                No target audience
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($ticket->available_for_malaysians || $ticket->available_for_non_malaysians)
                                        @if($ticket->available_for_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--primary); font-size: 0.875rem;">
                                                    Malaysian: RM {{ number_format($ticket->malaysian_adult_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                        @if($ticket->available_for_non_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--accent); font-size: 0.875rem;">
                                                    Non-Malaysian: ${{ number_format($ticket->non_malaysian_adult_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span style="color: var(--on-surface-variant);">No pricing set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->available_for_malaysians || $ticket->available_for_non_malaysians)
                                        @if($ticket->available_for_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--primary); font-size: 0.875rem;">
                                                    Malaysian: RM {{ number_format($ticket->malaysian_teen_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                        @if($ticket->available_for_non_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--accent); font-size: 0.875rem;">
                                                    Non-Malaysian: ${{ number_format($ticket->non_malaysian_teen_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span style="color: var(--on-surface-variant);">No pricing set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->available_for_malaysians || $ticket->available_for_non_malaysians)
                                        @if($ticket->available_for_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--primary); font-size: 0.875rem;">
                                                    Malaysian: RM {{ number_format($ticket->malaysian_university_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                        @if($ticket->available_for_non_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--accent); font-size: 0.875rem;">
                                                    Non-Malaysian: ${{ number_format($ticket->non_malaysian_university_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span style="color: var(--on-surface-variant);">No pricing set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->available_for_malaysians || $ticket->available_for_non_malaysians)
                                        @if($ticket->available_for_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--primary); font-size: 0.875rem;">
                                                    Malaysian: RM {{ number_format($ticket->malaysian_child_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                        @if($ticket->available_for_non_malaysians)
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="font-weight: 600; color: var(--accent); font-size: 0.875rem;">
                                                    Non-Malaysian: ${{ number_format($ticket->non_malaysian_child_price ?? 0, 2) }}
                                                </div>
                                            </div>
                                        @endif
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
                                        <button onclick="openViewTicketModal({{ $ticket->id }})" class="btn btn-outline" style="padding: 0.25rem 0.5rem; {{ $ticket->is_active ? '' : 'color: var(--error);' }}">
                                            <span class="material-icons" style="font-size: 16px; {{ $ticket->is_active ? 'color: var(--success);' : 'color: var(--error);' }}">
                                                {{ $ticket->is_active ? 'visibility' : 'visibility_off' }}
                                            </span>
                                        </button>
                                        <button class="btn btn-outline" 
                                                style="padding: 0.25rem 0.5rem; color: var(--error);"
                                                onclick="deleteTicket('{{ $ticket->id }}', {{ $totalBookings }})">
                                            <span class="material-icons" style="font-size: 16px;">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 3rem; color: var(--on-surface-variant);">
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
        
        <form id="editTicketForm" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitEditTicketForm();">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_ticket_id" name="ticket_id">
            <div class="modal-body">
                <!-- Success Message Container -->
                <div id="editSuccessMessage" class="success-message" style="display: none;">
                    <span class="material-icons">check_circle</span>
                    <span class="success-text"></span>
                </div>
                
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

                    <!-- Malaysian Availability Options for Edit -->
                    <div class="form-group full-width">
                        <label class="form-label">Target Audience *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="checkbox-group">
                                <input type="hidden" name="available_for_malaysians" value="0">
                                <input type="checkbox" 
                                       id="edit_available_for_malaysians" 
                                       name="available_for_malaysians" 
                                       class="checkbox-input" 
                                       value="1" 
                                       onchange="handleEditTargetAudienceChange('malaysians')">
                                <label for="edit_available_for_malaysians" class="checkbox-label">
                                    <span class="material-icons" style="font-size: 18px; color: var(--primary); margin-right: 0.5rem;">flag</span>
                                    Malaysian
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="hidden" name="available_for_non_malaysians" value="0">
                                <input type="checkbox" 
                                       id="edit_available_for_non_malaysians" 
                                       name="available_for_non_malaysians" 
                                       class="checkbox-input" 
                                       value="1" 
                                       onchange="handleEditTargetAudienceChange('non_malaysians')">
                                <label for="edit_available_for_non_malaysians" class="checkbox-label">
                                    <span class="material-icons" style="font-size: 18px; color: var(--accent); margin-right: 0.5rem;">public</span>
                                    Non-Malaysian
                                </label>
                            </div>
                        </div>
                        <small style="color: var(--on-surface-variant); margin-top: 0.5rem; display: block;">
                            Select which groups can purchase this ticket. You can select both options.
                        </small>
                    </div>

                    <!-- Malaysian Pricing Section for Edit -->
                    <div class="form-group full-width" id="editMalaysianPricingSection" style="display: none;">
                        <label class="form-label">
                            <span class="material-icons" style="font-size: 18px; color: var(--primary); margin-right: 0.5rem; vertical-align: middle;">flag</span>
                            Malaysian Pricing *
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                            <div>
                                <label class="form-label">Adult Price (RM)</label>
                                <input type="number" id="edit_malaysian_adult_price" name="malaysian_adult_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Teen Price (RM)</label>
                                <input type="number" id="edit_malaysian_teen_price" name="malaysian_teen_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">University Price (RM)</label>
                                <input type="number" id="edit_malaysian_university_price" name="malaysian_university_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Child Price (RM)</label>
                                <input type="number" id="edit_malaysian_child_price" name="malaysian_child_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Non-Malaysian Pricing Section for Edit -->
                    <div class="form-group full-width" id="editNonMalaysianPricingSection" style="display: none;">
                        <label class="form-label">
                            <span class="material-icons" style="font-size: 18px; color: var(--accent); margin-right: 0.5rem; vertical-align: middle;">public</span>
                            Non-Malaysian Pricing *
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                            <div>
                                <label class="form-label">Adult Price (USD)</label>
                                <input type="number" id="edit_non_malaysian_adult_price" name="non_malaysian_adult_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Teen Price (USD)</label>
                                <input type="number" id="edit_non_malaysian_teen_price" name="non_malaysian_teen_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">University Price (USD)</label>
                                <input type="number" id="edit_non_malaysian_university_price" name="non_malaysian_university_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Child Price (USD)</label>
                                <input type="number" id="edit_non_malaysian_child_price" name="non_malaysian_child_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
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
                        <label for="edit_ticket_image" class="form-label">Ticket Image</label>
                        <input type="file" 
                               id="edit_ticket_image" 
                               name="ticket_image" 
                               class="form-input" 
                               accept="image/*"
                               onchange="previewTicketImage(this, 'edit')">
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Upload a new image to replace the current one (JPG, PNG, WebP - Max: 2MB)
                        </small>
                        <div id="editImagePreview" style="margin-top: 1rem; display: none;">
                            <div id="editCurrentImage" style="margin-bottom: 0.5rem;">
                                <label style="font-size: 0.875rem; color: var(--on-surface-variant);">Current Image:</label>
                                <div id="editCurrentImageContainer"></div>
                            </div>
                            <div id="editNewImagePreview" style="display: none;">
                                <label style="font-size: 0.875rem; color: var(--on-surface-variant);">New Image Preview:</label>
                                <div>
                                    <img id="editPreviewImg" src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 1px solid var(--outline);">
                                    <button type="button" onclick="removeImagePreview('edit')" style="margin-left: 0.5rem; color: var(--error); background: none; border: none; cursor: pointer;">
                                        <span class="material-icons" style="font-size: 16px;">close</span>
                                    </button>
                                </div>
                            </div>
                        </div>
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
                <button type="button" class="btn btn-primary" onclick="submitEditTicketForm()" id="updateTicketBtn">
                    <span class="btn-text">
                        <span class="material-icons" style="font-size: 18px;">save</span>
                        Update Ticket
                    </span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner"></span>
                        Updating...
                    </span>
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
        
        <!-- Success Message for Create Ticket -->
        <div id="createSuccessMessage" style="display: none; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; margin: 1rem; border-radius: 8px; align-items: center;">
            <span class="material-icons" style="color: #28a745; margin-right: 0.5rem;">check_circle</span>
            <span class="success-text">Ticket created successfully!</span>
        </div>
        
        <form id="ticketForm" method="POST" action="{{ route('admin.tickets.store') }}" enctype="multipart/form-data">
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

                    <!-- Malaysian Availability Options -->
                    <div class="form-group full-width">
                        <label class="form-label">Target Audience *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="checkbox-group">
                                <input type="hidden" name="available_for_malaysians" value="0">
                                <input type="checkbox" 
                                       id="available_for_malaysians" 
                                       name="available_for_malaysians" 
                                       class="checkbox-input" 
                                       value="1" 
                                       onchange="handleTargetAudienceChange('malaysians')">
                                <label for="available_for_malaysians" class="checkbox-label">
                                    <span class="material-icons" style="font-size: 18px; color: var(--primary); margin-right: 0.5rem;">flag</span>
                                    Malaysian
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="hidden" name="available_for_non_malaysians" value="0">
                                <input type="checkbox" 
                                       id="available_for_non_malaysians" 
                                       name="available_for_non_malaysians" 
                                       class="checkbox-input" 
                                       value="1" 
                                       onchange="handleTargetAudienceChange('non_malaysians')">
                                <label for="available_for_non_malaysians" class="checkbox-label">
                                    <span class="material-icons" style="font-size: 18px; color: var(--accent); margin-right: 0.5rem;">public</span>
                                    Non-Malaysian
                                </label>
                            </div>
                        </div>
                        <small style="color: var(--on-surface-variant); margin-top: 0.5rem; display: block;">
                            Select which groups can purchase this ticket. You can select both options.
                        </small>
                    </div>

                    <!-- Malaysian Pricing Section -->
                    <div class="form-group full-width" id="malaysianPricingSection" style="display: none;">
                        <label class="form-label">
                            <span class="material-icons" style="font-size: 18px; color: var(--primary); margin-right: 0.5rem; vertical-align: middle;">flag</span>
                            Malaysian Pricing *
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                            <div>
                                <label class="form-label">Adult Price (RM)</label>
                                <input type="number" name="malaysian_adult_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Teen Price (RM)</label>
                                <input type="number" name="malaysian_teen_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">University Price (RM)</label>
                                <input type="number" name="malaysian_university_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Child Price (RM)</label>
                                <input type="number" name="malaysian_child_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Non-Malaysian Pricing Section -->
                    <div class="form-group full-width" id="nonMalaysianPricingSection" style="display: none;">
                        <label class="form-label">
                            <span class="material-icons" style="font-size: 18px; color: var(--accent); margin-right: 0.5rem; vertical-align: middle;">public</span>
                            Non-Malaysian Pricing *
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                            <div>
                                <label class="form-label">Adult Price (USD)</label>
                                <input type="number" name="non_malaysian_adult_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Teen Price (USD)</label>
                                <input type="number" name="non_malaysian_teen_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">University Price (USD)</label>
                                <input type="number" name="non_malaysian_university_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Child Price (USD)</label>
                                <input type="number" name="non_malaysian_child_price" step="0.01" min="0" class="form-input" placeholder="0.00">
                            </div>
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
                        <label for="ticket_image" class="form-label">Ticket Image</label>
                        <input type="file" 
                               id="ticket_image" 
                               name="ticket_image" 
                               class="form-input" 
                               accept="image/*"
                               onchange="previewTicketImage(this, 'add')">
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Upload an image for this ticket (JPG, PNG, WebP - Max: 2MB)
                        </small>
                        <div id="addImagePreview" style="margin-top: 1rem; display: none;">
                            <img id="addPreviewImg" src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 1px solid var(--outline);">
                            <button type="button" onclick="removeImagePreview('add')" style="margin-left: 0.5rem; color: var(--error); background: none; border: none; cursor: pointer;">
                                <span class="material-icons" style="font-size: 16px;">close</span>
                            </button>
                        </div>
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
                <button type="submit" class="btn btn-primary" id="createTicketBtn">
                    <span class="material-icons" style="font-size: 18px;">confirmation_number</span>
                    <span id="createBtnText">Create Ticket</span>
                    <span id="createBtnLoader" class="spinner" style="display: none; margin-left: 0.5rem;"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Modal Styles */
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
        margin-right: 0.5rem;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .btn-loading {
        display: flex;
        align-items: center;
    }
    
    .success-message {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    
    .success-message .material-icons {
        color: #28a745;
        font-size: 20px;
    }
    
    .loading-indicator {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #6c757d;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    
    .loading-indicator .spinner {
        width: 18px;
        height: 18px;
        border: 2px solid #6c757d;
        border-top-color: transparent;
    }
    
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
        max-width: 1000px;
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

        /* View Modal Styles */
        .form-input[readonly], 
        .form-textarea[readonly] {
            background: var(--surface-variant) !important;
            color: var(--on-surface) !important;
            cursor: default !important;
        }

        .form-input[readonly]:focus,
        .form-textarea[readonly]:focus {
            border-color: var(--border) !important;
            box-shadow: none !important;
        }

        /* Loading spinner animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-spinner {
            animation: spin 1s linear infinite;
        }
    }
</style>

<!-- View Ticket Modal -->
<div id="viewTicketModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeViewTicketModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">View Ticket Details</h3>
            <button type="button" class="modal-close" onclick="closeViewTicketModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Ticket Name</label>
                    <input type="text" id="view_ticket_name" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Event</label>
                    <input type="text" id="view_event_name" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div id="view_status_container" style="padding: 0.75rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--surface-variant);">
                        <span id="view_status"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Quantity</label>
                    <input type="text" id="view_total_quantity" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Available Quantity</label>
                    <input type="text" id="view_available_quantity" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Created Date</label>
                    <input type="text" id="view_created_at" class="form-input" readonly>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Currency Pricing</label>
                    
                    <!-- Malaysian Pricing Section -->
                    <div id="view_malaysian_pricing" style="display: none; margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.875rem; color: var(--primary); font-weight: 600;">🇲🇾 Malaysian Pricing (RM)</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">Adult Price</label>
                                <input type="text" id="view_malaysian_adult_price" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">Teen Price</label>
                                <input type="text" id="view_malaysian_teen_price" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">University Price</label>
                                <input type="text" id="view_malaysian_university_price" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">Child Price</label>
                                <input type="text" id="view_malaysian_child_price" class="form-input" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Non-Malaysian Pricing Section -->
                    <div id="view_non_malaysian_pricing" style="display: none;">
                        <label class="form-label" style="font-size: 0.875rem; color: var(--accent); font-weight: 600;">🌍 Non-Malaysian Pricing (RM)</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">Adult Price</label>
                                <input type="text" id="view_non_malaysian_adult_price" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">Teen Price</label>
                                <input type="text" id="view_non_malaysian_teen_price" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">University Price</label>
                                <input type="text" id="view_non_malaysian_university_price" class="form-input" readonly>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">Child Price</label>
                                <input type="text" id="view_non_malaysian_child_price" class="form-input" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- No Pricing Message -->
                    <div id="view_no_pricing" style="display: none;">
                        <input type="text" value="No pricing configured" class="form-input" readonly style="font-style: italic; color: var(--on-surface-variant);">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Description</label>
                    <textarea id="view_description" class="form-input" readonly style="min-height: 100px; resize: vertical;"></textarea>
                </div>

                <div class="form-group full-width" id="view_image_section" style="display: none;">
                    <label class="form-label">Ticket Image</label>
                    <div style="border: 1px solid var(--border); border-radius: 0.5rem; padding: 1rem; text-align: center; background: var(--surface-variant);">
                        <img id="view_ticket_image" src="" alt="Ticket Image" style="max-width: 100%; max-height: 300px; border-radius: 0.5rem;">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeViewTicketModal()">
                <span class="material-icons" style="font-size: 18px;">close</span>
                Close
            </button>
            <button type="button" class="btn btn-primary" onclick="openEditTicketFromView()" id="editFromViewBtn">
                <span class="material-icons" style="font-size: 18px;">edit</span>
                Edit Ticket
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Simple function definition
function openTicketModal() {
    var modal = document.getElementById('ticketModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        alert('Modal not found!');
    }
}

// Assign to window
window.openTicketModal = openTicketModal;

// Close modal function
function closeTicketModal() {
    var modal = document.getElementById('ticketModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Reset form
        var form = document.getElementById('ticketForm');
        if (form) {
            form.reset();
            // Hide pricing sections
            document.getElementById('malaysianPricingSection').style.display = 'none';
            document.getElementById('nonMalaysianPricingSection').style.display = 'none';
            // Reset button state
            resetCreateButton();
        }
        
        // Hide pricing sections
        var malaysianSection = document.getElementById('malaysianPricingSection');
        var nonMalaysianSection = document.getElementById('nonMalaysianPricingSection');
        if (malaysianSection) malaysianSection.style.display = 'none';
        if (nonMalaysianSection) nonMalaysianSection.style.display = 'none';
    }
}

window.closeTicketModal = closeTicketModal;

// Add new ticket to the table dynamically
function addTicketToTable(ticket) {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;
    
    // Remove "No tickets found" row if it exists
    const emptyRow = tbody.querySelector('tr td[colspan="10"]');
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    // Create new row HTML
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-ticket-id', ticket.id);
    
    // Format pricing information
    let pricingHtml = '';
    let targetAudienceHtml = '';
    
    if (ticket.available_for_malaysians) {
        targetAudienceHtml += `
            <span class="badge badge-success" style="background: var(--primary); color: white; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem;">
                <span class="material-icons" style="font-size: 12px;">flag</span>
                Malaysian
            </span>
        `;
        pricingHtml += `
            <div style="margin-bottom: 0.5rem;">
                <div style="font-weight: 600; color: var(--primary); font-size: 0.875rem;">
                    Malaysian: RM ${parseFloat(ticket.malaysian_adult_price || 0).toFixed(2)}
                </div>
            </div>
        `;
    }
    
    if (ticket.available_for_non_malaysians) {
        targetAudienceHtml += `
            <span class="badge badge-info" style="background: var(--accent); color: white; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem;">
                <span class="material-icons" style="font-size: 12px;">public</span>
                Non-Malaysian
            </span>
        `;
        pricingHtml += `
            <div>
                <div style="font-weight: 600; color: var(--accent); font-size: 0.875rem;">
                    Non-Malaysian: $${parseFloat(ticket.non_malaysian_adult_price || 0).toFixed(2)}
                </div>
            </div>
        `;
    }
    
    if (!ticket.available_for_malaysians && !ticket.available_for_non_malaysians) {
        targetAudienceHtml = `
            <span style="color: var(--error); font-style: italic; font-size: 0.875rem;">
                <span class="material-icons" style="font-size: 14px;">block</span>
                No target audience
            </span>
        `;
    }
    
    if (!pricingHtml) {
        pricingHtml = '<span style="color: var(--on-surface-variant); font-style: italic;">No pricing set</span>';
    }
    
    // Generate availability info
    let availabilityHtml;
    if (ticket.total_quantity) {
        availabilityHtml = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div>
                    <div style="font-size: 0.875rem; margin-bottom: 0.25rem;">
                        ${ticket.total_quantity} of ${ticket.total_quantity}
                    </div>
                    <div style="width: 80px; height: 8px; background: var(--border); border-radius: 4px; overflow: hidden;">
                        <div style="width: 100%; height: 100%; background: var(--success); transition: width 0.3s ease;"></div>
                    </div>
                </div>
            </div>
        `;
    } else {
        availabilityHtml = `
            <span class="badge badge-success">
                <span class="material-icons" style="font-size: 12px;">all_inclusive</span>
                Unlimited
            </span>
        `;
    }
    
    newRow.innerHTML = `
        <td>
            <div>
                <div style="font-weight: 600;">${ticket.ticket_name || 'Unnamed Ticket'}</div>
                <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                    ${ticket.description ? (ticket.description.length > 50 ? ticket.description.substring(0, 50) + '...' : ticket.description) : 'No description'}
                </div>
            </div>
        </td>
        <td>
            <span style="color: var(--on-surface-variant); font-style: italic;">
                <span class="material-icons" style="font-size: 14px;">event_busy</span>
                No specific event
            </span>
        </td>
        <td>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                ${targetAudienceHtml}
            </div>
        </td>
        <td>
            ${pricingHtml}
        </td>
        <td>
            ${availabilityHtml}
        </td>
        <td>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-icons" style="font-size: 16px; color: var(--accent);">book_online</span>
                0
            </div>
        </td>
        <td>
            <div style="font-weight: 600; color: var(--success);">
                RM 0.00
            </div>
        </td>
        <td>
            <div style="display: flex; gap: 0.5rem;">
                <button onclick="openEditTicketModal(${ticket.id})" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                    <span class="material-icons" style="font-size: 16px;">edit</span>
                </button>
                <button onclick="openViewTicketModal(${ticket.id})" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                    <span class="material-icons" style="font-size: 16px; color: var(--success);">visibility</span>
                </button>
                <button class="btn btn-outline" 
                        style="padding: 0.25rem 0.5rem; color: var(--error);"
                        onclick="deleteTicket('${ticket.id}', 0)">
                    <span class="material-icons" style="font-size: 16px;">delete</span>
                </button>
            </div>
        </td>
    `;
    
    // Insert at the beginning of the table (most recent first)
    tbody.insertBefore(newRow, tbody.firstChild);
    
    // Add a subtle animation to highlight the new row
    newRow.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
    setTimeout(() => {
        newRow.style.backgroundColor = '';
        newRow.style.transition = 'background-color 0.5s ease';
    }, 2000);
}

// Reset create button to normal state
function resetCreateButton() {
    const btn = document.getElementById('createTicketBtn');
    const btnText = document.getElementById('createBtnText');
    const btnLoader = document.getElementById('createBtnLoader');
    
    if (btn && btnText && btnLoader) {
        btn.disabled = false;
        btnText.textContent = 'Create Ticket';
        btnLoader.style.display = 'none';
    }
}

// Set create button to loading state
function setCreateButtonLoading() {
    const btn = document.getElementById('createTicketBtn');
    const btnText = document.getElementById('createBtnText');
    const btnLoader = document.getElementById('createBtnLoader');
    
    if (btn && btnText && btnLoader) {
        btn.disabled = true;
        btnText.textContent = 'Creating...';
        btnLoader.style.display = 'inline-block';
    }
}

// Submit create ticket form via AJAX
function submitCreateTicketForm(event) {
    // Event is already prevented in the event listener
    
    const form = document.getElementById('ticketForm');
    const formData = new FormData(form);
    
    setCreateButtonLoading();
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        // Log response for debugging
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if the response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // If it's not JSON, log the HTML response for debugging
            return response.text().then(text => {
                console.log('HTML Response:', text);
                throw new Error('Server returned HTML instead of JSON. Check browser console for the full response.');
            });
        }
        return response.json();
    })
    .then(data => {
        resetCreateButton();
        
        if (data.success) {
            // Show success message in the create modal
            showCreateSuccessMessage(data.message || 'Ticket created successfully!');
            
            // Add the new ticket to the table
            if (data.ticket) {
                addTicketToTable(data.ticket);
            }
            
            // Reset form
            form.reset();
            document.getElementById('malaysianPricingSection').style.display = 'none';
            document.getElementById('nonMalaysianPricingSection').style.display = 'none';
            
            // Don't reload the page - keep popup open with success message
        } else {
            alert('Error: ' + (data.message || 'Failed to create ticket'));
        }
    })
    .catch(error => {
        resetCreateButton();
        console.error('Error:', error);
        alert('An error occurred while creating the ticket: ' + error.message);
    });
}

// Target audience change handler
function handleTargetAudienceChange(audienceType) {
    if (audienceType === 'malaysians') {
        const checkbox = document.getElementById('available_for_malaysians');
        const section = document.getElementById('malaysianPricingSection');
        
        if (checkbox && section) {
            section.style.display = checkbox.checked ? 'block' : 'none';
        }
    } else if (audienceType === 'non_malaysians') {
        const checkbox = document.getElementById('available_for_non_malaysians');
        const section = document.getElementById('nonMalaysianPricingSection');
        
        if (checkbox && section) {
            section.style.display = checkbox.checked ? 'block' : 'none';
        }
    }
}

// Assign to window for global access
window.handleTargetAudienceChange = handleTargetAudienceChange;

// Edit ticket modal function
// Show success message in create modal
function showCreateSuccessMessage(message) {
    const successDiv = document.getElementById('createSuccessMessage');
    const successText = successDiv.querySelector('.success-text');
    
    successText.textContent = message;
    successDiv.style.display = 'flex';
    
    // Hide after 5 seconds
    setTimeout(() => {
        successDiv.style.display = 'none';
    }, 5000);
}

// Show success message in edit modal
function showSuccessMessage(message) {
    const successDiv = document.getElementById('editSuccessMessage');
    const successText = successDiv.querySelector('.success-text');
    
    successText.textContent = message;
    successDiv.style.display = 'flex';
    
    // Hide after 5 seconds
    setTimeout(() => {
        successDiv.style.display = 'none';
    }, 5000);
}

// Submit edit ticket form via AJAX
function submitEditTicketForm() {
    const form = document.getElementById('editTicketForm');
    const updateBtn = document.getElementById('updateTicketBtn');
    const btnText = updateBtn.querySelector('.btn-text');
    const btnLoading = updateBtn.querySelector('.btn-loading');
    
    // Show loading state
    btnText.style.display = 'none';
    btnLoading.style.display = 'flex';
    updateBtn.disabled = true;
    
    // Get form data
    const formData = new FormData(form);
    
    // Submit via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.redirected) {
            // If redirected, it's likely a success - reload the page
            window.location.reload();
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            // Show success message in modal instead of alert
            showSuccessMessage('Ticket updated successfully!');
            
            // Optionally refresh the table data without closing modal
            // You can add code here to refresh just the table if needed
        } else if (data && data.errors) {
            // Show validation errors
            let errorMessage = 'Please fix the following errors:\\n';
            for (let field in data.errors) {
                errorMessage += '- ' + data.errors[field].join(', ') + '\\n';
            }
            alert(errorMessage);
        } else {
            alert(data?.message || 'Error updating ticket. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating ticket. Please try again.');
    })
    .finally(() => {
        // Reset button state
        btnText.style.display = 'flex';
        btnLoading.style.display = 'none';
        updateBtn.disabled = false;
    });
}

function openEditTicketModal(ticketId) {
    console.log('Opening edit modal for ticket ID:', ticketId);
    
    var modal = document.getElementById('editTicketModal');
    if (!modal) {
        alert('Edit modal not found!');
        return;
    }
    
    // Show modal immediately
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Reset form and hide any previous success messages
    var form = document.getElementById('editTicketForm');
    if (form) {
        form.reset();
    }
    
    // Hide success message
    var successMsg = document.getElementById('editSuccessMessage');
    if (successMsg) {
        successMsg.style.display = 'none';
    }
    
    // Hide pricing sections initially
    document.getElementById('editMalaysianPricingSection').style.display = 'none';
    document.getElementById('editNonMalaysianPricingSection').style.display = 'none';
    
    // Show loading indicator at top of modal instead of disabling inputs
    var modalBody = document.querySelector('#editTicketModal .modal-body');
    var loadingDiv = document.createElement('div');
    loadingDiv.id = 'editLoadingIndicator';
    loadingDiv.className = 'loading-indicator';
    loadingDiv.innerHTML = '<span class="spinner"></span> Loading ticket data...';
    modalBody.insertBefore(loadingDiv, modalBody.firstChild);
    
    // Fetch ticket data immediately
    fetch('/admin/tickets/' + ticketId + '/edit')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch ticket data');
            }
            return response.json();
        })
        .then(data => {
            // Remove loading indicator
            var loadingIndicator = document.getElementById('editLoadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.remove();
            }
            
            console.log('Received ticket data:', data);
            
            if (data.success && data.ticket) {
                var ticket = data.ticket;
                
                // Populate basic fields quickly
                document.getElementById('edit_ticket_id').value = ticket.id || '';
                document.getElementById('edit_ticket_name').value = ticket.ticket_name || '';
                document.getElementById('edit_event_id').value = ticket.event_id || '';
                document.getElementById('edit_description').value = ticket.description || '';
                document.getElementById('edit_total_quantity').value = ticket.total_quantity || '';
                document.getElementById('edit_is_active').checked = ticket.is_active == 1;
                
                // Handle target audience checkboxes and pricing
                var malaysianCheckbox = document.getElementById('edit_available_for_malaysians');
                var nonMalaysianCheckbox = document.getElementById('edit_available_for_non_malaysians');
                
                if (malaysianCheckbox) {
                    malaysianCheckbox.checked = ticket.available_for_malaysians == 1;
                    if (ticket.available_for_malaysians == 1) {
                        document.getElementById('editMalaysianPricingSection').style.display = 'block';
                        
                        // Populate Malaysian pricing fields quickly
                        document.getElementById('edit_malaysian_adult_price').value = ticket.malaysian_adult_price || '';
                        document.getElementById('edit_malaysian_teen_price').value = ticket.malaysian_teen_price || '';
                        document.getElementById('edit_malaysian_university_price').value = ticket.malaysian_university_price || '';
                        document.getElementById('edit_malaysian_child_price').value = ticket.malaysian_child_price || '';
                    }
                }
                
                if (nonMalaysianCheckbox) {
                    nonMalaysianCheckbox.checked = ticket.available_for_non_malaysians == 1;
                    if (ticket.available_for_non_malaysians == 1) {
                        document.getElementById('editNonMalaysianPricingSection').style.display = 'block';
                        
                        // Populate Non-Malaysian pricing fields quickly
                        document.getElementById('edit_non_malaysian_adult_price').value = ticket.non_malaysian_adult_price || '';
                        document.getElementById('edit_non_malaysian_teen_price').value = ticket.non_malaysian_teen_price || '';
                        document.getElementById('edit_non_malaysian_university_price').value = ticket.non_malaysian_university_price || '';
                        document.getElementById('edit_non_malaysian_child_price').value = ticket.non_malaysian_child_price || '';
                    }
                }
                
                // Handle existing image display
                const imagePreview = document.getElementById('editImagePreview');
                const currentImageContainer = document.getElementById('editCurrentImageContainer');
                
                if (ticket.image_url) {
                    imagePreview.style.display = 'block';
                    // Remove 'storage/' prefix if it exists, then add the correct path
                    let imagePath = ticket.image_url;
                    if (imagePath.startsWith('storage/')) {
                        imagePath = imagePath.substring(8); // Remove 'storage/' prefix
                    }
                    currentImageContainer.innerHTML = `<img src="/storage/${imagePath}" alt="Current Image" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 1px solid var(--outline);">`;
                } else {
                    imagePreview.style.display = 'none';
                    currentImageContainer.innerHTML = '';
                }
                
                console.log('Form populated successfully');
                
                // Set form action to proper update route using Laravel route helper
                document.getElementById('editTicketForm').action = "{{ url('/admin/tickets') }}/" + ticketId;
                
            } else {
                alert('Error: ' + (data.message || 'Failed to load ticket data'));
            }
        })
        .catch(error => {
            // Remove loading indicator on error
            var loadingIndicator = document.getElementById('editLoadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.remove();
            }
            
            console.error('Error fetching ticket:', error);
            alert('Error loading ticket: ' + error.message);
        });
}

// Close edit modal function
function closeEditTicketModal() {
    var modal = document.getElementById('editTicketModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Reset form
        var form = document.getElementById('editTicketForm');
        if (form) {
            form.reset();
        }
        
        // Hide pricing sections
        var malaysianSection = document.getElementById('editMalaysianPricingSection');
        var nonMalaysianSection = document.getElementById('editNonMalaysianPricingSection');
        if (malaysianSection) malaysianSection.style.display = 'none';
        if (nonMalaysianSection) nonMalaysianSection.style.display = 'none';
        
        // Reset checkboxes
        var malaysianCheckbox = document.getElementById('edit_available_for_malaysians');
        var nonMalaysianCheckbox = document.getElementById('edit_available_for_non_malaysians');
        if (malaysianCheckbox) malaysianCheckbox.checked = false;
        if (nonMalaysianCheckbox) nonMalaysianCheckbox.checked = false;
    }
}

// Open view ticket modal
function openViewTicketModal(ticketId) {
    console.log('Opening view modal for ticket ID:', ticketId);
    
    const modal = document.getElementById('viewTicketModal');
    if (!modal) {
        alert('View modal not found!');
        return;
    }
    
    // Show modal immediately
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Show loading indicator at top of modal
    const modalBody = document.querySelector('#viewTicketModal .modal-body');
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'viewLoadingIndicator';
    loadingDiv.className = 'loading-indicator';
    loadingDiv.innerHTML = '<span class="spinner"></span> Loading ticket details...';
    modalBody.insertBefore(loadingDiv, modalBody.firstChild);
    
    // Fetch ticket data
    fetch('/admin/tickets/' + ticketId + '/edit')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch ticket data');
            }
            return response.json();
        })
        .then(data => {
            // Remove loading indicator
            const loadingIndicator = document.getElementById('viewLoadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.remove();
            }
            
            console.log('Received ticket data for view:', data);
            
            if (data.success && data.ticket) {
                const ticket = data.ticket;
                
                // Populate basic fields
                document.getElementById('view_ticket_name').value = ticket.ticket_name || '';
                document.getElementById('view_event_name').value = ticket.event?.name || 'No specific event';
                document.getElementById('view_total_quantity').value = ticket.total_quantity || '';
                document.getElementById('view_available_quantity').value = ticket.available_quantity || '';
                document.getElementById('view_description').value = ticket.description || '';
                document.getElementById('view_created_at').value = new Date(ticket.created_at).toLocaleDateString() || '';
                
                // Set status
                const statusElement = document.getElementById('view_status');
                if (ticket.is_active == 1) {
                    statusElement.innerHTML = '<span style="color: var(--primary); font-weight: 600;">✓ Active</span>';
                } else {
                    statusElement.innerHTML = '<span style="color: var(--error); font-weight: 600;">✗ Inactive</span>';
                }
                
                // Handle pricing display with form fields
                const malaysianPricingSection = document.getElementById('view_malaysian_pricing');
                const nonMalaysianPricingSection = document.getElementById('view_non_malaysian_pricing');
                const noPricingSection = document.getElementById('view_no_pricing');
                
                // Hide all sections initially
                malaysianPricingSection.style.display = 'none';
                nonMalaysianPricingSection.style.display = 'none';
                noPricingSection.style.display = 'none';
                
                let hasPricing = false;
                
                if (ticket.available_for_malaysians == 1) {
                    malaysianPricingSection.style.display = 'block';
                    document.getElementById('view_malaysian_adult_price').value = 'RM ' + (ticket.malaysian_adult_price || '0.00');
                    document.getElementById('view_malaysian_teen_price').value = 'RM ' + (ticket.malaysian_teen_price || '0.00');
                    document.getElementById('view_malaysian_university_price').value = 'RM ' + (ticket.malaysian_university_price || '0.00');
                    document.getElementById('view_malaysian_child_price').value = 'RM ' + (ticket.malaysian_child_price || '0.00');
                    hasPricing = true;
                }
                
                if (ticket.available_for_non_malaysians == 1) {
                    nonMalaysianPricingSection.style.display = 'block';
                    document.getElementById('view_non_malaysian_adult_price').value = 'RM ' + (ticket.non_malaysian_adult_price || '0.00');
                    document.getElementById('view_non_malaysian_teen_price').value = 'RM ' + (ticket.non_malaysian_teen_price || '0.00');
                    document.getElementById('view_non_malaysian_university_price').value = 'RM ' + (ticket.non_malaysian_university_price || '0.00');
                    document.getElementById('view_non_malaysian_child_price').value = 'RM ' + (ticket.non_malaysian_child_price || '0.00');
                    hasPricing = true;
                }
                
                if (!hasPricing) {
                    noPricingSection.style.display = 'block';
                }
                
                // Handle image display
                const imageSection = document.getElementById('view_image_section');
                const imageElement = document.getElementById('view_ticket_image');
                
                if (ticket.image_url) {
                    let imagePath = ticket.image_url;
                    if (imagePath.startsWith('storage/')) {
                        imagePath = imagePath.substring(8);
                    }
                    imageElement.src = `/storage/${imagePath}`;
                    imageSection.style.display = 'block';
                } else {
                    imageSection.style.display = 'none';
                }
                
                // Store ticket ID for edit function
                const editBtn = document.getElementById('editFromViewBtn');
                if (editBtn) {
                    editBtn.setAttribute('data-ticket-id', ticketId);
                }
                
            } else {
                alert('Error: ' + (data.message || 'Failed to load ticket data'));
            }
        })
        .catch(error => {
            // Remove loading indicator on error
            const loadingIndicator = document.getElementById('viewLoadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.remove();
            }
            
            console.error('Error fetching ticket:', error);
            alert('Error loading ticket: ' + error.message);
        });
}

// Close view ticket modal
function closeViewTicketModal() {
    const modal = document.getElementById('viewTicketModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Open edit modal from view modal
function openEditTicketFromView() {
    const editBtn = document.getElementById('editFromViewBtn');
    const ticketId = editBtn.getAttribute('data-ticket-id');
    
    if (ticketId) {
        closeViewTicketModal();
        openEditTicketModal(ticketId);
    } else {
        alert('Error: Ticket ID not found');
    }
}

// Edit form target audience handler
function handleEditTargetAudienceChange(audienceType) {
    console.log('handleEditTargetAudienceChange called for:', audienceType);
    
    if (audienceType === 'malaysians') {
        const checkbox = document.getElementById('edit_available_for_malaysians');
        const section = document.getElementById('editMalaysianPricingSection');
        
        console.log('Malaysian checkbox:', checkbox);
        console.log('Malaysian checkbox checked:', checkbox ? checkbox.checked : 'not found');
        console.log('Malaysian section:', section);
        
        if (checkbox && section) {
            if (checkbox.checked) {
                section.style.display = 'block';
                console.log('Malaysian section shown');
            } else {
                section.style.display = 'none';
                console.log('Malaysian section hidden');
            }
        } else {
            alert('Edit Malaysian elements not found! Checkbox: ' + !!checkbox + ', Section: ' + !!section);
        }
    } else if (audienceType === 'non_malaysians') {
        const checkbox = document.getElementById('edit_available_for_non_malaysians');
        const section = document.getElementById('editNonMalaysianPricingSection');
        
        console.log('Non-Malaysian checkbox:', checkbox);
        console.log('Non-Malaysian checkbox checked:', checkbox ? checkbox.checked : 'not found');
        console.log('Non-Malaysian section:', section);
        
        if (checkbox && section) {
            if (checkbox.checked) {
                section.style.display = 'block';
                console.log('Non-Malaysian section shown');
            } else {
                section.style.display = 'none';
                console.log('Non-Malaysian section hidden');
            }
        } else {
            alert('Edit Non-Malaysian elements not found! Checkbox: ' + !!checkbox + ', Section: ' + !!section);
        }
    }
}

// Delete ticket function
function deleteTicket(ticketId, totalBookings = 0) {
    let confirmMessage = 'Are you sure you want to delete this ticket? This action cannot be undone.';
    
    if (totalBookings > 0) {
        confirmMessage = `WARNING: This ticket has ${totalBookings} existing booking(s).\n\nDeleting this ticket will also remove all associated bookings and cannot be undone.\n\nAre you sure you want to proceed?`;
    }
    
    if (confirm(confirmMessage)) {
        // Use fetch to delete the ticket with AJAX
        fetch(`/admin/tickets/${ticketId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showSuccessMessage(data.message);
                
                // Remove the ticket row from the table
                const ticketRow = document.querySelector(`tr[data-ticket-id="${ticketId}"]`);
                if (ticketRow) {
                    ticketRow.remove();
                }
                
                // Refresh the page after a short delay to update the pagination and totals
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                alert('Error: ' + (data.message || 'Failed to delete ticket'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the ticket.');
        });
    }
}

// Assign edit functions to window
window.openEditTicketModal = openEditTicketModal;
window.closeEditTicketModal = closeEditTicketModal;
window.handleEditTargetAudienceChange = handleEditTargetAudienceChange;
window.deleteTicket = deleteTicket;
window.submitCreateTicketForm = submitCreateTicketForm;

// Add form event listener when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const ticketForm = document.getElementById('ticketForm');
    if (ticketForm) {
        // Remove any existing event listeners first
        ticketForm.removeEventListener('submit', submitCreateTicketForm);
        // Add the event listener
        ticketForm.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            submitCreateTicketForm(event);
            return false;
        });
    }
});
</script>
@endsection
