{{-- resources/views/student/clinic/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Clinic Records')
@section('portal_label', 'Student Clinic Portal')

@push('styles')
<style>
    .clinic-hero {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .hero-panel,
    .stat-panel,
    .content-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(241, 247, 255, 0.98));
        color: var(--text-main);
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 20px 55px rgba(15, 23, 42, 0.12);
    }

    .hero-panel {
        padding: 1.75rem;
        background:
            radial-gradient(circle at top right, rgba(29, 126, 242, 0.12), transparent 30%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(241, 247, 255, 0.98));
    }

    .hero-panel h2 {
        margin: 0 0 0.65rem;
        font-size: clamp(2rem, 4vw, 2.8rem);
    }

    .hero-panel p {
        margin: 0;
        max-width: 650px;
        color: var(--text-soft);
        line-height: 1.7;
    }

    .stat-panel {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background:
            linear-gradient(135deg, rgba(29, 126, 242, 0.14), rgba(13, 110, 253, 0.04)),
            #f8fbff;
    }

    .stat-label {
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .stat-value {
        font-size: 3rem;
        font-weight: 800;
        color: var(--accent-deep);
        line-height: 1;
    }

    .content-card {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .content-card h4 {
        margin: 0 0 1rem;
        font-size: 1.25rem;
    }

    .success-banner {
        margin-bottom: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 16px;
        background: var(--success-bg);
        color: var(--success-text);
        font-weight: 600;
    }

    .clinic-form-label {
        font-weight: 700;
        color: #17324d;
        margin-bottom: 0.45rem;
    }

    .clinic-input {
        border-radius: 16px;
        border: 1px solid #d4dfeb;
        padding: 0.9rem 1rem;
        background: #fff;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .clinic-input:focus {
        border-color: rgba(29, 126, 242, 0.55);
        box-shadow: 0 0 0 4px rgba(29, 126, 242, 0.12);
    }

    .clinic-button {
        border: 0;
        border-radius: 999px;
        padding: 0.9rem 1.2rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
        color: #fff;
    }

    .clinic-button:hover {
        opacity: 0.96;
    }

    .records-grid {
        display: grid;
        gap: 1rem;
    }

    .record-card {
        border-radius: 22px;
        border: 1px solid #d8e3ee;
        background: linear-gradient(180deg, #ffffff, #f9fbff);
        overflow: hidden;
    }

    .record-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        padding: 1.15rem 1.2rem 0.9rem;
        border-bottom: 1px solid #e8eef5;
    }

    .record-head strong {
        font-size: 1.05rem;
    }

    .record-date {
        color: var(--text-soft);
        font-size: 0.92rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.42rem 0.8rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #e3efff;
        color: #1456b8;
    }

    .status-badge.status-pending {
        background: #fff0cf;
        color: #9a6700;
    }

    .status-badge.status-completed {
        background: #ddf7e7;
        color: #146c43;
    }

    .record-body {
        padding: 1rem 1.2rem 1.2rem;
        display: grid;
        gap: 0.85rem;
    }

    .record-row strong {
        display: block;
        margin-bottom: 0.25rem;
        color: #17324d;
    }

    .empty-state {
        padding: 1.2rem;
        border-radius: 18px;
        background: #edf5ff;
        color: #4c617a;
    }

    @media (max-width: 900px) {
        .clinic-hero {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="clinic-hero">
    <div class="hero-panel">
        <h2>Clinic Records</h2>
        <p>Request a clinic appointment, track your submitted health concerns, and review the diagnosis, treatment, and appointment progress shared by the clinic team.</p>
    </div>

    <div class="stat-panel">
        <span class="stat-label">Your Total Records</span>
        <span class="stat-value">{{ $records->count() }}</span>
        <div class="text-secondary">Every request you submit appears below with its latest clinic status.</div>
    </div>
</div>

@if(session('success'))
    <div class="success-banner">{{ session('success') }}</div>
@endif

<div class="content-card">
    <h4>Request Clinic Appointment</h4>
    <form action="{{ route('student.clinic.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="symptoms" class="form-label clinic-form-label">Symptoms</label>
            <textarea name="symptoms" id="symptoms" class="form-control clinic-input" rows="5" placeholder="Describe your symptoms clearly so the clinic team can prepare for your visit." required>{{ old('symptoms') }}</textarea>
        </div>
        <button type="submit" class="clinic-button">Submit Request</button>
    </form>
</div>

<div class="content-card">
    <h4>Your Clinic Records</h4>

    @if($records && $records->count())
        <div class="records-grid">
            @foreach($records as $record)
                <article class="record-card">
                    <div class="record-head">
                        <div>
                            <strong>Clinic Request</strong>
                            <div class="record-date">
                                {{ $record->created_at ? $record->created_at->format('d-m-Y h:i A') : '-' }}
                            </div>
                        </div>
                        <span class="status-badge status-{{ strtolower($record->status) }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </div>

                    <div class="record-body">
                        <div class="record-row">
                            <strong>Symptoms</strong>
                            <span>{{ $record->symptoms ?? '-' }}</span>
                        </div>
                        <div class="record-row">
                            <strong>Diagnosis</strong>
                            <span>{{ $record->diagnosis ?? '-' }}</span>
                        </div>
                        <div class="record-row">
                            <strong>Treatment</strong>
                            <span>{{ $record->treatment ?? '-' }}</span>
                        </div>
                        <div class="record-row">
                            <strong>Appointment Date</strong>
                            <span>{{ $record->appointment_date ? $record->appointment_date->format('d-m-Y') : '-' }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="empty-state">No clinic records found.</div>
    @endif
</div>
@endsection
