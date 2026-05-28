<?php

namespace Tests\Feature;

use App\Models\ClinicStockItem;
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
            'email_verified_at' => now(),
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
            'email_verified_at' => now(),
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
            'email_verified_at' => now(),
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

    public function test_senior_nurse_cannot_confirm_stock(): void
    {
        $nurse = User::create([
            'name' => 'Tau Tau',
            'email' => 'nurse@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'senior_nurse_officer',
        ]);

        $item = ClinicStockItem::create([
            'medicine_name' => 'Panado',
            'opening_stock' => 100,
            'quantity_received' => 20,
            'quantity_issued' => 5,
            'status' => 'in_stock',
        ]);

        $response = $this->actingAs($nurse)->get(route('clinic', [
            'clinic_panel' => 'stock-details',
        ]));

        $response->assertOk();
        $response->assertSee('Add Comment');
        $response->assertDontSee('Confirm Stock');

        $confirmResponse = $this->actingAs($nurse)->post(route('clinic.stock.confirm', $item), [
            'clinic_panel' => 'stock-details',
        ]);

        $confirmResponse->assertRedirect(route('home'));
        $this->assertDatabaseHas('clinic_stock_items', [
            'id' => $item->id,
            'opening_stock' => 100,
            'quantity_received' => 20,
            'confirmed_by_user_id' => null,
            'confirmed_at' => null,
        ]);
    }

    public function test_deleted_clinic_stock_does_not_reappear_after_clinic_page_reload(): void
    {
        $executive = User::create([
            'name' => 'Clinic Executive',
            'email' => 'executive@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'role' => 'executive',
        ]);

        $item = ClinicStockItem::create([
            'medicine_name' => 'Panado',
            'opening_stock' => 10,
            'quantity_received' => 0,
            'quantity_issued' => 0,
            'status' => 'in_stock',
        ]);

        $deleteResponse = $this->actingAs($executive)->post(route('clinic.stock.delete', $item), [
            'clinic_panel' => 'stock-details',
        ]);

        $deleteResponse->assertRedirect(route('clinic'));
        $this->assertDatabaseMissing('clinic_stock_items', [
            'id' => $item->id,
        ]);
        $this->assertDatabaseCount('clinic_stock_items', 0);

        $this->actingAs($executive)->get(route('clinic'))->assertOk();

        $this->assertDatabaseCount('clinic_stock_items', 0);
    }
}
