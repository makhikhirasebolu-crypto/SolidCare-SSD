<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_is_denied_access_to_clinic_routes(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($student)->get(route('clinic'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Clinic access is restricted to the senior nurse officer, executive, and SSD assistants.');
    }

    public function test_student_cannot_open_or_submit_student_clinic_portal(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $portalResponse = $this->actingAs($student)->get(route('student.clinic.index'));
        $portalResponse->assertRedirect(route('home'));
        $portalResponse->assertSessionHas('error', 'Clinic access is restricted to the senior nurse officer, executive, and SSD assistants.');

        $submitResponse = $this->actingAs($student)->post(route('student.clinic.store'), [
            'symptoms' => 'Fever and headache',
        ]);

        $submitResponse->assertRedirect(route('home'));
        $submitResponse->assertSessionHas('error', 'Clinic access is restricted to the senior nurse officer, executive, and SSD assistants.');
        $this->assertDatabaseCount('clinic_records', 0);
    }

    public function test_student_dashboard_hides_clinic_access_button(): void
    {
        $student = User::create([
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response = $this->actingAs($student)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Health & Clinic', false);
        $response->assertDontSee('Access Clinic');
        $response->assertSee('Authorized staff only');
    }
}
