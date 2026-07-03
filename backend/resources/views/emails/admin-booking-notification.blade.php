<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Notification</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif; }
        .email-wrapper { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #d32f2f; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .header p { color: #ffcdd2; margin: 5px 0 0; font-size: 14px; }
        .content { padding: 30px; }
        .alert-box { background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin-bottom: 20px; }
        .alert-box strong { color: #e65100; font-size: 16px; }
        .booking-ref { font-size: 20px; font-weight: bold; color: #1a1a2e; text-align: center; padding: 15px; background-color: #e3f2fd; border-radius: 6px; margin: 20px 0; }
        .section { margin: 25px 0; }
        .section-title { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #ddd; }
        table.details { width: 100%; border-collapse: collapse; }
        table.details td { padding: 10px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        table.details td:first-child { color: #666; width: 40%; }
        table.details td:last-child { color: #333; font-weight: 600; }
        .customer-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 15px 0; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: bold; }
        .status-confirmed { background-color: #c8e6c9; color: #2e7d32; }
        .status-pending { background-color: #fff3e0; color: #ef6c00; }
        .status-cash { background-color: #e1f5fe; color: #01579b; }
        .total-row td { border-top: 2px solid #d32f2f !important; border-bottom: none !important; font-size: 18px !important; color: #d32f2f !important; }
        .action-buttons { margin: 25px 0; text-align: center; }
        .btn { display: inline-block; padding: 12px 30px; margin: 5px; background-color: #d32f2f; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .btn:hover { background-color: #b71c1c; }
        .footer { background-color: #1a1a2e; padding: 20px 30px; text-align: center; }
        .footer p { color: #999; font-size: 12px; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <h1>🎫 KUANTAN 188 ADMIN</h1>
            <p>New Booking Notification</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="alert-box">
                <strong>📬 New Booking Received!</strong><br>
                <span style="font-size: 14px; color: #666;">A customer has just completed a booking. Details below:</span>
            </div>

            <!-- Booking Reference -->
            <div class="booking-ref">
                📋 {{ $booking->booking_reference }}
            </div>

            <!-- Customer Information -->
            <div class="section">
                <div class="section-title">👤 Customer Information</div>
                <div class="customer-box">
                    <table class="details">
                        <tr>
                            <td>Name</td>
                            <td>{{ $booking->customer_name }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><a href="mailto:{{ $booking->email ?? $booking->customer_email }}">{{ $booking->email ?? $booking->customer_email }}</a></td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>{{ $booking->mobile_phone ?? $booking->customer_phone ?? 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <td>Country</td>
                            <td>{{ $booking->country ?? 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <td>Postal Code</td>
                            <td>{{ $booking->postal_code }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="section">
                <div class="section-title">🎟️ Booking Details</div>
                <table class="details">
                    <tr>
                        <td>Event/Ticket</td>
                        <td>{{ $booking->event_title ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Event Date</td>
                        <td>{{ $booking->event_date ? $booking->event_date->format('d M Y') : 'N/A' }}</td>
                    </tr>
                    @if($booking->selected_time)
                    <tr>
                        <td>Selected Time</td>
                        <td>{{ $booking->selected_time }}</td>
                    </tr>
                    @endif
                    @if($booking->is_all_day_pass)
                    <tr>
                        <td>Pass Type</td>
                        <td><strong>All Day Pass</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <td>Total Tickets</td>
                        <td><strong>{{ $booking->quantity }}</strong> ticket(s)</td>
                    </tr>
                    @if($booking->adult_tickets > 0)
                    <tr>
                        <td>&nbsp;&nbsp;↳ Adult Tickets</td>
                        <td>{{ $booking->adult_tickets }} @ RM{{ number_format($booking->adult_price, 2) }}</td>
                    </tr>
                    @endif
                    @if($booking->child_tickets > 0)
                    <tr>
                        <td>&nbsp;&nbsp;↳ Child Tickets</td>
                        <td>{{ $booking->child_tickets }} @ RM{{ number_format($booking->child_price, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Booking Date</td>
                        <td>{{ $booking->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Payment Details -->
            <div class="section">
                <div class="section-title">💳 Payment Information</div>
                <table class="details">
                    <tr>
                        <td>Payment Method</td>
                        <td>
                            @if($booking->payment_method === 'billplz' || $booking->payment_method === 'online_payment')
                                <span class="status-badge status-pending">Online Payment (Billplz)</span>
                            @elseif($booking->payment_method === 'cash_on_delivery')
                                <span class="status-badge status-cash">Cash on Delivery</span>
                            @else
                                {{ ucfirst(str_replace('_', ' ', $booking->payment_method)) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td>
                            @if($booking->payment_status === 'paid')
                                <span class="status-badge status-confirmed">✓ Paid</span>
                            @else
                                <span class="status-badge status-pending">⏳ Pending</span>
                            @endif
                        </td>
                    </tr>
                    @if($booking->payment_reference)
                    <tr>
                        <td>Payment Reference</td>
                        <td>{{ $booking->payment_reference }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Booking Status</td>
                        <td>
                            @if($booking->status === 'confirmed' || $booking->booking_status === 'confirmed')
                                <span class="status-badge status-confirmed">✓ Confirmed</span>
                            @else
                                <span class="status-badge status-pending">{{ ucfirst($booking->status ?? $booking->booking_status ?? 'Pending') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Total Amount</strong></td>
                        <td><strong>RM {{ number_format($booking->total_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ config('app.url') }}/admin/bookings" class="btn">View in Admin Panel</a>
            </div>

            <p style="color: #999; font-size: 12px; margin-top: 30px; text-align: center;">
                This is an automated notification sent to Kuantan 188 administrators.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Kuantan 188</strong> &mdash; Admin Notification System</p>
            <p>Booking ID: {{ $booking->id }} | Reference: {{ $booking->booking_reference }}</p>
        </div>
    </div>
</body>
</html>
