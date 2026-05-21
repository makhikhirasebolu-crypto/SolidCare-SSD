<?php

namespace App\Mail;

use App\Models\ClinicStockReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClinicStockExpiryReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClinicStockReceipt $receipt,
        public string $noticeWindow,
    ) {
    }

    public function envelope(): Envelope
    {
        $medicineName = optional($this->receipt->stockItem)->medicine_name ?? 'Clinic stock';

        return new Envelope(
            subject: $medicineName . ' expires in ' . $this->noticeWindow
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.clinic-stock-expiry-reminder'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
