<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_yearleader_dashboard_locks_accommodation_access(): void
    {
        $yearLeader = User::create([
            'name' => 'Year Leader',
            'email' => 'yearleader@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'yearleader',
        ]);

        $response = $this->actingAs($yearLeader)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Accommodation', false);
        $response->assertDontSee('Manage Housing');
        $response->assertSee('Authorised students only');
    }

    public function test_yearleader_cannot_open_accommodation_directly(): void
    {
        $yearLeader = User::create([
            'name' => 'Year Leader',
            'email' => 'yearleader-direct@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'yearleader',
        ]);

        $response = $this->actingAs($yearLeader)->get(route('accommodation'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Accommodation access is restricted to students and authorized accommodation staff.');
    }

    public function test_continuing_student_dashboard_locks_accommodation_access(): void
    {
        $student = User::create([
            'name' => 'Continuing Student',
            'email' => 'continuing@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
        ]);

        $response = $this->actingAs($student)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Accommodation', false);
        $response->assertDontSee('Manage Housing');
        $response->assertSee('Authorised students only');
    }

    public function test_continuing_student_cannot_open_accommodation_directly(): void
    {
        $student = User::create([
            'name' => 'Continuing Student',
            'email' => 'continuing-direct@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015688',
        ]);

        $response = $this->actingAs($student)->get(route('accommodation'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Accommodation access is restricted to students and authorized accommodation staff.');
    }

    public function test_new_student_can_open_accommodation(): void
    {
        $student = User::create([
            'name' => 'New Student',
            'email' => 'new-student@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'new',
        ]);

        $response = $this->actingAs($student)->get(route('accommodation'));

        $response->assertOk();
    }
}
