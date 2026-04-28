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
            footer {
                text-align: center;
                padding: 1.5rem 0;
                color: #cbd5e1;
            }
        </style>
    </head>
    <body>
        <div class="page-header">
            <h1>Accommodation Overview</h1>
            <p>Warden access to admitted students and room availability.</p>
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-custom mt-3">Back to Home</a>
        </div>

        <div class="container py-4">
            <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                <a href="{{ route('student.accommodation.rooms') }}" class="btn btn-success btn-custom">View Available Rooms</a>
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
                                    <th>Check-In Date</th>
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
                                        <td>{{ optional($application->check_in_date)->format('F j, Y') }}</td>
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
