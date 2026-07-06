<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FarmerOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $farmerName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Login Verification Code');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.farmer-otp');
    }
}
