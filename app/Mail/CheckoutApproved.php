<?php

namespace App\Mail;

use App\Models\AccommodationApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AccommodationApplication $application,
        public ?string $previousRoomLabel = null
    )
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your checkout request has been approved'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout-approved'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
