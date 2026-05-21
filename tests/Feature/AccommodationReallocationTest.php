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
