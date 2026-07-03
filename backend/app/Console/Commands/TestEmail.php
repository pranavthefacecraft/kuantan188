<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'email:test {recipient?}';
    protected $description = 'Send a test email to verify mail configuration';

    public function handle()
    {
        $recipient = $this->argument('recipient') ?? $this->ask('Enter recipient email address');

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Sending test email to: ' . $recipient);
        $this->info('Using mailer: ' . config('mail.default'));
        $this->info('From: ' . config('mail.from.address'));

        try {
            Mail::raw('This is a test email from Kuantan188 Booking System. If you receive this, your email configuration is working correctly!', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Test Email - Kuantan188 Booking System');
            });

            $this->info('✅ Email sent successfully!');
            $this->info('Check your inbox at: ' . $recipient);
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
