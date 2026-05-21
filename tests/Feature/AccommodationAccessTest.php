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
        $response->assertSee('Students / Accommodation staff only');
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
}
