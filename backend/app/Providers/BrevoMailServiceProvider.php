<?php

namespace App\Providers;

use App\Mail\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class BrevoMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Mail::extend('brevo_api', function (array $config) {
            $apiKey = $config['api_key'] ?? config('services.brevo.api_key');
            
            if (empty($apiKey)) {
                throw new \Exception('Brevo API key is not configured. Please set BREVO_API_KEY in your .env file.');
            }

            return new BrevoApiTransport($apiKey);
        });
    }
}
