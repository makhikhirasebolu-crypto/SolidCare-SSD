<?php

namespace Tests\Feature;

use App\Models\CounsellingBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CounsellingTest extends TestCase
{
    use RefreshDatabase;

    public function test_continuing_student_can_view_counselling_options(): void
    {
        config()->set('services.openai.emergency_counselling_model', 'gpt-5.4-mini');
        config()->set('services.openai.emergency_counselling_model_label', 'SolidCare Fine-Tuned Counselling Model');

        $user = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->get(route('counselling'));

        $response->assertOk();
        $response->assertSee('Student Chart Board');
        $response->assertSee('Book Session');
        $response->assertSee('Emergency AI Counselling');
        $response->assertSee('Select a Date & Time');
        $response->assertSee('Sex');
        $response->assertSee('Program');
        $response->assertSee('Year of study');
        $response->assertSee('Cause of counselling');
        $response->assertSee('Active emergency model');
        $response->assertSee('SolidCare Fine-Tuned Counselling Model');
        $response->assertSee('gpt-5.4-mini');
    }

    public function test_continuing_student_can_book_a_counselling_session(): void
    {
        $user = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->post(route('counselling.bookings.store'), [
            'sex' => 'Female',
            'reason' => 'Academic performance',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:30',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHas('success', 'Counselling session booked successfully.');

        $this->assertDatabaseHas('counselling_bookings', [
            'user_id' => $user->id,
            'student_name' => 'Nthati Lehana',
            'student_identity_number' => '901015687',
            'sex' => 'Female',
            'reason' => 'Academic performance',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'status' => 'pending',
        ]);
    }

    public function test_continuing_student_can_get_an_emergency_ai_reply(): void
    {
        config()->set('services.openai.api_key', 'test-openai-key');
        config()->set('services.openai.emergency_counselling_model', 'gpt-5.4-mini');
        config()->set('services.openai.emergency_counselling_model_label', 'SolidCare Fine-Tuned Counselling Model');

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => 'Take one slow breath with me. Tell me the strongest feeling you have right now.',
                    ]],
                ]],
            ], 200),
        ]);

        $user = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->postJson(route('counselling.emergency.reply'), [
            'message' => 'I feel overwhelmed and I need help calming down.',
            'messages' => [
                [
                    'role' => 'assistant',
                    'content' => 'You can talk to the emergency counselling assistant here.',
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'reply' => 'Take one slow breath with me. Tell me the strongest feeling you have right now.',
            'model' => 'gpt-5.4-mini',
            'model_label' => 'SolidCare Fine-Tuned Counselling Model',
        ]);
    }

    public function test_new_student_cannot_access_counselling_options(): void
    {
        $user = User::create([
            'name' => 'Mpho Moleli',
            'email' => 'mpho@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'new',
            'id_number' => '22045678',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->get(route('counselling'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Only continuing students can access counselling services.');
    }

    public function test_psychologist_can_view_counselling_management_portal(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        CounsellingBooking::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_identity_number' => $student->student_id,
            'sex' => 'Female',
            'reason' => 'Academic performance',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:30',
            'status' => 'pending',
        ]);

        $psychologist = User::create([
            'name' => 'Dr Palesa',
            'email' => 'psychologist@example.com',
            'password' => 'password123',
            'role' => 'psychologist',
        ]);

        $response = $this->actingAs($psychologist)->get(route('counselling'));

        $response->assertOk();
        $response->assertSee('Psychologist Counselling Desk');
        $response->assertSee('Manage Counselling Appointments');
        $response->assertSee('Booked Date and Time');
        $response->assertSee('Nthati Lehana');
        $response->assertSee('Program');
        $response->assertSee('Year of Study');
    }

    public function test_psychologist_can_update_counselling_appointment(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $booking = CounsellingBooking::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_identity_number' => $student->student_id,
            'sex' => 'Female',
            'reason' => 'Academic performance',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:30',
            'status' => 'pending',
        ]);

        $psychologist = User::create([
            'name' => 'Dr Palesa',
            'email' => 'psychologist@example.com',
            'password' => 'password123',
            'role' => 'psychologist',
        ]);

        $response = $this->actingAs($psychologist)->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'scheduled',
            'appointment_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'Arrive 15 minutes early for the session.',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHas('success', 'Counselling appointment updated successfully.');

        $this->assertDatabaseHas('counselling_bookings', [
            'id' => $booking->id,
            'status' => 'scheduled',
            'counsellor_notes' => 'Arrive 15 minutes early for the session.',
        ]);

        $this->assertNotNull($booking->fresh()->appointment_date);
    }

    public function test_psychologist_can_schedule_an_appointment_without_manually_changing_status(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student2@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015688',
            'disability' => 'no',
        ]);

        $booking = CounsellingBooking::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_identity_number' => $student->student_id,
            'sex' => 'Female',
            'reason' => 'Personal',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '3rd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '14:00',
            'status' => 'pending',
        ]);

        $psychologist = User::create([
            'name' => 'Dr Palesa',
            'email' => 'psychologist2@example.com',
            'password' => 'password123',
            'role' => 'psychologist',
        ]);

        $response = $this->actingAs($psychologist)->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'pending',
            'appointment_date' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'Session scheduled by the psychologist.',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHas('success', 'Counselling appointment updated successfully.');

        $this->assertDatabaseHas('counselling_bookings', [
            'id' => $booking->id,
            'status' => 'scheduled',
            'counsellor_notes' => 'Session scheduled by the psychologist.',
        ]);

        $this->assertNotNull($booking->fresh()->appointment_date);
    }

    public function test_psychologist_cannot_schedule_duplicate_appointment_date_and_time(): void
    {
        $firstStudent = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student-a@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015690',
            'disability' => 'no',
        ]);

        $secondStudent = User::create([
            'name' => 'Mpho Moleli',
            'email' => 'student-b@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015691',
            'disability' => 'no',
        ]);

        $existingAppointment = now()->addDays(4)->setTime(11, 30)->seconds(0);

        CounsellingBooking::create([
            'user_id' => $firstStudent->id,
            'student_name' => $firstStudent->name,
            'student_identity_number' => $firstStudent->student_id,
            'sex' => 'Female',
            'reason' => 'Personal',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '3rd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '14:00',
            'status' => 'scheduled',
            'appointment_date' => $existingAppointment,
        ]);

        $booking = CounsellingBooking::create([
            'user_id' => $secondStudent->id,
            'student_name' => $secondStudent->name,
            'student_identity_number' => $secondStudent->student_id,
            'sex' => 'Male',
            'reason' => 'Academic performance',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time' => '10:30',
            'status' => 'pending',
        ]);

        $psychologist = User::create([
            'name' => 'Dr Palesa',
            'email' => 'psychologist3@example.com',
            'password' => 'password123',
            'role' => 'psychologist',
        ]);

        $response = $this->actingAs($psychologist)->from(route('counselling'))->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'scheduled',
            'appointment_date' => $existingAppointment->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'Try to share a slot.',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHasErrors('appointment_date');

        $this->assertDatabaseHas('counselling_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
            'appointment_date' => null,
        ]);
    }

    public function test_student_cannot_update_counselling_appointment(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $booking = CounsellingBooking::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_identity_number' => $student->student_id,
            'sex' => 'Female',
            'reason' => 'Academic performance',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:30',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'scheduled',
            'appointment_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'Unauthorized change.',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Only psychologists can manage counselling appointments.');

        $this->assertDatabaseHas('counselling_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);
    }
}
