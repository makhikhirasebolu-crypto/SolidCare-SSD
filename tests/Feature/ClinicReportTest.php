<?php

namespace Tests\Feature;

use App\Models\ClinicStockItem;
use App\Models\ClinicStockReceipt;
use App\Models\ClinicStockUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_clinic_report_groups_stock_usage_by_disease(): void
    {
        $executive = $this->createExecutiveUser();
        [$paracetamol, $amoxicillin] = $this->createClinicItems();

        ClinicStockUsage::create([
            'clinic_stock_item_id' => $paracetamol->id,
            'user_id' => $executive->id,
            'student_id' => '901015687',
            'quantity_issued' => 2,
            'diagnosis' => 'Malaria',
            'usage_date' => '2026-04-14',
        ]);

        ClinicStockUsage::create([
            'clinic_stock_item_id' => $amoxicillin->id,
            'user_id' => $executive->id,
            'student_id' => '901015688',
            'quantity_issued' => 1,
            'diagnosis' => 'Malaria',
            'usage_date' => '2026-04-15',
        ]);

        ClinicStockUsage::create([
            'clinic_stock_item_id' => $paracetamol->id,
            'user_id' => $executive->id,
            'student_id' => '901015689',
            'quantity_issued' => 1,
            'diagnosis' => 'Typhoid',
            'usage_date' => '2026-04-16',
        ]);

        $response = $this->actingAs($executive)->get(route('clinic', [
            'report_generated' => 1,
            'report_type' => 'general',
        ]));

        $response->assertOk();
        $response->assertSee('Stock Used by Disease');
        $response->assertSee('Disease Summary Report');
        $response->assertSee('Malaria');
        $response->assertSee('Typhoid');
        $response->assertSee('Medicines used:');
        $response->assertSee('Paracetamol');
        $response->assertSee('Amoxicillin');
    }

    public function test_executive_clinic_report_shows_available_stock_for_all_system_items(): void
    {
        $executive = $this->createExecutiveUser();
        $this->createClinicItems();

        $response = $this->actingAs($executive)->get(route('clinic', [
            'report_generated' => 1,
            'report_type' => 'general',
        ]));

        $response->assertOk();
        $response->assertSee('Available Stock');
        $response->assertSee('2 entries');
        $response->assertSee('Paracetamol');
        $response->assertSee('Amoxicillin');
        $response->assertSee('150');
        $response->assertSee('90');
    }

    public function test_monthly_clinic_report_shows_only_currently_available_stock(): void
    {
        $executive = $this->createExecutiveUser();
        [$paracetamol, $amoxicillin] = $this->createClinicItems();

        $amoxicillin->update([
            'quantity_issued' => 90,
            'status' => 'out_of_stock',
        ]);

        $response = $this->actingAs($executive)->get(route('clinic', [
            'report_generated' => 1,
            'report_type' => 'month',
            'report_month' => 4,
            'report_year' => 2026,
        ]));

        $response->assertOk();
        $availableStockSection = $this->reportAvailableStockSection($response->getContent());

        $this->assertStringContainsString('Available Stock', $availableStockSection);
        $this->assertStringContainsString('1 entries', $availableStockSection);
        $this->assertStringContainsString('Paracetamol', $availableStockSection);
        $this->assertStringNotContainsString('Amoxicillin', $availableStockSection);
        $this->assertStringContainsString('150', $availableStockSection);
    }

    public function test_semester_clinic_report_shows_only_currently_available_stock(): void
    {
        $executive = $this->createExecutiveUser();
        [$paracetamol, $amoxicillin] = $this->createClinicItems();

        $paracetamol->update([
            'quantity_issued' => 150,
            'status' => 'out_of_stock',
        ]);

        $response = $this->actingAs($executive)->get(route('clinic', [
            'report_generated' => 1,
            'report_type' => 'semester',
            'report_semester' => 1,
            'report_year' => 2026,
        ]));

        $response->assertOk();
        $availableStockSection = $this->reportAvailableStockSection($response->getContent());

        $this->assertStringContainsString('Available Stock', $availableStockSection);
        $this->assertStringContainsString('1 entries', $availableStockSection);
        $this->assertStringNotContainsString('Paracetamol', $availableStockSection);
        $this->assertStringContainsString('Amoxicillin', $availableStockSection);
        $this->assertStringContainsString('90', $availableStockSection);
    }

    public function test_clinic_report_marks_stock_below_forty_percent_as_low_stock(): void
    {
        $executive = $this->createExecutiveUser();
        [$paracetamol] = $this->createClinicItems();

        $paracetamol->update([
            'quantity_issued' => 100,
            'status' => 'in_stock',
        ]);

        $response = $this->actingAs($executive)->get(route('clinic', [
            'report_generated' => 1,
            'report_type' => 'general',
        ]));

        $response->assertOk();
        $response->assertSee('Low Stock');
        $response->assertSee('1/2');

        $availableStockSection = $this->reportAvailableStockSection($response->getContent());

        $this->assertStringContainsString('Paracetamol', $availableStockSection);
        $this->assertStringContainsString('50', $availableStockSection);
        $this->assertStringContainsString('Low Stock', $availableStockSection);
    }

    public function test_clinic_report_keeps_stock_at_forty_one_percent_in_stock(): void
    {
        $executive = $this->createExecutiveUser();
        [$paracetamol] = $this->createClinicItems();

        $paracetamol->update([
            'opening_stock' => 100,
            'quantity_received' => 0,
            'quantity_issued' => 59,
            'status' => 'low_stock',
        ]);

        $response = $this->actingAs($executive)->get(route('clinic', [
            'report_generated' => 1,
            'report_type' => 'general',
        ]));

        $response->assertOk();
        $availableStockSection = $this->reportAvailableStockSection($response->getContent());

        $this->assertStringContainsString('Paracetamol', $availableStockSection);
        $this->assertStringContainsString('41', $availableStockSection);
        $this->assertStringContainsString('In Stock', $availableStockSection);
    }

    public function test_executive_can_download_clinic_report_grouped_by_disease(): void
    {
        $executive = $this->createExecutiveUser();
        [$paracetamol] = $this->createClinicItems();

        ClinicStockUsage::create([
            'clinic_stock_item_id' => $paracetamol->id,
            'user_id' => $executive->id,
            'student_id' => '901015687',
            'quantity_issued' => 2,
            'diagnosis' => 'Malaria',
            'usage_date' => '2026-04-14',
        ]);

        ClinicStockUsage::create([
            'clinic_stock_item_id' => $paracetamol->id,
            'user_id' => $executive->id,
            'student_id' => '901015688',
            'quantity_issued' => 1,
            'diagnosis' => 'Malaria',
            'usage_date' => '2026-04-15',
        ]);

        $response = $this->actingAs($executive)->get(route('clinic.report.download'));

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Disease Category Summary', $content);
        $this->assertStringContainsString('Stock Used by Disease', $content);
        $this->assertStringContainsString('Malaria', $content);
        $this->assertStringContainsString('901015687', $content);
        $this->assertStringContainsString('901015688', $content);
    }

    public function test_clinic_report_receipt_totals_follow_the_selected_report_type(): void
    {
        $executive = $this->createExecutiveUser();
        [$paracetamol] = $this->createClinicItems();

        ClinicStockReceipt::create([
            'clinic_stock_item_id' => $paracetamol->id,
            'user_id' => $executive->id,
            'quantity_received' => 30,
            'received_date' => '2026-04-10',
        ]);

        ClinicStockReceipt::create([
            'clinic_stock_item_id' => $paracetamol->id,
            'user_id' => $executive->id,
            'quantity_received' => 70,
            'received_date' => '2025-11-20',
        ]);

        $response = $this->actingAs($executive)->get(route('clinic', [
            'report_generated' => 1,
            'report_type' => 'year',
            'report_year' => 2026,
        ]));

        $response->assertOk();
        $response->assertSee('Year 2026');

        $content = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/Total Clinic Stock Received<\/small>\s*<span class="report-kpi-value">30<\/span>/',
            $content
        );
        $this->assertMatchesRegularExpression(
            '/<div class="donut-center">\s*<strong>30<\/strong>\s*<span>Units Received<\/span>/',
            $content
        );
    }

    public function test_clinic_report_results_are_hidden_until_generate_is_clicked(): void
    {
        $executive = $this->createExecutiveUser();
        $this->createClinicItems();

        $response = $this->actingAs($executive)->get(route('clinic'));

        $response->assertOk();
        $response->assertSee('Generate Report');
        $response->assertSee('No report displayed yet');
        $response->assertDontSee('Total Clinic Stock Received');
        $response->assertDontSee('Stock Used by Disease');
    }

    private function createExecutiveUser(): User
    {
        return User::create([
            'name' => 'Executive User',
            'email' => 'executive@example.com',
            'password' => 'password123',
            'role' => 'executive',
            'disability' => 'no',
        ]);
    }

    private function reportAvailableStockSection(string $content): string
    {
        $start = strpos($content, '<h4>Available Stock</h4>');
        $this->assertNotFalse($start);

        $end = strpos($content, '<h4>Disease Summary Report</h4>', $start);
        $this->assertNotFalse($end);

        return substr($content, $start, $end - $start);
    }

    /**
     * @return array{0: ClinicStockItem, 1: ClinicStockItem}
     */
    private function createClinicItems(): array
    {
        $paracetamol = ClinicStockItem::create([
            'medicine_name' => 'Paracetamol',
            'opening_stock' => 120,
            'quantity_received' => 30,
            'quantity_issued' => 0,
            'status' => 'in_stock',
        ]);

        $amoxicillin = ClinicStockItem::create([
            'medicine_name' => 'Amoxicillin',
            'opening_stock' => 80,
            'quantity_received' => 10,
            'quantity_issued' => 0,
            'status' => 'in_stock',
        ]);

        return [$paracetamol, $amoxicillin];
    }
}
