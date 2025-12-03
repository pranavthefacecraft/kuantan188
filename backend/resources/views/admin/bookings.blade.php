@extends('layouts.admin')

@section('title', 'Bookings Management')

@section('content')
<div class="grid">
    <!-- Header Actions -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Bookings Management</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">View and manage customer bookings</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <select class="btn btn-outline" style="padding: 0.5rem 1rem;">
                    <option>All Statuses</option>
                    <option>Confirmed</option>
                    <option>Pending</option>
                    <option>Cancelled</option>
                </select>
                <button class="btn btn-outline">
                    <span class="material-icons" style="font-size: 18px;">download</span>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking Reference</th>
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
                                        <div style="font-weight: 500;">{{ $booking->event->title ?? 'N/A' }}</div>
                                        <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                            {{ $booking->event->event_date ? $booking->event->event_date->format('M j, Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--accent);">public</span>
                                        {{ $booking->country->name ?? 'N/A' }}
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
                                            A: RM{{ number_format($booking->ticket->adult_price, 2) }} × {{ $booking->adult_quantity }}<br>
                                            C: RM{{ number_format($booking->ticket->child_price, 2) }} × {{ $booking->child_quantity }}
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
                                <td colspan="9" style="text-align: center; padding: 3rem; color: var(--on-surface-variant);">
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
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
                    location.reload();
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
</script>
@endsection