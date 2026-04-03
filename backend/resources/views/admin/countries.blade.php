@extends('layouts.admin')

@section('title', 'Currency Management')

@section('content')
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
        max-width: 600px;
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

    .form-input {
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        background: var(--surface);
        color: var(--on-surface);
        font-size: 0.875rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-check-input {
        width: 1.125rem;
        height: 1.125rem;
        /* border: 2px solid var(--border); */
        border-radius: 0.25rem;
        background: var(--surface);
        cursor: pointer;
        position: relative;
        appearance: none;
    }

    .form-check-input:checked {
        background: #66001D important!;
        border-color: #66001D;
    }

    .form-check-input:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.875rem;
        font-weight: bold;
    }

    .form-check-label {
        font-size: 0.875rem;
        color: #66001D;
        cursor: pointer;
    }
</style>

<div class="grid">
    <!-- Header Actions -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Currency Management</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">Manage supported currencies for ticket bookings</p>
            </div>
            <button onclick="openCurrencyModal()" class="btn btn-primary">
                <span class="material-icons" style="font-size: 18px;">add</span>
                Add New Currency
            </button>
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
                                        <button onclick="openEditCurrencyModal({{ $country->id }})" class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                            <span class="material-icons" style="font-size: 16px;">edit</span>
                                        </button>
                                        <button onclick="toggleCurrencyStatus({{ $country->id }}, {{ $country->is_active ? 'false' : 'true' }})" 
                                                class="btn btn-outline" 
                                                style="padding: 0.25rem 0.5rem; color: {{ $country->is_active ? 'var(--success)' : 'var(--on-surface-variant)' }};" 
                                                id="toggle-btn-{{ $country->id }}">
                                            <span class="material-icons" style="font-size: 16px;" id="toggle-icon-{{ $country->id }}">
                                                {{ $country->is_active ? 'visibility' : 'visibility_off' }}
                                            </span>
                                        </button>
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

