@extends('layouts.student')

@section('title', 'Counselling Support')
@section('portal_label', 'Student Counselling Portal')
@section('show_logout_action', '0')

@push('styles')
<style>
    .summary-grid,
    .panel-card,
    .stat-card,
    .session-card {
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 20px 55px rgba(15, 23, 42, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 251, 248, 0.98));
        color: var(--text-main);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 1.25rem;
        background: transparent;
        border: 0;
        box-shadow: none;
    }

    .panel-card,
    .stat-card {
        padding: 1.5rem;
    }

    .panel-card h2,
    .panel-card h4 {
        margin: 0 0 0.65rem;
    }

    .panel-copy,
    .stat-note,
    .safe-box p,
    .session-meta,
    .session-row span {
        color: var(--text-soft);
        line-height: 1.65;
    }

    .action-row {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
    }

    .action-button,
    .submit-button {
        border: 0;
        border-radius: 999px;
        padding: 0.95rem 1.2rem;
        font-weight: 700;
        color: #fff;
        cursor: pointer;
    }

    .action-button {
        min-width: 190px;
        background: linear-gradient(135deg, #0f6f49 0%, #0c5a3a 100%);
    }

    .action-button.is-alt {
        background: linear-gradient(135deg, #164e63 0%, #0f3f52 100%);
    }

    .action-button:not(.is-active) {
        opacity: 0.8;
    }

    .stat-card {
        display: grid;
        gap: 1rem;
        background: linear-gradient(135deg, rgba(22, 163, 74, 0.14), rgba(12, 74, 110, 0.08)), #f8fbff;
    }

    .stat-label {
        display: block;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #0f5132;
        line-height: 1;
    }

    .banner {
        margin-top: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 16px;
        font-weight: 600;
    }

    .banner-success {
        background: #ddf7e7;
        color: #146c43;
    }

    .banner-error {
        background: #fee2e2;
        color: #b91c1c;
    }

    .detail-panel {
        margin-top: 1.5rem;
    }

    .is-hidden {
        display: none;
    }

    .safe-box {
        margin: 1rem 0;
        padding: 1rem 1.1rem;
        border-radius: 18px;
        background: linear-gradient(180deg, #fff7ed, #fff1dc);
        border: 1px solid rgba(249, 115, 22, 0.15);
    }

    .model-spotlight {
        margin: 1rem 0 1.25rem;
        padding: 1rem 1.1rem;
        border-radius: 18px;
        border: 1px solid rgba(15, 118, 110, 0.12);
        background: linear-gradient(180deg, #eefbf8, #f7fffd);
    }

    .model-spotlight h5 {
        margin: 0 0 0.4rem;
        color: #0f5132;
    }

    .model-meta {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-top: 0.75rem;
    }

    .model-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.1);
        color: #115e59;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .safe-box strong {
        display: block;
        margin-bottom: 0.35rem;
        color: #9a3412;
    }

    .message-board {
        min-height: 280px;
        max-height: 460px;
        overflow-y: auto;
        display: grid;
        gap: 0.85rem;
        padding: 1rem;
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fffb, #eef8f7);
    }

    .chat-message {
        max-width: 86%;
        padding: 0.9rem 1rem;
        border-radius: 18px;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    .chat-message.assistant {
        background: #ffffff;
        border: 1px solid #d9e8e3;
        color: #17324d;
    }

    .chat-message.user {
        justify-self: end;
        background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
        color: #ffffff;
    }

    .chat-message.error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .form-grid .full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-weight: 700;
        color: #17324d;
        margin-bottom: 0.45rem;
    }

    .form-control,
    .form-select {
        border-radius: 16px;
        border: 1px solid #d4dfeb;
        padding: 0.9rem 1rem;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .submit-button {
        margin-top: 1rem;
        background: linear-gradient(135deg, #0f766e 0%, #0f5132 100%);
    }

    .sessions-list {
        display: grid;
        gap: 1rem;
        margin-top: 1.25rem;
    }

    .session-card {
        padding: 1.15rem;
    }

    .session-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 0.9rem;
    }

    .session-status {
        padding: 0.4rem 0.8rem;
        border-radius: 999px;
        background: #fff0cf;
        color: #9a6700;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .session-status.status-scheduled,
    .session-status.status-approved {
        background: #ddf7e7;
        color: #146c43;
    }

    .session-status.status-attended,
    .session-status.status-completed {
        background: #dcfce7;
        color: #166534;
    }

    .session-status.status-cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .session-row {
        margin-top: 0.55rem;
    }

    .session-row strong {
        display: block;
        margin-bottom: 0.2rem;
        color: #17324d;
    }

    @media (max-width: 900px) {
        .summary-grid,
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $recordedSessions = $bookings->count();
    $pendingSessions = $bookings->where('status', 'pending')->count();
    $defaultPanel = ($errors->any() || session('success')) ? 'book-session' : 'chart-board';
    $sexOptions = ['Male', 'Female', 'Other', 'Prefer not to say'];
    $studyYearOptions = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', '6th Year', 'Postgraduate'];
@endphp

<div class="summary-grid">
    <section class="panel-card">
        <h2>Counselling Support</h2>
        <p class="panel-copy">Choose the counselling option you need today. Your student chat board gives you practical support in the moment, while the booking section lets you request a counselling session with your preferred date and time.</p>
        <div class="action-row">
            <button type="button" class="action-button {{ $defaultPanel === 'chart-board' ? 'is-active' : '' }}" data-target-panel="chart-board">Student Chat Board</button>
            <button type="button" class="action-button is-alt {{ $defaultPanel === 'book-session' ? 'is-active' : '' }}" data-target-panel="book-session">Book Session</button>
        </div>
    </section>

    <aside class="stat-card">
        <div>
            <span class="stat-label">Recorded Sessions</span>
            <div class="stat-value">{{ $recordedSessions }}</div>
        </div>
        <div>
            <span class="stat-label">Pending Sessions</span>
            <div class="stat-value">{{ $pendingSessions }}</div>
        </div>
        <p class="stat-note">Use Student Chat Board for guided support with exams, workload, relationships, money concerns, or stress, and use Book Session when you want a counsellor follow-up recorded in the database.</p>
    </aside>
</div>

@if (session('success'))
    <div class="banner banner-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="banner banner-error">{{ $errors->first() }}</div>
@endif

<section class="panel-card detail-panel {{ $defaultPanel !== 'chart-board' ? 'is-hidden' : '' }}" data-panel="chart-board">
    <h4>Student Chat Board</h4>
    <p class="panel-copy">Use this space for calm, practical support with exams, workload, relationships, money concerns, or feeling overwhelmed.</p>

    <div class="safe-box">
        <strong>If You Feel Unsafe</strong>
        <p>If you may act on thoughts of harming yourself or someone else, or you are unsafe right now, contact campus security, local emergency services, the clinic, or someone you trust immediately. This chat can support planning, but it cannot replace urgent in-person help.</p>
    </div>

    <div class="model-spotlight">
        <h5>Student support assistant</h5>
        <p class="panel-copy mb-0">The support assistant is available for continuing students in the Student Chat Board.</p>
        <div class="model-meta">
            <span class="model-pill" id="active-emergency-model-label">{{ $emergencyChatMeta['model_label'] }}</span>
            <span class="model-pill" id="active-emergency-model-provider">{{ $emergencyChatMeta['model_provider'] }}</span>
        </div>
        <p class="panel-copy mb-0 mt-3" id="active-emergency-model-description">{{ $emergencyChatMeta['model_description'] }}</p>
    </div>

    <div class="message-board" id="emergency-chat-board" aria-live="polite">
        <div class="chat-message assistant">Hi. I am here to help you think this through. Tell me what feels hardest right now: exams, workload, relationships, money, or just feeling overwhelmed.</div>
    </div>

    <form id="emergency-chat-form" class="mt-3">
        <div class="mb-3">
            <label for="emergency-message" class="form-label">Student support message</label>
            <textarea id="emergency-message" class="form-control" rows="5" maxlength="2000" placeholder="Example: I feel stressed about my final exams and I need help making a study plan." required></textarea>
        </div>
        <button type="submit" class="submit-button" id="emergency-send-button">Send message</button>
    </form>
</section>

<section class="panel-card detail-panel {{ $defaultPanel !== 'book-session' ? 'is-hidden' : '' }}" data-panel="book-session">
    <h4>Book Session</h4>
    <p class="panel-copy">Fill in the form below to book a counselling session. When you submit it, the record is stored in the database through the existing counselling booking route.</p>

    <form action="{{ route('counselling.bookings.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div>
                <label for="sex" class="form-label">Sex</label>
                <select name="sex" id="sex" class="form-select" required>
                    <option value="">Select sex</option>
                    @foreach ($sexOptions as $sexOption)
                        <option value="{{ $sexOption }}" {{ old('sex') === $sexOption ? 'selected' : '' }}>{{ $sexOption }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="programme" class="form-label">Program</label>
                <input type="text" name="programme" id="programme" class="form-control" maxlength="255" value="{{ old('programme') }}" required>
            </div>

            <div>
                <label for="year_of_study" class="form-label">Year of study</label>
                <select name="year_of_study" id="year_of_study" class="form-select" required>
                    <option value="">Select year of study</option>
                    @foreach ($studyYearOptions as $studyYearOption)
                        <option value="{{ $studyYearOption }}" {{ old('year_of_study') === $studyYearOption ? 'selected' : '' }}>{{ $studyYearOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="full-width">
                <strong>Select a Date &amp; Time</strong>
                <p class="panel-copy mb-0">Choose your preferred counselling date and time below.</p>
            </div>

            <div>
                <label for="preferred_date" class="form-label">Preferred date</label>
                <input type="date" name="preferred_date" id="preferred_date" class="form-control" min="{{ now()->toDateString() }}" value="{{ old('preferred_date') }}" required>
            </div>

            <div>
                <label for="preferred_time" class="form-label">Preferred time</label>
                <input type="time" name="preferred_time" id="preferred_time" class="form-control" value="{{ old('preferred_time') }}" required>
            </div>

            <div class="full-width">
                <label for="reason" class="form-label">Cause of counselling</label>
                <textarea name="reason" id="reason" class="form-control" rows="4" maxlength="1000" placeholder="Briefly explain the cause or reason for this counselling request." required>{{ old('reason') }}</textarea>
            </div>
        </div>

        <button type="submit" class="submit-button">Book Counselling Session</button>
    </form>

    <div class="sessions-list">
        @forelse ($bookings as $booking)
            @php
                $statusKey = match (strtolower((string) $booking->status)) {
                    'approved' => 'scheduled',
                    'completed' => 'attended',
                    default => strtolower((string) $booking->status),
                };
            @endphp
            <article class="session-card">
                <div class="session-head">
                    <div>
                        <strong>{{ $booking->student_name }}</strong>
                        <div class="session-meta">Requested {{ $booking->created_at ? $booking->created_at->format('d-m-Y h:i A') : '-' }}</div>
                    </div>
                    <span class="session-status status-{{ $statusKey }}">{{ ucfirst($statusKey) }}</span>
                </div>

                <div class="session-row">
                    <strong>Preferred session</strong>
                    <span>{{ $booking->preferred_date ? $booking->preferred_date->format('d-m-Y') : '-' }} @if ($booking->preferred_time) at {{ $booking->preferred_time }} @endif</span>
                </div>
                <div class="session-row">
                    <strong>Program</strong>
                    <span>{{ $booking->programme ?: '-' }}</span>
                </div>
                <div class="session-row">
                    <strong>Cause</strong>
                    <span>{{ $booking->reason }}</span>
                </div>
            </article>
        @empty
            <div class="session-card">
                <strong>No booked counselling sessions yet.</strong>
            </div>
        @endforelse
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = Array.from(document.querySelectorAll('[data-target-panel]'));
        const panels = Array.from(document.querySelectorAll('[data-panel]'));

        const activatePanel = function (panelName) {
            buttons.forEach(function (button) {
                button.classList.toggle('is-active', button.dataset.targetPanel === panelName);
            });

            panels.forEach(function (panel) {
                panel.classList.toggle('is-hidden', panel.dataset.panel !== panelName);
            });
        };

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                activatePanel(button.dataset.targetPanel);
            });
        });

        activatePanel('{{ $defaultPanel }}');

        const chatBoard = document.getElementById('emergency-chat-board');
        const chatForm = document.getElementById('emergency-chat-form');
        const chatInput = document.getElementById('emergency-message');
        const sendButton = document.getElementById('emergency-send-button');
        const modelLabel = document.getElementById('active-emergency-model-label');
        const modelProvider = document.getElementById('active-emergency-model-provider');
        const modelDescription = document.getElementById('active-emergency-model-description');
        const emergencyReplyUrl = @json(route('counselling.emergency.reply', [], false));
        const conversation = [
            {
                role: 'assistant',
                content: 'Hi. I am here to help you think this through. Tell me what feels hardest right now: exams, workload, relationships, money, or just feeling overwhelmed.'
            }
        ];

        const appendMessage = function (role, content, extraClass) {
            if (!chatBoard) {
                return;
            }

            const message = document.createElement('div');
            message.className = 'chat-message ' + role + (extraClass ? ' ' + extraClass : '');
            message.textContent = content;
            chatBoard.appendChild(message);
            chatBoard.scrollTop = chatBoard.scrollHeight;
        };

        if (chatForm && chatInput && sendButton) {
            chatForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const message = chatInput.value.trim();
                if (!message) {
                    return;
                }

                appendMessage('user', message);
                conversation.push({ role: 'user', content: message });
                chatInput.value = '';
                sendButton.disabled = true;
                sendButton.textContent = 'Sending...';

                try {
                    const response = await fetch(emergencyReplyUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            message: message,
                            messages: conversation.slice(-8, -1),
                        }),
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const data = contentType.includes('application/json')
                        ? await response.json()
                        : { message: 'Student support chat is unavailable right now. Please refresh and try again.' };

                    if (!response.ok) {
                        throw new Error(data.message || 'Student support chat is unavailable right now.');
                    }

                    if (modelLabel && data.model_label) {
                        modelLabel.textContent = data.model_label;
                    }

                    if (modelProvider && data.model_provider) {
                        modelProvider.textContent = data.model_provider;
                    }

                    if (modelDescription && data.model_description) {
                        modelDescription.textContent = data.model_description;
                    }

                    appendMessage('assistant', data.reply);
                    conversation.push({ role: 'assistant', content: data.reply });
                } catch (error) {
                    appendMessage('error', error.message || 'Student support chat is unavailable right now.', 'error');
                } finally {
                    sendButton.disabled = false;
                    sendButton.textContent = 'Send message';
                    chatInput.focus();
                }
            });
        }
    });
</script>
@endsection
