<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif; }
        .email-wrapper { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #1a1a2e; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .header p { color: #cccccc; margin: 5px 0 0; font-size: 14px; }
        .content { padding: 30px; }
        .greeting { font-size: 18px; color: #333; margin-bottom: 20px; }
        .booking-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .booking-ref { font-size: 22px; font-weight: bold; color: #1a1a2e; text-align: center; padding: 15px; background-color: #e8f5e9; border-radius: 6px; margin-bottom: 20px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { color: #666; font-size: 14px; }
        .detail-value { color: #333; font-weight: bold; font-size: 14px; }
        table.details { width: 100%; border-collapse: collapse; }
        table.details td { padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        table.details td:first-child { color: #666; }
        table.details td:last-child { color: #333; font-weight: bold; text-align: right; }
        .total-row td { border-top: 2px solid #333 !important; border-bottom: none !important; font-size: 16px !important; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: bold; }
        .status-confirmed { background-color: #e8f5e9; color: #2e7d32; }
        .status-pending { background-color: #fff3e0; color: #ef6c00; }
        .payment-notice { background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; font-size: 14px; }
        .footer { background-color: #1a1a2e; padding: 20px 30px; text-align: center; }
        .footer p { color: #999; font-size: 12px; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <h1>KUANTAN 188</h1>
            <p>Booking Confirmation</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Hello {{ $booking->customer_name }},</p>

            @if($booking->payment_status === 'paid' || $booking->isCashPayment())
                <p>Your booking has been <strong>confirmed</strong>! Here are your booking details:</p>
            @else
                <p>Your booking has been received. Please complete your payment to confirm the booking.</p>
            @endif

            <!-- Booking Reference -->
            <div class="booking-ref">
                {{ $booking->booking_reference }}
            </div>

            <!-- Booking Details -->
            <div class="booking-box">
                <table class="details">
                    <tr>
                        <td>Event</td>
                        <td>{{ $booking->event_title ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>{{ $booking->event_date ? $booking->event_date->format('d M Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Tickets</td>
                        <td>{{ $booking->quantity }} ticket(s)</td>
                    </tr>
                    @if($booking->adult_tickets > 0)
                    <tr>
                        <td>&nbsp;&nbsp;Adult</td>
                        <td>{{ $booking->adult_tickets }}</td>
                    </tr>
                    @endif
                    @if($booking->child_tickets > 0)
                    <tr>
                        <td>&nbsp;&nbsp;Child</td>
                        <td>{{ $booking->child_tickets }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Payment Method</td>
                        <td>{{ $booking->payment_method === 'billplz' ? 'Online Payment (Billplz)' : 'Cash on Delivery' }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            @if($booking->payment_status === 'paid' || $booking->isCashPayment())
                                <span class="status-badge status-confirmed">Confirmed</span>
                            @else
                                <span class="status-badge status-pending">Pending Payment</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Amount</td>
                        <td>RM{{ number_format($booking->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            @if($booking->payment_status === 'pending' && $booking->isBillplzPayment())
                <div class="payment-notice">
                    <strong>Payment Pending:</strong> Please complete your online payment to secure this booking. If you have already paid, your booking will be confirmed shortly.
                </div>
            @endif

            @if($booking->isCashPayment())
                <div class="payment-notice" style="border-left-color: #4caf50; background-color: #e8f5e9;">
                    <strong>Cash on Delivery:</strong> Please bring the exact amount of <strong>RM{{ number_format($booking->total_amount, 2) }}</strong> when you arrive at the venue on the event day.
                </div>
            @endif

            <p style="color: #666; font-size: 14px; margin-top: 25px;">
                If you have any questions about your booking, please contact us at 
                <a href="mailto:info@kuantan188.com">info@kuantan188.com</a>.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Kuantan 188 &mdash; Event Ticketing Platform</p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