<!-- Add Currency Modal -->
<div id="currencyModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeCurrencyModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Add New Currency</h3>
            <button type="button" class="modal-close" onclick="closeCurrencyModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
        
        <form id="currencyForm" method="POST" action="{{ route('admin.countries.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="country_name" class="form-label">Country Name *</label>
                        <input type="text" 
                               id="country_name" 
                               name="name" 
                               class="form-input" 
                               placeholder="Enter country name"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="country_code" class="form-label">Country Code *</label>
                        <input type="text" 
                               id="country_code" 
                               name="country_code" 
                               class="form-input" 
                               placeholder="e.g., MY, SG, US"
                               maxlength="3"
                               style="text-transform: uppercase;"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="currency_code" class="form-label">Currency Code *</label>
                        <input type="text" 
                               id="currency_code" 
                               name="currency_code" 
                               class="form-input" 
                               placeholder="e.g., MYR, SGD, USD"
                               maxlength="3"
                               style="text-transform: uppercase;"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="currency_symbol" class="form-label">Currency Symbol *</label>
                        <input type="text" 
                               id="currency_symbol" 
                               name="currency_symbol" 
                               class="form-input" 
                               placeholder="e.g., RM, S$, $"
                               maxlength="5"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="exchange_rate" class="form-label">Exchange Rate to MYR</label>
                        <input type="number" 
                               id="exchange_rate" 
                               name="exchange_rate" 
                               class="form-input" 
                               step="0.0001"
                               min="0"
                               placeholder="1.0000"
                               value="1.0000">
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Rate to convert from this currency to MYR (1 unit = X MYR)
                        </small>
                    </div>

                    <div class="form-group full-width">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   class="form-check-input" 
                                   value="1" 
                                   checked>
                            <label for="is_active" class="form-check-label">
                                Active Currency
                            </label>
                        </div>
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Active currencies will be available for ticket pricing
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCurrencyModal()">
                    <span class="material-icons" style="font-size: 18px;">close</span>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px;">save</span>
                    Add Currency
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Currency Modal -->
<div id="editCurrencyModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeEditCurrencyModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Edit Currency</h3>
            <button type="button" class="modal-close" onclick="closeEditCurrencyModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
        
        <form id="editCurrencyForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_country_id" name="country_id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_country_name" class="form-label">Country Name *</label>
                        <input type="text" 
                               id="edit_country_name" 
                               name="name" 
                               class="form-input" 
                               placeholder="Enter country name"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="edit_country_code" class="form-label">Country Code *</label>
                        <input type="text" 
                               id="edit_country_code" 
                               name="country_code" 
                               class="form-input" 
                               placeholder="e.g., MY, SG, US"
                               maxlength="3"
                               style="text-transform: uppercase;"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="edit_currency_code" class="form-label">Currency Code *</label>
                        <input type="text" 
                               id="edit_currency_code" 
                               name="currency_code" 
                               class="form-input" 
                               placeholder="e.g., MYR, SGD, USD"
                               maxlength="3"
                               style="text-transform: uppercase;"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="edit_currency_symbol" class="form-label">Currency Symbol *</label>
                        <input type="text" 
                               id="edit_currency_symbol" 
                               name="currency_symbol" 
                               class="form-input" 
                               placeholder="e.g., RM, S$, $"
                               maxlength="5"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="edit_exchange_rate" class="form-label">Exchange Rate to MYR</label>
                        <input type="number" 
                               id="edit_exchange_rate" 
                               name="exchange_rate" 
                               class="form-input" 
                               step="0.0001"
                               min="0"
                               placeholder="1.0000"
                               value="1.0000">
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Rate to convert from this currency to MYR (1 unit = X MYR)
                        </small>
                    </div>

                    <div class="form-group full-width">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="edit_is_active" 
                                   name="is_active" 
                                   class="form-check-input" 
                                   value="1">
                            <label for="edit_is_active" class="form-check-label">
                                Active Currency
                            </label>
                        </div>
                        <small style="color: var(--on-surface-variant); margin-top: 0.25rem; display: block;">
                            Active currencies will be available for ticket pricing
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditCurrencyModal()">
                    <span class="material-icons" style="font-size: 18px;">close</span>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px;">save</span>
                    Update Currency
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openCurrencyModal() {
        document.getElementById('currencyModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeCurrencyModal() {
        document.getElementById('currencyModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('currencyForm').reset();
    }

    function openEditCurrencyModal(countryId) {
        // Fetch country data for editing
        fetch(`/admin/countries/${countryId}/edit`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const country = data.country;
                
                // Populate form fields
                document.getElementById('edit_country_id').value = country.id;
                document.getElementById('edit_country_name').value = country.name;
                document.getElementById('edit_country_code').value = country.code;
                document.getElementById('edit_currency_code').value = country.currency_code;
                document.getElementById('edit_currency_symbol').value = country.currency_symbol;
                document.getElementById('edit_exchange_rate').value = country.price_multiplier;
                document.getElementById('edit_is_active').checked = country.is_active;
                
                // Set form action
                document.getElementById('editCurrencyForm').action = `/admin/countries/${countryId}`;
                
                // Show modal
                document.getElementById('editCurrencyModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            } else {
                alert('Error loading currency data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading currency data. Please try again.');
        });
    }

    function closeEditCurrencyModal() {
        document.getElementById('editCurrencyModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('editCurrencyForm').reset();
    }

    function toggleCurrencyStatus(countryId, newStatus) {
        const toggleBtn = document.getElementById(`toggle-btn-${countryId}`);
        const toggleIcon = document.getElementById(`toggle-icon-${countryId}`);
        const originalColor = toggleBtn.style.color;
        const originalIcon = toggleIcon.textContent;
        
        // Show loading state
        toggleBtn.disabled = true;
        toggleIcon.textContent = 'hourglass_empty';
        toggleBtn.style.color = 'var(--on-surface-variant)';
        
        fetch(`/admin/countries/${countryId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                is_active: newStatus
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update button appearance
                const isActive = data.country.is_active;
                toggleIcon.textContent = isActive ? 'visibility' : 'visibility_off';
                toggleBtn.style.color = isActive ? 'var(--success)' : 'var(--on-surface-variant)';
                
                // Update onclick attribute for next toggle
                toggleBtn.setAttribute('onclick', `toggleCurrencyStatus(${countryId}, ${!isActive})`);
                
                // Update status badge in the table
                const statusCell = toggleBtn.closest('tr').querySelector('td:nth-last-child(2)');
                if (statusCell) {
                    statusCell.innerHTML = isActive 
                        ? '<span class="badge badge-success"><span class="material-icons" style="font-size: 12px;">check_circle</span> Active</span>'
                        : '<span class="badge badge-error"><span class="material-icons" style="font-size: 12px;">block</span> Inactive</span>';
                }
                
                // Show success message
                const statusText = isActive ? 'activated' : 'deactivated';
                // Optional: Add a small toast notification here
                console.log(`Currency ${statusText} successfully`);
            } else {
                alert('Error updating currency status: ' + (data.message || 'Unknown error'));
                // Restore original appearance
                toggleIcon.textContent = originalIcon;
                toggleBtn.style.color = originalColor;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating currency status. Please try again.');
            // Restore original appearance
            toggleIcon.textContent = originalIcon;
            toggleBtn.style.color = originalColor;
        })
        .finally(() => {
            toggleBtn.disabled = false;
        });
    }

    // Auto-uppercase currency and country codes
    document.addEventListener('DOMContentLoaded', function() {
        const currencyCodeInput = document.getElementById('currency_code');
        const countryCodeInput = document.getElementById('country_code');
        const editCurrencyCodeInput = document.getElementById('edit_currency_code');
        const editCountryCodeInput = document.getElementById('edit_country_code');
        
        if (currencyCodeInput) {
            currencyCodeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
        
        if (countryCodeInput) {
            countryCodeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        if (editCurrencyCodeInput) {
            editCurrencyCodeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
        
        if (editCountryCodeInput) {
            editCountryCodeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        // Handle add form submission
        document.getElementById('currencyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-icons" style="font-size: 18px;">hourglass_empty</span> Adding...';
            
            // Create FormData and submit
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('Network response was not ok');
            })
            .then(data => {
                if (data.success) {
                    closeCurrencyModal();
                    location.reload(); // Refresh to show new currency
                } else {
                    alert('Error adding currency: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding currency. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // Handle edit form submission
        document.getElementById('editCurrencyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-icons" style="font-size: 18px;">hourglass_empty</span> Updating...';
            
            // Create FormData and submit
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('Network response was not ok');
            })
            .then(data => {
                if (data.success) {
                    closeEditCurrencyModal();
                    location.reload(); // Refresh to show updated currency
                } else {
                    alert('Error updating currency: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating currency. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });

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