<?php

namespace App\Http\Controllers;

use App\Models\ClinicStockComment;
use App\Models\ClinicStockItem;
use App\Models\ClinicStockReceipt;
use App\Models\ClinicStockUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ClinicController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!$this->canAccessClinic($user)) {
            return redirect()->route('home')->with('error', 'Clinic access is restricted to the senior nurse officer, executive, and SSD assistants.');
        }

        return $this->renderClinicPage($user, $request);
    }

    public function storeStock(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'stock_entries' => ['required', 'array', 'min:1'],
            'stock_entries.*.medicine_name' => ['required', 'string', 'max:255'],
            'stock_entries.*.quantity_received' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['stock_entries'] as $entry) {
            $existingItem = ClinicStockItem::whereRaw('LOWER(medicine_name) = ?', [strtolower($entry['medicine_name'])])->first();
            $receivedQuantity = (int) $entry['quantity_received'];

            if ($existingItem) {
                $openingStock = $existingItem->opening_stock;
                $quantityReceived = $existingItem->quantity_received + $receivedQuantity;
                $quantityIssued = $existingItem->quantity_issued;

                $existingItem->update([
                    'opening_stock' => $openingStock,
                    'quantity_received' => $quantityReceived,
                    'quantity_issued' => $quantityIssued,
                    'confirmed_at' => null,
                    'confirmed_by_user_id' => null,
                    'status' => $this->resolveClinicStockStatus($openingStock + $quantityReceived - $quantityIssued),
                ]);

                $this->recordClinicStockReceipt($existingItem, $user, $receivedQuantity);
                continue;
            }

            $entry['status'] = $this->resolveClinicStockStatus($entry['quantity_received']);
            $entry['opening_stock'] = 0;
            $entry['quantity_issued'] = 0;

            $createdItem = ClinicStockItem::create($entry);
            $this->recordClinicStockReceipt($createdItem, $user, $receivedQuantity);
        }

        return redirect()->route('clinic')->with('success', count($data['stock_entries']) . ' stock item(s) added successfully.');
    }

    public function updateStock(Request $request, ClinicStockItem $item)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'opening_stock' => ['required', 'integer', 'min:0'],
            'quantity_received' => ['required', 'integer', 'min:0'],
            'quantity_issued' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:pending_review,in_stock,low_stock,out_of_stock'],
        ]);

        $previousQuantityReceived = $item->quantity_received;
        $balance = $data['opening_stock'] + $data['quantity_received'] - $data['quantity_issued'];

        $item->update([
            'opening_stock' => $data['opening_stock'],
            'quantity_received' => $data['quantity_received'],
            'quantity_issued' => $data['quantity_issued'],
            'confirmed_at' => $data['quantity_received'] > 0 ? null : $item->confirmed_at,
            'confirmed_by_user_id' => $data['quantity_received'] > 0 ? null : $item->confirmed_by_user_id,
            'status' => $this->resolveClinicStockStatus($balance),
        ]);

        $newlyRecordedReceived = $data['quantity_received'] - $previousQuantityReceived;
        if ($newlyRecordedReceived > 0) {
            $this->recordClinicStockReceipt($item->fresh(), $user, $newlyRecordedReceived);
        }

        return redirect()->route('clinic')->with('success', $item->medicine_name . ' stock updated successfully.');
    }

    public function confirmStock(ClinicStockItem $item)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!in_array($user->role, ['senior_nurse_officer', 'executive'], true)) {
            return redirect()->route('home');
        }

        $newOpeningStock = $item->opening_stock + $item->quantity_received;

        $item->update([
            'opening_stock' => $newOpeningStock,
            'quantity_received' => 0,
            'confirmed_at' => now(),
            'confirmed_by_user_id' => $user->id,
            'status' => $this->resolveClinicStockStatus($newOpeningStock - $item->quantity_issued),
        ]);

        return redirect()->route('clinic')->with('success', $item->medicine_name . ' stock confirmed successfully.');
    }

    public function commentStock(Request $request, ClinicStockItem $item)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!in_array($user->role, ['senior_nurse_officer', 'executive'], true)) {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:clinic_stock_comments,id'],
        ]);

        ClinicStockComment::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'message' => $data['message'],
        ]);

        return redirect()->route('clinic')->with('success', 'Comment saved for ' . $item->medicine_name . '.');
    }

    public function storeUsage(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'clinic_stock_item_id' => ['required', 'exists:clinic_stock_items,id'],
            'student_id' => ['required', 'string', 'max:100'],
            'quantity_issued' => ['required', 'integer', 'min:1'],
            'diagnosis' => ['required', 'string', 'max:2000'],
        ]);

        $item = ClinicStockItem::findOrFail($data['clinic_stock_item_id']);
        $nextIssued = $item->quantity_issued + $data['quantity_issued'];

        if ($nextIssued > ($item->opening_stock + $item->quantity_received)) {
            return redirect()->route('clinic')->withErrors([
                'quantity_issued' => 'Issued quantity cannot exceed available stock for ' . $item->medicine_name . '.',
            ]);
        }

        ClinicStockUsage::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => $user->id,
            'student_id' => $data['student_id'],
            'quantity_issued' => $data['quantity_issued'],
            'diagnosis' => $data['diagnosis'],
            'usage_date' => now()->toDateString(),
        ]);

        $this->syncClinicStockItemIssued($item, $nextIssued);

        return redirect()->route('clinic')->with('success', 'Stock usage recorded for ' . $item->medicine_name . '.');
    }

    public function updateUsage(Request $request, ClinicStockUsage $usage)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'clinic_stock_item_id' => ['required', 'exists:clinic_stock_items,id'],
            'student_id' => ['required', 'string', 'max:100'],
            'quantity_issued' => ['required', 'integer', 'min:1'],
            'diagnosis' => ['required', 'string', 'max:2000'],
        ]);

        $oldItem = ClinicStockItem::findOrFail($usage->clinic_stock_item_id);
        $newItem = ClinicStockItem::findOrFail($data['clinic_stock_item_id']);

        if ($oldItem->id === $newItem->id) {
            $nextIssued = max(0, $oldItem->quantity_issued - $usage->quantity_issued + $data['quantity_issued']);

            if ($nextIssued > ($oldItem->opening_stock + $oldItem->quantity_received)) {
                return redirect()->route('clinic')->withErrors([
                    'quantity_issued' => 'Updated issued quantity cannot exceed available stock for ' . $oldItem->medicine_name . '.',
                ]);
            }

            $usage->update([
                'student_id' => $data['student_id'],
                'quantity_issued' => $data['quantity_issued'],
                'diagnosis' => $data['diagnosis'],
            ]);

            $this->syncClinicStockItemIssued($oldItem, $nextIssued);

            return redirect()->route('clinic')->with('success', 'Stock usage updated successfully.');
        }

        $oldItemNextIssued = max(0, $oldItem->quantity_issued - $usage->quantity_issued);
        $newItemNextIssued = $newItem->quantity_issued + $data['quantity_issued'];

        if ($newItemNextIssued > ($newItem->opening_stock + $newItem->quantity_received)) {
            return redirect()->route('clinic')->withErrors([
                'quantity_issued' => 'Updated issued quantity cannot exceed available stock for ' . $newItem->medicine_name . '.',
            ]);
        }

        $usage->update([
            'clinic_stock_item_id' => $newItem->id,
            'student_id' => $data['student_id'],
            'quantity_issued' => $data['quantity_issued'],
            'diagnosis' => $data['diagnosis'],
        ]);

        $this->syncClinicStockItemIssued($oldItem, $oldItemNextIssued);
        $this->syncClinicStockItemIssued($newItem, $newItemNextIssued);

        return redirect()->route('clinic')->with('success', 'Stock usage updated successfully.');
    }

    public function deleteUsage(ClinicStockUsage $usage)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'senior_nurse_officer') {
            return redirect()->route('home');
        }

        $item = ClinicStockItem::findOrFail($usage->clinic_stock_item_id);
        $nextIssued = max(0, $item->quantity_issued - $usage->quantity_issued);

        $usage->delete();
        $this->syncClinicStockItemIssued($item, $nextIssued);

        return redirect()->route('clinic')->with('success', 'Stock usage deleted successfully.');
    }

    public function downloadReport(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!$this->canGenerateClinicReport($user)) {
            return redirect()->route('clinic')->with('error', 'Only executive and senior nurse officer can download clinic reports.');
        }

        [
            'reportType' => $reportType,
            'reportYear' => $reportYear,
            'reportMonth' => $reportMonth,
            'reportSemester' => $reportSemester,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportLabel' => $reportLabel,
        ] = $this->reportFiltersFromRequest($request);

        $stockUsagesQuery = ClinicStockUsage::with(['stockItem']);
        $stockReceiptsQuery = ClinicStockReceipt::query()
            ->join('clinic_stock_items', 'clinic_stock_receipts.clinic_stock_item_id', '=', 'clinic_stock_items.id')
            ->select('clinic_stock_receipts.*', 'clinic_stock_items.medicine_name as stock_medicine_name');

        if ($reportType !== 'general') {
            $stockUsagesQuery->whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()]);
            $stockReceiptsQuery->whereBetween('clinic_stock_receipts.received_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        $stockUsages = $stockUsagesQuery->latest()->get();
        $stockReceipts = $stockReceiptsQuery
            ->latest('clinic_stock_receipts.received_date')
            ->latest('clinic_stock_receipts.id')
            ->get();
        $diseaseUsageGroups = $this->groupClinicUsagesByDisease($stockUsages);
        $diseaseBreakdown = $this->summarizeClinicDiseaseBreakdown($stockUsages);

        $fileName = 'clinic-stock-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($stockReceipts, $diseaseUsageGroups, $diseaseBreakdown, $reportLabel) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Report Period', $reportLabel]);
            fputcsv($handle, []);
            fputcsv($handle, ['Disease Category Summary']);
            fputcsv($handle, ['Disease', 'Cases', 'Quantity Used', 'Medicines Used', 'Latest Date']);

            if ($diseaseBreakdown->isEmpty()) {
                fputcsv($handle, ['No disease categories recorded for this report period.']);
            } else {
                foreach ($diseaseBreakdown as $disease) {
                    fputcsv($handle, [
                        $disease['label'],
                        $disease['case_count'],
                        $disease['quantity_issued'],
                        implode(', ', $disease['medicine_names']),
                        $disease['latest_used_at']?->format('Y-m-d') ?? '',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Stock Received Report']);
            fputcsv($handle, ['Medicine', 'Quantity Received', 'Date Received']);

            foreach ($stockReceipts as $receipt) {
                fputcsv($handle, [
                    $receipt->stock_medicine_name ?? 'Unknown',
                    $receipt->quantity_received,
                    optional($receipt->received_date)->format('Y-m-d') ?? optional($receipt->created_at)->format('Y-m-d') ?? '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Stock Used by Disease']);

            if ($diseaseUsageGroups->isEmpty()) {
                fputcsv($handle, ['No stock usage records exist for the selected period.']);
            } else {
                foreach ($diseaseUsageGroups as $diseaseGroup) {
                    fputcsv($handle, [
                        'Disease',
                        $diseaseGroup['label'],
                        'Cases',
                        $diseaseGroup['case_count'],
                        'Quantity Used',
                        $diseaseGroup['quantity_issued'],
                    ]);
                    fputcsv($handle, [
                        'Medicines Used',
                        implode(', ', $diseaseGroup['medicine_names']),
                        'Latest Date',
                        $diseaseGroup['latest_used_at']?->format('Y-m-d') ?? '',
                    ]);
                    fputcsv($handle, ['Medicine', 'Student ID', 'Quantity Used', 'Date Used']);

                    foreach ($diseaseGroup['usages'] as $usage) {
                        fputcsv($handle, [
                            optional($usage->stockItem)->medicine_name ?? 'Unknown',
                            $usage->student_id,
                            $usage->quantity_issued,
                            optional($usage->usage_date)->format('Y-m-d') ?? optional($usage->created_at)->format('Y-m-d') ?? '',
                        ]);
                    }

                    fputcsv($handle, []);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    // Protected helper methods

    protected function canAccessClinic(User $user): bool
    {
        return in_array($user->role, ['executive', 'ssd_assistant_1', 'ssd_assistant_2', 'senior_nurse_officer'], true);
    }

    protected function canGenerateClinicReport(User $user): bool
    {
        return in_array($user->role, ['executive', 'senior_nurse_officer'], true);
    }

    protected function renderClinicPage(User $user, Request $request)
    {
        $this->ensureDefaultClinicStockExists();

        [
            'reportType' => $reportType,
            'reportYear' => $reportYear,
            'reportMonth' => $reportMonth,
            'reportSemester' => $reportSemester,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportLabel' => $reportLabel,
        ] = $this->reportFiltersFromRequest($request);

        $stockItems = ClinicStockItem::with([
                'confirmer',
                'comments.user',
                'comments.replies.user',
            ])
            ->orderBy('medicine_name')
            ->get();

        $stockUsagesQuery = ClinicStockUsage::with(['stockItem']);
        $stockReceiptsQuery = ClinicStockReceipt::query()
            ->join('clinic_stock_items', 'clinic_stock_receipts.clinic_stock_item_id', '=', 'clinic_stock_items.id')
            ->select('clinic_stock_receipts.*', 'clinic_stock_items.medicine_name as stock_medicine_name');

        if ($reportType !== 'general') {
            $stockUsagesQuery->whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()]);
            $stockReceiptsQuery->whereBetween('clinic_stock_receipts.received_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        $stockUsages = $stockUsagesQuery
            ->latest()
            ->get();

        $stockReceipts = $stockReceiptsQuery
            ->latest('clinic_stock_receipts.received_date')
            ->latest('clinic_stock_receipts.id')
            ->get();
        $diseaseUsageGroups = $this->groupClinicUsagesByDisease($stockUsages);
        $diseaseBreakdown = $this->summarizeClinicDiseaseBreakdown($stockUsages);

        $allClinicStockReceipts = ClinicStockReceipt::query()
            ->join('clinic_stock_items', 'clinic_stock_receipts.clinic_stock_item_id', '=', 'clinic_stock_items.id')
            ->select('clinic_stock_receipts.*', 'clinic_stock_items.medicine_name as stock_medicine_name')
            ->latest('clinic_stock_receipts.received_date')
            ->latest('clinic_stock_receipts.id')
            ->get();

        $totalClinicReceived = (int) ClinicStockReceipt::sum('quantity_received');

        return view('clinic.executive', compact(
            'user',
            'stockItems',
            'stockUsages',
            'stockReceipts',
            'diseaseUsageGroups',
            'diseaseBreakdown',
            'allClinicStockReceipts',
            'totalClinicReceived',
            'reportType',
            'reportYear',
            'reportMonth',
            'reportSemester',
            'reportLabel'
        ));
    }

    protected function ensureDefaultClinicStockExists(): void
    {
        if (ClinicStockItem::exists()) {
            return;
        }

        foreach ([
            ['medicine_name' => 'Medicine 0', 'opening_stock' => 0, 'quantity_received' => 0, 'quantity_issued' => 0, 'status' => 'out_of_stock'],
            ['medicine_name' => 'Paracetamol', 'opening_stock' => 120, 'quantity_received' => 30, 'quantity_issued' => 40, 'status' => 'in_stock'],
            ['medicine_name' => 'Amoxicillin', 'opening_stock' => 50, 'quantity_received' => 0, 'quantity_issued' => 22, 'status' => 'low_stock'],
        ] as $stockItem) {
            ClinicStockItem::create($stockItem);
        }
    }

    protected function resolveClinicStockStatus(int $balance): string
    {
        if ($balance >= 40) {
            return 'in_stock';
        }

        if ($balance < 30) {
            return 'low_stock';
        }

        return 'out_of_stock';
    }

    protected function syncClinicStockItemIssued(ClinicStockItem $item, int $quantityIssued): void
    {
        $safeIssued = max(0, $quantityIssued);

        $item->update([
            'quantity_issued' => $safeIssued,
            'status' => $this->resolveClinicStockStatus(
                $item->opening_stock + $item->quantity_received - $safeIssued
            ),
        ]);
    }

    protected function recordClinicStockReceipt(ClinicStockItem $item, User $user, int $quantityReceived): void
    {
        if ($quantityReceived <= 0) {
            return;
        }

        ClinicStockReceipt::create([
            'clinic_stock_item_id' => $item->id,
            'user_id' => $user->id,
            'quantity_received' => $quantityReceived,
            'received_date' => now()->toDateString(),
        ]);
    }

    protected function reportFiltersFromRequest(Request $request): array
    {
        $reportType = in_array($request->query('report_type'), ['general', 'semester', 'month', 'year'], true)
            ? $request->query('report_type')
            : 'general';
        $reportYear = (int) ($request->query('report_year', now()->year));
        $reportYear = $reportYear >= 2000 && $reportYear <= 2100 ? $reportYear : (int) now()->year;
        $reportMonth = (int) ($request->query('report_month', now()->month));
        $reportMonth = $reportMonth >= 1 && $reportMonth <= 12 ? $reportMonth : (int) now()->month;
        $reportSemester = (int) ($request->query('report_semester', 1));
        $reportSemester = in_array($reportSemester, [1, 2], true) ? $reportSemester : 1;

        [$startDate, $endDate, $reportLabel] = $this->resolveReportDateRange(
            $reportType,
            $reportYear,
            $reportMonth,
            $reportSemester
        );

        return compact('reportType', 'reportYear', 'reportMonth', 'reportSemester', 'startDate', 'endDate', 'reportLabel');
    }

    protected function resolveReportDateRange(
        string $reportType,
        int $reportYear,
        int $reportMonth,
        int $reportSemester
    ): array {
        if ($reportType === 'general') {
            return [Carbon::minValue(), Carbon::maxValue(), 'All Time'];
        }

        if ($reportType === 'year') {
            $startDate = Carbon::create($reportYear, 1, 1)->startOfDay();
            $endDate = Carbon::create($reportYear, 12, 31)->endOfDay();
            return [$startDate, $endDate, 'Year ' . $reportYear];
        }

        if ($reportType === 'month') {
            $startDate = Carbon::create($reportYear, $reportMonth, 1)->startOfDay();
            $endDate = Carbon::create($reportYear, $reportMonth, 1)->endOfMonth()->endOfDay();
            return [$startDate, $endDate, Carbon::create($reportYear, $reportMonth)->format('F Y')];
        }

        $startMonth = $reportSemester === 1 ? 8 : 1;
        $endMonth = $reportSemester === 1 ? 12 : 5;
        $startDate = Carbon::create($reportYear, $startMonth, 1)->startOfDay();
        $endDate = Carbon::create($reportYear, $endMonth, 1)->endOfMonth()->endOfDay();

        return [$startDate, $endDate, 'Semester ' . $reportSemester . ' (' . $reportYear . ')'];
    }

    protected function groupClinicUsagesByDisease(Collection $stockUsages): Collection
    {
        return $stockUsages
            ->groupBy(function ($usage) {
                $diagnosis = strtolower($usage->diagnosis ?? '');
                if (str_contains($diagnosis, 'malaria')) {
                    return 'Malaria';
                }
                if (str_contains($diagnosis, 'flu') || str_contains($diagnosis, 'influenza') || str_contains($diagnosis, 'cough') || str_contains($diagnosis, 'cold')) {
                    return 'Flu/Cold';
                }
                if (str_contains($diagnosis, 'headache') || str_contains($diagnosis, 'pain')) {
                    return 'Headache/Pain';
                }
                if (str_contains($diagnosis, 'stomach') || str_contains($diagnosis, 'abdominal') || str_contains($diagnosis, 'diarrhea') || str_contains($diagnosis, 'nausea')) {
                    return 'Stomach Issues';
                }
                if (str_contains($diagnosis, 'skin') || str_contains($diagnosis, 'rash')) {
                    return 'Skin Conditions';
                }
                if (str_contains($diagnosis, 'allergy') || str_contains($diagnosis, 'allergic')) {
                    return 'Allergies';
                }
                return 'Other';
            })
            ->map(function ($group) {
                return [
                    'label' => $group->first(),
                    'case_count' => $group->count(),
                    'quantity_issued' => $group->sum('quantity_issued'),
                    'medicine_names' => $group->pluck('stockItem.medicine_name')->filter()->unique()->values()->all(),
                    'latest_used_at' => $group->max('usage_date'),
                    'usages' => $group,
                ];
            })
            ->sortByDesc('case_count')
            ->values();
    }

    protected function summarizeClinicDiseaseBreakdown(Collection $stockUsages): Collection
    {
        return $this->groupClinicUsagesByDisease($stockUsages)
            ->map(function ($group) {
                return [
                    'label' => $group['label'],
                    'case_count' => $group['case_count'],
                    'quantity_issued' => $group['quantity_issued'],
                    'medicine_names' => $group['medicine_names'],
                    'latest_used_at' => $group['latest_used_at'] instanceof Carbon ? Carbon::parse($group['latest_used_at']) : null,
                ];
            });
    }
}