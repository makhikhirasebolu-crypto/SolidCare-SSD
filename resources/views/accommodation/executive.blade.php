<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Accommodation Management - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top, rgba(59, 130, 246, 0.18), transparent 30%),
                    linear-gradient(135deg, #0f172a 0%, #111827 45%, #020617 100%);
                color: #e5e7eb;
                min-height: 100vh;
            }
            .page-header {
                padding: 2rem 1rem 1rem;
                text-align: center;
            }
            .page-header h1 {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
                color: #f8fafc;
            }
            .page-header p {
                color: #cbd5e1;
                font-size: 1rem;
            }
            .card {
                border-radius: 1rem;
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                background: #f8fafc;
                color: #1f2937;
                border: 1px solid rgba(148, 163, 184, 0.18);
            }
            .card-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                margin-bottom: 0.85rem;
            }
            .card-heading h5 {
                margin: 0;
            }
            .notice-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 2.2rem;
                padding: 0.35rem 0.7rem;
                border-radius: 999px;
                font-size: 0.85rem;
                font-weight: 700;
                line-height: 1;
                color: #fff;
            }
            .notice-badge.success {
                background: #15803d;
            }
            .notice-badge.warning {
                background: #b45309;
            }
            .notice-badge.info {
                background: #2563eb;
            }
            .notice-copy {
                margin-top: 0.35rem;
                font-size: 0.92rem;
                font-weight: 600;
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            .header-actions {
                display: flex;
                gap: 0.85rem;
                justify-content: center;
                flex-wrap: wrap;
                margin-top: 1rem;
            }
            footer {
                text-align: center;
                padding: 1.5rem 0;
                color: #cbd5e1;
            }
        </style>
    </head>
    <body>
        <div class="page-header">
            <h1>Accommodation Management</h1>
            <p>Admit students, approve checkouts, and view available rooms.</p>
            <div class="header-actions">
                <a href="{{ route('accommodation.report') }}" class="btn btn-light btn-custom">Generate Report</a>
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-custom">Back to Home</a>
            </div>
        </div>

        <div class="container py-4">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="card-heading">
                            <h5>Pending Admissions</h5>
                            <span class="notice-badge success">{{ $pendingAdmissionsCount }}</span>
                        </div>
                        <p class="text-muted">Review and admit incoming students into available rooms.</p>
                        <p class="notice-copy text-success">
                            {{ $pendingAdmissionsCount > 0 ? $pendingAdmissionsCount . ' new application' . ($pendingAdmissionsCount === 1 ? '' : 's') . ' waiting.' : 'No new admission notifications.' }}
                        </p>
                        <a href="{{ route('student.accommodation.pending') }}" class="btn btn-success btn-custom">Admit Student</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="card-heading">
                            <h5>Checkout Approvals</h5>
                            <span class="notice-badge warning">{{ $checkoutApprovalsCount }}</span>
                        </div>
                        <p class="text-muted">Approve or reject checkout requests and manage departure clearances.</p>
                        <p class="notice-copy text-warning">
                            {{ $checkoutApprovalsCount > 0 ? $checkoutApprovalsCount . ' new checkout request' . ($checkoutApprovalsCount === 1 ? '' : 's') . '.' : 'No new checkout notifications.' }}
                        </p>
                        <a href="{{ route('student.accommodation.checkout') }}" class="btn btn-warning btn-custom">Approve Checkout</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="card-heading">
                            <h5>Available Rooms</h5>
                            <span class="notice-badge info">{{ $availableRoomsCount }}</span>
                        </div>
                        <p class="text-muted">See current room availability and residential capacity.</p>
                        <p class="notice-copy text-primary">
                            {{ $availableRoomsCount > 0 ? $availableRoomsCount . ' room' . ($availableRoomsCount === 1 ? '' : 's') . ' currently available.' : 'No rooms currently available.' }}
                        </p>
                        <a href="{{ route('student.accommodation.rooms') }}" class="btn btn-primary btn-custom">View Rooms</a>
                    </div>
                </div>
            </div>
        </div>

        <footer>&copy; 2026 SolidCare SSD. All rights reserved.</footer>
    </body>
</html>
