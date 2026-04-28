<?php

namespace App\Mail;

use App\Models\AccommodationApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccommodationApplication $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your checkout request has been received'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout-request-submitted'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
