@extends('layouts.student')

@section('title', 'Psychologist Counselling Desk')
@section('portal_label', 'Psychologist Counselling Desk')

@push('styles')
<style>
    .desk-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.9fr) minmax(260px, 0.9fr);
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .hero-card,
    .summary-card,
    .booking-card {
        background: rgba(255, 255, 255, 0.95);
        color: #13253a;
        border-radius: 24px;
        padding: 1.5rem;
        box-shadow: 0 22px 50px rgba(3, 14, 28, 0.18);
    }

    .hero-card h2,
    .booking-card h4 {
        margin: 0 0 0.6rem;
    }

    .hero-card p,
    .summary-card p,
    .booking-meta,
    .field-note {
        color: #58677b;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-top: 1rem;
    }

    .summary-pill {
        background: #eef6ff;
        border-radius: 18px;
        padding: 0.95rem 1rem;
    }

    .summary-pill strong {
        display: block;
        font-size: 1.45rem;
        color: #0f4fa8;
    }

    .flash-stack {
        display: grid;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .flash-banner {
        border-radius: 16px;
        padding: 0.9rem 1rem;
        font-weight: 600;
    }

    .flash-banner.is-success {
        background: #ddf7e7;
        color: #146c43;
    }

    .flash-banner.is-error {
        background: #fee2e2;
        color: #b91c1c;
    }

    .booked-card {
        background: rgba(255, 255, 255, 0.94);
        color: #13253a;
        border-radius: 24px;
        padding: 1.3rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 22px 50px rgba(3, 14, 28, 0.12);
    }

    .booked-card h4 {
        margin: 0 0 0.35rem;
    }

    .booked-card p {
        margin: 0 0 1rem;
        color: #58677b;
    }

    .booked-slot-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
    }

    .booked-slot {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.7rem 0.9rem;
        border-radius: 16px;
        background: #e5e7eb;
        border: 1px solid #d1d5db;
        color: #4b5563;
        font-size: 0.92rem;
        line-height: 1.35;
    }

    .booked-slot.booked-slot-attended {
        background: #d1d5db;
        border-color: #9ca3af;
    }

    .booked-slot.is-selected {
        background: #9ca3af;
        border-color: #6b7280;
        color: #ffffff;
    }

    .booking-grid {
        display: grid;
        gap: 1rem;
    }

    .booking-topbar {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .booking-topbar strong {
        font-size: 1.05rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.5rem 0.9rem;
        font-weight: 700;
        font-size: 0.84rem;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .status-badge.status-pending {
        background: #fff0cf;
        color: #9a6700;
    }

    .status-badge.status-approved,
    .status-badge.status-scheduled {
        background: #ddf7e7;
        color: #146c43;
    }

    .status-badge.status-completed,
    .status-badge.status-attended {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.status-cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .booking-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem 1rem;
        margin-bottom: 1rem;
    }

    .detail-block {
        background: #f7fafc;
        border-radius: 16px;
        padding: 0.85rem 0.95rem;
    }

    .detail-block strong {
        display: block;
        color: #102033;
        margin-bottom: 0.35rem;
    }

    .booking-form {
        display: grid;
        gap: 1rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .desk-label {
        display: block;
        margin-bottom: 0.4rem;
        font-weight: 700;
        color: #102033;
    }

    .desk-input {
        border-radius: 14px;
        border: 1px solid #d0deef;
        padding: 0.8rem 0.95rem;
    }

    .desk-input.is-booked-choice {
        background: #e5e7eb;
        border-color: #9ca3af;
        color: #374151;
    }

    .booking-conflict {
        display: none;
        border-radius: 14px;
        padding: 0.8rem 0.95rem;
        background: #e5e7eb;
        border: 1px solid #9ca3af;
        color: #374151;
        font-weight: 600;
    }

    .booking-conflict.is-visible {
        display: block;
    }

    .desk-button {
        width: fit-content;
        padding: 0.9rem 1.2rem;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #1d7ef2 0%, #0f4fa8 100%);
        color: #fff;
        font-weight: 700;
        cursor: pointer;
    }

    .empty-state {
        background: rgba(255, 255, 255, 0.94);
        color: #13253a;
        border-radius: 24px;
        padding: 2rem;
        text-align: center;
    }

    .report-shell {
        background: rgba(255, 255, 255, 0.97);
        color: #13253a;
        border-radius: 28px;
        margin-bottom: 1.5rem;
        overflow: hidden;
        box-shadow: 0 24px 54px rgba(3, 14, 28, 0.16);
    }

    .report-browser-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1.35rem;
        background: linear-gradient(135deg, #0f4fa8 0%, #1d7ef2 100%);
        color: #ffffff;
    }

    .report-browser-dots {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .report-browser-dots span {
        width: 0.72rem;
        height: 0.72rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.8);
    }

    .report-browser-title {
        flex: 1;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .report-browser-badge {
        border-radius: 999px;
        padding: 0.38rem 0.82rem;
        background: rgba(255, 255, 255, 0.18);
        font-size: 0.84rem;
        font-weight: 700;
    }

    .report-toolbar,
    .report-filter-actions {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .report-toolbar {
        padding: 1.3rem 1.5rem 0;
    }

    .report-eyebrow {
        display: inline-block;
        margin-bottom: 0.35rem;
        color: #0f4fa8;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .report-heading {
        margin: 0 0 0.45rem;
    }

    .report-subcopy,
    .report-kpi-meta,
    .report-card-copy,
    .report-note {
        color: #58677b;
    }

    .report-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .report-period-chip {
        display: inline-flex;
        flex-direction: column;
        gap: 0.2rem;
        padding: 0.8rem 1rem;
        border-radius: 18px;
        background: #eef6ff;
        border: 1px solid #d7e7ff;
    }

    .report-period-chip span {
        font-size: 0.8rem;
        color: #58677b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .report-board-body {
        padding: 1.5rem;
        display: grid;
        gap: 1.2rem;
    }

    .report-filter-card,
    .report-card {
        background: #ffffff;
        border: 1px solid #e3edf8;
        border-radius: 22px;
        padding: 1.15rem 1.2rem;
    }

    .report-filter-card {
        background: #f8fbff;
    }

    .report-filter-grid,
    .report-kpi-grid,
    .report-table-grid {
        display: grid;
        gap: 1rem;
    }

    .report-filter-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 1rem;
    }

    .report-kpi-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .report-table-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-kpi-card {
        background: #ffffff;
        border: 1px solid #e3edf8;
        border-radius: 20px;
        padding: 1rem 1.05rem;
        box-shadow: 0 12px 28px rgba(15, 79, 168, 0.08);
    }

    .report-kpi-card small {
        display: block;
        margin-bottom: 0.45rem;
        color: #58677b;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .report-kpi-value {
        display: block;
        font-size: 1.8rem;
        line-height: 1.05;
        font-weight: 800;
        color: #102033;
        margin-bottom: 0.35rem;
    }

    .report-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .report-card-header h4 {
        margin: 0 0 0.35rem;
    }

    .report-card-header p {
        margin: 0;
    }

    .report-count-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 3rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: #eef6ff;
        color: #0f4fa8;
        font-weight: 800;
    }

    .report-table-wrap {
        overflow: auto;
    }

    .report-table {
        width: 100%;
        min-width: 620px;
        border-collapse: collapse;
    }

    .report-table th,
    .report-table td {
        padding: 0.82rem 0.75rem;
        border-bottom: 1px solid #e5edf7;
        text-align: left;
        vertical-align: top;
    }

    .report-table th {
        color: #3b4b5f;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #f8fbff;
    }

    .report-table td {
        color: #13253a;
    }

    .report-table td p {
        margin: 0;
    }

    .report-empty {
        border-radius: 18px;
        padding: 1rem;
        background: #f8fbff;
        color: #58677b;
    }

    .desk-button-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .desk-button.is-secondary {
        background: #ffffff;
        color: #0f4fa8;
        border: 1px solid #bfd8ff;
    }

    @media (max-width: 1100px) {
        .report-filter-grid,
        .report-kpi-grid,
        .report-table-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .desk-hero,
        .booking-details,
        .form-grid,
        .report-filter-grid,
        .report-kpi-grid,
        .report-table-grid {
            grid-template-columns: 1fr;
        }

        .booking-topbar,
        .report-browser-bar {
            flex-direction: column;
            align-items: flex-start;
        }

        .report-toolbar,
        .report-filter-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
@php
    $normalizeStatus = static function (?string $status): string {
        return match (strtolower((string) $status)) {
            'approved' => 'scheduled',
            'completed' => 'attended',
            default => strtolower((string) $status),
        };
    };

    $pendingBookings = $bookings->filter(fn ($booking) => $normalizeStatus($booking->status) === 'pending')->count();
    $scheduledBookings = $bookings->filter(fn ($booking) => $normalizeStatus($booking->status) === 'scheduled')->count();
    $attendedBookings = $bookings->filter(fn ($booking) => $normalizeStatus($booking->status) === 'attended')->count();
    $statusOptions = [
        'pending' => 'Pending',
        'scheduled' => 'Scheduled',
        'attended' => 'Attended',
        'cancelled' => 'Cancelled',
    ];
    $bookedSlots = $bookings
        ->filter(function ($booking) use ($normalizeStatus) {
            return $booking->appointment_date && in_array($normalizeStatus($booking->status), ['scheduled', 'attended'], true);
        })
        ->sortBy('appointment_date')
        ->map(function ($booking) use ($normalizeStatus) {
            return [
                'booking_id' => (string) $booking->id,
                'status' => $normalizeStatus($booking->status),
                'value' => $booking->appointment_date->format('Y-m-d\TH:i'),
                'display' => $booking->appointment_date->format('d-m-Y h:i A'),
                'student' => $booking->student_name,
            ];
        })
        ->values();
    $activeBookingId = (string) old('active_booking');
    $reportBookings = $reportBookings ?? collect();
    $reportPendingSessions = $reportBookings
        ->filter(fn ($booking) => $normalizeStatus($booking->status) === 'pending')
        ->sortBy(fn ($booking) => optional($booking->preferred_date ?? $booking->created_at)->timestamp ?? PHP_INT_MAX)
        ->values();
    $reportScheduledSessions = $reportBookings
        ->filter(fn ($booking) => $normalizeStatus($booking->status) === 'scheduled')
        ->sortBy(fn ($booking) => optional($booking->appointment_date ?? $booking->created_at)->timestamp ?? PHP_INT_MAX)
        ->values();
    $reportAttendedSessions = $reportBookings
        ->filter(fn ($booking) => $normalizeStatus($booking->status) === 'attended')
        ->sortByDesc(fn ($booking) => optional($booking->appointment_date ?? $booking->updated_at ?? $booking->created_at)->timestamp ?? 0)
        ->values();
    $reportCancelledSessions = $reportBookings
        ->filter(fn ($booking) => $normalizeStatus($booking->status) === 'cancelled')
        ->values();
    $reportScopeNote = ($reportType ?? 'general') === 'general'
        ? 'All counselling requests are included in this report.'
        : 'This report includes bookings created or scheduled within ' . ($reportLabel ?? 'the selected period') . '.';
@endphp

@if (session('success') || $errors->any())
    <div class="flash-stack">
        @if (session('success'))
            <div class="flash-banner is-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash-banner is-error">{{ $errors->first() }}</div>
        @endif
    </div>
@endif

<div class="desk-hero">
    <section class="hero-card">
        <h2>Manage Counselling Appointments</h2>
        <p>Review student counselling requests, schedule date and time as needed, and mark each session as attended after it happens. Grey booking slots below already belong to another student.</p>
    </section>

    <aside class="summary-card">
        <h4>Today's Overview</h4>
        <p>Keep an eye on the requests that still need attention and the sessions already scheduled or attended.</p>

        <div class="summary-grid">
            <div class="summary-pill">
                <span>Total Requests</span>
                <strong>{{ $bookings->count() }}</strong>
            </div>
            <div class="summary-pill">
                <span>Pending</span>
                <strong>{{ $pendingBookings }}</strong>
            </div>
            <div class="summary-pill">
                <span>Scheduled</span>
                <strong>{{ $scheduledBookings }}</strong>
            </div>
            <div class="summary-pill">
                <span>Attended</span>
                <strong>{{ $attendedBookings }}</strong>
            </div>
        </div>
    </aside>
</div>

<section class="report-shell">
    <div class="report-browser-bar">
        <div class="report-browser-dots" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="report-browser-title">SolidCare analytics / counselling-report</div>
        <div class="report-browser-badge">Psychologist</div>
    </div>

    <div class="report-toolbar">
        <div>
            <span class="report-eyebrow">Counselling Reporting Workspace</span>
            <h3 class="report-heading">Student Attendance and Pending Sessions</h3>
            <p class="report-subcopy mb-0">{{ $reportScopeNote }}</p>
        </div>

        <div class="report-actions">
            <div class="report-period-chip">
                <span>Selected Period</span>
                <strong>{{ $reportLabel ?? 'General Report (All Records)' }}</strong>
            </div>
            <a
                href="{{ route('counselling.report.download', ['report_type' => $reportType ?? 'general', 'report_semester' => $reportSemester ?? 1, 'report_month' => $reportMonth ?? now()->month, 'report_year' => $reportYear ?? now()->year]) }}"
                class="desk-button desk-button-link"
            >
                Download CSV
            </a>
        </div>
    </div>

    <div class="report-board-body">
        <form method="GET" action="{{ route('counselling') }}" class="report-filter-card">
            <div class="report-filter-grid">
                <div>
                    <label class="desk-label" for="report-type-select">Report Type</label>
                    <select name="report_type" id="report-type-select" class="form-select desk-input" required>
                        <option value="general" @selected(($reportType ?? 'general') === 'general')>General Report</option>
                        <option value="semester" @selected(($reportType ?? 'general') === 'semester')>Semester</option>
                        <option value="month" @selected(($reportType ?? 'general') === 'month')>Month</option>
                        <option value="year" @selected(($reportType ?? 'general') === 'year')>Year</option>
                    </select>
                </div>

                <div class="report-semester-fields">
                    <label class="desk-label">Semester</label>
                    <select name="report_semester" class="form-select desk-input">
                        <option value="1" @selected(($reportSemester ?? 1) === 1)>Semester 1</option>
                        <option value="2" @selected(($reportSemester ?? 1) === 2)>Semester 2</option>
                    </select>
                </div>

                <div class="report-month-fields">
                    <label class="desk-label">Month</label>
                    <select name="report_month" class="form-select desk-input">
                        @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}" @selected(($reportMonth ?? now()->month) === $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="desk-label">Year</label>
                    <input type="number" name="report_year" class="form-control desk-input" min="2000" max="2100" value="{{ $reportYear ?? now()->year }}" required>
                </div>
            </div>

            <div class="report-filter-actions">
                <button type="submit" class="desk-button">Generate Report</button>
                <a href="{{ route('counselling') }}" class="desk-button desk-button-link is-secondary">Reset Filters</a>
            </div>
        </form>

        <div class="report-kpi-grid">
            <article class="report-kpi-card">
                <small>Total Requests in Scope</small>
                <span class="report-kpi-value">{{ number_format($reportBookings->count()) }}</span>
                <p class="report-kpi-meta mb-0">Cancelled: {{ number_format($reportCancelledSessions->count()) }}</p>
            </article>

            <article class="report-kpi-card">
                <small>Students Seen</small>
                <span class="report-kpi-value">{{ number_format($reportAttendedSessions->count()) }}</span>
                <p class="report-kpi-meta mb-0">Students who have already gone for counselling.</p>
            </article>

            <article class="report-kpi-card">
                <small>Pending Sessions</small>
                <span class="report-kpi-value">{{ number_format($reportPendingSessions->count()) }}</span>
                <p class="report-kpi-meta mb-0">Requests still waiting for review or scheduling.</p>
            </article>

            <article class="report-kpi-card">
                <small>Scheduled Sessions</small>
                <span class="report-kpi-value">{{ number_format($reportScheduledSessions->count()) }}</span>
                <p class="report-kpi-meta mb-0">Appointments already placed on the counselling calendar.</p>
            </article>
        </div>

        <div class="report-table-grid">
            <article class="report-card">
                <div class="report-card-header">
                    <div>
                        <h4>Students Who Have Gone for Counselling</h4>
                        <p class="report-card-copy">Attended sessions captured in the selected report window.</p>
                    </div>
                    <span class="report-count-chip">{{ number_format($reportAttendedSessions->count()) }}</span>
                </div>

                @if ($reportAttendedSessions->isEmpty())
                    <div class="report-empty">No attended counselling sessions were found for this report period.</div>
                @else
                    <div class="report-table-wrap">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Programme</th>
                                    <th>Session Date</th>
                                    <th>Requested On</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportAttendedSessions as $booking)
                                    <tr>
                                        <td>
                                            <strong>{{ $booking->student_name }}</strong><br>
                                            <span class="report-note">ID {{ $booking->student_identity_number ?: '-' }}</span>
                                        </td>
                                        <td>
                                            {{ $booking->programme ?: '-' }}<br>
                                            <span class="report-note">Year {{ $booking->year_of_study ?: '-' }}</span>
                                        </td>
                                        <td>{{ $booking->appointment_date ? $booking->appointment_date->format('d-m-Y h:i A') : '-' }}</td>
                                        <td>{{ $booking->created_at ? $booking->created_at->format('d-m-Y h:i A') : '-' }}</td>
                                        <td>{{ $booking->counsellor_notes ?: 'No notes recorded.' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>

            <article class="report-card">
                <div class="report-card-header">
                    <div>
                        <h4>Pending Sessions</h4>
                        <p class="report-card-copy">Requests still waiting for action in the selected report window.</p>
                    </div>
                    <span class="report-count-chip">{{ number_format($reportPendingSessions->count()) }}</span>
                </div>

                @if ($reportPendingSessions->isEmpty())
                    <div class="report-empty">No pending counselling sessions were found for this report period.</div>
                @else
                    <div class="report-table-wrap">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Preferred Session</th>
                                    <th>Requested On</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportPendingSessions as $booking)
                                    <tr>
                                        <td>
                                            <strong>{{ $booking->student_name }}</strong><br>
                                            <span class="report-note">{{ $booking->programme ?: '-' }} / Year {{ $booking->year_of_study ?: '-' }}</span>
                                        </td>
                                        <td>
                                            {{ $booking->preferred_date ? $booking->preferred_date->format('d-m-Y') : '-' }}
                                            @if ($booking->preferred_time)
                                                <br><span class="report-note">at {{ $booking->preferred_time }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $booking->created_at ? $booking->created_at->format('d-m-Y h:i A') : '-' }}</td>
                                        <td>{{ $booking->reason }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </div>
    </div>
</section>

<section class="booked-card">
    <h4>Booked Date and Time</h4>
    <p>These grey slots are already taken. If you choose one of them in a form, the selected field turns grey and the booking cannot be saved until you pick a free date and time.</p>

    @if ($bookedSlots->isNotEmpty())
        <div class="booked-slot-grid" id="booked-slot-grid">
            @foreach ($bookedSlots as $slot)
                <span
                    class="booked-slot booked-slot-{{ $slot['status'] }}"
                    data-booking-id="{{ $slot['booking_id'] }}"
                    data-slot-value="{{ $slot['value'] }}"
                >
                    {{ $slot['display'] }} &bull; {{ $slot['student'] }} &bull; {{ ucfirst($slot['status']) }}
                </span>
            @endforeach
        </div>
    @else
        <p class="mb-0">No appointment date and time has been booked yet.</p>
    @endif
</section>

@if ($bookings->isEmpty())
    <section class="empty-state">
        <h4>No counselling requests yet</h4>
        <p class="mb-0">New student bookings will appear here as soon as they are submitted.</p>
    </section>
@else
    <div class="booking-grid">
        @foreach ($bookings as $booking)
            @php
                $statusKey = $normalizeStatus($booking->status);
                $isActiveBooking = $activeBookingId !== '' && $activeBookingId === (string) $booking->id;
                $appointmentDateValue = $isActiveBooking
                    ? old('appointment_date')
                    : optional($booking->appointment_date)->format('Y-m-d\TH:i');
                $statusValue = $isActiveBooking ? old('status', $statusKey) : $statusKey;
                $notesValue = $isActiveBooking ? old('counsellor_notes', $booking->counsellor_notes) : $booking->counsellor_notes;
            @endphp

            <section class="booking-card">
                <div class="booking-topbar">
                    <div>
                        <strong>{{ $booking->student_name }}</strong>
                        <div class="booking-meta">
                            ID {{ $booking->student_identity_number ?: '-' }}
                            @if ($booking->user?->email)
                                &bull; {{ $booking->user->email }}
                            @endif
                        </div>
                    </div>

                    <span class="status-badge status-{{ $statusKey }}">
                        {{ ucfirst($statusKey) }}
                    </span>
                </div>

                <div class="booking-details">
                    <div class="detail-block">
                        <strong>Sex</strong>
                        <span>{{ $booking->sex ?: '-' }}</span>
                    </div>
                    <div class="detail-block">
                        <strong>Program</strong>
                        <span>{{ $booking->programme ?: '-' }}</span>
                    </div>
                    <div class="detail-block">
                        <strong>Year of Study</strong>
                        <span>{{ $booking->year_of_study ?: '-' }}</span>
                    </div>
                    <div class="detail-block">
                        <strong>Cause</strong>
                        <span>{{ $booking->reason }}</span>
                    </div>
                    <div class="detail-block">
                        <strong>Preferred Session</strong>
                        <span>
                            {{ $booking->preferred_date ? $booking->preferred_date->format('d-m-Y') : '-' }}
                            @if ($booking->preferred_time)
                                at {{ $booking->preferred_time }}
                            @endif
                        </span>
                    </div>
                    <div class="detail-block">
                        <strong>Requested On</strong>
                        <span>{{ $booking->created_at ? $booking->created_at->format('d-m-Y h:i A') : '-' }}</span>
                    </div>
                    <div class="detail-block">
                        <strong>Scheduled Date and Time</strong>
                        <span>{{ $booking->appointment_date ? $booking->appointment_date->format('d-m-Y h:i A') : 'Not scheduled yet' }}</span>
                    </div>
                </div>

                <form action="{{ route('counselling.bookings.update', $booking) }}" method="POST" class="booking-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="active_booking" value="{{ $booking->id }}">

                    <div class="form-grid">
                        <div>
                            <label for="status-{{ $booking->id }}" class="desk-label">Appointment status</label>
                            <select name="status" id="status-{{ $booking->id }}" class="form-select desk-input" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $statusValue === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="appointment-date-{{ $booking->id }}" class="desk-label">Appointment date and time</label>
                            <input
                                type="datetime-local"
                                name="appointment_date"
                                id="appointment-date-{{ $booking->id }}"
                                class="form-control desk-input js-appointment-input"
                                value="{{ $appointmentDateValue }}"
                                data-booking-id="{{ $booking->id }}"
                                data-conflict-target="booking-conflict-{{ $booking->id }}"
                            >
                            <div class="field-note mt-2">Set the exact appointment date and time here. If the chosen slot is already booked, it will turn grey and cannot be saved.</div>
                            <div class="booking-conflict mt-2" id="booking-conflict-{{ $booking->id }}">
                                This date and time is already booked. Choose another slot.
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes-{{ $booking->id }}" class="desk-label">Counsellor notes</label>
                        <textarea
                            name="counsellor_notes"
                            id="notes-{{ $booking->id }}"
                            class="form-control desk-input"
                            rows="4"
                            maxlength="1000"
                            placeholder="Add session notes, instructions, or follow-up details for the student."
                        >{{ $notesValue }}</textarea>
                    </div>

                    <button type="submit" class="desk-button">Save Appointment</button>
                </form>
            </section>
        @endforeach
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const reportTypeSelect = document.getElementById('report-type-select');
        const reportSemesterFields = document.querySelectorAll('.report-semester-fields');
        const reportMonthFields = document.querySelectorAll('.report-month-fields');
        const slotChips = Array.from(document.querySelectorAll('.booked-slot[data-slot-value]'));
        const appointmentInputs = document.querySelectorAll('.js-appointment-input');

        const normalizeValue = function (value) {
            return (value || '').slice(0, 16);
        };

        const updateReportFieldVisibility = function () {
            if (!reportTypeSelect) {
                return;
            }

            const type = reportTypeSelect.value;
            const showSemester = type === 'semester';
            const showMonth = type === 'month';

            reportSemesterFields.forEach(function (element) {
                element.style.display = showSemester ? '' : 'none';
            });

            reportMonthFields.forEach(function (element) {
                element.style.display = showMonth ? '' : 'none';
            });
        };

        if (reportTypeSelect) {
            reportTypeSelect.addEventListener('change', updateReportFieldVisibility);
            updateReportFieldVisibility();
        }

        appointmentInputs.forEach(function (input) {
            const bookingId = input.dataset.bookingId;
            const conflictTarget = document.getElementById(input.dataset.conflictTarget);

            const syncConflictState = function () {
                const selectedValue = normalizeValue(input.value);
                let hasConflict = false;

                slotChips.forEach(function (chip) {
                    if (chip.dataset.selectedFor === bookingId) {
                        chip.classList.remove('is-selected');
                        chip.dataset.selectedFor = '';
                    }

                    if (selectedValue !== '' && chip.dataset.bookingId !== bookingId && chip.dataset.slotValue === selectedValue) {
                        chip.classList.add('is-selected');
                        chip.dataset.selectedFor = bookingId;
                        hasConflict = true;
                    }
                });

                input.classList.toggle('is-booked-choice', hasConflict);
                input.setCustomValidity(hasConflict ? 'This date and time is already booked.' : '');

                if (conflictTarget) {
                    conflictTarget.classList.toggle('is-visible', hasConflict);
                }
            };

            input.addEventListener('input', syncConflictState);
            input.addEventListener('change', syncConflictState);
            syncConflictState();
        });
    });
</script>
@endsection
