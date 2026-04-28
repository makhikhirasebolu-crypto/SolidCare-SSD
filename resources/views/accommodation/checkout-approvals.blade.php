<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Checkout Approvals - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top, rgba(245, 158, 11, 0.16), transparent 34%),
                    linear-gradient(135deg, #111827 0%, #1f2937 55%, #020617 100%);
                color: #e5e7eb;
                min-height: 100vh;
            }
            .page-header {
                padding: 2rem 1rem 1rem;
                text-align: center;
            }
            .page-header h1 {
                font-size: 2.4rem;
                margin-bottom: 0.5rem;
            }
            .page-header p {
                color: #cbd5e1;
                margin-bottom: 0;
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            .request-card {
                background: #f8fafc;
                color: #1f2937;
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
            }
            .status-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.35rem 0.75rem;
                border-radius: 999px;
                background: #fef3c7;
                color: #92400e;
                font-weight: 700;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.9rem 1.2rem;
            }
            .info-item {
                background: #f8fafc;
                border-radius: 0.8rem;
                padding: 0.85rem 1rem;
                border: 1px solid #e2e8f0;
            }
            .info-label {
                display: block;
                margin-bottom: 0.3rem;
                font-size: 0.8rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
            }
            .action-bar {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                margin-top: 1.25rem;
            }
            .approve-button {
                border: none;
                border-radius: 999px;
                padding: 0.8rem 1.1rem;
                font-weight: 700;
                color: #fff;
                background: #15803d;
                cursor: pointer;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }
            .approve-button:hover {
                transform: translateY(-1px);
                opacity: 0.92;
            }
            .empty-state {
                background: rgba(248, 250, 252, 0.1);
                border: 1px dashed rgba(203, 213, 225, 0.4);
                border-radius: 1rem;
                padding: 2rem;
                text-align: center;
                color: #e2e8f0;
            }
            footer {
                text-align: center;
                padding: 1.5rem 0;
                color: #cbd5e1;
            }
            @media (max-width: 768px) {
                .info-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="page-header">
            <h1>Checkout Approvals</h1>
            <p>Approve or reject checkout requests and release rooms only after departure clearance.</p>
            <a href="{{ route('accommodation') }}" class="btn btn-outline-light btn-custom mt-3">Back to Accommodation</a>
        </div>

        <div class="container py-4">
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

            @if ($applications->isEmpty())
                <div class="empty-state">
                    <h4 class="mb-2">No Checkout Requests</h4>
                    <p class="mb-0">There are currently no student applications waiting for checkout approval.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($applications as $application)
                        <div class="col-12">
                            <div class="request-card p-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                                    <div>
                                        <h4 class="mb-1">{{ $application->full_name }}</h4>
                                        <p class="mb-0 text-secondary">{{ optional($application->user)->email ?? $application->email }}</p>
                                    </div>
                                    <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                                </div>

                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Student Contact</span>
                                        <span>{{ $application->contact_number }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Faculty / Programme</span>
                                        <span>{{ $application->faculty }} / {{ $application->programme }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Allocated Room</span>
                                        <span>
                                            @if ($application->room)
                                                {{ $application->room->block_name }}-{{ str_pad((string) $application->room->room_number, 2, '0', STR_PAD_LEFT) }}
                                            @else
                                                No room assigned
                                            @endif
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Checkout Date</span>
                                        <span>{{ optional($application->checkout_date)->format('F j, Y') ?: 'Not provided' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Requested At</span>
                                        <span>{{ optional($application->checkout_requested_at)->format('F j, Y g:i A') ?: $application->updated_at->format('F j, Y g:i A') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Check-In Date</span>
                                        <span>{{ optional($application->check_in_date)->format('F j, Y') }}</span>
                                    </div>
                                    <div class="info-item" style="grid-column: 1 / -1;">
                                        <span class="info-label">Reason for Checkout</span>
                                        <span>{{ $application->checkout_reason ?: 'No reason provided.' }}</span>
                                    </div>
                                </div>

                                <div class="action-bar">
                                    <form method="POST" action="{{ route('student.accommodation.checkout.status', $application) }}" class="w-100">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="rejection_reason_{{ $application->id }}" class="form-label fw-semibold text-secondary">Rejection Reason</label>
                                            <textarea
                                                id="rejection_reason_{{ $application->id }}"
                                                name="rejection_reason"
                                                class="form-control"
                                                rows="3"
                                                placeholder="Required when rejecting this checkout request."
                                            >{{ old('rejection_reason') }}</textarea>
                                        </div>

                                        <div class="action-bar">
                                            <button type="submit" name="decision" value="approved" class="approve-button">Approve Checkout</button>
                                            <button type="submit" name="decision" value="rejected" class="btn btn-danger btn-custom">Reject Checkout</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <footer>&copy; 2026 SolidCare SSD. All rights reserved.</footer>
    </body>
</html>
