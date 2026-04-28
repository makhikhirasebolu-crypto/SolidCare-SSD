<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Accommodation - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top, rgba(59, 130, 246, 0.18), transparent 32%),
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
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            .status-panel {
                background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
                border: 1px solid rgba(59, 130, 246, 0.18);
                border-radius: 1rem;
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }
            .status-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                flex-wrap: wrap;
                margin-bottom: 1rem;
            }
            .status-header h5 {
                margin-bottom: 0.35rem;
                color: #0f172a;
            }
            .status-header p {
                margin: 0;
                color: #475569;
            }
            .status-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.45rem 0.9rem;
                border-radius: 999px;
                font-size: 0.82rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: #1e3a8a;
                background: #dbeafe;
            }
            .status-badge.pending,
            .status-badge.checkout_requested {
                background: #fef3c7;
                color: #92400e;
            }
            .status-badge.admitted,
            .status-badge.checked_out {
                background: #dcfce7;
                color: #166534;
            }
            .status-badge.conditional {
                background: #ffedd5;
                color: #9a3412;
            }
            .status-badge.rejected,
            .status-badge.checkout_rejected {
                background: #fee2e2;
                color: #991b1b;
            }
            .application-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }
            .application-item {
                background: #ffffff;
                border: 1px solid rgba(148, 163, 184, 0.2);
                border-radius: 0.9rem;
                padding: 0.9rem 1rem;
            }
            .application-item.wide {
                grid-column: 1 / -1;
            }
            .application-label {
                display: block;
                margin-bottom: 0.3rem;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #64748b;
            }
            .application-value {
                color: #0f172a;
                font-weight: 600;
                word-break: break-word;
            }
            .section-heading {
                margin: 1.5rem 0 1rem;
                color: #0f172a;
            }
            footer {
                text-align: center;
                padding: 1.5rem 0;
                color: #cbd5e1;
            }
            @media (max-width: 768px) {
                .application-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="page-header">
            <h1>Accommodation</h1>
            <p>Apply or request checkout for student housing.</p>
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-custom mt-3">Back to Home</a>
        </div>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card p-4 mb-4">
                        <h4 class="mb-3">Welcome, {{ $user->name }}</h4>
                        <div class="mb-3">
                            <strong>Student type:</strong> {{ ucfirst($user->student_type) }}
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if ($user->student_type === 'new')
                            @if (isset($application))
                                @php
                                    $statusLabel = ucfirst(str_replace('_', ' ', $application->status));
                                    $appliedAt = optional($application->created_at)->format('F j, Y g:i A') ?? 'Not recorded';
                                    $submittedAddress = collect([$application->district, $application->village])->filter()->implode(', ');
                                @endphp

                                <div class="status-panel">
                                    <div class="status-header">
                                        <div>
                                            <h5>Accommodation Application Summary</h5>
                                            <p>Your submitted application details are shown below.</p>
                                        </div>
                                        <span class="status-badge {{ $application->status }}">{{ $statusLabel }}</span>
                                    </div>

                                    <div class="application-grid">
                                        <div class="application-item">
                                            <span class="application-label">Application Status</span>
                                            <div class="application-value">{{ $statusLabel }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Applied On</span>
                                            <div class="application-value">{{ $appliedAt }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Full Name</span>
                                            <div class="application-value">{{ $application->full_name ?: $user->name }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Student ID</span>
                                            <div class="application-value">{{ $application->student_id ?: ($user->student_id ?? 'Not recorded') }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Email Address</span>
                                            <div class="application-value">{{ $application->email ?: $user->email }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Contact Number</span>
                                            <div class="application-value">{{ $application->contact_number ?: 'Not recorded' }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">National ID / Passport</span>
                                            <div class="application-value">{{ $application->national_id ?: ($user->id_number ?? 'Not recorded') }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Nationality</span>
                                            <div class="application-value">{{ $application->nationality ?: 'Not recorded' }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Faculty</span>
                                            <div class="application-value">{{ $application->faculty ?: 'Not recorded' }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Programme</span>
                                            <div class="application-value">{{ $application->programme ?: 'Not recorded' }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Intake</span>
                                            <div class="application-value">{{ $application->intake ?: 'Not recorded' }}</div>
                                        </div>
                                        <div class="application-item">
                                            <span class="application-label">Check-In Date</span>
                                            <div class="application-value">{{ optional($application->check_in_date)->format('F j, Y') ?: 'Not recorded' }}</div>
                                        </div>
                                        <div class="application-item wide">
                                            <span class="application-label">Application Address</span>
                                            <div class="application-value">{{ $submittedAddress !== '' ? $submittedAddress : ($application->address ?: 'Not recorded') }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    You already have an accommodation application with status: <strong>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</strong>.
                                </div>

                                @if (in_array($application->status, ['admitted', 'checkout_requested', 'checkout_rejected'], true) && $application->room)
                                    <div class="alert alert-success">
                                        Allocated Room:
                                        <strong>
                                            {{ $application->room->block_name }}-{{ str_pad((string) $application->room->room_number, 2, '0', STR_PAD_LEFT) }}
                                        </strong>
                                    </div>
                                @endif

                                @if ($application->status === 'admitted')
                                    <div class="alert alert-info">
                                        Your accommodation has been admitted. A confirmation email will be sent by the accommodation team.
                                    </div>
                                @endif

                                @if ($application->status === 'conditional')
                                    <div class="alert alert-warning">
                                        Your application is currently conditional while the accommodation team completes the final review.
                                    </div>
                                @endif

                                @if ($application->status === 'rejected')
                                    <div class="alert alert-danger">
                                        Your accommodation application was rejected.
                                        @if ($application->rejection_reason)
                                            <div class="mt-2"><strong>Reason:</strong> {{ $application->rejection_reason }}</div>
                                        @endif
                                    </div>
                                @endif

                                @if ($application->status === 'checkout_requested')
                                    <div class="alert alert-info">
                                        Your checkout request has been submitted and is awaiting executive approval.
                                    </div>
                                @endif

                                @if ($application->status === 'checkout_rejected')
                                    <div class="alert alert-danger">
                                        Your last checkout request was rejected.
                                        @if ($application->rejection_reason)
                                            <div class="mt-2"><strong>Reason:</strong> {{ $application->rejection_reason }}</div>
                                        @endif
                                    </div>
                                @endif

                                @if ($application->status === 'checked_out')
                                    <div class="alert alert-success">
                                        Your checkout has been completed successfully and your room has been released.
                                    </div>
                                @endif

                                @if (in_array($application->status, ['admitted', 'checkout_rejected'], true))
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="card border-0 shadow-sm p-4 h-100">
                                                <h5>{{ $application->status === 'checkout_rejected' ? 'Request Checkout Again' : 'Checkout Request' }}</h5>
                                                <p class="text-muted">
                                                    {{ $application->status === 'checkout_rejected'
                                                        ? 'Resolve the issue noted by the accommodation team, then submit a fresh checkout request.'
                                                        : 'Request checkout approval once your residence period is complete.' }}
                                                </p>
                                                <a href="{{ route('student.accommodation.checkout') }}" class="btn btn-secondary btn-custom">
                                                    {{ $application->status === 'checkout_rejected' ? 'Submit New Checkout Request' : 'Request Checkout' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif (! in_array($application->status, ['rejected', 'checked_out', 'checkout_requested', 'conditional'], true))
                                    <div class="alert alert-secondary">
                                        Checkout will become available once your accommodation application has been admitted.
                                    </div>
                                @endif
                            @else
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm p-4 h-100">
                                            <h5>Apply for Accommodation</h5>
                                            <p class="text-muted">Submit your request for room placement and housing support.</p>
                                            <a href="{{ route('student.accommodation.apply') }}" class="btn btn-primary btn-custom">Start Application</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                Only new students can submit accommodation applications and checkout requests here. Please contact the accommodation office if you need additional support.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <footer>&copy; 2026 SolidCare SSD. All rights reserved.</footer>
    </body>
</html>
