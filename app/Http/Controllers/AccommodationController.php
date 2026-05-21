<?php

namespace App\Http\Controllers;

use App\Models\AccommodationApplication;
use App\Models\AccommodationRoom;
use App\Models\User;
use App\Mail\AccommodationStatusUpdated;
use App\Mail\CheckoutApproved;
use App\Mail\CheckoutRejected;
use App\Mail\CheckoutRequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AccommodationController extends Controller
{
    public function index()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();
        $application = AccommodationApplication::with('room')
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if ($user->role === 'executive') {
            $this->ensureDefaultAccommodationRoomsExist();

            $pendingAdmissionsCount = AccommodationApplication::where('status', 'pending')->count();
            $checkoutApprovalsCount = AccommodationApplication::where('status', 'checkout_requested')->count();
            $availableRoomsCount = AccommodationRoom::withCount([
                    'applications as occupied_beds' => function ($query) {
                        $query->whereIn('status', $this->occupyingAccommodationStatuses());
                    },
                ])
                ->get()
                ->filter(function ($room) {
                    return $room->occupied_beds < $room->capacity;
                })
                ->count();

            return view('accommodation.executive', compact(
                'user',
                'pendingAdmissionsCount',
                'checkoutApprovalsCount',
                'availableRoomsCount'
            ));
        }

        if ($user->role === 'warden') {
            $this->ensureDefaultAccommodationRoomsExist();

            $admittedApplications = AccommodationApplication::with(['user', 'room'])
                ->whereIn('status', $this->occupyingAccommodationStatuses())
                ->latest()
                ->get();

            $rooms = AccommodationRoom::withCount([
                    'applications as occupied_beds' => function ($query) {
                        $query->whereIn('status', $this->occupyingAccommodationStatuses());
                    },
                ])
                ->orderBy('block_name')
                ->orderBy('room_number')
                ->get()
                ->groupBy('block_name');

            return view('accommodation.warden', compact('user', 'admittedApplications', 'rooms'));
        }

        return view('accommodation.student', compact('user', 'application'));
    }

    public function pendingAdmissions()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if (!in_array($user->role, ['executive', 'warden'], true)) {
            return redirect()->route('accommodation');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        $applications = AccommodationApplication::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $availableRooms = AccommodationRoom::withCount([
                'applications as occupied_beds' => function ($query) {
                    $query->whereIn('status', $this->occupyingAccommodationStatuses());
                },
            ])
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get()
            ->filter(function ($room) {
                return $room->occupied_beds < $room->capacity;
            })
            ->values();

        return view('accommodation.pending-admissions', compact('user', 'applications', 'availableRooms'));
    }

    public function roomManagement()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        $rooms = AccommodationRoom::withCount([
                'applications as occupied_beds' => function ($query) {
                    $query->whereIn('status', $this->occupyingAccommodationStatuses());
                },
            ])
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get()
            ->groupBy('block_name');

        return view('accommodation.rooms', compact('user', 'rooms'));
    }

    public function downloadReport()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation')->with('error', 'Only executive can download accommodation reports.');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        $applications = AccommodationApplication::with(['user', 'room'])
            ->latest('created_at')
            ->latest('id')
            ->get();

        $rooms = AccommodationRoom::withCount([
                'applications as occupied_beds' => function ($query) {
                    $query->whereIn('status', $this->occupyingAccommodationStatuses());
                },
            ])
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get();

        $pendingApplications = $applications
            ->where('status', 'pending')
            ->values();

        $checkoutRequests = $applications
            ->where('status', 'checkout_requested')
            ->values();

        $statusSummary = $applications
            ->countBy(fn (AccommodationApplication $application) => Str::headline($application->status))
            ->sortKeys();

        $availableRoomsCount = $rooms
            ->filter(fn (AccommodationRoom $room) => $room->occupied_beds < $room->capacity)
            ->count();
        $occupiedRoomsCount = $rooms
            ->filter(fn (AccommodationRoom $room) => $room->occupied_beds > 0)
            ->count();
        $occupiedBedsCount = $rooms->sum('occupied_beds');
        $totalCapacity = $rooms->sum('capacity');
        $availableBedsCount = max(0, $totalCapacity - $occupiedBedsCount);

        $fileName = 'accommodation-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use (
            $applications,
            $availableBedsCount,
            $availableRoomsCount,
            $checkoutRequests,
            $occupiedBedsCount,
            $occupiedRoomsCount,
            $pendingApplications,
            $rooms,
            $statusSummary,
            $totalCapacity,
            $user
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Accommodation Management Report']);
            fputcsv($handle, ['Generated By', $user->name ?: 'Executive']);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Dashboard Summary']);
            fputcsv($handle, ['Pending Admissions', $pendingApplications->count()]);
            fputcsv($handle, ['Checkout Approvals', $checkoutRequests->count()]);
            fputcsv($handle, ['Available Rooms', $availableRoomsCount]);
            fputcsv($handle, ['Occupied Rooms', $occupiedRoomsCount]);
            fputcsv($handle, ['Occupied Beds', $occupiedBedsCount]);
            fputcsv($handle, ['Available Beds', $availableBedsCount]);
            fputcsv($handle, ['Total Room Capacity', $totalCapacity]);
            fputcsv($handle, ['Total Applications', $applications->count()]);
            fputcsv($handle, []);

            fputcsv($handle, ['Application Status Breakdown']);
            fputcsv($handle, ['Status', 'Count']);
            if ($statusSummary->isEmpty()) {
                fputcsv($handle, ['No accommodation applications found.', '']);
            } else {
                foreach ($statusSummary as $status => $count) {
                    fputcsv($handle, [$status, $count]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Pending Admissions']);
            fputcsv($handle, ['Student Name', 'Student ID', 'Application Email', 'Account Email', 'Faculty', 'Programme', 'Check-In Date', 'Submitted']);
            if ($pendingApplications->isEmpty()) {
                fputcsv($handle, ['No pending admissions.', '', '', '', '', '', '', '']);
            } else {
                foreach ($pendingApplications as $application) {
                    fputcsv($handle, [
                        $application->full_name,
                        $application->student_id ?: optional($application->user)->student_id ?: '',
                        $application->email,
                        optional($application->user)->email ?? '',
                        $application->faculty,
                        $application->programme,
                        optional($application->check_in_date)->format('Y-m-d') ?? '',
                        optional($application->created_at)->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Checkout Requests']);
            fputcsv($handle, ['Student Name', 'Student ID', 'Room', 'Checkout Date', 'Requested At', 'Reason']);
            if ($checkoutRequests->isEmpty()) {
                fputcsv($handle, ['No checkout requests awaiting approval.', '', '', '', '', '']);
            } else {
                foreach ($checkoutRequests as $application) {
                    fputcsv($handle, [
                        $application->full_name,
                        $application->student_id ?: optional($application->user)->student_id ?: '',
                        $this->formatAccommodationRoomLabel($application->room) ?? 'Not assigned',
                        optional($application->checkout_date)->format('Y-m-d') ?? '',
                        optional($application->checkout_requested_at)->format('Y-m-d H:i:s')
                            ?? optional($application->updated_at)->format('Y-m-d H:i:s')
                            ?? '',
                        $application->checkout_reason ?? '',
                    ]);
                }
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Room Availability']);
            fputcsv($handle, ['Block', 'Room Number', 'Capacity', 'Occupied Beds', 'Available Beds', 'Occupancy Status']);
            foreach ($rooms as $room) {
                $availableBeds = max(0, $room->capacity - $room->occupied_beds);

                fputcsv($handle, [
                    $room->block_name,
                    str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT),
                    $room->capacity,
                    $room->occupied_beds,
                    $availableBeds,
                    $availableBeds > 0 ? 'Available' : 'Full',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function storeRoom(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        $data = $request->validate([
            'block_name' => ['required', 'string', 'max:20'],
            'room_number' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $blockName = strtoupper(trim($data['block_name']));

        AccommodationRoom::firstOrCreate(
            [
                'block_name' => $blockName,
                'room_number' => $data['room_number'],
            ],
            [
                'capacity' => 4,
            ]
        );

        return redirect()->route('student.accommodation.rooms')->with('success', 'Room created successfully in block ' . $blockName . '.');
    }

    public function seedDefaultRooms()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        $this->ensureDefaultAccommodationRoomsExist();

        return redirect()->route('student.accommodation.rooms')->with('success', 'Default room blocks AG and AF have been created with 15 rooms each.');
    }

    public function updateAdmissionStatus(Request $request, AccommodationApplication $application)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        $data = $request->validate([
            'status' => ['required', 'in:admitted,rejected,conditional'],
            'accommodation_room_id' => ['nullable', 'exists:accommodation_rooms,id'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $roomId = $data['accommodation_room_id'] ?? null;

        if ($data['status'] === 'admitted') {
            if (!$roomId) {
                return redirect()
                    ->route('student.accommodation.pending')
                    ->withErrors(['accommodation_room_id' => 'Please select a room before approving the student.'])
                    ->withInput();
            }

            $room = AccommodationRoom::withCount([
                    'applications as occupied_beds' => function ($query) {
                        $query->whereIn('status', $this->occupyingAccommodationStatuses());
                    },
                ])
                ->findOrFail($roomId);

            if ($room->occupied_beds >= $room->capacity) {
                return redirect()
                    ->route('student.accommodation.pending')
                    ->withErrors(['accommodation_room_id' => 'The selected room is already full. Please choose another room.'])
                    ->withInput();
            }
        }

        if ($data['status'] === 'rejected' && blank($data['rejection_reason'] ?? null)) {
            return redirect()
                ->route('student.accommodation.pending')
                ->withErrors(['rejection_reason' => 'Please provide a reason when rejecting the application.'])
                ->withInput();
        }

        $updatePayload = [
            'status' => $data['status'],
            'accommodation_room_id' => $data['status'] === 'admitted' ? $roomId : null,
        ];

        if (Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
            $updatePayload['rejection_reason'] = $data['status'] === 'rejected'
                ? trim((string) ($data['rejection_reason'] ?? ''))
                : null;
        }

        $application->update($updatePayload);

        $this->sendAccommodationStatusEmail($application);

        return redirect()
            ->route('student.accommodation.pending')
            ->with('success', 'Application status updated to ' . ucfirst($data['status']) . '.');
    }

    public function apply()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role === 'executive') {
            return redirect()->route('accommodation');
        }

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit accommodation applications.');
        }

        $existing = $this->findExistingAccommodationApplication(
            $user,
            $this->normalizeNationalId($user->id_number)
        );

        if ($existing) {
            return redirect()
                ->route('accommodation')
                ->with('error', $this->duplicateAccommodationApplicationMessage($existing, $user));
        }

        $applicationFee = $this->accommodationApplicationFee();

        return view('accommodation.apply', compact('user', 'applicationFee'));
    }

    public function store(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role === 'executive') {
            return redirect()->route('accommodation');
        }

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit accommodation applications.');
        }

        $request->merge([
            'email' => is_string($request->input('email'))
                ? Str::lower(trim($request->input('email')))
                : $request->input('email'),
            'national_id' => $this->normalizeNationalId($request->input('national_id')),
        ]);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:100', 'regex:/^\d+$/'],
            'contact_number' => ['required', 'string', 'max:50'],
            'national_id' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'marital_status' => ['required', 'string', 'max:100'],
            'nationality' => ['required', 'string', 'max:100'],
            'nationality_other' => ['nullable', 'string', 'max:100', 'required_if:nationality,Other'],
            'gender' => ['required', 'in:female'],
            'age' => ['required', 'integer', 'min:16', 'max:120'],
            'faculty' => ['required', 'string', 'max:100'],
            'programme' => ['required', 'string', 'max:255'],
            'intake' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'string', 'max:100'],
            'check_in_date' => ['required', 'date'],
            'district' => ['required', 'string', 'max:255'],
            'village' => ['required', 'string', 'max:255'],
            'next_of_kin_name' => ['required', 'string', 'max:255'],
            'next_of_kin_relationship' => ['required', 'string', 'max:100'],
            'next_of_kin_contact' => ['required', 'string', 'max:100'],
            'special_conditions_remark' => ['nullable', 'string', 'max:2000'],
            'has_physical_disability' => ['required', 'boolean'],
            'physical_disability_details' => ['nullable', 'string', 'max:1000', 'required_if:has_physical_disability,1'],
            'has_high_blood_pressure' => ['required', 'boolean'],
            'has_diabetes' => ['required', 'boolean'],
            'has_asthma' => ['required', 'boolean'],
            'chronic_illness_other' => ['nullable', 'string', 'max:1000'],
            'on_chronic_treatment' => ['required', 'boolean'],
            'treatment_frequency' => ['nullable', 'string', 'max:1000', 'required_if:on_chronic_treatment,1'],
            'payment_method' => ['required', 'in:mpesa,ecocash'],
            'payment_phone_number' => ['required', 'string', 'max:50'],
        ], [
            'student_id.regex' => 'Student ID must contain numbers only.',
        ]);

        foreach ([
            'has_physical_disability',
            'has_high_blood_pressure',
            'has_diabetes',
            'has_asthma',
            'on_chronic_treatment',
        ] as $booleanField) {
            $data[$booleanField] = $request->boolean($booleanField);
        }

        if ($data['nationality'] === 'Other') {
            $data['nationality'] = $data['nationality_other'];
        }

        if (! $data['has_physical_disability']) {
            $data['physical_disability_details'] = null;
        }

        if (! $data['on_chronic_treatment']) {
            $data['treatment_frequency'] = null;
        }

        $existing = $this->findExistingAccommodationApplication($user, $data['national_id']);

        if ($existing) {
            return redirect()
                ->route('accommodation')
                ->with('error', $this->duplicateAccommodationApplicationMessage($existing, $user));
        }

        $applicationFee = $this->accommodationApplicationFee();

        $paymentReference = 'ACC-' . now()->format('YmdHis') . '-' . strtoupper((string) $user->id);

        $applicationPayload = [
            'user_id' => $user->id,
            'full_name' => $data['full_name'],
            'student_id' => $data['student_id'],
            'contact_number' => $data['contact_number'],
            'national_id' => $data['national_id'],
            'email' => $data['email'],
            'marital_status' => $data['marital_status'],
            'nationality' => $data['nationality'],
            'gender' => $data['gender'],
            'age' => $data['age'],
            'faculty' => $data['faculty'],
            'programme' => $data['programme'],
            'intake' => $data['intake'],
            'semester' => $data['semester'],
            'check_in_date' => $data['check_in_date'],
            'address' => collect([$data['district'], $data['village']])->filter()->implode(', '),
            'status' => 'pending',
        ];

        foreach ([
            'student_id',
            'gender',
            'intake',
            'semester',
            'district',
            'village',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_contact',
            'special_conditions_remark',
            'has_physical_disability',
            'physical_disability_details',
            'has_high_blood_pressure',
            'has_diabetes',
            'has_asthma',
            'chronic_illness_other',
            'on_chronic_treatment',
            'treatment_frequency',
        ] as $column) {
            if (Schema::hasColumn('accommodation_applications', $column)) {
                $applicationPayload[$column] = $data[$column] ?? null;
            }
        }

        if (Schema::hasColumn('accommodation_applications', 'payment_method')) {
            $applicationPayload['payment_method'] = $data['payment_method'];
        }

        if (Schema::hasColumn('accommodation_applications', 'payment_phone_number')) {
            $applicationPayload['payment_phone_number'] = $data['payment_phone_number'];
        }

        if (Schema::hasColumn('accommodation_applications', 'payment_reference')) {
            $applicationPayload['payment_reference'] = $paymentReference;
        }

        if (Schema::hasColumn('accommodation_applications', 'payment_amount')) {
            $applicationPayload['payment_amount'] = $applicationFee;
        }

        if (Schema::hasColumn('accommodation_applications', 'payment_status')) {
            $applicationPayload['payment_status'] = 'paid';
        }

        if (Schema::hasColumn('accommodation_applications', 'paid_at')) {
            $applicationPayload['paid_at'] = now();
        }

        AccommodationApplication::create($applicationPayload);

        return redirect()->route('accommodation')->with('success', 'Accommodation application submitted successfully. Payment received via ' . strtoupper($data['payment_method']) . '.');
    }

    public function checkout()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role === 'executive') {
            $applicationsQuery = AccommodationApplication::with(['user', 'room'])
                ->where('status', 'checkout_requested');

            if (Schema::hasColumn('accommodation_applications', 'checkout_requested_at')) {
                $applicationsQuery->orderByDesc('checkout_requested_at');
            } else {
                $applicationsQuery->latest('updated_at');
            }

            $applications = $applicationsQuery
                ->latest('id')
                ->get();

            return view('accommodation.checkout-approvals', compact('user', 'applications'));
        }

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit checkout requests.');
        }

        $application = AccommodationApplication::where('user_id', $user->id)
            ->whereIn('status', $this->checkoutEligibleStatuses())
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('accommodation')->with('error', 'Checkout is available only after your accommodation has been admitted.');
        }

        return view('accommodation.checkout', compact('user', 'application'));
    }

    public function storeCheckout(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->student_type !== 'new') {
            return redirect()->route('accommodation')->with('error', 'Only new students can submit checkout requests.');
        }

        $application = AccommodationApplication::where('user_id', $user->id)
            ->whereIn('status', $this->checkoutEligibleStatuses())
            ->latest()
            ->first();

        if (!$application) {
            return redirect()->route('accommodation')->with('error', 'Checkout is available only after your accommodation has been admitted.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:100'],
            'checkout_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $checkoutPayload = [
            'status' => 'checkout_requested',
        ];

        if (Schema::hasColumn('accommodation_applications', 'checkout_date')) {
            $checkoutPayload['checkout_date'] = $data['checkout_date'];
        }

        if (Schema::hasColumn('accommodation_applications', 'checkout_reason')) {
            $checkoutPayload['checkout_reason'] = $data['reason'];
        }

        if (Schema::hasColumn('accommodation_applications', 'checkout_requested_at')) {
            $checkoutPayload['checkout_requested_at'] = now();
        }

        if (Schema::hasColumn('accommodation_applications', 'checked_out_at')) {
            $checkoutPayload['checked_out_at'] = null;
        }

        if (Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
            $checkoutPayload['rejection_reason'] = null;
        }

        $application->update($checkoutPayload);

        $checkoutEmail = $application->email ?: optional($application->user)->email;

        if ($checkoutEmail) {
            try {
                Mail::to($checkoutEmail)->send(new CheckoutRequestSubmitted($application));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('accommodation')->with('success', 'Checkout request submitted successfully.');
    }

    public function updateCheckoutStatus(Request $request, AccommodationApplication $application)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'executive') {
            return redirect()->route('accommodation');
        }

        if ($application->status !== 'checkout_requested') {
            return redirect()
                ->route('student.accommodation.checkout')
                ->with('error', 'Only pending checkout requests can be processed.');
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['decision'] === 'rejected' && blank($data['rejection_reason'] ?? null)) {
            return redirect()
                ->route('student.accommodation.checkout')
                ->withErrors(['rejection_reason' => 'Please provide a reason when rejecting the checkout request.'])
                ->withInput();
        }

        $application->loadMissing(['user', 'room']);
        $previousRoomLabel = $this->formatAccommodationRoomLabel($application->room);

        $approvalPayload = [
            'status' => $data['decision'] === 'approved' ? 'checked_out' : 'checkout_rejected',
        ];

        if ($data['decision'] === 'approved') {
            $approvalPayload['accommodation_room_id'] = null;
        }

        if (Schema::hasColumn('accommodation_applications', 'checked_out_at')) {
            $approvalPayload['checked_out_at'] = $data['decision'] === 'approved' ? now() : null;
        }

        if (Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
            $approvalPayload['rejection_reason'] = $data['decision'] === 'rejected'
                ? trim((string) ($data['rejection_reason'] ?? ''))
                : null;
        }

        $application->update($approvalPayload);

        $checkoutApprovalEmail = $application->email ?: optional($application->user)->email;

        if ($checkoutApprovalEmail) {
            try {
                $application->refresh()->loadMissing(['user', 'room']);

                $mailable = $data['decision'] === 'approved'
                    ? new CheckoutApproved($application, $previousRoomLabel)
                    : new CheckoutRejected($application);

                Mail::to($checkoutApprovalEmail)->send($mailable);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('student.accommodation.checkout')
            ->with(
                'success',
                $data['decision'] === 'approved'
                    ? 'Checkout approved successfully and the allocated room has been released.'
                    : 'Checkout request rejected successfully.'
            );
    }

    // Protected helper methods

    protected function ensureDefaultAccommodationRoomsExist(): void
    {
        if (AccommodationRoom::exists()) {
            return;
        }

        foreach (['AG', 'AF'] as $blockName) {
            for ($roomNumber = 1; $roomNumber <= 15; $roomNumber++) {
                AccommodationRoom::firstOrCreate(
                    [
                        'block_name' => $blockName,
                        'room_number' => $roomNumber,
                    ],
                    [
                        'capacity' => 4,
                    ]
                );
            }
        }
    }

    protected function occupyingAccommodationStatuses(): array
    {
        return ['admitted', 'checkout_requested', 'checkout_rejected'];
    }

    protected function checkoutEligibleStatuses(): array
    {
        return ['admitted', 'checkout_rejected'];
    }

    protected function findExistingAccommodationApplication(User $user, ?string $nationalId = null): ?AccommodationApplication
    {
        return AccommodationApplication::query()
            ->where(function ($query) use ($user, $nationalId) {
                $query->where('user_id', $user->id);

                if (filled($nationalId)) {
                    $query->orWhere('national_id', $nationalId);
                }
            })
            ->latest('updated_at')
            ->latest('id')
            ->first();
    }

    protected function duplicateAccommodationApplicationMessage(AccommodationApplication $application, User $user): string
    {
        return $application->user_id === $user->id
            ? 'You already have an accommodation application on file.'
            : 'An accommodation application already exists for this national ID.';
    }

    protected function normalizeNationalId(mixed $nationalId): ?string
    {
        if (!is_string($nationalId)) {
            return null;
        }

        $nationalId = Str::upper(trim($nationalId));

        return $nationalId !== '' ? $nationalId : null;
    }

    protected function formatAccommodationRoomLabel(?AccommodationRoom $room): ?string
    {
        if (!$room) {
            return null;
        }

        return $room->block_name . '-' . str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT);
    }

    protected function accommodationApplicationFee(): float
    {
        return 105.00;
    }

    protected function sendAccommodationStatusEmail(AccommodationApplication $application): array
    {
        $application->loadMissing(['room', 'user']);

        $recipients = $this->accommodationStatusRecipients($application);
        $statusLabel = Str::headline($application->status);

        if ($recipients === []) {
            return [
                'success' => false,
                'message' => 'Accommodation status updated to ' . $statusLabel . ', but no email address was found for the student.',
            ];
        }

        $submittedRecipients = [];
        $failedRecipients = [];
        $messageIds = [];

        foreach ($recipients as $recipient) {
            try {
                $sentMessage = Mail::to($recipient)->send(new AccommodationStatusUpdated($application));

                $submittedRecipients[] = $recipient;

                if ($sentMessage?->getMessageId()) {
                    $messageIds[] = $sentMessage->getMessageId();
                }
            } catch (\Throwable $exception) {
                report($exception);
                $failedRecipients[] = $recipient;
            }
        }

        \Illuminate\Support\Facades\Log::info('Accommodation status email processed.', [
            'application_id' => $application->id,
            'status' => $application->status,
            'mailer' => config('mail.default'),
            'submitted_recipients' => $submittedRecipients,
            'failed_recipients' => $failedRecipients,
            'message_ids' => $messageIds,
        ]);

        if ($submittedRecipients === []) {
            return [
                'success' => false,
                'message' => 'Accommodation status updated to ' . $statusLabel . ', but the email could not be handed off to the mail service for ' . $this->formatEmailRecipientList($recipients) . '.',
            ];
        }

        $message = 'Accommodation status updated to ' . $statusLabel . '. Email sent to ' . $this->formatEmailRecipientList($submittedRecipients) . '.';

        if ($messageIds !== []) {
            $message .= ' Reference ID ' . (count($messageIds) === 1 ? 'is ' : 's are ') . $this->formatEmailRecipientList($messageIds) . '.';
        }

        if ($failedRecipients !== []) {
            $message .= ' Delivery attempt failed for ' . $this->formatEmailRecipientList($failedRecipients) . '.';
        }

        return [
            'success' => $failedRecipients === [],
            'message' => $message,
        ];
    }

    protected function accommodationStatusRecipients(AccommodationApplication $application): array
    {
        $recipients = [];

        foreach ([$application->email, optional($application->user)->email] as $email) {
            $email = is_string($email) ? trim($email) : '';

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $recipients[Str::lower($email)] = $email;
        }

        return array_values($recipients);
    }

    protected function formatEmailRecipientList(array $recipients): string
    {
        $recipients = array_values($recipients);
        $count = count($recipients);

        if ($count === 0) {
            return 'no recipients';
        }

        if ($count === 1) {
            return $recipients[0];
        }

        if ($count === 2) {
            return $recipients[0] . ' and ' . $recipients[1];
        }

        $lastRecipient = array_pop($recipients);

        return implode(', ', $recipients) . ', and ' . $lastRecipient;
    }
}
