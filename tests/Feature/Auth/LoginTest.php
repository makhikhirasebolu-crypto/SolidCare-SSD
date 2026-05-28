<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_log_in_with_a_normalized_email(): void
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
            'email_verified_at' => now(),
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
            'email_verified_at' => now(),
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
        Notification::fake();

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
        Notification::assertNothingSent();
    }

    public function test_registration_does_not_require_admin_email_approval(): void
    {
        Notification::fake();

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
        Notification::assertNothingSent();
    }

    public function test_limkokwing_name_surname_email_registers_as_admin_from_student_portal(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Lineo Admin',
            'email' => '  Lineo.Admin@Limkokwing.Ac.Ls  ',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Admin account created successfully. Welcome to SolidCare SSD.');

        $this->assertDatabaseHas('admins', [
            'name' => 'Lineo Admin',
            'email' => 'lineo.admin@limkokwing.ac.ls',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'lineo.admin@limkokwing.ac.ls',
        ]);
        $this->assertDatabaseCount('students', 0);
        $this->assertAuthenticated('admin');
        $this->assertGuest('web');
        Notification::assertNothingSent();
    }

    public function test_another_admin_can_register_from_student_portal_when_admins_already_exist(): void
    {
        Admin::create([
            'name' => 'First Admin',
            'email' => 'first.admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);
        Admin::create([
            'name' => 'Second Admin',
            'email' => 'second.admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $response = $this->from(route('register'))->post('/register', [
            'name' => 'Third Admin',
            'email' => 'third.admin@limkokwing.ac.ls',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseCount('admins', 3);
        $this->assertDatabaseHas('admins', [
            'name' => 'Third Admin',
            'email' => 'third.admin@limkokwing.ac.ls',
        ]);
        $this->assertAuthenticated('admin');
    }

    public function test_admin_can_create_a_staff_user_without_email_verification_flow(): void
    {
        Notification::fake();

        $admin = Admin::create([
            'name' => 'SSD Admin',
            'email' => 'admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.users.store'), [
            'name' => 'Pending Staff',
            'email' => '  Staff@Example.com  ',
            'password' => 'temporary123',
            'password_confirmation' => 'temporary123',
            'role' => 'psychologist',
        ]);

        $response->assertRedirect(route('dashboard', ['create_user' => 1]));
        $response->assertSessionHas('success', 'User created successfully. Temporary password: temporary123 (expires in 2 days).');

        $this->assertDatabaseHas('users', [
            'name' => 'Pending Staff',
            'email' => 'staff@example.com',
            'role' => 'psychologist',
            'password_temporary' => true,
        ]);

        Notification::assertNothingSent();
    }

    public function test_admin_members_report_shows_active_temporary_passwords(): void
    {
        $admin = Admin::create([
            'name' => 'SSD Admin',
            'email' => 'admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.users.store'), [
            'name' => 'Pending Staff',
            'email' => 'staff@example.com',
            'password' => 'temporary123',
            'password_confirmation' => 'temporary123',
            'role' => 'psychologist',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('dashboard', ['members_report' => 1]));

        $response->assertOk();
        $response->assertSee('Temporary Password');
        $response->assertSee('temporary123');
        $response->assertSee('Expires');
    }

    public function test_admin_can_reissue_active_temporary_passwords_created_before_display_was_enabled(): void
    {
        $admin = Admin::create([
            'name' => 'SSD Admin',
            'email' => 'admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $member = User::create([
            'name' => 'Old Temporary Staff',
            'email' => 'old-temp@example.com',
            'password' => 'old-temporary123',
            'role' => 'warden',
            'password_temporary' => true,
            'temporary_password_expires_at' => now()->addDay(),
            'temporary_password_plain' => null,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('dashboard', ['members_report' => 1]))
            ->assertOk()
            ->assertSee('Reissue visible password');

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.users.temporary-password.reissue', $member));

        $response->assertRedirect(route('dashboard', ['members_report' => 1]));
        $response->assertSessionHas('success');

        $member->refresh();

        $this->assertNotNull($member->temporary_password_plain);
        $this->assertTrue(Hash::check($member->temporary_password_plain, $member->password));
        $this->assertTrue($member->temporary_password_expires_at->isFuture());

        $this->actingAs($admin, 'admin')
            ->get(route('dashboard', ['members_report' => 1]))
            ->assertOk()
            ->assertSee($member->temporary_password_plain)
            ->assertDontSee('Reissue visible password');
    }

    public function test_admin_can_create_yearleader_with_programme_and_numeric_year(): void
    {
        Notification::fake();

        $admin = Admin::create([
            'name' => 'SSD Admin',
            'email' => 'admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.users.store'), [
            'name' => 'Year Leader',
            'email' => 'yearleader@example.com',
            'password' => 'temporary123',
            'password_confirmation' => 'temporary123',
            'role' => 'yearleader',
            'faculty' => 'Faculty of Communication and Information Technology',
            'class' => 'BSc (Hons) in Information Technology',
            'year' => '4',
        ]);

        $response->assertRedirect(route('dashboard', ['create_user' => 1]));

        $this->assertDatabaseHas('year_leaders', [
            'faculty' => 'Faculty of Communication and Information Technology',
            'class' => 'BSc (Hons) in Information Technology',
            'year' => '4',
        ]);

        Notification::assertNothingSent();
    }

    public function test_admin_cannot_create_yearleader_with_year_outside_programme_length(): void
    {
        Notification::fake();

        $admin = Admin::create([
            'name' => 'SSD Admin',
            'email' => 'admin@limkokwing.ac.ls',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('dashboard', ['create_user' => 1]))
            ->post(route('admin.users.store'), [
                'name' => 'Diploma Year Leader',
                'email' => 'diploma-yearleader@example.com',
                'password' => 'temporary123',
                'password_confirmation' => 'temporary123',
                'role' => 'yearleader',
                'faculty' => 'Faculty of Creativity in Tourism and Hospitality',
                'class' => 'Diploma in Hotel Management',
                'year' => '4',
            ]);

        $response
            ->assertRedirect(route('dashboard', ['create_user' => 1]))
            ->assertSessionHasErrors('year');

        $this->assertDatabaseMissing('users', [
            'email' => 'diploma-yearleader@example.com',
        ]);

        Notification::assertNothingSent();
    }

    public function test_registration_does_not_send_verification_email_or_block_home_access(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Nthati Lehana',
            'email' => 'missing@example.invalid',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'student_type' => 'continuing',
            'student_id' => '901015687',
            'disability' => 'no',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', [
            'email' => 'missing@example.invalid',
            'email_verified_at' => null,
        ]);

        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        Notification::assertNothingSent();
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
