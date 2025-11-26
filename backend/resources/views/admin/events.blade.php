@extends('layouts.admin')

@section('title', 'Events Management')

@section('content')
<div class="grid">
    @if (session('success'))
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 1.5rem;">
            <span class="material-icons" style="font-size: 16px; margin-right: 0.5rem;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <!-- Header Actions -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Events Management</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">Manage your events and their tickets</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openEventModal()">
                <span class="material-icons" style="font-size: 18px;">add</span>
                Add New Event
            </button>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Pricing</th>
                            <th>Tickets</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        @if($event->image_url)
                                            <div style="width: 60px; height: 60px; border-radius: 0.5rem; overflow: hidden; flex-shrink: 0;">
                                                <img src="{{ asset($event->image_url) }}" 
                                                     alt="{{ $event->name }}" 
                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @else
                                            <div style="width: 60px; height: 60px; border-radius: 0.5rem; background: var(--surface-variant); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <span class="material-icons" style="color: var(--on-surface-variant); font-size: 24px;">image</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight: 600;">{{ $event->name }}</div>
                                            @if($event->description)
                                                <div style="font-size: 0.875rem; color: var(--on-surface-variant); margin-top: 0.25rem;">
                                                    {{ Str::limit($event->description, 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $event->date->format('M j, Y') }}</div>
                                    <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                        {{ $event->date->format('H:i') }}
                                    </div>
                                </td>
                                <td>{{ $event->location }}</td>
                                <td>
                                    <div style="font-size: 0.875rem;">
                                        @if($event->price)
                                            <div style="display: flex; align-items: center; gap: 0.25rem;">
                                                <span class="material-icons" style="font-size: 14px; color: var(--primary);">person</span>
                                                <div>
                                                    <div style="font-weight: 600;">RM {{ number_format($event->price, 2) }}</div>
                                                    <div style="font-size: 0.75rem; color: var(--on-surface-variant);">per person</div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="color: var(--on-surface-variant); font-style: italic;">Free</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--primary);">confirmation_number</span>
                                        {{ $event->tickets ? $event->tickets->count() : 0 }} tickets
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $bookingsCount = $event->tickets ? $event->tickets->sum(function($ticket) {
                                            return $ticket->bookings ? $ticket->bookings->count() : 0;
                                        }) : 0;
                                    @endphp
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--accent);">book_online</span>
                                        {{ $bookingsCount }} bookings
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $revenue = $event->tickets ? $event->tickets->sum(function($ticket) {
                                            return $ticket->bookings ? $ticket->bookings->where('status', 'confirmed')->sum('total_amount') : 0;
                                        }) : 0;
                                    @endphp
                                    <div style="font-weight: 600; color: var(--success);">
                                        RM {{ number_format($revenue, 2) }}
                                    </div>
                                </td>
                                <td>
                                    @if(!$event->is_active)
                                        <span class="badge badge-error" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                            <span class="material-icons" style="font-size: 12px;">block</span>
                                            Inactive
                                        </span>
                                    @elseif($event->date->isFuture())
                                        <span class="badge badge-success">
                                            <span class="material-icons" style="font-size: 12px;">schedule</span>
                                            Upcoming
                                        </span>
                                    @elseif($event->date->isToday())
                                        <span class="badge badge-warning">
                                            <span class="material-icons" style="font-size: 12px;">today</span>
                                            Today
                                        </span>
                                    @else
                                        <span class="badge badge-error" style="background: rgba(100, 116, 139, 0.1); color: var(--on-surface-variant);">
                                            <span class="material-icons" style="font-size: 12px;">history</span>
                                            Completed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="openEditEventModal({{ $event->id }})" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <span class="material-icons" style="font-size: 16px;">edit</span>
                                        </button>
                                        <button onclick="toggleEventStatus({{ $event->id }}, {{ $event->is_active ? 'true' : 'false' }})" 
                                                class="btn btn-outline" 
                                                style="padding: 0.25rem 0.5rem;" 
                                                title="{{ $event->is_active ? 'Deactivate Event' : 'Activate Event' }}">
                                            <span class="material-icons" style="font-size: 16px; color: {{ $event->is_active ? '#10b981' : '#ef4444' }};">
                                                {{ $event->is_active ? 'visibility' : 'visibility_off' }}
                                            </span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem; color: var(--on-surface-variant);">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                        <span class="material-icons" style="font-size: 48px; opacity: 0.3;">event_busy</span>
                                        <div>
                                            <div style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">No events found</div>
                                            <div>Create your first event to get started</div>
                                        </div>
                                        <a href="#" class="btn btn-primary">
                                            <span class="material-icons" style="font-size: 18px;">add</span>
                                            Create Event
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add Event Modal -->
    <div id="eventModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeEventModal()"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Add New Event</h3>
                <button type="button" class="modal-close" onclick="closeEventModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <form id="eventForm" method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" onsubmit="return debugFormSubmission(event)">
                @csrf
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-input" 
                                   required
                                   placeholder="Enter event title">
                        </div>

                        <div class="form-group">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" 
                                   id="location" 
                                   name="location" 
                                   class="form-input" 
                                   required
                                   placeholder="Enter event location">
                        </div>

                        <div class="form-group">
                            <label for="price" class="form-label">Price Per Person (RM)</label>
                            <input type="number" 
                                   id="price" 
                                   name="price" 
                                   class="form-input" 
                                   step="0.01" 
                                   min="0" 
                                   placeholder="0.00">
                            <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                                Customers can select quantity when booking tickets
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="event_date" class="form-label">Event Date & Time *</label>
                            <input type="datetime-local" 
                                   id="event_date" 
                                   name="event_date" 
                                   class="form-input" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="event_image" class="form-label">Event Image</label>
                            <div class="file-upload-container">
                                <input type="file" 
                                       id="event_image" 
                                       name="event_image" 
                                       class="file-input" 
                                       accept="image/*"
                                       onchange="previewImage(this)">
                                <label for="event_image" class="file-upload-label">
                                    <span class="material-icons">cloud_upload</span>
                                    <span class="upload-text">Choose Image or Drag & Drop</span>
                                    <span class="upload-hint">Supports: JPG, PNG, GIF (Max: 5MB)</span>
                                </label>
                                <div id="imagePreview" class="image-preview" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview">
                                    <button type="button" class="remove-image" onclick="removeImage()">
                                        <span class="material-icons">close</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-textarea" 
                                      rows="4"
                                      placeholder="Enter event description"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="booking_start_date" class="form-label">Booking Start Date</label>
                            <input type="datetime-local" 
                                   id="booking_start_date" 
                                   name="booking_start_date" 
                                   class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="booking_end_date" class="form-label">Booking End Date</label>
                            <input type="datetime-local" 
                                   id="booking_end_date" 
                                   name="booking_end_date" 
                                   class="form-input">
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
                                    Event is active and bookable
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEventModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons" style="font-size: 18px;">save</span>
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Modal Styles */
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

    /* Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--on-surface);
        margin-bottom: 0.5rem;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border);
        border-radius: 0.5rem;
        background: var(--surface);
        color: var(--on-surface);
        font-size: 0.875rem;
        transition: all 0.2s ease;
        outline: none;
    }

    .form-input:focus,
    .form-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
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
        transition: all 0.2s ease;
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

    /* File Upload Styles */
    .file-upload-container {
        position: relative;
    }

    .file-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .file-upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        border: 2px dashed var(--border);
        border-radius: 0.75rem;
        background: var(--surface-variant);
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }

    .file-upload-label:hover {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
    }

    .file-upload-label .material-icons {
        font-size: 2.5rem;
        color: var(--on-surface-variant);
        margin-bottom: 0.5rem;
    }

    .upload-text {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--on-surface);
        margin-bottom: 0.25rem;
    }

    .upload-hint {
        font-size: 0.75rem;
        color: var(--on-surface-variant);
    }

    .image-preview {
        position: relative;
        margin-top: 1rem;
        border-radius: 0.75rem;
        overflow: hidden;
        background: var(--surface);
        border: 1px solid var(--border);
    }

    .image-preview img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
    }

    .remove-image {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .remove-image:hover {
        background: rgba(0, 0, 0, 0.9);
    }

    .remove-image .material-icons {
        font-size: 1.125rem;
    }

    /* File upload drag and drop */
    .file-upload-label.drag-over {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .modal-container {
            margin: 1rem;
            max-width: none;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
    }
</style>
    <!-- Edit Event Modal -->
    <div id="editEventModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeEditEventModal()"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Edit Event</h3>
                <button type="button" class="modal-close" onclick="closeEditEventModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <form id="editEventForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_event_id" name="event_id">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_title" class="form-label">Event Title *</label>
                            <input type="text" 
                                   id="edit_title" 
                                   name="title" 
                                   class="form-input" 
                                   required
                                   placeholder="Enter event title">
                        </div>

                        <div class="form-group">
                            <label for="edit_location" class="form-label">Location *</label>
                            <input type="text" 
                                   id="edit_location" 
                                   name="location" 
                                   class="form-input" 
                                   required
                                   placeholder="Enter event location">
                        </div>

                        <div class="form-group">
                            <label for="edit_price" class="form-label">Price Per Person (RM)</label>
                            <input type="number" 
                                   id="edit_price" 
                                   name="price" 
                                   class="form-input" 
                                   step="0.01" 
                                   min="0" 
                                   placeholder="0.00">
                            <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                                Customers can select quantity when booking tickets
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="edit_event_date" class="form-label">Event Date & Time *</label>
                            <input type="datetime-local" 
                                   id="edit_event_date" 
                                   name="event_date" 
                                   class="form-input" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="edit_event_image" class="form-label">Event Image</label>
                            <div class="file-upload-container">
                                <input type="file" 
                                       id="edit_event_image" 
                                       name="event_image" 
                                       class="file-input" 
                                       accept="image/*"
                                       onchange="previewEditImage(this)">
                                <label for="edit_event_image" class="file-upload-label">
                                    <span class="material-icons">cloud_upload</span>
                                    <span class="upload-text">Choose New Image or Drag & Drop</span>
                                    <span class="upload-hint">Supports: JPG, PNG, GIF (Max: 5MB)</span>
                                </label>
                                <div id="editImagePreview" class="image-preview" style="display: none;">
                                    <img id="editPreviewImg" src="" alt="Preview">
                                    <button type="button" class="remove-image" onclick="removeEditImage()">
                                        <span class="material-icons">close</span>
                                    </button>
                                </div>
                                <div id="currentImagePreview" class="image-preview" style="display: none;">
                                    <img id="currentImg" src="" alt="Current Image">
                                    <div style="position: absolute; bottom: 0.5rem; left: 0.5rem; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">Current Image</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea id="edit_description" 
                                      name="description" 
                                      class="form-textarea" 
                                      rows="4"
                                      placeholder="Enter event description"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="edit_booking_start_date" class="form-label">Booking Start Date</label>
                            <input type="datetime-local" 
                                   id="edit_booking_start_date" 
                                   name="booking_start_date" 
                                   class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="edit_booking_end_date" class="form-label">Booking End Date</label>
                            <input type="datetime-local" 
                                   id="edit_booking_end_date" 
                                   name="booking_end_date" 
                                   class="form-input">
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="edit_is_active" name="is_active" value="1" checked>
                                Active Event
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditEventModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons" style="font-size: 18px;">save</span>
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function closeEventModal() {
        document.getElementById('eventModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('eventForm').reset();
        removeImage(); // Clear any uploaded image
        
        // Reset checkbox to default (checked)
        document.getElementById('is_active').checked = true;
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEventModal();
        }
    });

    // Image preview function
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const uploadLabel = document.querySelector('.file-upload-label');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                input.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                uploadLabel.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    }

    // Remove image function
    function removeImage() {
        const input = document.getElementById('event_image');
        const preview = document.getElementById('imagePreview');
        const uploadLabel = document.querySelector('.file-upload-label');
        
        input.value = '';
        preview.style.display = 'none';
        uploadLabel.style.display = 'flex';
    }

    // Drag and drop functionality
    function setupDragAndDrop() {
        const uploadLabel = document.querySelector('.file-upload-label');
        const fileInput = document.getElementById('event_image');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, () => {
                uploadLabel.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, () => {
                uploadLabel.classList.remove('drag-over');
            }, false);
        });

        uploadLabel.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                previewImage(fileInput);
            }
        }, false);
    }

    function debugFormSubmission(event) {
        const checkbox = document.getElementById('is_active');
        console.log('Checkbox checked:', checkbox.checked);
        console.log('Checkbox value:', checkbox.value);
        
        // Let form submit normally
        return true;
    }

    // Toggle event status
    function toggleEventStatus(eventId, currentStatus) {
        if (confirm('Are you sure you want to ' + (currentStatus ? 'deactivate' : 'activate') + ' this event?')) {
            fetch(`/admin/events/${eventId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to update the UI
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the event status.');
            });
        }
    }

    // Initialize drag and drop when modal opens
    function openEventModal() {
        document.getElementById('eventModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Set default booking start date to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('booking_start_date').value = now.toISOString().slice(0, 16);
        
        // Setup drag and drop
        setupDragAndDrop();
    }

    // Form submission handling
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Creating...';
        submitBtn.disabled = true;
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEventModal();
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success';
                successDiv.style.cssText = 'background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 1.5rem; display: flex; align-items: center;';
                successDiv.innerHTML = '<span class="material-icons" style="font-size: 16px; margin-right: 0.5rem;">check_circle</span>' + data.message;
                
                const contentDiv = document.querySelector('.grid');
                contentDiv.insertBefore(successDiv, contentDiv.firstChild);
                
                // Refresh the events table
                setTimeout(() => location.reload(), 1500);
            } else {
                alert('Error creating event: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating event. Please try again.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Edit Event Modal Functions
    function openEditEventModal(eventId) {
        console.log('Opening edit modal for event ID:', eventId);
        
        // Show loading state
        const modal = document.getElementById('editEventModal');
        if (!modal) {
            console.error('Edit modal element not found');
            alert('Edit modal not found. Please refresh the page.');
            return;
        }
        
        // Fetch event data
        fetch(`/admin/events/${eventId}/edit`, {
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
                const event = data.event;
                
                // Populate form fields
                document.getElementById('edit_event_id').value = event.id;
                document.getElementById('edit_title').value = event.title;
                document.getElementById('edit_location').value = event.location;
                document.getElementById('edit_price').value = event.price || '';
                document.getElementById('edit_description').value = event.description || '';
                document.getElementById('edit_is_active').checked = event.is_active;
                
                // Format dates for datetime-local inputs
                if (event.event_date) {
                    document.getElementById('edit_event_date').value = formatDateForInput(event.event_date);
                }
                if (event.booking_start_date) {
                    document.getElementById('edit_booking_start_date').value = formatDateForInput(event.booking_start_date);
                }
                if (event.booking_end_date) {
                    document.getElementById('edit_booking_end_date').value = formatDateForInput(event.booking_end_date);
                }
                
                // Show current image if exists
                const currentImagePreview = document.getElementById('currentImagePreview');
                const currentImg = document.getElementById('currentImg');
                const editImagePreview = document.getElementById('editImagePreview');
                
                if (editImagePreview) editImagePreview.style.display = 'none';
                
                if (event.image_url) {
                    if (currentImg) currentImg.src = `/` + event.image_url;
                    if (currentImagePreview) currentImagePreview.style.display = 'block';
                } else {
                    if (currentImagePreview) currentImagePreview.style.display = 'none';
                }
                
                // Set form action
                const form = document.getElementById('editEventForm');
                if (form) {
                    form.action = `/admin/events/${eventId}`;
                }
                
                // Show modal
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                console.log('Modal should be visible now');
            } else {
                console.error('Server error:', data.message);
                alert('Error loading event data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading event data: ' + error.message + '. Please check the console for more details.');
        });
    }

    function closeEditEventModal() {
        document.getElementById('editEventModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('editEventForm').reset();
        document.getElementById('editImagePreview').style.display = 'none';
        document.getElementById('currentImagePreview').style.display = 'none';
    }

    function previewEditImage(input) {
        const preview = document.getElementById('editImagePreview');
        const previewImg = document.getElementById('editPreviewImg');
        const currentImagePreview = document.getElementById('currentImagePreview');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                input.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                currentImagePreview.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    }

    function removeEditImage() {
        document.getElementById('edit_event_image').value = '';
        document.getElementById('editImagePreview').style.display = 'none';
        
        // Show current image again if it exists
        const currentImagePreview = document.getElementById('currentImagePreview');
        const currentImg = document.getElementById('currentImg');
        if (currentImg.src && currentImg.src !== window.location.href) {
            currentImagePreview.style.display = 'block';
        }
    }

    function formatDateForInput(dateString) {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Handle edit form submission
    document.getElementById('editEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="material-icons" style="font-size: 18px;">hourglass_empty</span> Updating...';
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        const eventId = document.getElementById('edit_event_id').value;
        
        fetch(`/admin/events/${eventId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEditEventModal();
                
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success';
                successDiv.style.cssText = 'background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 1.5rem; display: flex; align-items: center;';
                successDiv.innerHTML = '<span class="material-icons" style="font-size: 16px; margin-right: 0.5rem;">check_circle</span>' + data.message;
                
                const contentDiv = document.querySelector('.grid');
                contentDiv.insertBefore(successDiv, contentDiv.firstChild);
                
                // Refresh the events table
                setTimeout(() => location.reload(), 1500);
            } else {
                alert('Error updating event: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating event. Please try again.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Close edit modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEditEventModal();
        }
    });
</script>
@endsection