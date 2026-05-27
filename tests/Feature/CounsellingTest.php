<?php

namespace Tests\Feature;

use App\Mail\CounsellingSessionScheduled;
use App\Models\CounsellingBooking;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->get(route('counselling'));

        $response->assertOk();
        $response->assertSee('Student Chat Board');
        $response->assertSee('Book Session');
        $response->assertSee('Student support assistant');
        $response->assertSee('Select a Date & Time');
        $response->assertSee('Campus');
        $response->assertSee('MP campus');
        $response->assertSee('Main campus');
        $response->assertSee('Sex');
        $response->assertSee('Program');
        $response->assertSee('Year of study');
        $response->assertSee('1st Year');
        $response->assertSee('2nd Year');
        $response->assertSee('3rd Year');
        $response->assertSee('4th Year');
        $response->assertDontSee('5th Year');
        $response->assertDontSee('6th Year');
        $response->assertDontSee('Postgraduate');
        $response->assertSee('Cause of counselling');
        $response->assertSee('Preferred session');
        $response->assertSee('Choose a date to see available sessions.');
        $response->assertSee('closingMinutes = (16 * 60) + 30', false);
        $response->assertSee('Grief and loss');
        $response->assertSee('Sexual assault');
        $response->assertSee('Mental illness');
        $response->assertSee('Relationships');
        $response->assertSee('Family dynamics');
        $response->assertSee('Depression and anxiety');
        $response->assertSee('Academic issue');
        $response->assertSee('SolidCare Fine-Tuned Counselling Model');
    }

    public function test_continuing_student_can_book_a_counselling_session(): void
    {
        $user = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->post(route('counselling.bookings.store'), [
            'campus' => 'MP campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '09:20',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHas('success', 'Counselling session booked successfully.');

        $this->assertDatabaseHas('counselling_bookings', [
            'user_id' => $user->id,
            'student_name' => 'Nthati Lehana',
            'student_identity_number' => '901015687',
            'campus' => 'MP campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'status' => 'pending',
        ]);
    }

    public function test_student_must_choose_a_campus_to_book_a_counselling_session(): void
    {
        $user = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'campus-required@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015689',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->from(route('counselling'))->post(route('counselling.bookings.store'), [
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '09:20',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHasErrors('campus');
        $this->assertDatabaseCount('counselling_bookings', 0);
    }

    public function test_student_preferred_time_must_end_before_closing_time(): void
    {
        $user = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'time-window@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015692',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($user)->from(route('counselling'))->post(route('counselling.bookings.store'), [
            'campus' => 'Main campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '16:00',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHasErrors('preferred_time');
        $this->assertDatabaseCount('counselling_bookings', 0);
    }

    public function test_student_cannot_book_a_conflicting_counselling_session_time(): void
    {
        $firstStudent = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'first-conflict@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015700',
            'disability' => 'no',
        ]);

        $secondStudent = User::create([
            'name' => 'Boitumelo Molefe',
            'email' => 'second-conflict@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015701',
            'disability' => 'no',
        ]);

        $preferredDate = now()->addDays(2)->toDateString();

        CounsellingBooking::create([
            'user_id' => $firstStudent->id,
            'student_name' => $firstStudent->name,
            'student_identity_number' => $firstStudent->student_id,
            'campus' => 'MP campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => $preferredDate,
            'preferred_time' => '09:20',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($secondStudent)->from(route('counselling'))->post(route('counselling.bookings.store'), [
            'campus' => 'MP campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => $preferredDate,
            'preferred_time' => '09:20',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHasErrors('preferred_time');
        $this->assertDatabaseCount('counselling_bookings', 1);
    }

    public function test_student_can_book_same_time_on_a_different_campus(): void
    {
        $firstStudent = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'mp-campus-slot@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015702',
            'disability' => 'no',
        ]);

        $secondStudent = User::create([
            'name' => 'Boitumelo Molefe',
            'email' => 'main-campus-slot@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015703',
            'disability' => 'no',
        ]);

        $preferredDate = now()->addDays(2)->toDateString();

        CounsellingBooking::create([
            'user_id' => $firstStudent->id,
            'student_name' => $firstStudent->name,
            'student_identity_number' => $firstStudent->student_id,
            'campus' => 'MP campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => $preferredDate,
            'preferred_time' => '09:20',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($secondStudent)->from(route('counselling'))->post(route('counselling.bookings.store'), [
            'campus' => 'Main campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => $preferredDate,
            'preferred_time' => '09:20',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHas('success', 'Counselling session booked successfully.');
        $this->assertDatabaseCount('counselling_bookings', 2);
    }

    public function test_student_can_see_booked_session_date_and_time(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'booked-session@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015693',
            'disability' => 'no',
        ]);

        $appointmentDate = now()->addDays(2)->setTime(10, 40)->seconds(0);

        CounsellingBooking::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_identity_number' => $student->student_id,
            'campus' => 'Main campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:40',
            'status' => 'scheduled',
            'appointment_date' => $appointmentDate,
        ]);

        $response = $this->actingAs($student)->get(route('counselling'));

        $response->assertOk();
        $response->assertSee('Booked session');
        $response->assertSee($appointmentDate->format('d-m-Y h:i A'));
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
            'email_verified_at' => now(),
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

        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'I feel overwhelmed and I need help calming down.',
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => 'Take one slow breath with me. Tell me the strongest feeling you have right now.',
        ]);
    }

    public function test_student_chat_board_loads_saved_chat_messages(): void
    {
        $user = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'saved-chat@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015688',
            'disability' => 'no',
        ]);

        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => 'I need help planning my revision.',
        ]);
        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => 'Let us make a small revision plan.',
        ]);

        $response = $this->actingAs($user)->get(route('counselling'));

        $response
            ->assertOk()
            ->assertSee('I need help planning my revision.')
            ->assertSee('Let us make a small revision plan.');
    }

    public function test_new_student_cannot_access_counselling_options(): void
    {
        $user = User::create([
            'name' => 'Mpho Moleli',
            'email' => 'mpho@gmail.com',
            'email_verified_at' => now(),
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
            'email_verified_at' => now(),
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
            'preferred_time' => '10:40',
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
        $response->assertSee('Counselling Management Desk');
        $response->assertSee('Manage Counselling Appointments');
        $response->assertSee('Booked Date and Time');
        $response->assertSee('Choose a date, then select a working-hour slot');
        $response->assertSee('1 hour 15 minute');
        $response->assertSee('slotIntervalMinutes = sessionMinutes + slotGapMinutes', false);
        $response->assertDontSee('Delete Session');
        $response->assertSee('Nthati Lehana');
        $response->assertSee('Program');
        $response->assertSee('Year of Study');
    }

    public function test_psychologist_can_update_counselling_appointment(): void
    {
        Mail::fake();

        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student@example.com',
            'email_verified_at' => now(),
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
            'preferred_time' => '10:40',
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
            'appointment_date' => now()->addDays(2)->setTime(9, 20)->format('Y-m-d\TH:i'),
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

        Mail::assertSent(CounsellingSessionScheduled::class, function (CounsellingSessionScheduled $mail) use ($booking) {
            return $mail->hasTo('student@example.com')
                && $mail->booking->is($booking);
        });
    }

    public function test_executive_can_manage_counselling_appointments(): void
    {
        Mail::fake();

        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'executive-managed-student@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015704',
            'disability' => 'no',
        ]);

        $booking = CounsellingBooking::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_identity_number' => $student->student_id,
            'campus' => 'MP campus',
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:40',
            'status' => 'pending',
        ]);

        $executive = User::create([
            'name' => 'Executive User',
            'email' => 'executive-counselling@example.com',
            'password' => 'password123',
            'role' => 'executive',
        ]);

        $portalResponse = $this->actingAs($executive)->get(route('counselling'));

        $portalResponse->assertOk();
        $portalResponse->assertSee('Counselling Management Desk');
        $portalResponse->assertSee('Manage Counselling Appointments');

        $updateResponse = $this->actingAs($executive)->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'scheduled',
            'appointment_date' => now()->addDays(2)->setTime(9, 20)->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'Executive scheduled this counselling follow-up.',
        ]);

        $updateResponse->assertRedirect(route('counselling'));
        $updateResponse->assertSessionHas('success', 'Counselling appointment updated successfully.');

        $this->assertDatabaseHas('counselling_bookings', [
            'id' => $booking->id,
            'status' => 'scheduled',
            'counsellor_notes' => 'Executive scheduled this counselling follow-up.',
        ]);
    }

    public function test_psychologist_can_schedule_an_appointment_without_manually_changing_status(): void
    {
        Mail::fake();

        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student2@example.com',
            'email_verified_at' => now(),
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
            'appointment_date' => now()->addDays(3)->setTime(12, 0)->format('Y-m-d\TH:i'),
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

        Mail::assertSent(CounsellingSessionScheduled::class, function (CounsellingSessionScheduled $mail) use ($booking) {
            return $mail->hasTo('student2@example.com')
                && $mail->booking->is($booking);
        });
    }

    public function test_psychologist_cannot_schedule_duplicate_appointment_date_and_time(): void
    {
        $firstStudent = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student-a@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015690',
            'disability' => 'no',
        ]);

        $secondStudent = User::create([
            'name' => 'Mpho Moleli',
            'email' => 'student-b@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015691',
            'disability' => 'no',
        ]);

        $existingAppointment = now()->addDays(4)->setTime(10, 40)->seconds(0);

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
            'preferred_time' => '10:40',
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

    public function test_psychologist_cannot_schedule_overlapping_one_hour_fifteen_minute_session(): void
    {
        $firstStudent = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student-overlap-a@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015700',
            'disability' => 'no',
        ]);

        $secondStudent = User::create([
            'name' => 'Mpho Moleli',
            'email' => 'student-overlap-b@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015701',
            'disability' => 'no',
        ]);

        $existingAppointment = now()->addDays(5)->setTime(9, 20)->seconds(0);

        CounsellingBooking::create([
            'user_id' => $firstStudent->id,
            'student_name' => $firstStudent->name,
            'student_identity_number' => $firstStudent->student_id,
            'sex' => 'Female',
            'reason' => 'Relationships',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '3rd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:40',
            'status' => 'scheduled',
            'appointment_date' => $existingAppointment,
        ]);

        $booking = CounsellingBooking::create([
            'user_id' => $secondStudent->id,
            'student_name' => $secondStudent->name,
            'student_identity_number' => $secondStudent->student_id,
            'sex' => 'Male',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time' => '12:30',
            'status' => 'pending',
        ]);

        $psychologist = User::create([
            'name' => 'Dr Palesa',
            'email' => 'psychologist-overlap@example.com',
            'password' => 'password123',
            'role' => 'psychologist',
        ]);

        $response = $this->actingAs($psychologist)->from(route('counselling'))->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'scheduled',
            'appointment_date' => $existingAppointment->copy()->addMinutes(45)->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'This overlaps the first appointment.',
        ]);

        $response->assertRedirect(route('counselling'));
        $response->assertSessionHasErrors('appointment_date');

        $this->assertDatabaseHas('counselling_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
            'appointment_date' => null,
        ]);
    }

    public function test_psychologist_cannot_schedule_outside_working_hours(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'student-hours@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015702',
            'disability' => 'no',
        ]);

        $booking = CounsellingBooking::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_identity_number' => $student->student_id,
            'sex' => 'Female',
            'reason' => 'Academic issue',
            'programme' => 'Bachelor of Information Technology',
            'year_of_study' => '2nd Year',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:40',
            'status' => 'pending',
        ]);

        $psychologist = User::create([
            'name' => 'Dr Palesa',
            'email' => 'psychologist-hours@example.com',
            'password' => 'password123',
            'role' => 'psychologist',
        ]);

        $response = $this->actingAs($psychologist)->from(route('counselling'))->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'scheduled',
            'appointment_date' => now()->addDays(2)->setTime(16, 0)->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'This would end after working hours.',
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
            'email_verified_at' => now(),
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
            'preferred_time' => '10:40',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)->patch(route('counselling.bookings.update', $booking), [
            'active_booking' => $booking->id,
            'status' => 'scheduled',
            'appointment_date' => now()->addDays(2)->setTime(9, 20)->format('Y-m-d\TH:i'),
            'counsellor_notes' => 'Unauthorized change.',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Only counselling managers can manage counselling appointments.');

        $this->assertDatabaseHas('counselling_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);
    }

}

