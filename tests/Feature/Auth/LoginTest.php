<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_log_in_with_a_normalized_email(): void
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

        $response = $this->post('/login', [
            'email' => '  LEHANANTHATI@GMAIL.COM  ',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_continuing_student_can_log_in_with_student_id(): void
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

        $response = $this->post('/login', [
            'email' => '901015687',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_still_log_in_with_email(): void
    {
        $admin = Admin::create([
            'name' => 'SSD Admin',
            'email' => 'admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'ADMIN@LIMKOKWING.AC.LS',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated('admin');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_login_failure_shows_a_hint_for_common_email_domain_typos(): void
    {
        $response = $this->from('/')->post('/login', [
            'email' => 'lehananthati@gamil.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('login_email_suggestion', 'lehananthati@gmail.com');
        $response->assertSessionHasInput('email', 'lehananthati@gamil.com');
    }

    public function test_student_can_log_in_when_a_common_email_domain_typo_is_corrected(): void
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

        $response = $this->post('/login', [
            'email' => 'lehananthati@gamil.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Signed in using the corrected email address.');
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_logs_in_the_student_and_redirects_home(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nthati Lehana',
            'email' => '  LehanaNthati@Gmail.com  ',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'lehananthati@gmail.com',
            'student_type' => 'continuing',
            'student_id' => '901015687',
        ]);

        $this->assertDatabaseHas('students', [
            'student_type' => 'continuing',
            'student_id' => '901015687',
        ]);

        $this->assertAuthenticated();
        $response->assertSessionHas('status', 'Account created successfully. Welcome to SolidCare SSD.');
    }

    public function test_registration_does_not_require_admin_email_approval(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nthati Lehana',
            'email' => 'lehananthati@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'lehananthati@gmail.com',
            'role' => 'student',
            'student_type' => 'continuing',
            'student_id' => '901015687',
        ]);
        $this->assertDatabaseCount('students', 1);
        $this->assertAuthenticated();
    }

    public function test_admin_can_create_a_student_user_without_email_approval_flow(): void
    {
        $admin = Admin::create([
            'name' => 'SSD Admin',
            'email' => 'admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.users.store'), [
            'name' => 'Pending Student',
            'email' => '  Student@Example.com  ',
            'password' => 'temporary123',
            'password_confirmation' => 'temporary123',
            'role' => 'student',
        ]);

        $response->assertRedirect(route('admin.users.create'));
        $response->assertSessionHas('success', 'User created successfully. Temporary password: temporary123 (expires in 2 days).');

        $this->assertDatabaseHas('users', [
            'name' => 'Pending Student',
            'email' => 'student@example.com',
            'role' => 'student',
            'student_type' => null,
            'password_temporary' => true,
        ]);
    }

    public function test_new_student_registration_is_denied_when_national_id_already_exists(): void
    {
        $existingUser = User::create([
            'name' => 'Existing Student',
            'email' => 'existing.student@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'student_type' => 'new',
            'id_number' => 'A12345678',
            'disability' => 'no',
        ]);

        \App\Models\Student::create([
            'user_id' => $existingUser->id,
            'student_type' => 'new',
            'id_number' => 'A12345678',
            'disability' => 'no',
        ]);

        $response = $this->from(route('register'))->post('/register', [
            'name' => 'Another Student',
            'email' => 'another.student@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'student_type' => 'new',
            'id_number' => '  a12345678  ',
            'disability' => 'no',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'id_number' => 'This national ID is already registered.',
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('students', 1);
        $this->assertGuest();
    }
}
