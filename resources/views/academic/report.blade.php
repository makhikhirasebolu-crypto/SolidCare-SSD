{{-- resources/views/academic/report.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Referrals Report | SolidCare SSD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --report-primary: #1e3a5f;
            --report-secondary: #2c5282;
            --report-accent: #3182ce;
            --report-violet: #805ad5;
            --report-success: #38a169;
            --report-warning: #d69e2e;
            --report-danger: #e53e3e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0f4f8;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            padding: 1.5rem;
        }
        .report-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .report-header {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .report-browser-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .report-browser-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .report-browser-dot:nth-child(1) { background: #ff5f57; }
        .report-browser-dot:nth-child(2) { background: #febc2e; }
        .report-browser-dot:nth-child(3) { background: #28c840; }
        .report-browser-title {
            margin-left: auto;
            font-size: 0.8rem;
            color: #64748b;
        }
        .report-browser-badge {
            font-size: 0.7rem;
            background: #eef2ff;
            color: #2c5282;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .report-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .report-eyebrow {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .report-heading {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f2b4d;
            margin: 0.25rem 0;
        }
        .report-subcopy {
            color: #64748b;
            font-size: 0.85rem;
            line-height: 1.6;
            max-width: 600px;
        }
        .report-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .report-period-chip {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .report-period-chip span {
            font-size: 0.65rem;
            text-transform: uppercase;
            color: #64748b;
        }
        .report-period-chip strong {
            font-size: 0.85rem;
            color: #1e293b;
        }
        .btn-custom {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-light { background: white; border: 1px solid #e2e8f0; color: #1e293b; }
        .btn-outline-primary { background: transparent; border: 1px solid #2c5282; color: #2c5282; }
        .btn-success { background: #38a169; border: none; color: white; }
        .btn-success:hover { background: #2f855a; }
        .report-board-body {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .report-generator-launch {
            text-align: center;
            padding: 2rem;
        }
        .report-generator-launch h3 {
            font-size: 1.25rem;
            color: #0f2b4d;
            margin-bottom: 0.5rem;
        }
        .report-generator-launch p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
        .report-filter-card {
            background: #f8fafc;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
        }
        .report-generator-menu-copy {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 1rem;
        }
        .report-type-menu-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .report-type-menu-button {
            padding: 0.75rem 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #1e293b;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .report-type-menu-button:hover {
            border-color: #2c5282;
            color: #2c5282;
        }
        .report-type-menu-button.is-active {
            background: #2c5282;
            border-color: #2c5282;
            color: white;
        }
        .report-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .report-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #5c6e8c;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        .form-control, .form-select {
            padding: 0.65rem 0.8rem;
            border: 1px solid #cfdfed;
            border-radius: 8px;
            font-size: 0.85rem;
        }
        .report-generator-reset {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .report-generator-reset a {
            color: #64748b;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .report-generator-reset a:hover {
            color: #2c5282;
        }
        .hidden { display: none !important; }
        .report-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .report-kpi-card {
            background: #f8fafc;
            border-radius: 1rem;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .report-kpi-card small {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .report-kpi-value {
            display: block;
            font-size: 2rem;
            font-weight: 800;
            color: #0f2b4d;
            margin: 0.5rem 0;
        }
        .report-kpi-meta {
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }
        .report-kpi-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color);
        }
        .report-section {
            margin-bottom: 2rem;
        }
        .report-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #0f2b4d;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-table th {
            background: #f8fafc;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #5c6e8c;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
        }
        .report-table td {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            color: #1e293b;
            border-bottom: 1px solid #f0f2f5;
        }
        .report-table tr:hover {
            background: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending { background: #ffedd5; color: #9a3412; }
        .status-reviewed { background: #e0f2fe; color: #0f4c81; }
        .status-resolved { background: #e3f7ec; color: #166534; }
        .priority-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .priority-critical { background: #fee2e2; color: #b91c1c; }
        .priority-urgent { background: #fff3e3; color: #b45309; }
        .priority-normal { background: #e6f0ff; color: #1e4a76; }
        .report-charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .report-chart-card {
            background: #f8fafc;
            border-radius: 1rem;
            padding: 1.25rem;
        }
        .report-chart-card h4 {
            font-size: 0.9rem;
            color: #0f2b4d;
            margin-bottom: 1rem;
        }
        .progress-bar-container {
            margin-bottom: 0.75rem;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        .progress-label span:first-child { color: #1e293b; }
        .progress-label span:last-child { color: #64748b; }
        .progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: var(--report-primary);
        }
        .progress-fill.critical { background: #e53e3e; }
        .progress-fill.urgent { background: #d69e2e; }
        .progress-fill.normal { background: #3182ce; }
        .progress-fill.pending { background: #f59e0b; }
        .progress-fill.reviewed { background: #3182ce; }
        .progress-fill.resolved { background: #38a169; }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #2c5282;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .back-btn:hover { color: #1e3a5f; }
    </style>
</head>
<body>
    <div class="report-container">
        <a href="{{ route('academic.referrals') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Academic Supports
        </a>

        <div class="report-header">
            <div class="report-browser-bar">
                <span></span>
                <span></span>
                <span></span>
                <span class="report-browser-title">SolidCare analytics / academic-referrals-report</span>
                <span class="report-browser-badge">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
            </div>

            <div class="report-toolbar">
                <div>
                    <span class="report-eyebrow">Academic Reporting Workspace</span>
                    <h3 class="report-heading">Referrals Report Overview</h3>
                    <p class="report-subcopy">
                        A comprehensive analytics view for academic referrals, showing referral trends, 
                        priority distribution, status breakdown, and year leader referral patterns.
                    </p>
                </div>

                <div class="report-actions">
                    <div class="report-period-chip">
                        <span>Selected Period</span>
                        <strong>{{ $reportLabel }}</strong>
                    </div>
                    <a href="{{ route('academic.referrals') }}" class="btn btn-light btn-custom">
                        <i class="fas fa-times"></i> Done
                    </a>
                </div>
            </div>
        </div>

        <div class="report-board-body">
            <!-- Report Generator Section -->
            <div class="report-generator-launch">
                <h3>Generate Report</h3>
                <p>Select a report type to display academic referrals analytics and summaries for the period you want.</p>

                <div class="d-grid gap-2 mb-3">
                    <button type="button" id="show-report-generator" class="btn btn-success btn-custom">
                        <i class="fas fa-chart-bar"></i> Generate Report
                    </button>
                </div>

                <form method="GET" action="{{ route('academic.referrals.report') }}" class="report-filter-card report-generator-card {{ request()->boolean('report_generated') ? '' : 'hidden' }}" id="academic-report-filter-form">
                    <input type="hidden" name="report_generated" value="1">
                    <input type="hidden" name="type" id="report-type-input" value="{{ $reportType }}">

                    <div class="report-generator-menu-copy">Choose a report type from the menu below.</div>

                    <div class="report-type-menu-grid" role="menu" aria-label="Report types">
                        <button type="button" class="report-type-menu-button {{ $reportType === 'general' ? 'is-active' : '' }}" data-report-type="general">General Report</button>
                        <button type="button" class="report-type-menu-button {{ $reportType === 'week' ? 'is-active' : '' }}" data-report-type="week">Weekly Report</button>
                        <button type="button" class="report-type-menu-button {{ $reportType === 'month' ? 'is-active' : '' }}" data-report-type="month">Monthly Report</button>
                        <button type="button" class="report-type-menu-button {{ $reportType === 'semester' ? 'is-active' : '' }}" data-report-type="semester">Semester Report</button>
                        <button type="button" class="report-type-menu-button {{ $reportType === 'year' ? 'is-active' : '' }}" data-report-type="year">Yearly Report</button>
                    </div>

                    <div class="report-filter-grid">
                        <div data-report-field="year">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" min="2000" max="2100" value="{{ $year }}" required>
                        </div>
                        <div data-report-field="month">
                            <label class="form-label">Month</label>
                            <select name="month" class="form-select">
                                @for ($reportMonth = 1; $reportMonth <= 12; $reportMonth++)
                                    <option value="{{ $reportMonth }}" @selected($month === $reportMonth)>{{ \Carbon\Carbon::create()->month($reportMonth)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div data-report-field="week">
                            <label class="form-label">Week Start Date</label>
                            <input type="date" name="week_start_date" class="form-control" value="{{ $weekStartDate->toDateString() }}" required>
                        </div>
                        <div data-report-field="week">
                            <label class="form-label">Week End Date</label>
                            <input type="date" class="form-control" value="{{ $endDate->toDateString() }}" readonly>
                        </div>
                        <div data-report-field="semester">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="1" @selected($semester === 1)>Semester 1</option>
                                <option value="2" @selected($semester === 2)>Semester 2</option>
                            </select>
                        </div>
                    </div>

                    <div class="report-filter-actions">
                        <button type="submit" class="btn btn-success btn-custom">
                            <i class="fas fa-chart-line"></i> Generate Selected Report
                        </button>
                    </div>

                    <div class="report-generator-reset">
                        <a href="{{ route('academic.referrals.report') }}">Reset Filters</a>
                    </div>
                </form>
            </div>

            <div class="report-kpi-grid">
                <article class="report-kpi-card" style="--accent-color: var(--report-primary);">
                    <small>Total Referrals</small>
                    <span class="report-kpi-value">{{ number_format($totalReferrals) }}</span>
                    <p class="report-kpi-meta">All student referrals recorded in the system for the selected period.</p>
                    <div class="report-kpi-accent"></div>
                </article>

                <article class="report-kpi-card" style="--accent-color: var(--report-warning);">
                    <small>Pending Review</small>
                    <span class="report-kpi-value">{{ number_format($pendingReferrals->count()) }}</span>
                    <p class="report-kpi-meta">Referrals waiting for SSD staff follow-up and attendance.</p>
                    <div class="report-kpi-accent"></div>
                </article>

                <article class="report-kpi-card" style="--accent-color: var(--report-accent);">
                    <small>Reviewed Cases</small>
                    <span class="report-kpi-value">{{ number_format($reviewedReferrals->count()) }}</span>
                    <p class="report-kpi-meta">Referrals that have been attended to by SSD staff.</p>
                    <div class="report-kpi-accent"></div>
                </article>

                <article class="report-kpi-card" style="--accent-color: var(--report-success);">
                    <small>Resolved Cases</small>
                    <span class="report-kpi-value">{{ number_format($resolvedReferrals->count()) }}</span>
                    <p class="report-kpi-meta">Referrals that have been marked as resolved and closed.</p>
                    <div class="report-kpi-accent"></div>
                </article>
            </div>

            <div class="report-charts-grid">
                <div class="report-chart-card">
                    <h4><i class="fas fa-exclamation-triangle"></i> Priority Distribution</h4>
                    @foreach ($priorityData as $priority => $count)
                        <div class="progress-bar-container">
                            <div class="progress-label">
                                <span>{{ $priority }}</span>
                                <span>{{ $count }} ({{ $totalReferrals > 0 ? round(($count / $totalReferrals) * 100) : 0 }}%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill {{ strtolower($priority) }}" style="width: {{ $totalReferrals > 0 ? ($count / $totalReferrals) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="report-chart-card">
                    <h4><i class="fas fa-tasks"></i> Status Breakdown</h4>
                    @foreach ($statusData as $status => $count)
                        <div class="progress-bar-container">
                            <div class="progress-label">
                                <span>{{ $status }}</span>
                                <span>{{ $count }} ({{ $totalReferrals > 0 ? round(($count / $totalReferrals) * 100) : 0 }}%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill {{ strtolower($status) }}" style="width: {{ $totalReferrals > 0 ? ($count / $totalReferrals) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($referrerData->isNotEmpty())
            <div class="report-section">
                <h4 class="report-section-title"><i class="fas fa-user-plus"></i> Referrals by Year Leader</h4>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Year Leader</th>
                            <th>Total Referrals</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referrerData as $referrer => $count)
                        <tr>
                            <td>{{ $referrer }}</td>
                            <td>{{ number_format($count) }}</td>
                            <td>{{ $totalReferrals > 0 ? round(($count / $totalReferrals) * 100) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if ($monthlyData->isNotEmpty())
            <div class="report-section">
                <h4 class="report-section-title"><i class="fas fa-calendar-alt"></i> {{ $trendTitle }}</h4>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Referrals</th>
                            <th>Pending</th>
                            <th>Resolved</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monthlyData as $month => $data)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>{{ number_format($data['total']) }}</td>
                            <td>{{ number_format($data['pending']) }}</td>
                            <td>{{ number_format($data['resolved']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div class="report-section">
                <h4 class="report-section-title"><i class="fas fa-list"></i> All Referrals</h4>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Programme</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Referred By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($referrals as $referral)
                        <tr>
                            <td>{{ $referral->student_name }}</td>
                            <td>{{ $referral->student_id }}</td>
                            <td>{{ $referral->programme ?? 'N/A' }}</td>
                            <td><span class="priority-badge priority-{{ strtolower($referral->priority ?? 'normal') }}">{{ $referral->priority ?? 'Normal' }}</span></td>
                            <td><span class="status-badge status-{{ $referral->status }}">{{ ucfirst($referral->status) }}</span></td>
                            <td>{{ $referral->referrer->name ?? 'Unknown' }}</td>
                            <td>{{ $referral->created_at->format('M j, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b;">No referrals found for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuButtons = document.querySelectorAll('.report-type-menu-button');
            const form = document.getElementById('academic-report-filter-form');
            const typeInput = document.getElementById('report-type-input');
            const showGeneratorButton = document.getElementById('show-report-generator');
            const reportFields = document.querySelectorAll('[data-report-field]');

            const syncReportFields = () => {
                const activeType = typeInput ? typeInput.value : 'general';

                reportFields.forEach(field => {
                    const fieldType = field.dataset.reportField;
                    const shouldShow = activeType !== 'general' && (
                        fieldType === 'year'
                        || fieldType === activeType
                        || (activeType === 'month' && fieldType === 'month')
                        || (activeType === 'semester' && fieldType === 'semester')
                        || (activeType === 'week' && fieldType === 'week')
                    );

                    field.classList.toggle('hidden', !shouldShow);
                });
            };

            if (showGeneratorButton && form) {
                showGeneratorButton.addEventListener('click', function() {
                    form.classList.toggle('hidden');
                });
            }

            menuButtons.forEach(button => {
                button.addEventListener('click', function() {
                    menuButtons.forEach(btn => btn.classList.remove('is-active'));
                    this.classList.add('is-active');
                    if (typeInput) {
                        typeInput.value = this.dataset.reportType;
                    }
                    syncReportFields();
                });
            });

            syncReportFields();
        });
    </script>
</body>
</html>
