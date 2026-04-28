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
