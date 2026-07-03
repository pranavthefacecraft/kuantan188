# Brevo API Email Integration

This system uses Brevo (formerly Sendinblue) API for sending emails, bypassing SMTP firewall restrictions.

## Setup Instructions

### 1. Get Brevo API Key

1. Log in to your Brevo account: https://app.brevo.com/
2. Go to **SMTP & API** → **API Keys**
3. Create a new API key or copy an existing one
4. The API key format looks like: `xkeysib-...` (starts with "xkeysib-")

### 2. Configure Environment Variables

Add these to your `.env` file:

```env
# Brevo API Configuration
BREVO_API_KEY=xkeysib-your-api-key-here
MAIL_MAILER=brevo_api

# Email From Settings
MAIL_FROM_ADDRESS="pranav@thefacecraft.com"
MAIL_FROM_NAME="Kuantan188 Bookings"
```

### 3. Clear Configuration Cache

After updating `.env`, run:

```bash
php artisan config:clear
```

### 4. Test Email Sending

#### Option A: Web Browser Test
Visit: `https://admin.tfcmockup.com/test-brevo-api.php?email=your-email@example.com`

#### Option B: Command Line Test
```bash
php artisan brevo:test your-email@example.com
```

## How It Works

### Custom Transport Driver

The system uses a custom mail transport (`App\Mail\BrevoApiTransport`) that:
- Sends emails via Brevo's REST API (`POST /v3/smtp/email`)
- Bypasses SMTP port restrictions (no ports 25, 465, 587 needed)
- Works through HTTP/HTTPS (port 80/443)
- Supports HTML/text emails, attachments, CC, BCC

### Configuration Files

1. **Transport Class**: `app/Mail/BrevoApiTransport.php`
   - Handles API communication with Brevo

2. **Service Provider**: `app/Providers/BrevoMailServiceProvider.php`
   - Registers the custom transport with Laravel

3. **Mail Config**: `config/mail.php`
   - Defines the `brevo_api` mailer configuration

4. **Services Config**: `config/services.php`
   - Stores Brevo API key configuration

5. **Provider Registration**: `bootstrap/providers.php`
   - Registers the service provider

## Troubleshooting

### "BREVO_API_KEY is not set"
- Check your `.env` file has `BREVO_API_KEY=xkeysib-...`
- Run `php artisan config:clear`
- Verify the key is valid in Brevo dashboard

### "Brevo API error: Unauthorized"
- Your API key is invalid or expired
- Generate a new API key in Brevo dashboard
- Update `.env` and run `php artisan config:clear`

### "From email not verified"
- The `MAIL_FROM_ADDRESS` must be verified in Brevo
- Go to Brevo → **Senders & IP** → **Senders**
- Add and verify your sender email address

## API Rate Limits

Free Brevo plans have daily sending limits:
- **Free**: 300 emails/day
- **Starter**: 20,000 emails/month
- **Business**: 60,000 emails/month

Check your plan at: https://app.brevo.com/settings/plan

## Booking Confirmation Emails

Once configured, booking confirmation emails will be sent automatically when customers complete a booking through the frontend.

The email includes:
- Booking reference number
- Event details (name, date, venue)
- Customer information
- Ticket quantities and total amount
- Payment method

## Testing in Development

For local development, you can use:
```env
MAIL_MAILER=log
```

This will write emails to `storage/logs/laravel.log` instead of sending them.

## Additional Resources

- Brevo API Documentation: https://developers.brevo.com/reference/sendtransacemail
- Laravel Mail Documentation: https://laravel.com/docs/mail
- Support: Contact Brevo support or check Laravel logs in `storage/logs/`
