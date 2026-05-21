<?php

namespace Tests\Feature;

use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTestRouteTest extends TestCase
{
    public function test_test_email_page_loads(): void
    {
        $this->get(route('mail.test'))
            ->assertOk()
            ->assertSee('Send Test Email');
    }

    public function test_test_email_can_be_sent(): void
    {
        Mail::fake();

        $response = $this->from(route('mail.test'))->post(route('mail.test.send'), [
            'to' => 'student@example.com',
            'subject' => 'SolidCare SSD test email',
            'message' => 'This is a test email from SolidCare SSD.',
        ]);

        $response
            ->assertRedirect(route('mail.test'))
            ->assertSessionHas('status', 'Test email sent to student@example.com.');

        Mail::assertSent(TestEmail::class, function (TestEmail $mail) {
            return $mail->hasTo('student@example.com')
                && $mail->subjectText === 'SolidCare SSD test email'
                && $mail->bodyText === 'This is a test email from SolidCare SSD.';
        });
    }
}
