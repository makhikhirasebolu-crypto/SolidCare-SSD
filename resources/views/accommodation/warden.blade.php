<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Warden Accommodation View - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top, rgba(22, 163, 74, 0.14), transparent 34%),
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
            }
            .page-header p {
                color: #cbd5e1;
                margin-bottom: 0;
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            .panel-card {
                background: #f8fafc;
                color: #1f2937;
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
            }
            .section-title {
                color: #f8fafc;
                margin-bottom: 1rem;
            }
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1rem;
                margin-bottom: 1.5rem;
            }
            .summary-card {
                background: #f8fafc;
                color: #1f2937;
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
                padding: 1.1rem;
            }
            .summary-card h4 {
                color: #0f172a;
                font-weight: 800;
                margin-bottom: 0.75rem;
            }
            .summary-metrics {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                margin-bottom: 0.9rem;
            }
            .summary-metric {
                background: #eef2ff;
                border: 1px solid rgba(99, 102, 241, 0.12);
                border-radius: 0.8rem;
                padding: 0.75rem;
            }
            .summary-metric span {
                display: block;
                color: #64748b;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
            }
            .summary-metric strong {
                display: block;
                color: #0f172a;
                font-size: 1.25rem;
                margin-top: 0.2rem;
            }
            .occupied-list {
                color: #475569;
                margin: 0;
            }
            .room-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 1rem;
            }
            .room-tile {
                background: #eef2ff;
                border-radius: 1rem;
                padding: 1rem;
                border: 1px solid rgba(99, 102, 241, 0.12);
            }
            .room-tile strong {
                display: block;
                color: #1e293b;
                font-size: 1.05rem;
            }
            .room-tile span {
                display: block;
                margin-top: 0.35rem;
                color: #64748b;
            }
            .table thead th {
                white-space: nowrap;
            }
            .action-panel {
                background: #eef6ff;
                border: 1px solid rgba(59, 130, 246, 0.16);
                border-radius: 0.9rem;
                padding: 0.9rem;
                min-width: 260px;
            }
            .action-panel form {
                margin: 0;
            }
            .request-card {
                background: #f8fafc;
                color: #1f2937;
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
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
            <h1>Accommodation Overview</h1>
            <p>Residential staff access to admitted students, reallocation approvals, and room availability.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                @if (in_array($user->role, ['warden', 'ssd_assistant_2'], true))
                    <a href="{{ route('accommodation.report') }}" class="btn btn-light btn-custom">Generate Report</a>
                @endif
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-custom">Back to Home</a>
            </div>
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

            <h3 class="section-title">Room Reallocation Requests</h3>

            <div class="mb-5">
                @if ($reallocationRequests->isEmpty())
                    <div class="panel-card p-4">
                        <p class="mb-0 text-secondary">There are no room reallocation requests awaiting approval.</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($reallocationRequests as $requestApplication)
                            <div class="col-12">
                                <div class="request-card p-4">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                                        <div>
                                            <h4 class="mb-1">{{ $requestApplication->full_name }}</h4>
                                            <p class="mb-0 text-secondary">{{ optional($requestApplication->user)->email ?? $requestApplication->email }}</p>
                                        </div>
                                        <span class="badge text-bg-warning">Pending Reallocation</span>
                                    </div>

                                    <div class="info-grid mb-3">
                                        <div class="info-item">
                                            <span class="info-label">Current Room</span>
                                            <span>
                                                @if ($requestApplication->room)
                                                    {{ $requestApplication->room->block_name }}-{{ str_pad((string) $requestApplication->room->room_number, 2, '0', STR_PAD_LEFT) }}
                                                @else
                                                    Not assigned
                                                @endif
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Requested Room</span>
                                            <span>
                                                @if ($requestApplication->requestedRoom)
                                                    {{ $requestApplication->requestedRoom->block_name }}-{{ str_pad((string) $requestApplication->requestedRoom->room_number, 2, '0', STR_PAD_LEFT) }}
                                                @else
                                                    Not selected
                                                @endif
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Requested At</span>
                                            <span>{{ optional($requestApplication->reallocation_requested_at)->format('F j, Y g:i A') ?: $requestApplication->updated_at->format('F j, Y g:i A') }}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Contact</span>
                                            <span>{{ $requestApplication->contact_number }}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Admitted By</span>
                                            <span>
                                                @if ($requestApplication->admissionProcessedBy)
                                                    {{ $requestApplication->admissionProcessedBy->name ?: $requestApplication->admissionProcessedBy->email }}
                                                    ({{ \Illuminate\Support\Str::headline($requestApplication->admissionProcessedBy->role) }})
                                                @else
                                                    Not recorded
                                                @endif
                                            </span>
                                        </div>
                                        <div class="info-item" style="grid-column: 1 / -1;">
                                            <span class="info-label">Reason</span>
                                            <span>{{ $requestApplication->reallocation_reason ?: 'No reason provided.' }}</span>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('student.accommodation.reallocation.status', $requestApplication) }}" class="d-flex flex-wrap gap-2">
                                        @csrf
                                        <button type="submit" name="decision" value="approved" class="btn btn-success btn-custom">Approve Request</button>
                                        <button type="submit" name="decision" value="rejected" class="btn btn-danger btn-custom">Reject Request</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <h3 class="section-title">Admitted Students</h3>

            <div class="panel-card p-4 mb-5">
                @if ($admittedApplications->isEmpty())
                    <p class="mb-0 text-secondary">There are currently no admitted students.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Contact</th>
                                    <th>Faculty</th>
                                    <th>Programme</th>
                                    <th>Allocated Room</th>
                                    <th>Admitted By</th>
                                    <th>Last Reallocated By</th>
                                    <th>Check-In Date</th>
                                    <th>Reallocate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admittedApplications as $application)
                                    <tr>
                                        <td>{{ $application->full_name }}</td>
                                        <td>{{ $application->contact_number }}</td>
                                        <td>{{ $application->faculty }}</td>
                                        <td>{{ $application->programme }}</td>
                                        <td>
                                            @if ($application->room)
                                                {{ $application->room->block_name }}-{{ str_pad((string) $application->room->room_number, 2, '0', STR_PAD_LEFT) }}
                                            @else
                                                Not assigned
                                            @endif
                                        </td>
                                        <td>
                                            @if ($application->admissionProcessedBy)
                                                {{ $application->admissionProcessedBy->name ?: $application->admissionProcessedBy->email }}
                                                <span class="text-secondary d-block">{{ \Illuminate\Support\Str::headline($application->admissionProcessedBy->role) }}</span>
                                            @else
                                                Not recorded
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $reallocationActor = $application->roomReallocatedBy ?: $application->reallocationApprovedBy;
                                            @endphp
                                            @if ($reallocationActor)
                                                {{ $reallocationActor->name ?: $reallocationActor->email }}
                                                <span class="text-secondary d-block">{{ \Illuminate\Support\Str::headline($reallocationActor->role) }}</span>
                                            @else
                                                Not reallocated
                                            @endif
                                        </td>
                                        <td>{{ optional($application->check_in_date)->format('F j, Y') }}</td>
                                        <td>
                                            <div class="action-panel">
                                                <form method="POST" action="{{ route('student.accommodation.reallocate', $application) }}">
                                                    @csrf
                                                    <div class="d-flex flex-column gap-2">
                                                        <select name="accommodation_room_id" class="form-select form-select-sm" required>
                                                            <option value="">Select room</option>
                                                            @foreach ($availableRooms as $room)
                                                                @if ((int) $room->id !== (int) $application->accommodation_room_id)
                                                                    <option value="{{ $room->id }}">
                                                                        {{ $room->block_name }}-{{ str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT) }}
                                                                        | {{ $room->capacity - $room->occupied_beds }} spaces left
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                        <textarea
                                                            name="reallocation_reason"
                                                            class="form-control form-control-sm"
                                                            rows="2"
                                                            placeholder="Reason for reallocation"
                                                            required
                                                        >{{ old('reallocation_reason') }}</textarea>
                                                        <button type="submit" class="btn btn-primary btn-sm">Reallocate</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <h3 class="section-title">Available Rooms</h3>

            @if ($rooms->isEmpty())
                <div class="panel-card p-4">
                    <p class="mb-0 text-secondary">No rooms are available yet.</p>
                </div>
            @else
                <div class="summary-grid">
                    @foreach ($rooms as $blockName => $blockRooms)
                        @php
                            $totalRooms = $blockRooms->count();
                            $occupiedRooms = $blockRooms->filter(fn ($room) => (int) $room->occupied_beds > 0);
                            $availableRoomsCount = $blockRooms->filter(fn ($room) => (int) $room->occupied_beds < (int) $room->capacity)->count();
                            $occupiedBeds = $blockRooms->sum(fn ($room) => (int) $room->occupied_beds);
                            $availableBeds = $blockRooms->sum(fn ($room) => max(0, (int) $room->capacity - (int) $room->occupied_beds));
                            $occupiedRoomLabels = $occupiedRooms
                                ->map(fn ($room) => $room->block_name . '-' . str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT) . ' (' . $room->occupied_beds . '/' . $room->capacity . ')')
                                ->implode(', ');
                        @endphp
                        <div class="summary-card">
                            <h4>Block {{ $blockName }} Summary</h4>
                            <div class="summary-metrics">
                                <div class="summary-metric">
                                    <span>Available Rooms</span>
                                    <strong>{{ $availableRoomsCount }}/{{ $totalRooms }}</strong>
                                </div>
                                <div class="summary-metric">
                                    <span>Available Beds</span>
                                    <strong>{{ $availableBeds }}</strong>
                                </div>
                                <div class="summary-metric">
                                    <span>Occupied Rooms</span>
                                    <strong>{{ $occupiedRooms->count() }}</strong>
                                </div>
                                <div class="summary-metric">
                                    <span>Occupied Beds</span>
                                    <strong>{{ $occupiedBeds }}</strong>
                                </div>
                            </div>
                            <p class="occupied-list">
                                <strong>Occupied:</strong>
                                {{ $occupiedRoomLabels !== '' ? $occupiedRoomLabels : 'No rooms occupied' }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    @foreach ($rooms as $blockName => $blockRooms)
                        <div class="col-12">
                            <div class="panel-card p-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                    <h4 class="mb-0">Block {{ $blockName }}</h4>
                                    <span class="text-secondary">{{ $blockRooms->count() }} rooms</span>
                                </div>

                                <div class="room-grid">
                                    @foreach ($blockRooms as $room)
                                        <div class="room-tile">
                                            <strong>{{ $room->block_name }}-{{ str_pad((string) $room->room_number, 2, '0', STR_PAD_LEFT) }}</strong>
                                            <span>Capacity: {{ $room->capacity }} students</span>
                                            <span>Occupied: {{ $room->occupied_beds }}/{{ $room->capacity }}</span>
                                            <span>Available: {{ $room->capacity - $room->occupied_beds }} spaces left</span>
                                        </div>
                                    @endforeach
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
