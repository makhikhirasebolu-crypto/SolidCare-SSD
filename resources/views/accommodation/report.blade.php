<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Accommodation Report - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            :root {
                --page-bg: #eef3f8;
                --surface: #ffffff;
                --surface-soft: #f5f8fc;
                --ink: #102033;
                --muted: #64748b;
                --line: #dbe3ee;
                --brand: #0f766e;
                --brand-deep: #164e63;
                --blue: #2563eb;
                --amber: #b45309;
                --rose: #be123c;
                --green: #15803d;
            }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background:
                    linear-gradient(180deg, #0f172a 0, #143247 285px, var(--page-bg) 285px, var(--page-bg) 100%);
                color: var(--ink);
                min-height: 100vh;
            }
            .report-shell {
                max-width: 1360px;
                margin: 0 auto;
                padding: 1.25rem 1rem 2.5rem;
            }
            .report-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.1rem;
                color: #dbeafe;
            }
            .report-brand {
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                font-weight: 800;
            }
            .report-brand-mark {
                display: inline-grid;
                width: 2.35rem;
                height: 2.35rem;
                place-items: center;
                border-radius: 0.5rem;
                background: #f8fafc;
                color: var(--brand-deep);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
            }
            .report-hero {
                display: flex;
                justify-content: space-between;
                gap: 1.5rem;
                align-items: stretch;
                flex-wrap: wrap;
                padding: 1.35rem;
                margin-bottom: 1rem;
                border: 1px solid rgba(226, 232, 240, 0.2);
                border-radius: 0.5rem;
                background: rgba(255, 255, 255, 0.08);
                box-shadow: 0 24px 60px rgba(2, 6, 23, 0.2);
            }
            .report-hero h1 {
                margin: 0.35rem 0 0.6rem;
                font-size: clamp(2rem, 4vw, 3.1rem);
                color: #f8fafc;
                font-weight: 800;
            }
            .report-eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.34rem 0.72rem;
                border-radius: 999px;
                background: rgba(20, 184, 166, 0.15);
                color: #99f6e4;
                border: 1px solid rgba(153, 246, 228, 0.24);
                font-size: 0.82rem;
                font-weight: 700;
                text-transform: uppercase;
            }
            .report-hero p {
                max-width: 760px;
                margin: 0;
                color: #cbd5e1;
            }
            .report-actions {
                display: flex;
                gap: 0.85rem;
                align-items: center;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .report-chip {
                min-width: 220px;
                padding: 0.95rem;
                border-radius: 0.5rem;
                background: rgba(15, 23, 42, 0.38);
                border: 1px solid rgba(226, 232, 240, 0.18);
                color: #e2e8f0;
            }
            .report-chip span {
                display: block;
                font-size: 0.78rem;
                text-transform: uppercase;
                color: #94a3b8;
                margin-bottom: 0.25rem;
            }
            .report-chip strong {
                font-size: 1rem;
            }
            .report-chip-form {
                min-width: 260px;
            }
            .report-chip-form form {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            .report-chip-select {
                border-radius: 0.55rem;
                border: 1px solid rgba(148, 163, 184, 0.24);
                background: rgba(248, 250, 252, 0.96);
                color: var(--ink);
                font-weight: 700;
            }
            .report-chip-submit {
                width: 100%;
                border-radius: 0.55rem;
                font-weight: 700;
            }
            .report-button {
                border-radius: 0.6rem;
                padding: 0.8rem 1.05rem;
                font-weight: 700;
            }
            .report-panel {
                background: var(--surface);
                color: var(--ink);
                border-radius: 0.5rem;
                border: 1px solid var(--line);
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.09);
            }
            .report-kpis {
                display: grid;
                grid-template-columns: repeat(6, minmax(0, 1fr));
                gap: 0.9rem;
                margin-bottom: 1rem;
            }
            .report-kpi {
                position: relative;
                overflow: hidden;
                padding: 1rem;
            }
            .report-kpi::before {
                content: "";
                position: absolute;
                inset: 0 auto 0 0;
                width: 0.32rem;
                background: var(--card-accent, var(--brand));
            }
            .report-kpi small {
                display: block;
                text-transform: uppercase;
                color: var(--muted);
                font-weight: 700;
                margin-bottom: 0.55rem;
            }
            .report-kpi strong {
                display: block;
                font-size: 2rem;
                line-height: 1;
                margin-bottom: 0.45rem;
            }
            .report-kpi p {
                margin: 0;
                color: var(--muted);
                font-size: 0.92rem;
            }
            .report-grid {
                display: grid;
                grid-template-columns: minmax(280px, 0.75fr) minmax(0, 1.25fr);
                gap: 1rem;
                align-items: start;
                margin-bottom: 1rem;
            }
            .report-section {
                padding: 1.1rem;
                margin-bottom: 1rem;
            }
            .report-section-header {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                align-items: flex-start;
                margin-bottom: 0.95rem;
            }
            .report-section h2,
            .report-section h3 {
                margin: 0 0 0.35rem;
                font-size: 1.25rem;
            }
            .report-section p {
                margin: 0;
                color: var(--muted);
            }
            .report-note {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                white-space: nowrap;
                padding: 0.4rem 0.6rem;
                border-radius: 999px;
                background: #eff6ff;
                color: #1d4ed8;
                font-size: 0.82rem;
                font-weight: 800;
            }
            .status-summary {
                display: grid;
                gap: 0.65rem;
            }
            .status-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.75rem;
                border: 1px solid var(--line);
                border-radius: 0.5rem;
                background: var(--surface-soft);
            }
            .status-count {
                min-width: 2.25rem;
                text-align: center;
                padding: 0.35rem 0.55rem;
                border-radius: 0.55rem;
                background: #fff;
                color: var(--ink);
                font-weight: 800;
                box-shadow: inset 0 0 0 1px var(--line);
            }
            .status-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.34rem 0.62rem;
                border-radius: 999px;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
            }
            .status-badge.pending {
                background: #e0f2fe;
                color: #0369a1;
            }
            .status-badge.admitted {
                background: #dcfce7;
                color: #166534;
            }
            .status-badge.checkout_rejected {
                background: #ffe4e6;
                color: #be123c;
            }
            .status-badge.conditional {
                background: #ffedd5;
                color: #9a3412;
            }
            .status-badge.rejected,
            .status-badge.checked_out {
                background: #fee2e2;
                color: #991b1b;
            }
            .status-badge.checkout_requested {
                background: #fef3c7;
                color: #92400e;
            }
            .table-wrap {
                overflow-x: auto;
                border: 1px solid var(--line);
                border-radius: 0.5rem;
            }
            .report-table {
                width: 100%;
                border-collapse: collapse;
                background: #fff;
            }
            .report-table th,
            .report-table td {
                padding: 0.78rem 0.85rem;
                border-bottom: 1px solid #e8eef6;
                vertical-align: top;
                text-align: left;
                white-space: nowrap;
            }
            .report-table th {
                position: sticky;
                top: 0;
                z-index: 1;
                background: #f8fafc;
                font-size: 0.8rem;
                text-transform: uppercase;
                color: var(--muted);
            }
            .report-table tbody tr:hover {
                background: #f8fbff;
            }
            .report-table tbody tr:last-child td {
                border-bottom: 0;
            }
            .report-table .cell-person {
                min-width: 210px;
                white-space: normal;
            }
            .report-table .cell-wide {
                min-width: 230px;
                white-space: normal;
            }
            .report-table .cell-note {
                max-width: 280px;
                white-space: normal;
            }
            .subtle-text {
                color: var(--muted);
                font-size: 0.9rem;
            }
            .room-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 0.75rem;
            }
            .room-card {
                padding: 0.85rem;
                border: 1px solid var(--line);
                border-radius: 0.5rem;
                background: #fff;
            }
            .room-card-head {
                display: flex;
                justify-content: space-between;
                gap: 0.5rem;
                align-items: center;
                margin-bottom: 0.65rem;
                font-weight: 800;
            }
            .room-meter {
                height: 0.45rem;
                overflow: hidden;
                border-radius: 999px;
                background: #e2e8f0;
                margin-bottom: 0.65rem;
            }
            .room-meter span {
                display: block;
                height: 100%;
                width: var(--occupied-width);
                background: var(--room-color, var(--brand));
            }
            .room-meta {
                display: flex;
                justify-content: space-between;
                gap: 0.5rem;
                color: var(--muted);
                font-size: 0.88rem;
            }
            .empty-state {
                padding: 3rem 1.5rem;
                text-align: center;
            }
            .empty-state h2 {
                color: #f8fafc;
                margin-bottom: 0.65rem;
            }
            .empty-state p {
                margin: 0;
                color: #cbd5e1;
            }
            .report-close-bar {
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
                padding: 1rem;
                margin-top: 1rem;
                background: #fff;
                border: 1px solid var(--line);
                border-radius: 0.5rem;
                box-shadow: 0 14px 35px rgba(15, 23, 42, 0.08);
            }
            .report-close-button {
                min-width: 170px;
                border-radius: 0.5rem;
                padding: 0.82rem 1.1rem;
                font-weight: 800;
            }
            footer {
                text-align: center;
                color: #cbd5e1;
                padding: 1rem 0 0;
            }
            @media (max-width: 1200px) {
                .report-kpis {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
                .report-grid {
                    grid-template-columns: 1fr;
                }
            }
            @media (max-width: 768px) {
                .report-kpis {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
                .report-actions {
                    width: 100%;
                    justify-content: stretch;
                }
                .report-chip,
                .report-button {
                    width: 100%;
                }
                .report-close-bar {
                    justify-content: stretch;
                }
                .report-close-button {
                    width: 100%;
                }
            }
            @media (max-width: 576px) {
                .report-kpis {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-shell">
            <div class="report-topbar">
                <div class="report-brand">
                    <span class="report-brand-mark">SC</span>
                    <span>SolidCare SSD</span>
                </div>
                <span>{{ now()->format('M j, Y') }}</span>
            </div>

            <div class="report-hero">
                <div>
                    <span class="report-eyebrow">Accommodation Reporting Workspace</span>
                    <h1>Accommodation Intake Report</h1>
                    <p>The latest year opens automatically. You can change the year and generate report details based on the selected year.</p>
                </div>

                <div class="report-actions">
                    <div class="report-chip report-chip-form">
                        <span>Selected Year</span>
                        @if ($availableYears->isNotEmpty())
                            <form method="GET" action="{{ route('accommodation.report') }}">
                                <select name="year" class="form-select report-chip-select">
                                    @foreach ($availableYears as $year)
                                        <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-light report-chip-submit">Generate Report</button>
                            </form>
                        @else
                            <strong>Not available yet</strong>
                        @endif
                    </div>
                    <div class="report-chip">
                        <span>Latest Intake In Year</span>
                        <strong>{{ $currentIntake ?: 'Not available yet' }}</strong>
                    </div>
                    <a
                        href="{{ $selectedYear ? route('accommodation.report.download', ['year' => $selectedYear]) : '#' }}"
                        class="btn btn-light report-button {{ $selectedYear ? '' : 'disabled' }}"
                        @if (! $selectedYear) aria-disabled="true" @endif
                    >
                        Download CSV
                    </a>
                    <a href="{{ route('accommodation') }}" class="btn btn-outline-light report-button">Done</a>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
            @endif

            @if (! $selectedYear)
                <div class="empty-state">
                    <h2>No Yearly Intake Report</h2>
                    <p>Accommodation reports will appear here once at least one application has a saved intake value.</p>
                </div>
            @else
                <div class="report-kpis">
                    <article class="report-panel report-kpi" style="--card-accent: var(--blue);">
                        <small>Total Applications</small>
                        <strong>{{ number_format($applications->count()) }}</strong>
                        <p>Applications recorded for {{ $selectedYear }}.</p>
                    </article>
                    <article class="report-panel report-kpi" style="--card-accent: var(--amber);">
                        <small>Pending Admissions</small>
                        <strong>{{ number_format($pendingApplications->count()) }}</strong>
                        <p>Students still waiting for an admission decision.</p>
                    </article>
                    <article class="report-panel report-kpi" style="--card-accent: var(--green);">
                        <small>Active Residents</small>
                        <strong>{{ number_format($activeResidents->count()) }}</strong>
                        <p>Students in residence or still occupying a room.</p>
                    </article>
                    <article class="report-panel report-kpi" style="--card-accent: var(--rose);">
                        <small>Checkout Requests</small>
                        <strong>{{ number_format($checkoutRequests->count()) }}</strong>
                        <p>Students in {{ $selectedYear }} requesting checkout approval.</p>
                    </article>
                    <article class="report-panel report-kpi" style="--card-accent: var(--brand);">
                        <small>Available Rooms</small>
                        <strong>{{ number_format($availableRoomsCount) }}</strong>
                        <p>Rooms with at least one free bed right now.</p>
                    </article>
                    <article class="report-panel report-kpi" style="--card-accent: var(--brand-deep);">
                        <small>Available Beds</small>
                        <strong>{{ number_format($availableBedsCount) }}</strong>
                        <p>{{ number_format($occupiedBedsCount) }} of {{ number_format($totalCapacity) }} beds are occupied.</p>
                    </article>
                </div>

                <div class="report-grid">
                    <section class="report-panel report-section">
                        <div class="report-section-header">
                            <div>
                                <h2>Status Breakdown</h2>
                                <p>Application status distribution for {{ $selectedYear }}.</p>
                            </div>
                            <span class="report-note">{{ number_format($applications->count()) }} records</span>
                        </div>
                        <div class="status-summary">
                            @forelse ($statusSummary as $status => $count)
                                <div class="status-row">
                                    <span class="status-badge {{ \Illuminate\Support\Str::slug(strtolower($status), '_') }}">
                                        {{ $status }}
                                    </span>
                                    <span class="status-count">{{ number_format($count) }}</span>
                                </div>
                            @empty
                                <div class="status-row">
                                    <span>No accommodation applications found for this year.</span>
                                    <span class="status-count">0</span>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="report-panel report-section">
                        <div class="report-section-header">
                            <div>
                                <h2>Room Availability Snapshot</h2>
                                <p>Live room occupancy across the accommodation blocks.</p>
                            </div>
                            <span class="report-note">{{ number_format($rooms->count()) }} rooms</span>
                        </div>
                        <div class="room-grid">
                            @forelse ($rooms as $room)
                                @php
                                    $availableBeds = max(0, $room->capacity - $room->occupied_beds);
                                    $occupiedPercent = $room->capacity > 0 ? min(100, round(($room->occupied_beds / $room->capacity) * 100)) : 0;
                                    $isFull = $room->occupied_beds >= $room->capacity;
                                @endphp
                                <article class="room-card">
                                    <div class="room-card-head">
                                        <span>{{ $room->block_name }}-{{ str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="status-badge {{ $isFull ? 'rejected' : 'admitted' }}">{{ $isFull ? 'Full' : 'Available' }}</span>
                                    </div>
                                    <div class="room-meter" style="--room-color: {{ $isFull ? 'var(--rose)' : 'var(--brand)' }};">
                                        <span style="--occupied-width: {{ $occupiedPercent }}%;"></span>
                                    </div>
                                    <div class="room-meta">
                                        <span>{{ number_format($room->occupied_beds) }} occupied</span>
                                        <span>{{ number_format($availableBeds) }} free</span>
                                    </div>
                                </article>
                            @empty
                                <p>No rooms available in the system.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <section class="report-panel report-section">
                    <div class="report-section-header">
                        <div>
                            <h2>Applications In This Year</h2>
                            <p>All accommodation applications currently recorded for {{ $selectedYear }}.</p>
                        </div>
                        <span class="report-note">{{ number_format($applications->count()) }} applications</span>
                    </div>
                    <div class="table-wrap">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Student ID</th>
                                    <th>Faculty</th>
                                    <th>Programme</th>
                                    <th>Status</th>
                                    <th>Current Room</th>
                                    <th>Admission Done By</th>
                                    <th>Previous Room</th>
                                    <th>Requested Room</th>
                                    <th>Reallocation</th>
                                    <th>Accepted By</th>
                                    <th>Reallocated By</th>
                                    <th>Why</th>
                                    <th>Check-In</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($applications as $application)
                                    <tr>
                                        <td class="cell-person">
                                            <strong>{{ $application->full_name }}</strong><br>
                                            <span class="subtle-text">{{ $application->email }}</span>
                                        </td>
                                        <td>{{ $application->student_id ?: optional($application->user)->student_id ?: 'Not recorded' }}</td>
                                        <td class="cell-wide">{{ $application->faculty ?: 'Not recorded' }}</td>
                                        <td class="cell-wide">{{ $application->programme ?: 'Not recorded' }}</td>
                                        <td>
                                            <span class="status-badge {{ $application->status }}">
                                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($application->room)->block_name ? optional($application->room)->block_name . '-' . str_pad((string) $application->room->room_number, 2, '0', STR_PAD_LEFT) : 'Not assigned' }}</td>
                                        <td>{{ $application->admissionProcessedBy ? (($application->admissionProcessedBy->name ?: $application->admissionProcessedBy->email) . ' (' . \Illuminate\Support\Str::headline($application->admissionProcessedBy->role) . ')') : 'Not recorded' }}</td>
                                        <td>{{ optional($application->previousRoom)->block_name ? optional($application->previousRoom)->block_name . '-' . str_pad((string) $application->previousRoom->room_number, 2, '0', STR_PAD_LEFT) : 'Not recorded' }}</td>
                                        <td>{{ optional($application->requestedRoom)->block_name ? optional($application->requestedRoom)->block_name . '-' . str_pad((string) $application->requestedRoom->room_number, 2, '0', STR_PAD_LEFT) : 'Not selected' }}</td>
                                        <td>{{ $application->reallocation_status ? ucfirst(str_replace('_', ' ', $application->reallocation_status)) : 'No reallocation' }}</td>
                                        <td>{{ $application->reallocationApprovedBy ? (($application->reallocationApprovedBy->name ?: $application->reallocationApprovedBy->email) . ' (' . \Illuminate\Support\Str::headline($application->reallocationApprovedBy->role) . ')') : 'Not recorded' }}</td>
                                        <td>{{ $application->roomReallocatedBy ? (($application->roomReallocatedBy->name ?: $application->roomReallocatedBy->email) . ' (' . \Illuminate\Support\Str::headline($application->roomReallocatedBy->role) . ')') : 'Not recorded' }}</td>
                                        <td class="cell-note">{{ $application->reallocation_reason ?: 'Not provided' }}</td>
                                        <td>{{ optional($application->check_in_date)->format('M j, Y') ?: 'Not set' }}</td>
                                        <td>{{ optional($application->updated_at)->format('M j, Y g:i A') ?: optional($application->created_at)->format('M j, Y g:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15">No accommodation applications found for this year.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <div class="report-close-bar">
                <a href="{{ route('accommodation') }}" class="btn btn-primary report-close-button">Done</a>
            </div>

            <footer>&copy; 2026 SolidCare SSD. All rights reserved.</footer>
        </div>
    </body>
</html>
