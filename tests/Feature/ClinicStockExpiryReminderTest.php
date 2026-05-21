<?php

namespace Tests\Feature;

use App\Mail\ClinicStockExpiryReminder;
use App\Models\ClinicStockItem;
use App\Models\ClinicStockReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClinicStockExpiryReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_must_record_expiry_date_when_adding_clinic_stock(): void
    {
        $nurse = User::create([
            'name' => 'Senior Nurse',
            'email' => 'nurse@example.com',
            'password' => 'password123',
            'role' => 'senior_nurse_officer',
        ]);

        $missingExpiryResponse = $this->actingAs($nurse)->post(route('clinic.stock.store'), [
            'stock_entries' => [
                [
                    'medicine_name' => 'Panado',
                    'quantity_received' => 20,
                ],
            ],
        ]);

        $missingExpiryResponse->assertSessionHasErrors('stock_entries.0.expiry_date');

        $expiryDate = now()->addMonths(2)->toDateString();

        $response = $this->actingAs($nurse)->post(route('clinic.stock.store'), [
            'stock_entries' => [
                [
                    'medicine_name' => 'Panado',
                    'quantity_received' => 20,
                    'expiry_date' => $expiryDate,
                ],
            ],
        ]);

        $response->assertRedirect(route('clinic'));

        $this->assertDatabaseHas('clinic_stock_items', [
            'medicine_name' => 'Panado',
            'quantity_received' => 20,
            'expiry_date' => $expiryDate . ' 00:00:00',
        ]);

        $this->assertDatabaseHas('clinic_stock_receipts', [
            'quantity_received' => 20,
            'expiry_date' => $expiryDate . ' 00:00:00',
        ]);
    }

    public function test_expiry_reminder_emails_nurse_and_executive_when_stock_is_available(): void
    {
        Mail::fake();

        User::create([
            'name' => 'Senior Nurse',
            'email' => 'nurse@example.com',
            'password' => 'password123',
            'role' => 'senior_nurse_officer',
        ]);
        User::create([
            'name' => 'Executive',
            'email' => 'executive@example.com',
            'password' => 'password123',
            'role' => 'executive',
        ]);

        $item = ClinicStockItem::create([
            'medicine_name' => 'Panado',
            'opening_stock' => 0,
            'quantity_received' => 10,
            'quantity_issued' => 0,
            'expiry_date' => now()->addMonthNoOverflow()->toDateString(),
            'status' => 'in_stock',
        ]);

        $receipt = ClinicStockReceipt::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => User::where('role', 'senior_nurse_officer')->value('id'),
            'quantity_received' => 10,
            'received_date' => now()->toDateString(),
            'expiry_date' => now()->addMonthNoOverflow()->toDateString(),
        ]);

        $this->artisan('clinic:send-expiry-reminders')
            ->assertExitCode(0);

        Mail::assertSent(ClinicStockExpiryReminder::class, 2);
        Mail::assertSent(ClinicStockExpiryReminder::class, function (ClinicStockExpiryReminder $mail) use ($receipt) {
            return $mail->receipt->is($receipt)
                && $mail->noticeWindow === '1 month'
                && $mail->hasTo('nurse@example.com');
        });
        Mail::assertSent(ClinicStockExpiryReminder::class, function (ClinicStockExpiryReminder $mail) use ($receipt) {
            return $mail->receipt->is($receipt)
                && $mail->noticeWindow === '1 month'
                && $mail->hasTo('executive@example.com');
        });

        $this->assertNotNull($receipt->fresh()->expiry_month_notice_sent_at);
    }

    public function test_expiry_reminder_is_not_sent_when_stock_is_unavailable(): void
    {
        Mail::fake();

        $nurse = User::create([
            'name' => 'Senior Nurse',
            'email' => 'nurse@example.com',
            'password' => 'password123',
            'role' => 'senior_nurse_officer',
        ]);
        User::create([
            'name' => 'Executive',
            'email' => 'executive@example.com',
            'password' => 'password123',
            'role' => 'executive',
        ]);

        $item = ClinicStockItem::create([
            'medicine_name' => 'Panado',
            'opening_stock' => 0,
            'quantity_received' => 10,
            'quantity_issued' => 10,
            'expiry_date' => now()->addWeek()->toDateString(),
            'status' => 'out_of_stock',
        ]);

        ClinicStockReceipt::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => $nurse->id,
            'quantity_received' => 10,
            'received_date' => now()->toDateString(),
            'expiry_date' => now()->addWeek()->toDateString(),
        ]);

        $this->artisan('clinic:send-expiry-reminders')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
