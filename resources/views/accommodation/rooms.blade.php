<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Room Management - SolidCare SSD</title>
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
            .panel-card {
                background: #f8fafc;
                color: #1f2937;
                border-radius: 1rem;
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
            }
            .form-label {
                font-weight: 700;
                color: #334155;
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            .block-title {
                color: #0f172a;
                font-weight: 800;
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
                font-size: 1.1rem;
                color: #1e293b;
            }
            .room-tile span {
                display: block;
                margin-top: 0.35rem;
                color: #64748b;
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
            <h1>Room and Block Management</h1>
            <p>
                @if ($user->role === 'warden')
                    View room occupancy and available spaces for accommodation.
                @else
                    Create blocks and rooms for accommodation. Each room is fixed to a capacity of 4 students.
                @endif
            </p>
            <a href="{{ route('accommodation') }}" class="btn btn-outline-light btn-custom mt-3">Back to Accommodation</a>
        </div>

        <div class="container py-4">
            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if ($rooms->isEmpty())
                <div class="panel-card p-4">
                    <h4 class="mb-2">No Rooms Yet</h4>
                    <p class="mb-0 text-secondary">Create a room manually or use the default block generator to add AG and AF.</p>
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
                                    <h4 class="block-title mb-0">Block {{ $blockName }}</h4>
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
