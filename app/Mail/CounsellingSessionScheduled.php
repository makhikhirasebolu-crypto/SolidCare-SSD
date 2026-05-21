<?php

namespace App\Mail;

use App\Models\CounsellingBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CounsellingSessionScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CounsellingBooking $booking)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your counselling session has been scheduled'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.counselling-session-scheduled'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
