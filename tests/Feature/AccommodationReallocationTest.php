<?php

namespace Tests\Feature;

use App\Models\AccommodationApplication;
use App\Models\AccommodationRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationReallocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_request_room_reallocation_with_preferred_room(): void
    {
        [$student, $application, , $requestedRoom] = $this->createAdmittedApplication();

        $response = $this->actingAs($student)->post(route('student.accommodation.reallocation.store'), [
            'requested_accommodation_room_id' => $requestedRoom->id,
            'reallocation_reason' => 'Closer to accessible facilities.',
        ]);

        $response->assertRedirect(route('accommodation'));
        $response->assertSessionHas('success', 'Room reallocation request submitted to the warden.');

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'accommodation_room_id' => $application->accommodation_room_id,
            'requested_accommodation_room_id' => $requestedRoom->id,
            'reallocation_status' => 'pending',
            'reallocation_reason' => 'Closer to accessible facilities.',
        ]);
    }

    public function test_warden_can_approve_student_room_reallocation_request(): void
    {
        [$student, $application, , $requestedRoom] = $this->createAdmittedApplication();
        $warden = $this->createWarden();

        $this->actingAs($student)->post(route('student.accommodation.reallocation.store'), [
            'requested_accommodation_room_id' => $requestedRoom->id,
        ]);

        $response = $this->actingAs($warden)->post(route('student.accommodation.reallocation.status', $application), [
            'decision' => 'approved',
        ]);

        $response->assertRedirect(route('accommodation'));
        $response->assertSessionHas('success', 'Room reallocation request approved successfully.');

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'accommodation_room_id' => $requestedRoom->id,
            'requested_accommodation_room_id' => null,
            'reallocation_status' => 'approved',
        ]);
    }

    public function test_warden_can_directly_reallocate_admitted_student(): void
    {
        [, $application, , $newRoom] = $this->createAdmittedApplication();
        $warden = $this->createWarden();

        $response = $this->actingAs($warden)->post(route('student.accommodation.reallocate', $application), [
            'accommodation_room_id' => $newRoom->id,
            'reallocation_reason' => 'Student needs a quieter room.',
        ]);

        $response->assertRedirect(route('accommodation'));
        $response->assertSessionHas('success', 'Student room reallocated successfully.');

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'accommodation_room_id' => $newRoom->id,
            'previous_accommodation_room_id' => $application->accommodation_room_id,
            'requested_accommodation_room_id' => null,
            'reallocation_status' => 'direct',
            'reallocation_reason' => 'Student needs a quieter room.',
            'room_reallocated_by_user_id' => $warden->id,
        ]);
    }

    public function test_ssd_assistant_2_can_manage_reallocation_and_view_report_audit(): void
    {
        [$student, $application, $currentRoom, $requestedRoom] = $this->createAdmittedApplication();
        $assistant = User::create([
            'name' => 'SSD Assistant Two',
            'email' => 'assistant2@example.com',
            'password' => 'password123',
            'role' => 'ssd_assistant_2',
        ]);

        $this->actingAs($student)->post(route('student.accommodation.reallocation.store'), [
            'requested_accommodation_room_id' => $requestedRoom->id,
            'reallocation_reason' => 'Closer to accessible facilities.',
        ]);

        $response = $this->actingAs($assistant)->post(route('student.accommodation.reallocation.status', $application), [
            'decision' => 'approved',
        ]);

        $response->assertRedirect(route('accommodation'));

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'accommodation_room_id' => $requestedRoom->id,
            'previous_accommodation_room_id' => $currentRoom->id,
            'reallocation_status' => 'approved',
            'reallocation_reason' => 'Closer to accessible facilities.',
            'reallocation_approved_by_user_id' => $assistant->id,
        ]);

        $reportResponse = $this->actingAs($assistant)->get(route('accommodation.report', [
            'year' => now()->year,
        ]));

        $reportResponse
            ->assertOk()
            ->assertSee('SSD Assistant Two')
            ->assertSee('Closer to accessible facilities.')
            ->assertSee('AG-01')
            ->assertSee('AF-02');
    }

    public function test_warden_can_view_accommodation_report(): void
    {
        $this->createAdmittedApplication();
        $warden = $this->createWarden();

        $response = $this->actingAs($warden)->get(route('accommodation.report', [
            'year' => now()->year,
        ]));

        $response
            ->assertOk()
            ->assertSee('Accommodation Intake Report')
            ->assertSee('Student Resident');
    }

    public function test_ssd_assistant_2_can_view_payment_report_for_admitted_students_only(): void
    {
        $assistant = User::create([
            'name' => 'SSD Assistant Two',
            'email' => 'assistant2-payments@example.com',
            'password' => 'password123',
            'role' => 'ssd_assistant_2',
        ]);

        $makaungRoom = AccommodationRoom::create([
            'block_name' => 'Makaung',
            'room_number' => 1,
            'capacity' => 4,
        ]);
        $otherRoom = AccommodationRoom::create([
            'block_name' => 'AG',
            'room_number' => 1,
            'capacity' => 4,
        ]);

        $this->createApplicationForRoom('Makaung Resident', $makaungRoom);
        $this->createApplicationForRoom('Other Resident', $otherRoom);
        $pendingMakaung = $this->createApplicationForRoom('Pending Makaung', $makaungRoom);
        $pendingMakaung->update(['status' => 'pending']);

        $response = $this->actingAs($assistant)->get(route('accommodation.payment-report'));

        $response
            ->assertOk()
            ->assertSee('Accommodation Payment Report')
            ->assertSee('Admitted Students')
            ->assertSee('Makaung Resident')
            ->assertSee('Makaung-01')
            ->assertSee('Other Resident')
            ->assertSee('AG-01')
            ->assertDontSee('Pending Makaung');
    }

    public function test_ssd_assistant_2_confirms_makaung_payment_with_receipt_number(): void
    {
        $assistant = User::create([
            'name' => 'SSD Assistant Two',
            'email' => 'assistant2-confirm@example.com',
            'password' => 'password123',
            'role' => 'ssd_assistant_2',
        ]);

        $makaungRoom = AccommodationRoom::create([
            'block_name' => 'Makaung',
            'room_number' => 2,
            'capacity' => 4,
        ]);
        $application = $this->createApplicationForRoom('Paid Makaung Resident', $makaungRoom);

        $response = $this->actingAs($assistant)->post(route('accommodation.payment-report.confirm', $application), [
            'payment_receipt_number' => 'RCPT-2026-001',
        ]);

        $response
            ->assertRedirect(route('accommodation.payment-report'))
            ->assertSessionHas('success', 'Payment receipt confirmed for Paid Makaung Resident.');

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'payment_receipt_number' => 'RCPT-2026-001',
            'payment_status' => 'confirmed',
            'payment_confirmed_by_user_id' => $assistant->id,
        ]);
    }

    public function test_ssd_assistant_2_confirms_payment_from_overview_with_full_name_and_receipt_number(): void
    {
        $assistant = User::create([
            'name' => 'SSD Assistant Two',
            'email' => 'assistant2-overview-confirm@example.com',
            'password' => 'password123',
            'role' => 'ssd_assistant_2',
        ]);

        $room = AccommodationRoom::create([
            'block_name' => 'AF',
            'room_number' => 1,
            'capacity' => 4,
        ]);
        $application = $this->createApplicationForRoom('Overview Paid Student', $room);
        $application->update([
            'student_id' => '20261234',
            'payment_amount' => 105,
            'payment_method' => 'mpesa',
        ]);

        $response = $this->actingAs($assistant)->post(route('accommodation.payment-receipts.confirm'), [
            'full_name' => 'Overview Paid Student',
            'payment_receipt_number' => 'AF-REC-2026-77',
        ]);

        $response
            ->assertRedirect(route('accommodation', ['payment_full_name' => 'Overview Paid Student']))
            ->assertSessionHas('success', 'Payment receipt confirmed for Overview Paid Student.');

        $this->assertDatabaseHas('accommodation_applications', [
            'id' => $application->id,
            'payment_receipt_number' => 'AF-REC-2026-77',
            'payment_status' => 'confirmed',
            'payment_confirmed_by_user_id' => $assistant->id,
        ]);

        $viewResponse = $this->actingAs($assistant)->get(route('accommodation', [
            'payment_full_name' => 'Overview Paid Student',
        ]));

        $viewResponse
            ->assertOk()
            ->assertSee('Payment Receipt Confirmation')
            ->assertSee('Student Payment Report')
            ->assertSee('Confirmed Payment Report')
            ->assertSee('Overview Paid Student')
            ->assertSee('20261234')
            ->assertSee('AF-REC-2026-77')
            ->assertSee('M 105.00')
            ->assertSee('MPESA')
            ->assertSee('AF-01');
    }

    public function test_warden_cannot_confirm_accommodation_payments(): void
    {
        $warden = $this->createWarden();

        $response = $this->actingAs($warden)->get(route('accommodation.payment-report'));

        $response
            ->assertRedirect(route('accommodation'))
            ->assertSessionHas('error', 'Only SSD Assistant 2 can confirm accommodation payments.');
    }

    public function test_warden_admitted_students_are_arranged_by_allocated_room(): void
    {
        $warden = $this->createWarden();

        $afOne = AccommodationRoom::create([
            'block_name' => 'AF',
            'room_number' => 1,
            'capacity' => 4,
        ]);
        $afTwo = AccommodationRoom::create([
            'block_name' => 'AF',
            'room_number' => 2,
            'capacity' => 4,
        ]);
        $agOne = AccommodationRoom::create([
            'block_name' => 'AG',
            'room_number' => 1,
            'capacity' => 4,
        ]);

        $this->createApplicationForRoom('Gamma Student', $agOne);
        $this->createApplicationForRoom('Alpha Student', $afOne);
        $this->createApplicationForRoom('Beta Student', $afTwo);

        $response = $this->actingAs($warden)->get(route('accommodation'));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Alpha Student', 'AF-01', 'Beta Student', 'AF-02', 'Gamma Student', 'AG-01']);
    }

    public function test_warden_and_ssd_assistant_2_can_communicate_in_accommodation(): void
    {
        $warden = $this->createWarden();
        $assistant = User::create([
            'name' => 'SSD Assistant Two',
            'email' => 'assistant2-chat@example.com',
            'password' => 'password123',
            'role' => 'ssd_assistant_2',
        ]);

        $response = $this->actingAs($warden)
            ->from(route('accommodation'))
            ->post(route('accommodation.messages.store'), [
                'message' => 'Please check AF-02 reallocation before end of day.',
            ]);

        $response
            ->assertRedirect(route('accommodation'))
            ->assertSessionHas('success', 'Accommodation message sent successfully.');

        $this->assertDatabaseHas('accommodation_messages', [
            'user_id' => $warden->id,
            'message' => 'Please check AF-02 reallocation before end of day.',
        ]);

        $viewResponse = $this->actingAs($assistant)->get(route('accommodation'));

        $viewResponse
            ->assertOk()
            ->assertSee('Warden and SSD Assistant 2 Communication')
            ->assertSee('Please check AF-02 reallocation before end of day.')
            ->assertSee('Warden User');
    }

    public function test_accommodation_messages_allow_replies(): void
    {
        $warden = $this->createWarden();
        $assistant = User::create([
            'name' => 'SSD Assistant Two',
            'email' => 'assistant2-reply@example.com',
            'password' => 'password123',
            'role' => 'ssd_assistant_2',
        ]);

        $this->actingAs($warden)->post(route('accommodation.messages.store'), [
            'message' => 'Please attend the students waiting for accommodation support.',
        ]);

        $parentMessage = \App\Models\AccommodationMessage::whereNull('parent_id')->firstOrFail();

        $response = $this->actingAs($assistant)
            ->from(route('accommodation'))
            ->post(route('accommodation.messages.store'), [
                'parent_id' => $parentMessage->id,
                'message' => 'I will attend them and update the room list.',
            ]);

        $response
            ->assertRedirect(route('accommodation'))
            ->assertSessionHas('success', 'Accommodation reply sent successfully.');

        $this->assertDatabaseHas('accommodation_messages', [
            'parent_id' => $parentMessage->id,
            'user_id' => $assistant->id,
            'message' => 'I will attend them and update the room list.',
        ]);

        $viewResponse = $this->actingAs($warden)->get(route('accommodation'));

        $viewResponse
            ->assertOk()
            ->assertSee('Please attend the students waiting for accommodation support.')
            ->assertSee('I will attend them and update the room list.')
            ->assertSee('Reply');
    }

    public function test_executive_cannot_post_accommodation_communication_message(): void
    {
        $executive = User::create([
            'name' => 'Executive User',
            'email' => 'executive-chat@example.com',
            'password' => 'password123',
            'role' => 'executive',
        ]);

        $response = $this->actingAs($executive)
            ->from(route('accommodation'))
            ->post(route('accommodation.messages.store'), [
                'message' => 'This should not be posted.',
            ]);

        $response
            ->assertRedirect(route('accommodation'))
            ->assertSessionHas('error', 'Only the warden and SSD Assistant 2 can use accommodation communication.');

        $this->assertDatabaseMissing('accommodation_messages', [
            'user_id' => $executive->id,
            'message' => 'This should not be posted.',
        ]);
    }

    private function createAdmittedApplication(): array
    {
        $student = User::create([
            'name' => 'Student Resident',
            'email' => 'resident@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'new',
            'student_id' => '20260010',
            'disability' => 'no',
        ]);

        $currentRoom = AccommodationRoom::create([
            'block_name' => 'AG',
            'room_number' => 1,
            'capacity' => 4,
        ]);

        $requestedRoom = AccommodationRoom::create([
            'block_name' => 'AF',
            'room_number' => 2,
            'capacity' => 4,
        ]);

        $application = AccommodationApplication::create([
            'user_id' => $student->id,
            'accommodation_room_id' => $currentRoom->id,
            'full_name' => 'Student Resident',
            'contact_number' => '58000010',
            'national_id' => '1234567890110',
            'email' => 'resident.application@example.com',
            'marital_status' => 'Single',
            'nationality' => 'Mosotho',
            'age' => 20,
            'faculty' => 'Science and Technology',
            'programme' => 'Bachelor of Information Technology',
            'intake' => 'January ' . now()->year,
            'check_in_date' => now()->subDays(5)->toDateString(),
            'address' => 'Roma, Lesotho',
            'status' => 'admitted',
        ]);

        return [$student, $application, $currentRoom, $requestedRoom];
    }

    private function createApplicationForRoom(string $studentName, AccommodationRoom $room): AccommodationApplication
    {
        $student = User::create([
            'name' => $studentName,
            'email' => str_replace(' ', '.', strtolower($studentName)) . '@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'new',
            'student_id' => str_replace(' ', '-', strtolower($studentName)),
            'disability' => 'no',
        ]);

        return AccommodationApplication::create([
            'user_id' => $student->id,
            'accommodation_room_id' => $room->id,
            'full_name' => $studentName,
            'contact_number' => '58000010',
            'national_id' => '1234567890110',
            'email' => str_replace(' ', '.', strtolower($studentName)) . '.application@example.com',
            'marital_status' => 'Single',
            'nationality' => 'Mosotho',
            'age' => 20,
            'faculty' => 'Science and Technology',
            'programme' => 'Bachelor of Information Technology',
            'intake' => 'January ' . now()->year,
            'check_in_date' => now()->subDays(5)->toDateString(),
            'address' => 'Roma, Lesotho',
            'status' => 'admitted',
        ]);
    }

    private function createWarden(): User
    {
        return User::create([
            'name' => 'Warden User',
            'email' => 'warden@example.com',
            'password' => 'password123',
            'role' => 'warden',
        ]);
    }
}
