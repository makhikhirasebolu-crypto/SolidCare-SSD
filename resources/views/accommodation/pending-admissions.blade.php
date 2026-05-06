<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Pending Admissions - SolidCare SSD</title>
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
                font-size: 2.4rem;
                margin-bottom: 0.5rem;
                color: #f8fafc;
            }
            .page-header p {
                color: #cbd5e1;
                margin-bottom: 0;
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            .application-card {
                background: #f8fafc;
                color: #1f2937;
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
            }
            .application-card h4 {
                margin-bottom: 0.35rem;
            }
            .application-card .status-badge {
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
            .application-card .status-badge.admitted {
                background: #dcfce7;
                color: #166534;
            }
            .application-card .status-badge.rejected {
                background: #fee2e2;
                color: #991b1b;
            }
            .application-card .status-badge.conditional {
                background: #ffedd5;
                color: #9a3412;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.9rem 1.2rem;
            }
            .info-item {
                background: #eef2ff;
                border-radius: 0.8rem;
                padding: 0.85rem 1rem;
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
            .empty-state {
                background: rgba(248, 250, 252, 0.1);
                border: 1px dashed rgba(203, 213, 225, 0.4);
                border-radius: 1rem;
                padding: 2rem;
                text-align: center;
                color: #e2e8f0;
            }
            .decision-bar {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                margin-top: 1.25rem;
            }
            .decision-form {
                margin: 0;
                width: 100%;
            }
            .allocation-panel {
                margin-top: 1.25rem;
                background: #eef6ff;
                border-radius: 1rem;
                padding: 1rem;
                border: 1px solid rgba(59, 130, 246, 0.16);
            }
            .allocation-label {
                display: block;
                margin-bottom: 0.5rem;
                font-size: 0.82rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #475569;
            }
            .decision-button {
                border: none;
                border-radius: 999px;
                padding: 0.8rem 1.1rem;
                font-weight: 700;
                color: #fff;
                cursor: pointer;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }
            .decision-button:hover {
                transform: translateY(-1px);
                opacity: 0.92;
            }
            .decision-button.approve {
                background: #15803d;
            }
            .decision-button.reject {
                background: #b91c1c;
            }
            .decision-button.conditional {
                background: #b45309;
            }
            .section-heading {
                margin-bottom: 1.25rem;
            }
            .section-heading h3 {
                margin-bottom: 0.35rem;
                color: #f8fafc;
            }
            .section-heading p {
                margin-bottom: 0;
                color: #cbd5e1;
            }
            .helper-note {
                margin-top: 1rem;
                color: #cbd5e1;
                font-size: 0.92rem;
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
            <h1>Pending Admissions</h1>
            <p>Review all students who have applied for accommodation and see their submitted information.</p>
            <a href="{{ route('accommodation') }}" class="btn btn-outline-light btn-custom mt-3">Back to Accommodation</a>
        </div>

        <div class="container py-4">
            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
            @endif

            @if (session('accommodation_email_status'))
                <div class="alert alert-info mb-4">{{ session('accommodation_email_status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="section-heading">
                <h3>Pending Applications</h3>
                <p>Review new accommodation requests and update each student&apos;s status.</p>
            </div>

            @if ($applications->isEmpty())
                <div class="empty-state">
                    <h4 class="mb-2">No Pending Applications</h4>
                    <p class="mb-0">There are currently no students waiting for accommodation admission.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($applications as $application)
                        @php
                            $physicalDisabilityLabel = is_null($application->has_physical_disability)
                                ? 'Not recorded'
                                : ($application->has_physical_disability ? 'Yes' : 'No');
                            $highBloodPressureLabel = is_null($application->has_high_blood_pressure)
                                ? 'Not recorded'
                                : ($application->has_high_blood_pressure ? 'Yes' : 'No');
                            $diabetesLabel = is_null($application->has_diabetes)
                                ? 'Not recorded'
                                : ($application->has_diabetes ? 'Yes' : 'No');
                            $asthmaLabel = is_null($application->has_asthma)
                                ? 'Not recorded'
                                : ($application->has_asthma ? 'Yes' : 'No');
                            $onTreatmentLabel = is_null($application->on_chronic_treatment)
                                ? 'Not recorded'
                                : ($application->on_chronic_treatment ? 'Yes' : 'No');

                            $healthSummary = collect([
                                'Physical disability: ' . $physicalDisabilityLabel
                                    . ($application->physical_disability_details ? ' - ' . $application->physical_disability_details : ''),
                                'High blood pressure: ' . $highBloodPressureLabel,
                                'Diabetes: ' . $diabetesLabel,
                                'Asthma: ' . $asthmaLabel,
                                $application->chronic_illness_other ? 'Other chronic illness: ' . $application->chronic_illness_other : null,
                                'On treatment: ' . $onTreatmentLabel
                                    . ($application->treatment_frequency ? ' - ' . $application->treatment_frequency : ''),
                            ])->filter()->implode(' | ');

                            $nextOfKinSummary = collect([
                                $application->next_of_kin_name,
                                $application->next_of_kin_relationship,
                                $application->next_of_kin_contact,
                            ])->filter()->implode(' | ');
                        @endphp
                        <div class="col-12">
                            <div class="application-card p-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                                    <div>
                                        <h4>{{ $application->full_name }}</h4>
                                        <p class="mb-0 text-secondary">{{ $application->email }}</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-md-end">
                                        <span class="status-badge {{ $application->status }}">{{ ucfirst($application->status) }}</span>
                                    </div>
                                </div>

                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Student ID</span>
                                        <span>{{ $application->student_id ?: optional($application->user)->student_id ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Contact Number</span>
                                        <span>{{ $application->contact_number }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">National ID</span>
                                        <span>{{ $application->national_id }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Nationality</span>
                                        <span>{{ $application->nationality }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Gender</span>
                                        <span>{{ $application->gender ? ucfirst($application->gender) : 'Not recorded' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Age</span>
                                        <span>{{ $application->age }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Marital Status</span>
                                        <span>{{ $application->marital_status }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Intake</span>
                                        <span>{{ $application->intake ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Semester</span>
                                        <span>{{ $application->semester ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Faculty</span>
                                        <span>{{ $application->faculty }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Programme</span>
                                        <span>{{ $application->programme }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Check-In Date</span>
                                        <span>{{ optional($application->check_in_date)->format('F j, Y') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Student Account Email</span>
                                        <span>{{ optional($application->user)->email ?? $application->email }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Submitted</span>
                                        <span>{{ $application->created_at->format('F j, Y g:i A') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">District</span>
                                        <span>{{ $application->district ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Village</span>
                                        <span>{{ $application->village ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="info-item" style="grid-column: 1 / -1;">
                                        <span class="info-label">Next Of Kin</span>
                                        <span>{{ $nextOfKinSummary ?: 'Not recorded' }}</span>
                                    </div>
                                    <div class="info-item" style="grid-column: 1 / -1;">
                                        <span class="info-label">Address (Home Address)</span>
                                        <span>{{ $application->address }}</span>
                                    </div>
                                    <div class="info-item" style="grid-column: 1 / -1;">
                                        <span class="info-label">Student Health Information</span>
                                        <span>{{ $healthSummary ?: 'Not recorded' }}</span>
                                    </div>
                                    @if ($application->special_conditions_remark)
                                        <div class="info-item" style="grid-column: 1 / -1;">
                                            <span class="info-label">Remark</span>
                                            <span>{{ $application->special_conditions_remark }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if ($user->role === 'executive')
                                    <form method="POST" action="{{ route('student.accommodation.status', $application) }}" class="decision-form">
                                        @csrf

                                        <div class="allocation-panel">
                                            <label for="room_{{ $application->id }}" class="allocation-label">Room Allocation</label>
                                            <select id="room_{{ $application->id }}" name="accommodation_room_id" class="form-select">
                                                <option value="">Select available room</option>
                                                @foreach ($availableRooms as $room)
                                                    <option value="{{ $room->id }}">
                                                        {{ $room->block_name }}-{{ str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT) }}
                                                        | {{ $room->occupied_beds }}/{{ $room->capacity }} occupied
                                                        | {{ $room->capacity - $room->occupied_beds }} spaces left
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="allocation-panel">
                                            <label for="rejection_reason_{{ $application->id }}" class="allocation-label">Rejection Reason</label>
                                            <textarea
                                                id="rejection_reason_{{ $application->id }}"
                                                name="rejection_reason"
                                                class="form-control"
                                                rows="3"
                                                placeholder="Required when rejecting this application."
                                            >{{ old('rejection_reason') }}</textarea>
                                        </div>

                                        <div class="decision-bar">
                                            <button type="submit" name="status" value="admitted" class="decision-button approve">Approve Student</button>
                                            <button type="submit" name="status" value="rejected" class="decision-button reject">Reject Student</button>
                                            <button type="submit" name="status" value="conditional" class="decision-button conditional">Conditional</button>
                                        </div>
                                    </form>
                                @else
                                    <p class="helper-note mb-0">Only executive can update application statuses from this page.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        <footer>&copy; 2026 SolidCare SSD. All rights reserved.</footer>
    </body>
</html>
