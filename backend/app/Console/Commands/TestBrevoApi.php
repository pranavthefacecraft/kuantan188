<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestBrevoApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brevo:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Brevo API email sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing Brevo API email sending...');
        $this->line('');
        
        // Check configuration
        $apiKey = config('services.brevo.api_key');
        $mailer = config('mail.default');
        
        $this->table(
            ['Configuration', 'Value'],
            [
                ['Default Mailer', $mailer],
                ['Brevo API Key Set', $apiKey ? 'Yes (' . strlen($apiKey) . ' chars)' : 'No'],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
            ]
        );
        
        if (empty($apiKey)) {
            $this->error('BREVO_API_KEY is not set!');
            $this->line('');
            $this->line('Please add the following to your .env file:');
            $this->line('BREVO_API_KEY=your_brevo_api_key_here');
            $this->line('MAIL_MAILER=brevo_api');
            $this->line('');
            $this->line('Then run: php artisan config:clear');
            return 1;
        }
        
        if ($mailer !== 'brevo_api') {
            $this->warn("Warning: Default mailer is '{$mailer}', not 'brevo_api'");
            $this->line('Set MAIL_MAILER=brevo_api in .env to use Brevo API by default');
            $this->line('');
        }
        
        try {
            $this->info("Sending test email to: {$email}");
            
            Mail::raw(
                "This is a test email sent via Brevo API.\n\n" .
                "Sent at: " . now()->format('Y-m-d H:i:s') . "\n" .
                "From: Kuantan188 Booking System\n\n" .
                "If you received this email, the Brevo API integration is working correctly!",
                function ($message) use ($email) {
                    $message->to($email, 'Test Recipient')
                            ->subject('Kuantan188 - Brevo API Test Email');
                }
            );
            
            $this->line('');
            $this->info('✓ Email sent successfully!');
            $this->line('');
            $this->line("Check {$email} for the test email.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->line('');
            $this->error('✗ Failed to send email!');
            $this->line('');
            $this->error('Error: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->line('');
                $this->line('Stack trace:');
                $this->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }
}
