<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Accommodation Payment Report - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            :root {
                --page-bg: #eef3f8;
                --surface: #ffffff;
                --ink: #102033;
                --muted: #64748b;
                --line: #dbe3ee;
                --brand: #0f766e;
                --blue: #2563eb;
            }
            body {
                min-height: 100vh;
                background: linear-gradient(180deg, #0f172a 0, #143247 280px, var(--page-bg) 280px, var(--page-bg) 100%);
                color: var(--ink);
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .page-shell {
                max-width: 1280px;
                margin: 0 auto;
                padding: 1.25rem 1rem 2.5rem;
            }
            .topbar,
            .hero {
                color: #e2e8f0;
            }
            .topbar {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.1rem;
            }
            .brand {
                display: inline-flex;
                gap: 0.65rem;
                align-items: center;
                font-weight: 800;
            }
            .brand-mark {
                display: inline-grid;
                width: 2.35rem;
                height: 2.35rem;
                place-items: center;
                border-radius: 0.5rem;
                background: #f8fafc;
                color: #164e63;
            }
            .hero {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                flex-wrap: wrap;
                align-items: flex-end;
                padding: 1.35rem;
                margin-bottom: 1rem;
                border: 1px solid rgba(226, 232, 240, 0.2);
                border-radius: 0.5rem;
                background: rgba(255, 255, 255, 0.08);
                box-shadow: 0 24px 60px rgba(2, 6, 23, 0.2);
            }
            .hero h1 {
                margin: 0.35rem 0 0.6rem;
                color: #f8fafc;
                font-size: clamp(2rem, 4vw, 3rem);
                font-weight: 800;
            }
            .eyebrow {
                display: inline-flex;
                padding: 0.34rem 0.72rem;
                border-radius: 999px;
                background: rgba(20, 184, 166, 0.15);
                color: #99f6e4;
                border: 1px solid rgba(153, 246, 228, 0.24);
                font-size: 0.82rem;
                font-weight: 800;
                text-transform: uppercase;
            }
            .hero p {
                max-width: 760px;
                margin: 0;
                color: #cbd5e1;
            }
            .btn-custom {
                border-radius: 0.55rem;
                padding: 0.78rem 1rem;
                font-weight: 800;
            }
            .kpis {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.9rem;
                margin-bottom: 1rem;
            }
            .panel {
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: 0.5rem;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.09);
            }
            .kpi {
                padding: 1rem;
                border-left: 0.32rem solid var(--accent, var(--brand));
            }
            .kpi small {
                display: block;
                color: var(--muted);
                font-weight: 800;
                text-transform: uppercase;
            }
            .kpi strong {
                display: block;
                margin: 0.45rem 0;
                font-size: 2rem;
                line-height: 1;
            }
            .section {
                padding: 1.1rem;
            }
            .section-header {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                align-items: flex-start;
                margin-bottom: 1rem;
            }
            .section h2 {
                margin: 0 0 0.35rem;
                font-size: 1.25rem;
            }
            .section p,
            .subtle {
                color: var(--muted);
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
                text-align: left;
                vertical-align: middle;
                white-space: nowrap;
            }
            .report-table th {
                background: #f8fafc;
                color: var(--muted);
                font-size: 0.8rem;
                text-transform: uppercase;
            }
            .cell-person {
                min-width: 220px;
                white-space: normal;
            }
            .receipt-form {
                display: flex;
                gap: 0.5rem;
                min-width: 310px;
            }
            .receipt-form .form-control {
                border-radius: 0.5rem;
            }
            .badge-soft {
                display: inline-flex;
                padding: 0.34rem 0.62rem;
                border-radius: 999px;
                font-size: 0.78rem;
                font-weight: 800;
                text-transform: uppercase;
            }
            .badge-confirmed {
                background: #dcfce7;
                color: #166534;
            }
            .badge-pending {
                background: #fef3c7;
                color: #92400e;
            }
            footer {
                text-align: center;
                color: #cbd5e1;
                padding: 1rem 0 0;
            }
            @media (max-width: 768px) {
                .kpis {
                    grid-template-columns: 1fr;
                }
                .receipt-form {
                    min-width: 260px;
                }
            }
        </style>
    </head>
    <body>
        <div class="page-shell">
            <div class="topbar">
                <div class="brand">
                    <span class="brand-mark">SC</span>
                    <span>SolidCare SSD</span>
                </div>
                <span>{{ now()->format('M j, Y') }}</span>
            </div>

            <div class="hero">
                <div>
                    <span class="eyebrow">SSD Assistant 2</span>
                    <h1>Accommodation Payment Report</h1>
                    <p>Confirm receipt numbers for admitted accommodation students and review saved payment details.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('accommodation') }}" class="btn btn-outline-light btn-custom">Back</a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="kpis">
                <article class="panel kpi" style="--accent: var(--blue);">
                    <small>Admitted Students</small>
                    <strong>{{ number_format($applications->count()) }}</strong>
                    <span class="subtle">Students retrieved from admitted accommodation records.</span>
                </article>
                <article class="panel kpi" style="--accent: var(--brand);">
                    <small>Confirmed Payments</small>
                    <strong>{{ number_format($confirmedPaymentsCount) }}</strong>
                    <span class="subtle">Records with receipt numbers saved.</span>
                </article>
                <article class="panel kpi" style="--accent: #b45309;">
                    <small>Waiting Confirmation</small>
                    <strong>{{ number_format($pendingPaymentsCount) }}</strong>
                    <span class="subtle">Students still needing receipt confirmation.</span>
                </article>
            </div>

            <section class="panel section mb-4">
                <div class="section-header">
                    <div>
                        <h2>Confirmed Payment Report</h2>
                    </div>
                </div>
                @if ($confirmedPaymentReports->isEmpty())
                    <p class="mb-0 subtle">No payment receipts have been confirmed yet.</p>
                @else
                    <div class="table-wrap">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Receipt No.</th>
                                    <th>Room</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Confirmed By</th>
                                    <th>Confirmed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($confirmedPaymentReports as $payment)
                                    <tr>
                                        <td class="cell-person">
                                            <strong>{{ $payment->application->full_name }}</strong><br>
                                            <span class="subtle">{{ $payment->application->email ?: optional($payment->application->user)->email ?: 'No email recorded' }}</span>
                                        </td>
                                        <td>{{ $payment->receipt_number }}</td>
                                        <td>
                                            @if ($payment->application->room)
                                                {{ $payment->application->room->block_name }}-{{ str_pad((string) $payment->application->room->room_number, 2, '0', STR_PAD_LEFT) }}
                                            @else
                                                Not assigned
                                            @endif
                                        </td>
                                        <td>{{ optional($payment->payment_month)->format('F Y') ?: 'Not recorded' }}</td>
                                        <td>M {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td>{{ $monthlyRentPaymentMethodLabel ?? 'Standard Lesotho Bank' }}</td>
                                        <td>{{ $payment->status ? \Illuminate\Support\Str::headline($payment->status) : 'Not recorded' }}</td>
                                        <td>
                                            @if ($payment->confirmedBy)
                                                {{ $payment->confirmedBy->name ?: $payment->confirmedBy->email }}<br>
                                                <span class="subtle">{{ \Illuminate\Support\Str::headline($payment->confirmedBy->role) }}</span>
                                            @else
                                                Not recorded
                                            @endif
                                        </td>
                                        <td>{{ optional($payment->confirmed_at)->format('F j, Y g:i A') ?: 'Not recorded' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="panel section">
                <div class="section-header">
                    <div>
                        <h2>Unpaid Rent Report</h2>
                    </div>
                </div>
                @if ($unpaidRentReports->isEmpty())
                    <p class="mb-0 subtle">No students are currently waiting for payment confirmation.</p>
                @else
                    <div class="table-wrap">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Receipt No.</th>
                                    <th>Room</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Confirmed By</th>
                                    <th>Confirmed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($unpaidRentReports as $report)
                                    @php($application = $report['application'])
                                    <tr>
                                        <td class="cell-person">
                                            <strong>{{ $application->full_name }}</strong><br>
                                            <span class="subtle">{{ $application->email ?: optional($application->user)->email ?: 'No email recorded' }}</span>
                                        </td>
                                        <td>Not captured</td>
                                        <td>
                                            @if ($application->room)
                                                {{ $application->room->block_name }}-{{ str_pad((string) $application->room->room_number, 2, '0', STR_PAD_LEFT) }}
                                            @else
                                                Not assigned
                                            @endif
                                        </td>
                                        <td>{{ $report['months_label'] }}</td>
                                        <td>M {{ number_format((float) $report['amount_due'], 2) }}</td>
                                        <td>{{ $monthlyRentPaymentMethodLabel ?? 'Standard Lesotho Bank' }}</td>
                                        <td>Waiting Confirmation</td>
                                        <td>Not recorded</td>
                                        <td>Not recorded</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <footer>&copy; 2026 SolidCare SSD. All rights reserved.</footer>
        </div>
    </body>
</html>
