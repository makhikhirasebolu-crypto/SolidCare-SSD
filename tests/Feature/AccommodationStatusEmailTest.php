<?php

namespace Tests\Feature;

use App\Models\AccommodationApplication;
use App\Models\AccommodationRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccommodationStatusEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_admitting_student_sends_accommodation_status_email(): void
    {
        [$executive, $application] = $this->createExecutiveAndApplication();
        $room = AccommodationRoom::create([
            'block_name' => 'A',
            'room_number' => 4,
            'capacity' => 4,
        ]);

        $response = $this->actingAs($executive)->post(route('student.accommodation.status', $application), [
            'status' => 'admitted',
            'accommodation_room_id' => $room->id,
        ]);

        $response->assertRedirect(route('student.accommodation.pending'));
        $response->assertSessionHas('success', 'Application status updated to Admitted.');
        $this->assertSessionHasStatusEmail($response, 'admitted', [$application->email, 'student.account@example.com']);

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'status' => 'admitted',
            'accommodation_room_id' => $room->id,
            'admission_processed_by_user_id' => $executive->id,
        ]);

        $this->assertRawAccommodationEmailSent($application->email, 'Accommodation Approved', 'Your accommodation application has been approved.');
        $this->assertRawAccommodationEmailSent('student.account@example.com', 'Accommodation Approved', 'Your accommodation application has been approved.');
        $this->assertRawAccommodationEmailSent($application->email, 'Accommodation Approved', 'Allocated Room:</strong> A-04');
    }

    public function test_executive_can_resend_accommodation_status_email_for_a_decided_application(): void
    {
        [$executive, $application] = $this->createExecutiveAndApplication();
        $room = AccommodationRoom::create([
            'block_name' => 'B',
            'room_number' => 7,
            'capacity' => 4,
        ]);

        $application->update([
            'status' => 'admitted',
            'accommodation_room_id' => $room->id,
        ]);

        $response = $this->actingAs($executive)->post(route('student.accommodation.resend-email', $application));

        $response->assertRedirect(route('student.accommodation.pending'));
        $this->assertSessionHasStatusEmail($response, 'admitted', [$application->email, 'student.account@example.com']);

        $this->assertRawAccommodationEmailSent($application->email, 'Accommodation Approved', 'Your accommodation application has been approved.');
        $this->assertRawAccommodationEmailSent('student.account@example.com', 'Accommodation Approved', 'Your accommodation application has been approved.');
        $this->assertRawAccommodationEmailSent($application->email, 'Accommodation Approved', 'Allocated Room:</strong> B-07');
    }

    public function test_pending_admissions_page_hides_recent_decisions(): void
    {
        [$executive, $application] = $this->createExecutiveAndApplication();

        $this->actingAs($executive)->post(route('student.accommodation.status', $application), [
            'status' => 'rejected',
            'rejection_reason' => 'No rooms are currently available for this applicant.',
        ]);

        $response = $this->actingAs($executive)->get(route('student.accommodation.pending'));

        $response
            ->assertOk()
            ->assertSee('No Pending Applications')
            ->assertDontSee('Recent Admission Decisions')
            ->assertDontSee($application->full_name)
            ->assertDontSee('Decision Done By')
            ->assertDontSee('Resend Email')
            ->assertDontSee(route('student.accommodation.resend-email', $application), false);
    }

    public function test_executive_cannot_resend_email_for_pending_application(): void
    {
        Mail::fake();

        [$executive, $application] = $this->createExecutiveAndApplication();

        $response = $this->actingAs($executive)->post(route('student.accommodation.resend-email', $application));

        $response->assertRedirect(route('student.accommodation.pending'));
        $response->assertSessionHas('error', 'Only admitted, conditional, or rejected applications can have status emails resent.');

        Mail::assertNothingSent();
    }

    #[DataProvider('nonAdmittedStatusesProvider')]
    public function test_executive_status_updates_send_accommodation_status_email_for_non_admitted_outcomes(string $status): void
    {
        [$executive, $application] = $this->createExecutiveAndApplication();

        $payload = [
            'status' => $status,
        ];

        if ($status === 'rejected') {
            $payload['rejection_reason'] = 'No rooms are currently available for this applicant.';
        }

        $response = $this->actingAs($executive)->post(route('student.accommodation.status', $application), $payload);

        $response->assertRedirect(route('student.accommodation.pending'));
        $response->assertSessionHas('success', 'Application status updated to ' . ucfirst($status) . '.');
        $this->assertSessionHasStatusEmail($response, $status, [$application->email, 'student.account@example.com']);

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'status' => $status,
            'accommodation_room_id' => null,
            'admission_processed_by_user_id' => $executive->id,
        ]);

        $expectedSubject = $status === 'rejected'
            ? 'Accommodation Rejected'
            : 'Accommodation Application Conditional';
        $expectedBody = $status === 'rejected'
            ? 'Your accommodation application has been rejected.'
            : 'Your accommodation application status is now Conditional.';

        $this->assertRawAccommodationEmailSent($application->email, $expectedSubject, $expectedBody);
        $this->assertRawAccommodationEmailSent('student.account@example.com', $expectedSubject, $expectedBody);
    }

    public static function nonAdmittedStatusesProvider(): array
    {
        return [
            'rejected' => ['rejected'],
            'conditional' => ['conditional'],
        ];
    }

    private function createExecutiveAndApplication(): array
    {
        $executive = User::create([
            'name' => 'Executive User',
            'email' => 'executive@example.com',
            'password' => 'password123',
            'role' => 'executive',
        ]);

        $student = User::create([
            'name' => 'Student Applicant',
            'email' => 'student.account@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'new',
            'student_id' => '20260001',
            'disability' => 'no',
        ]);

        $application = AccommodationApplication::create([
            'user_id' => $student->id,
            'full_name' => 'Student Applicant',
            'contact_number' => '58000001',
            'national_id' => '1234567890123',
            'email' => 'student.application@example.com',
            'marital_status' => 'Single',
            'nationality' => 'Mosotho',
            'age' => 19,
            'faculty' => 'Science and Technology',
            'programme' => 'Bachelor of Information Technology',
            'check_in_date' => now()->addDays(7)->toDateString(),
            'address' => 'Roma, Lesotho',
            'status' => 'pending',
        ]);

        return [$executive, $application];
    }

    private function statusEmailMessage(string $status, array $recipients): string
    {
        return 'Accommodation status updated to ' . Str::headline($status) . '. Email sent to ' . implode(' and ', $recipients) . '.';
    }

    private function assertSessionHasStatusEmail($response, string $status, array $recipients): void
    {
        $expectedStart = $this->statusEmailMessage($status, $recipients);

        $response->assertSessionHas('accommodation_email_status');

        $actual = session('accommodation_email_status');

        $this->assertIsString($actual);
        $this->assertStringStartsWith($expectedStart, $actual);
    }

    private function assertRawAccommodationEmailSent(string $recipient, string $subject, string $bodyExcerpt): void
    {
        $transport = app('mailer')->getSymfonyTransport();

        $this->assertInstanceOf(ArrayTransport::class, $transport);

        $matched = $transport->messages()->contains(function ($sentMessage) use ($recipient, $subject, $bodyExcerpt) {
            $message = $sentMessage->getOriginalMessage();

            return collect($message->getTo())->contains(fn ($address) => $address->getAddress() === $recipient)
                && $message->getSubject() === $subject
                && str_contains((string) ($message->getTextBody() ?: $message->getHtmlBody()), $bodyExcerpt);
        });

        $this->assertTrue($matched, "Expected raw email to {$recipient} with subject {$subject} was not sent.");
    }
}
