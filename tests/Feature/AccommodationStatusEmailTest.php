<?php

namespace Tests\Feature;

use App\Mail\AccommodationStatusUpdated;
use App\Models\AccommodationApplication;
use App\Models\AccommodationRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccommodationStatusEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_admitting_student_sends_accommodation_status_email(): void
    {
        Mail::fake();

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
        $response->assertSessionHas(
            'accommodation_email_status',
            $this->statusHandoffMessage('admitted', [$application->email, 'student.account@example.com'])
        );

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'status' => 'admitted',
            'accommodation_room_id' => $room->id,
        ]);

        Mail::assertSent(AccommodationStatusUpdated::class, function (AccommodationStatusUpdated $mail) use ($application, $room) {
            return $mail->hasTo($application->email)
                && $mail->application->id === $application->id
                && $mail->application->status === 'admitted'
                && optional($mail->application->room)->id === $room->id;
        });

        Mail::assertSent(AccommodationStatusUpdated::class, function (AccommodationStatusUpdated $mail) use ($application, $room) {
            return $mail->hasTo('student.account@example.com')
                && $mail->application->id === $application->id
                && $mail->application->status === 'admitted'
                && optional($mail->application->room)->id === $room->id;
        });
    }

    public function test_executive_can_resend_accommodation_status_email_for_a_decided_application(): void
    {
        Mail::fake();

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
        $response->assertSessionHas(
            'accommodation_email_status',
            $this->statusHandoffMessage('admitted', [$application->email, 'student.account@example.com'])
        );

        Mail::assertSent(AccommodationStatusUpdated::class, function (AccommodationStatusUpdated $mail) use ($application, $room) {
            return $mail->hasTo($application->email)
                && $mail->application->id === $application->id
                && $mail->application->status === 'admitted'
                && optional($mail->application->room)->id === $room->id;
        });

        Mail::assertSent(AccommodationStatusUpdated::class, function (AccommodationStatusUpdated $mail) use ($application, $room) {
            return $mail->hasTo('student.account@example.com')
                && $mail->application->id === $application->id
                && $mail->application->status === 'admitted'
                && optional($mail->application->room)->id === $room->id;
        });
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
        Mail::fake();

        [$executive, $application] = $this->createExecutiveAndApplication();

        $response = $this->actingAs($executive)->post(route('student.accommodation.status', $application), [
            'status' => $status,
        ]);

        $response->assertRedirect(route('student.accommodation.pending'));
        $response->assertSessionHas('success', 'Application status updated to ' . ucfirst($status) . '.');
        $response->assertSessionHas(
            'accommodation_email_status',
            $this->statusHandoffMessage($status, [$application->email, 'student.account@example.com'])
        );

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'status' => $status,
            'accommodation_room_id' => null,
        ]);

        Mail::assertSent(AccommodationStatusUpdated::class, function (AccommodationStatusUpdated $mail) use ($application, $status) {
            return $mail->hasTo($application->email)
                && $mail->application->id === $application->id
                && $mail->application->status === $status;
        });

        Mail::assertSent(AccommodationStatusUpdated::class, function (AccommodationStatusUpdated $mail) use ($application, $status) {
            return $mail->hasTo('student.account@example.com')
                && $mail->application->id === $application->id
                && $mail->application->status === $status;
        });
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

    private function statusHandoffMessage(string $status, array $recipients): string
    {
        return 'Accommodation status updated to ' . Str::headline($status) . '. Email handoff completed for ' . implode(' and ', $recipients) . '. This does not confirm inbox delivery.';
    }
}
