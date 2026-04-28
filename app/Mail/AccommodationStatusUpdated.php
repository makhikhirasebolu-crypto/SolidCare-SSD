<?php

namespace App\Mail;

use App\Models\AccommodationApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class AccommodationStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccommodationApplication $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine()
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.accommodation-status-updated',
            with: [
                'statusLabel' => $this->statusLabel(),
                'heading' => $this->heading(),
                'summary' => $this->summary(),
                'nextSteps' => $this->nextSteps(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function statusLabel(): string
    {
        return Str::headline($this->application->status);
    }

    protected function subjectLine(): string
    {
        return match ($this->application->status) {
            'admitted' => 'Your accommodation application has been admitted',
            'conditional' => 'Your accommodation application is conditional',
            'rejected' => 'Your accommodation application status has been updated',
            default => 'Your accommodation application status has been updated',
        };
    }

    protected function heading(): string
    {
        return match ($this->application->status) {
            'admitted' => 'Accommodation Application Admitted',
            'conditional' => 'Accommodation Application Conditional',
            'rejected' => 'Accommodation Application Update',
            default => 'Accommodation Status Updated',
        };
    }

    protected function summary(): string
    {
        return match ($this->application->status) {
            'admitted' => 'Your accommodation application has been admitted successfully.',
            'conditional' => 'Your accommodation application is currently conditional while the accommodation team completes a final review.',
            'rejected' => 'Your accommodation application was not approved at this time.',
            default => 'Your accommodation application status has been updated.',
        };
    }

    protected function nextSteps(): string
    {
        return match ($this->application->status) {
            'admitted' => 'Please log in to SolidCare SSD to review your room allocation details and prepare for your check-in date.',
            'conditional' => 'Please log in to SolidCare SSD to review your application details and follow any further instructions from the accommodation team.',
            'rejected' => 'Please log in to SolidCare SSD to review the latest application details or contact the accommodation team if you need further guidance.',
            default => 'Please log in to SolidCare SSD to review the latest details of your accommodation application.',
        };
    }
}
