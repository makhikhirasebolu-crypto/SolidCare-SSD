<?php

namespace App\Http\Controllers;

use App\Counselling\Models\EmergencyCounsellingModel;
use App\Counselling\Support\DatasetEmergencyCounsellingResponder;
use App\Models\CounsellingBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CounsellingController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($this->canManageCounselling($user)) {
            [
                'reportType' => $reportType,
                'reportYear' => $reportYear,
                'reportMonth' => $reportMonth,
                'reportSemester' => $reportSemester,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'reportLabel' => $reportLabel,
            ] = $this->reportFiltersFromRequest($request);

            $bookings = CounsellingBooking::with('user')
                ->latest()
                ->get();

            $reportBookings = $reportType === 'general'
                ? $bookings
                : $this->counsellingReportQuery($reportType, $startDate, $endDate)
                    ->with('user')
                    ->latest()
                    ->get();

            return view('counselling.psychologist', compact(
                'user',
                'bookings',
                'reportBookings',
                'reportType',
                'reportYear',
                'reportMonth',
                'reportSemester',
                'reportLabel'
            ));
        }

        if (!$this->canAccessStudentCounselling($user)) {
            return redirect()->route('home')->with('error', 'Only continuing students can access counselling services.');
        }

        $emergencyCounsellingModel = EmergencyCounsellingModel::fromConfig();
        $datasetResponder = app(DatasetEmergencyCounsellingResponder::class);
        $apiKey = config('services.openai.api_key');
        $emergencyChatMeta = is_string($apiKey) && trim($apiKey) !== ''
            ? [
                'model' => $emergencyCounsellingModel->identifier(),
                'model_label' => $emergencyCounsellingModel->label(),
                'model_provider' => $emergencyCounsellingModel->provider(),
                'model_description' => $emergencyCounsellingModel->description(),
            ]
            : $datasetResponder->meta();
        $bookings = CounsellingBooking::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('counselling.student', compact('user', 'bookings', 'emergencyChatMeta'));
    }

    public function storeBooking(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!$this->canAccessStudentCounselling($user)) {
            return redirect()->route('home')->with('error', 'Only continuing students can access counselling services.');
        }

        $data = $request->validate([
            'sex' => ['required', 'string', 'max:50'],
            'reason' => ['required', 'string', 'max:1000'],
            'programme' => ['required', 'string', 'max:255'],
            'year_of_study' => ['required', 'string', 'max:50'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', 'date_format:H:i'],
        ]);

        CounsellingBooking::create([
            'user_id' => $user->id,
            'student_name' => $user->name,
            'student_identity_number' => $user->student_id ?: $user->id_number,
            'sex' => $data['sex'],
            'reason' => $data['reason'],
            'programme' => $data['programme'],
            'year_of_study' => $data['year_of_study'],
            'preferred_date' => $data['preferred_date'],
            'preferred_time' => $data['preferred_time'],
            'status' => 'pending',
        ]);

        return redirect()->route('counselling')->with('success', 'Counselling session booked successfully.');
    }

    public function updateBooking(Request $request, CounsellingBooking $booking)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!$this->canManageCounselling($user)) {
            return redirect()->route('home')->with('error', 'Only psychologists can manage counselling appointments.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in($this->counsellingBookingStatuses())],
            'appointment_date' => ['nullable', 'date', 'after_or_equal:today'],
            'counsellor_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointmentDate = $data['appointment_date'] ?? null;
        $resolvedAppointmentDate = blank($appointmentDate)
            ? $booking->appointment_date
            : Carbon::parse($appointmentDate)->seconds(0);
        $resolvedStatus = $data['status'];

        if ($resolvedAppointmentDate && $resolvedStatus === 'pending') {
            $resolvedStatus = 'scheduled';
        }

        if ($resolvedStatus === 'cancelled') {
            $resolvedAppointmentDate = null;
        }

        if (in_array($resolvedStatus, ['scheduled', 'attended'], true) && !$resolvedAppointmentDate) {
            return back()
                ->withErrors([
                    'appointment_date' => 'Set an appointment date and time before marking this booking as scheduled or attended.',
                ])
                ->withInput();
        }

        if ($resolvedAppointmentDate) {
            $hasConflict = CounsellingBooking::query()
                ->whereKeyNot($booking->id)
                ->whereNotNull('appointment_date')
                ->where('appointment_date', $resolvedAppointmentDate->format('Y-m-d H:i:s'))
                ->exists();

            if ($hasConflict) {
                return back()
                    ->withErrors([
                        'appointment_date' => 'That appointment date and time is already booked. Choose another slot.',
                    ])
                    ->withInput();
            }
        }

        $booking->update([
            'status' => $resolvedStatus,
            'appointment_date' => $resolvedAppointmentDate,
            'counsellor_notes' => $data['counsellor_notes'] ?? null,
        ]);

        return redirect()->route('counselling')->with('success', 'Counselling appointment updated successfully.');
    }

    public function emergencyReply(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return response()->json([
                'message' => 'Please sign in to continue.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!$this->canAccessStudentCounselling($user)) {
            return response()->json([
                'message' => 'Only continuing students can access counselling services.',
            ], 403);
        }

        $emergencyCounsellingModel = EmergencyCounsellingModel::fromConfig();
        $datasetResponder = app(DatasetEmergencyCounsellingResponder::class);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'messages' => ['nullable', 'array', 'max:10'],
            'messages.*.role' => ['required_with:messages', 'in:user,assistant'],
            'messages.*.content' => ['required_with:messages', 'string', 'max:2000'],
        ]);

        $history = collect($data['messages'] ?? [])
            ->take(-8)
            ->map(function (array $message) {
                return [
                    'role' => $message['role'],
                    'content' => $message['content'],
                ];
            })
            ->values()
            ->all();

        $apiKey = config('services.openai.api_key');
        if (!is_string($apiKey) || trim($apiKey) === '') {
            return response()->json($datasetResponder->respond($data['message'], $history));
        }

        $payload = $emergencyCounsellingModel->buildPayload($data['message'], $history);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', $payload)
                ->throw()
                ->json();
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json($datasetResponder->respond($data['message'], $history));
        }

        $reply = $this->extractEmergencyCounsellingReply((array) $response);
        if (!$reply) {
            return response()->json($datasetResponder->respond($data['message'], $history));
        }

        return response()->json([
            'reply' => $reply,
            'model' => $payload['model'],
            'model_label' => $emergencyCounsellingModel->label(),
            'model_provider' => $emergencyCounsellingModel->provider(),
            'model_description' => $emergencyCounsellingModel->description(),
        ]);
    }

    public function downloadReport(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (!$this->canManageCounselling($user)) {
            return redirect()->route('counselling')->with('error', 'Only psychologists can download counselling reports.');
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

        $reportBookings = $this->counsellingReportQuery($reportType, $startDate, $endDate)
            ->with('user')
            ->latest()
            ->get();

        $statusSummary = $reportBookings->countBy(fn (CounsellingBooking $booking) => $this->normalizeCounsellingStatus($booking->status));
        $attendedBookings = $reportBookings
            ->filter(fn (CounsellingBooking $booking) => $this->normalizeCounsellingStatus($booking->status) === 'attended')
            ->sortByDesc(fn (CounsellingBooking $booking) => optional($booking->appointment_date ?? $booking->updated_at ?? $booking->created_at)->timestamp ?? 0)
            ->values();
        $pendingBookings = $reportBookings
            ->filter(fn (CounsellingBooking $booking) => $this->normalizeCounsellingStatus($booking->status) === 'pending')
            ->sortBy(fn (CounsellingBooking $booking) => optional($booking->preferred_date ?? $booking->created_at)->timestamp ?? PHP_INT_MAX)
            ->values();

        $fileName = 'counselling-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use (
            $attendedBookings,
            $pendingBookings,
            $reportBookings,
            $reportLabel,
            $statusSummary,
            $user
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Report Period', $reportLabel]);
            fputcsv($handle, ['Generated By', $user->name ?: 'Psychologist']);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            fputcsv($handle, ['Total Requests', $reportBookings->count()]);
            fputcsv($handle, ['Pending Sessions', $statusSummary->get('pending', 0)]);
            fputcsv($handle, ['Scheduled Sessions', $statusSummary->get('scheduled', 0)]);
            fputcsv($handle, ['Students Who Have Gone For Counselling', $statusSummary->get('attended', 0)]);
            fputcsv($handle, ['Cancelled Sessions', $statusSummary->get('cancelled', 0)]);
            fputcsv($handle, []);

            fputcsv($handle, ['Students Who Have Gone For Counselling']);
            fputcsv($handle, ['Student Name', 'Identity Number', 'Programme', 'Year of Study', 'Session Date', 'Requested On', 'Counsellor Notes']);

            if ($attendedBookings->isEmpty()) {
                fputcsv($handle, ['No attended counselling sessions found for this report period.']);
            } else {
                foreach ($attendedBookings as $booking) {
                    fputcsv($handle, [
                        $booking->student_name,
                        $booking->student_identity_number ?: '-',
                        $booking->programme ?: '-',
                        $booking->year_of_study ?: '-',
                        $booking->appointment_date?->format('Y-m-d H:i') ?: '-',
                        $booking->created_at?->format('Y-m-d H:i') ?: '-',
                        $this->flattenCsvCell($booking->counsellor_notes) ?: '-',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Pending Sessions']);
            fputcsv($handle, ['Student Name', 'Identity Number', 'Programme', 'Year of Study', 'Preferred Session', 'Requested On', 'Reason']);

            if ($pendingBookings->isEmpty()) {
                fputcsv($handle, ['No pending counselling sessions found for this report period.']);
            } else {
                foreach ($pendingBookings as $booking) {
                    $preferredSession = trim(implode(' ', array_filter([
                        $booking->preferred_date?->format('Y-m-d'),
                        $booking->preferred_time ? 'at ' . $booking->preferred_time : null,
                    ])));

                    fputcsv($handle, [
                        $booking->student_name,
                        $booking->student_identity_number ?: '-',
                        $booking->programme ?: '-',
                        $booking->year_of_study ?: '-',
                        $preferredSession !== '' ? $preferredSession : '-',
                        $booking->created_at?->format('Y-m-d H:i') ?: '-',
                        $this->flattenCsvCell($booking->reason) ?: '-',
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    // Protected helper methods

    protected function canAccessStudentCounselling(User $user): bool
    {
        return $user->role === 'student' && $user->student_type === 'continuing';
    }

    protected function canManageCounselling(User $user): bool
    {
        return $user->role === 'psychologist';
    }

    protected function normalizeCounsellingStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'approved' => 'scheduled',
            'completed' => 'attended',
            default => strtolower((string) $status),
        };
    }

    protected function counsellingBookingStatuses(): array
    {
        return ['pending', 'scheduled', 'attended', 'cancelled'];
    }

    protected function extractEmergencyCounsellingReply(array $response): ?string
    {
        $outputText = $response['output_text'] ?? null;
        if (is_string($outputText) && trim($outputText) !== '') {
            return trim($outputText);
        }

        $texts = [];

        foreach ($response['output'] ?? [] as $outputItem) {
            if (($outputItem['type'] ?? null) !== 'message') {
                continue;
            }

            foreach ($outputItem['content'] ?? [] as $contentItem) {
                if (($contentItem['type'] ?? null) === 'output_text') {
                    $texts[] = $contentItem['text'] ?? '';
                }
            }
        }

        $reply = trim(implode("\n\n", array_filter($texts)));

        return $reply !== '' ? $reply : null;
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

    protected function counsellingReportQuery(string $reportType, Carbon $startDate, Carbon $endDate)
    {
        return CounsellingBooking::query()
            ->whereBetween('created_at', [$startDate, $endDate]);
    }

    protected function flattenCsvCell(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return str_replace(["\n", "\r"], ' ', $value);
    }
}
