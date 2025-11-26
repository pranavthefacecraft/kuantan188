@extends('layouts.admin')

@section('title', 'Countries Management')

@section('content')
<div class="grid">
    <!-- Header Actions -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Countries Management</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">Manage supported countries for ticket bookings</p>
            </div>
            <a href="#" class="btn btn-primary">
                <span class="material-icons" style="font-size: 18px;">add</span>
                Add New Country
            </a>
        </div>
    </div>

    <!-- Countries Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>Currency Code</th>
                            <th>Total Tickets</th>
                            <th>Total Bookings</th>
                            <th>Total Revenue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($countries as $country)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        @if($country->flag_emoji)
                                            <span style="font-size: 1.5rem;">{{ $country->flag_emoji }}</span>
                                        @else
                                            <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.75rem;">
                                                {{ strtoupper(substr($country->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight: 600;">{{ $country->name }}</div>
                                            @if($country->code)
                                                <div style="font-size: 0.875rem; color: var(--on-surface-variant);">
                                                    {{ strtoupper($country->code) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($country->currency_code)
                                        <span style="font-weight: 500; color: var(--accent);">
                                            {{ strtoupper($country->currency_code) }}
                                        </span>
                                    @else
                                        <span style="color: var(--on-surface-variant);">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--primary);">confirmation_number</span>
                                        {{ $country->tickets ? $country->tickets->count() : 0 }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $totalBookings = $country->tickets ? $country->tickets->sum(function($ticket) {
                                            return $ticket->bookings ? $ticket->bookings->where('status', 'confirmed')->count() : 0;
                                        }) : 0;
                                    @endphp
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="material-icons" style="font-size: 16px; color: var(--accent);">book_online</span>
                                        {{ $totalBookings }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $totalRevenue = $country->tickets ? $country->tickets->sum(function($ticket) {
                                            return $ticket->bookings ? $ticket->bookings->where('status', 'confirmed')->sum('total_amount') : 0;
                                        }) : 0;
                                    @endphp
                                    <div style="font-weight: 600; color: var(--success);">
                                        RM {{ number_format($totalRevenue, 2) }}
                                    </div>
                                </td>
                                <td>
                                    @if($country->is_active ?? true)
                                        <span class="badge badge-success">
                                            <span class="material-icons" style="font-size: 12px;">check_circle</span>
                                            Active
                                        </span>
                                    @else
                                        <span class="badge badge-error">
                                            <span class="material-icons" style="font-size: 12px;">block</span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="#" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <span class="material-icons" style="font-size: 16px;">edit</span>
                                        </a>
                                        <a href="#" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <span class="material-icons" style="font-size: 16px;">visibility</span>
                                        </a>
                                        @if(!$country->tickets || $country->tickets->count() == 0)
                                            <button class="btn btn-outline" 
                                                    style="padding: 0.25rem 0.5rem; color: var(--error);"
                                                    onclick="deleteCountry('{{ $country->id }}')">
                                                <span class="material-icons" style="font-size: 16px;">delete</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--on-surface-variant);">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                        <span class="material-icons" style="font-size: 48px; opacity: 0.3;">public</span>
                                        <div>
                                            <div style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">No countries found</div>
                                            <div>Add countries to enable international bookings</div>
                                        </div>
                                        <a href="#" class="btn btn-primary">
                                            <span class="material-icons" style="font-size: 18px;">add</span>
                                            Add Country
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($countries->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $countries->links() }}
                </div>
            @endif
        </div>
    </div>


</div>
@endsection

@section('scripts')
<script>
    function deleteCountry(countryId) {
        if (confirm('Are you sure you want to delete this country? This action cannot be undone.')) {
            fetch(`/api/countries/${countryId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting country');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting country');
            });
        }
    }
</script>
@endsection