@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="grid">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">System Settings</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">Configure system settings and SMTP email configuration</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn btn-outline" onclick="testSmtpConnection()">
                    <span class="material-icons" style="font-size: 18px;">email</span>
                    Test SMTP
                </button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <!-- SMTP Settings -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin: 0 0 1.5rem 0; font-size: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons" style="color: var(--primary);">email</span>
                    SMTP Email Configuration
                </h3>
                
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label for="smtp_host" class="form-label">SMTP Host</label>
                        <input type="text" 
                               id="smtp_host" 
                               name="smtp_host" 
                               class="form-control @error('smtp_host') is-invalid @enderror"
                               value="{{ old('smtp_host', $settings['smtp']['host']) }}"
                               placeholder="smtp.gmail.com"
                               required>
                        @error('smtp_host')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="smtp_port" class="form-label">SMTP Port</label>
                        <input type="number" 
                               id="smtp_port" 
                               name="smtp_port" 
                               class="form-control @error('smtp_port') is-invalid @enderror"
                               value="{{ old('smtp_port', $settings['smtp']['port']) }}"
                               placeholder="587"
                               min="1" 
                               max="65535"
                               required>
                        @error('smtp_port')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="smtp_username" class="form-label">SMTP Username</label>
                        <input type="text" 
                               id="smtp_username" 
                               name="smtp_username" 
                               class="form-control @error('smtp_username') is-invalid @enderror"
                               value="{{ old('smtp_username', $settings['smtp']['username']) }}"
                               placeholder="your-email@gmail.com"
                               required>
                        @error('smtp_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="smtp_password" class="form-label">SMTP Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   id="smtp_password" 
                                   name="smtp_password" 
                                   class="form-control @error('smtp_password') is-invalid @enderror"
                                   placeholder="Leave blank to keep current password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('smtp_password')">
                                <span class="material-icons" style="font-size: 18px;">visibility</span>
                            </button>
                        </div>
                        @error('smtp_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Leave blank to keep current password</small>
                    </div>

                    <div class="form-group">
                        <label for="smtp_encryption" class="form-label">Encryption</label>
                        <select id="smtp_encryption" 
                                name="smtp_encryption" 
                                class="form-control @error('smtp_encryption') is-invalid @enderror"
                                required>
                            <option value="tls" {{ old('smtp_encryption', $settings['smtp']['encryption']) == 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ old('smtp_encryption', $settings['smtp']['encryption']) == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="null" {{ old('smtp_encryption', $settings['smtp']['encryption']) == 'null' ? 'selected' : '' }}>None</option>
                        </select>
                        @error('smtp_encryption')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="from_email" class="form-label">From Email</label>
                        <input type="email" 
                               id="from_email" 
                               name="from_email" 
                               class="form-control @error('from_email') is-invalid @enderror"
                               value="{{ old('from_email', $settings['smtp']['from_email']) }}"
                               placeholder="noreply@kuantan188.com"
                               required>
                        @error('from_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="from_name" class="form-label">From Name</label>
                        <input type="text" 
                               id="from_name" 
                               name="from_name" 
                               class="form-control @error('from_name') is-invalid @enderror"
                               value="{{ old('from_name', $settings['smtp']['from_name']) }}"
                               placeholder="Kuantan188"
                               required>
                        @error('from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- General Settings -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin: 0 0 1.5rem 0; font-size: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons" style="color: var(--primary);">settings</span>
                    General Settings
                </h3>
                
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label for="app_name" class="form-label">Application Name</label>
                        <input type="text" 
                               id="app_name" 
                               name="app_name" 
                               class="form-control @error('app_name') is-invalid @enderror"
                               value="{{ old('app_name', $settings['general']['app_name']) }}"
                               required>
                        @error('app_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="app_url" class="form-label">Application URL</label>
                        <input type="url" 
                               id="app_url" 
                               name="app_url" 
                               class="form-control @error('app_url') is-invalid @enderror"
                               value="{{ old('app_url', $settings['general']['app_url']) }}"
                               required>
                        @error('app_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="timezone" class="form-label">Timezone</label>
                        <select id="timezone" 
                                name="timezone" 
                                class="form-control @error('timezone') is-invalid @enderror"
                                required>
                            <option value="UTC" {{ old('timezone', $settings['general']['timezone']) == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Asia/Kuala_Lumpur" {{ old('timezone', $settings['general']['timezone']) == 'Asia/Kuala_Lumpur' ? 'selected' : '' }}>Asia/Kuala_Lumpur</option>
                            <option value="Asia/Singapore" {{ old('timezone', $settings['general']['timezone']) == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore</option>
                            <option value="Asia/Bangkok" {{ old('timezone', $settings['general']['timezone']) == 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok</option>
                        </select>
                        @error('timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Settings -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin: 0 0 1.5rem 0; font-size: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons" style="color: var(--primary);">book_online</span>
                    Booking Settings
                </h3>
                
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="send_confirmation_email" 
                                   name="send_confirmation_email" 
                                   class="form-check-input"
                                   value="1"
                                   {{ old('send_confirmation_email', $settings['booking']['send_confirmation_email']) ? 'checked' : '' }}>
                            <label for="send_confirmation_email" class="form-check-label">
                                Send Confirmation Emails to Customers
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="send_admin_notification" 
                                   name="send_admin_notification" 
                                   class="form-check-input"
                                   value="1"
                                   {{ old('send_admin_notification', $settings['booking']['send_admin_notification']) ? 'checked' : '' }}>
                            <label for="send_admin_notification" class="form-check-label">
                                Send Admin Notifications for New Bookings
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin_email" class="form-label">Admin Email</label>
                        <input type="email" 
                               id="admin_email" 
                               name="admin_email" 
                               class="form-control @error('admin_email') is-invalid @enderror"
                               value="{{ old('admin_email', $settings['booking']['admin_email']) }}"
                               required>
                        @error('admin_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="card">
            <div class="card-body">
                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-outline" onclick="resetForm()">
                        Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons" style="font-size: 18px;">save</span>
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- SMTP Test Modal -->
    <div id="smtpTestModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeSmtpTestModal()"></div>
        <div class="modal-container" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Test SMTP Connection</h3>
                <button type="button" class="modal-close" onclick="closeSmtpTestModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="test_email" class="form-label">Test Email Address</label>
                    <input type="email" 
                           id="test_email" 
                           class="form-control"
                           placeholder="Enter email to receive test message"
                           required>
                </div>
                <div id="smtpTestResult" style="margin-top: 1rem;"></div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeSmtpTestModal()">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="sendTestEmail()">
                    <span class="material-icons" style="font-size: 16px;">send</span>
                    Send Test Email
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--on-surface);
    }

    .form-control {
        display: block;
        width: 100%;
        padding: 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: var(--on-surface);
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        color: var(--on-surface);
        background-color: var(--surface);
        border-color: var(--primary);
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
    }

    .form-control.is-invalid {
        border-color: var(--error);
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: var(--error);
    }

    .form-check {
        display: block;
        min-height: 1.5rem;
        padding-left: 1.5em;
        margin-bottom: 0.125rem;
    }

    .form-check-input {
        float: left;
        margin-left: -1.5em;
        margin-top: 0.25em;
    }

    .form-check-label {
        color: var(--on-surface);
    }

    .input-group {
        display: flex;
    }

    .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .alert {
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.5rem;
    }

    .alert-success {
        color: var(--success);
        background-color: rgba(var(--success-rgb), 0.1);
        border-color: rgba(var(--success-rgb), 0.2);
    }

    .alert-danger {
        color: var(--error);
        background-color: rgba(var(--error-rgb), 0.1);
        border-color: rgba(var(--error-rgb), 0.2);
    }

    /* Modal styles */
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

    .spinner {
        width: 30px;
        height: 30px;
        border: 3px solid var(--border);
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: inline-block;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('scripts')
<script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('.material-icons');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }

    function resetForm() {
        if (confirm('Are you sure you want to reset all changes?')) {
            document.querySelector('form').reset();
        }
    }

    function testSmtpConnection() {
        document.getElementById('smtpTestModal').style.display = 'flex';
        document.getElementById('test_email').focus();
    }

    function closeSmtpTestModal() {
        document.getElementById('smtpTestModal').style.display = 'none';
        document.getElementById('smtpTestResult').innerHTML = '';
    }

    function sendTestEmail() {
        const email = document.getElementById('test_email').value;
        const resultDiv = document.getElementById('smtpTestResult');
        
        if (!email) {
            resultDiv.innerHTML = '<div class="alert alert-danger">Please enter an email address</div>';
            return;
        }

        // Show loading
        resultDiv.innerHTML = '<div style="text-align: center;"><div class="spinner" style="margin: 0 auto;"></div><p>Sending test email...</p></div>';

        fetch('/admin/test-smtp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                test_email: email
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="alert alert-danger">Error testing SMTP connection</div>';
            console.error('Error:', error);
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSmtpTestModal();
        }
    });
</script>
@endsection