<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SSD Accommodation Form</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --ink: #0f172a;
    --muted: #5b6b82;
    --line: #d8dee8;
    --line-strong: #b8c3d6;
    --panel: rgba(255, 255, 255, 0.92);
    --panel-soft: rgba(247, 249, 252, 0.94);
    --accent: #0f766e;
    --accent-deep: #134e4a;
    --accent-soft: #ccfbf1;
    --highlight: #f59e0b;
    --warning-soft: #fff7ed;
    --danger-soft: #fef2f2;
    --success-soft: #ecfdf5;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Manrope', 'Segoe UI', sans-serif;
    color: var(--ink);
    background:
        radial-gradient(circle at top left, rgba(15, 118, 110, 0.2), transparent 30%),
        radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.2), transparent 28%),
        linear-gradient(135deg, #0b1324 0%, #111827 45%, #1f2937 100%);
}

.page-shell {
    max-width: 1320px;
    margin: 0 auto;
    padding: 28px 20px 36px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

.topbar-link {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.8rem 1.15rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(15, 23, 42, 0.35);
    color: #f8fafc;
    text-decoration: none;
    font-weight: 700;
    backdrop-filter: blur(14px);
}

.hero {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(280px, 0.95fr);
    gap: 22px;
    align-items: start;
}

.paper-form,
.guide-shell {
    background: var(--panel);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 28px;
    box-shadow: 0 26px 60px rgba(15, 23, 42, 0.28);
    backdrop-filter: blur(16px);
}

.paper-form {
    overflow: hidden;
}

.form-head {
    padding: 28px 30px 22px;
    background:
        linear-gradient(135deg, rgba(15, 118, 110, 0.12), rgba(255, 255, 255, 0.4)),
        linear-gradient(180deg, #f8fafc 0%, #eef5f4 100%);
    border-bottom: 1px solid var(--line);
}

.form-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    background: var(--accent-soft);
    color: var(--accent-deep);
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.form-title {
    margin: 1rem 0 0.4rem;
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    line-height: 1.08;
}

.form-subtitle {
    margin: 0;
    max-width: 62ch;
    color: var(--muted);
    line-height: 1.7;
    font-size: 0.98rem;
}

.alert-stack {
    padding: 20px 30px 0;
    display: grid;
    gap: 0.85rem;
}

.alert-box {
    padding: 0.95rem 1rem;
    border-radius: 18px;
    border: 1px solid transparent;
    font-size: 0.95rem;
    line-height: 1.6;
}

.alert-box.success {
    background: var(--success-soft);
    border-color: #bbf7d0;
    color: #166534;
}

.alert-box.error {
    background: var(--danger-soft);
    border-color: #fecaca;
    color: #991b1b;
}

.form-body {
    padding: 24px 30px 30px;
}

.form-section + .form-section {
    margin-top: 24px;
}

.section-card {
    background: var(--panel-soft);
    border: 1px solid var(--line);
    border-radius: 24px;
    padding: 22px 20px;
}

.section-header {
    margin-bottom: 16px;
}

.section-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.15rem;
    height: 2.15rem;
    margin-bottom: 0.85rem;
    border-radius: 999px;
    background: #e2e8f0;
    color: #1e293b;
    font-size: 0.82rem;
    font-weight: 800;
}

.section-title {
    margin: 0 0 0.35rem;
    font-size: 1.2rem;
}

.section-copy {
    margin: 0;
    color: var(--muted);
    font-size: 0.93rem;
    line-height: 1.65;
}

.field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.field {
    min-width: 0;
}

.field.full {
    grid-column: 1 / -1;
}

.field label,
.field .field-label {
    display: block;
    margin-bottom: 0.45rem;
    font-size: 0.79rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #334155;
}

.field-note {
    margin-top: 0.45rem;
    color: var(--muted);
    font-size: 0.83rem;
    line-height: 1.55;
}

input,
select,
textarea {
    width: 100%;
    border: 1px solid var(--line-strong);
    border-radius: 16px;
    padding: 0.9rem 0.95rem;
    background: #fff;
    color: var(--ink);
    font: inherit;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}

textarea {
    resize: vertical;
    min-height: 120px;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.12);
}

.choice-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
}

.choice-pill {
    position: relative;
}

.choice-pill input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.choice-pill span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 52px;
    border-radius: 16px;
    border: 1px solid var(--line-strong);
    background: #fff;
    color: #334155;
    font-weight: 700;
    text-align: center;
    padding: 0.8rem 0.9rem;
    transition: all 0.18s ease;
}

.choice-pill input:checked + span {
    border-color: var(--accent);
    background: var(--accent-soft);
    color: var(--accent-deep);
    box-shadow: 0 12px 24px rgba(15, 118, 110, 0.12);
}

.conditional-field[hidden] {
    display: none !important;
}

.payment-card {
    background: linear-gradient(135deg, rgba(15, 118, 110, 0.08), rgba(245, 158, 11, 0.08));
}

.fee-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    margin-top: 1rem;
    padding: 0.85rem 1rem;
    border-radius: 18px;
    background: #fff;
    border: 1px solid rgba(15, 118, 110, 0.16);
    color: var(--accent-deep);
    font-weight: 800;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 26px;
}

.actions-copy {
    color: var(--muted);
    font-size: 0.92rem;
    line-height: 1.6;
    max-width: 42ch;
}

.submit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    border: none;
    border-radius: 999px;
    padding: 1rem 1.4rem;
    min-width: 240px;
    background: linear-gradient(135deg, var(--accent) 0%, #0f766e 50%, #115e59 100%);
    color: #f8fafc;
    font: inherit;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 16px 30px rgba(15, 118, 110, 0.26);
}

.submit-btn:hover {
    transform: translateY(-1px);
}

.guide-shell {
    padding: 22px;
    display: grid;
    gap: 16px;
    align-self: start;
    position: sticky;
    top: 18px;
}

.guide-card {
    padding: 18px;
    border-radius: 22px;
    background: #f8fafc;
    border: 1px solid var(--line);
}

.guide-card h3,
.guide-card h4 {
    margin: 0 0 0.65rem;
}

.guide-card p {
    margin: 0;
    color: var(--muted);
    line-height: 1.65;
    font-size: 0.92rem;
}

.guide-card ul {
    margin: 0;
    padding-left: 1.1rem;
    color: #334155;
    line-height: 1.7;
    font-size: 0.92rem;
}

.guide-card li + li {
    margin-top: 0.35rem;
}

.guide-highlight {
    background: linear-gradient(135deg, rgba(15, 118, 110, 0.12), rgba(255, 255, 255, 0.92));
}

.meta-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    margin-top: 1rem;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 0.8rem;
    border-radius: 999px;
    background: #fff;
    border: 1px solid var(--line);
    color: #1e293b;
    font-size: 0.82rem;
    font-weight: 700;
}

.guide-alert {
    background: var(--warning-soft);
    border-color: #fed7aa;
}

.guide-alert p {
    color: #9a3412;
}

@media (max-width: 1040px) {
    .hero {
        grid-template-columns: 1fr;
    }

    .guide-shell {
        position: static;
    }
}

@media (max-width: 720px) {
    .page-shell {
        padding-inline: 14px;
    }

    .form-head,
    .form-body,
    .alert-stack {
        padding-left: 18px;
        padding-right: 18px;
    }

    .section-card {
        padding: 18px 16px;
    }

    .field-grid {
        grid-template-columns: 1fr;
    }

    .actions {
        align-items: stretch;
    }

    .submit-btn {
        width: 100%;
    }
}
</style>
</head>

<body>
@php
    $limkokwingFaculties = config('limkokwing.faculties', []);
    $maritalStatusOptions = ['Single', 'Married', 'Divorced', 'Widowed', 'Other'];
    $semesterOptions = ['S1', 'S2', 'Other'];
    $nationalityOptions = ['Mosotho', 'South African', 'Zimbabwean', 'Motswana', 'Swati'];
    $selectedNationality = old('nationality', 'Mosotho');
    $isOtherNationality = $selectedNationality !== '' && ($selectedNationality === 'Other' || ! in_array($selectedNationality, $nationalityOptions, true));
    $otherNationality = old('nationality_other', $isOtherNationality && $selectedNationality !== 'Other' ? $selectedNationality : '');
@endphp

<div class="page-shell">
    <div class="topbar">
        <a href="{{ route('accommodation') }}" class="topbar-link">&larr; Back to Accommodation</a>
        <a href="{{ route('home') }}" class="topbar-link">Home Dashboard</a>
    </div>

    <div class="hero">
        <section class="paper-form">
            <div class="form-head">
                <div class="form-kicker">SSD003 Student Service Department</div>
                <h1 class="form-title">Student Accommodation Application Form</h1>
            </div>

            @if (session('success') || $errors->any())
                <div class="alert-stack">
                    @if (session('success'))
                        <div class="alert-box success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-box error">
                            <strong>Please check the hostel form details below.</strong>
                            <ul style="margin: 0.7rem 0 0; padding-left: 1.1rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            <div class="form-body">
                <form method="POST" action="{{ route('student.accommodation.store') }}">
                    @csrf

                    <section class="form-section">
                        <div class="section-card">
                            <div class="section-header">
                                <div class="section-label">01</div>
                                <h2 class="section-title">Student Personal Information</h2>
                            </div>

                            <div class="field-grid">
                                <div class="field">
                                    <label for="full_name">Name(s) as in passport</label>
                                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $user->name) }}" required>
                                </div>

                                <div class="field">
                                    <label for="student_id">Student ID</label>
                                    <input id="student_id" type="text" name="student_id" value="{{ old('student_id', $user->student_id ?? '') }}" placeholder="901015587" inputmode="numeric" pattern="[0-9]+" title="Student ID must contain numbers only" required>
                                </div>

                                <div class="field">
                                    <label for="contact_number">Contact No</label>
                                    <input id="contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" required>
                                </div>

                                <div class="field">
                                    <label for="national_id">National ID / Passport</label>
                                    <input id="national_id" type="text" name="national_id" value="{{ old('national_id', $user->id_number ?? '') }}" required>
                                </div>

                                <div class="field">
                                    <label for="email">Email Address</label>
                                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>

                                <div class="field">
                                    <label for="marital_status">Marital Status</label>
                                    <select id="marital_status" name="marital_status" required>
                                        <option value="">Select marital status</option>
                                        @foreach ($maritalStatusOptions as $statusOption)
                                        <option value="{{ $statusOption }}"{{ old('marital_status') === $statusOption ? ' selected' : '' }}>{{ $statusOption }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="nationality">Nationality</label>
                                    <select id="nationality" name="nationality" required>
                                        <option value="">Select nationality</option>
                                        @foreach ($nationalityOptions as $nationalityOption)
                                            <option value="{{ $nationalityOption }}"{{ $selectedNationality === $nationalityOption ? ' selected' : '' }}>{{ $nationalityOption }}</option>
                                        @endforeach
                                        <option value="Other"{{ $isOtherNationality ? ' selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div class="field" data-nationality-other-wrapper{{ $isOtherNationality ? '' : ' hidden' }}>
                                    <label for="nationality_other">Write your nationality</label>
                                    <input id="nationality_other" type="text" name="nationality_other" value="{{ $otherNationality }}"{{ $isOtherNationality ? ' required' : '' }}>
                                </div>

                                <div class="field">
                                    <span class="field-label">Gender</span>
                                    <div class="choice-grid">
                                        <label class="choice-pill">
                                            <input type="radio" name="gender" value="female" checked required>
                                            <span>Female</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="age">Age</label>
                                    <input id="age" type="number" name="age" min="16" max="120" value="{{ old('age') }}" required>
                                </div>

                                <div class="field">
                                    <label for="intake">Intake</label>
                                    <input id="intake" type="text" name="intake" value="{{ old('intake') }}" placeholder="e.g. August 2026 Intake" required>
                                </div>

                                <div class="field">
                                    <label for="semester">Semester</label>
                                    <select id="semester" name="semester" required>
                                        <option value="">Select semester</option>
                                        @foreach ($semesterOptions as $semesterOption)
                                            <option value="{{ $semesterOption }}"{{ old('semester') === $semesterOption ? ' selected' : '' }}>{{ $semesterOption }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="faculty">Faculty</label>
                                    <select id="faculty" name="faculty" data-current-value="{{ old('faculty') }}" required>
                                        <option value="">Select Faculty</option>
                                        @foreach ($limkokwingFaculties as $facultyKey => $faculty)
                                            <option value="{{ $faculty['label'] }}" data-faculty-key="{{ $facultyKey }}">{{ $faculty['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="programme">Programme</label>
                                    <select id="programme" name="programme" data-current-value="{{ old('programme') }}" required>
                                        <option value="">Select Programme</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="check_in_date">Check-In Date</label>
                                    <input id="check_in_date" type="date" name="check_in_date" value="{{ old('check_in_date') }}" required>
                                </div>

                                <div class="field">
                                    <label for="district">District</label>
                                    <input id="district" type="text" name="district" value="{{ old('district') }}" required>
                                </div>

                                <div class="field">
                                    <label for="village">Village</label>
                                    <input id="village" type="text" name="village" value="{{ old('village') }}" required>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="form-section">
                        <div class="section-card">
                            <div class="section-header">
                                <div class="section-label">02</div>
                                <h2 class="section-title">Next Of Kin</h2>
                            </div>

                            <div class="field-grid">
                                <div class="field">
                                    <label for="next_of_kin_name">Name(s)</label>
                                    <input id="next_of_kin_name" type="text" name="next_of_kin_name" value="{{ old('next_of_kin_name') }}" required>
                                </div>

                                <div class="field">
                                    <label for="next_of_kin_relationship">Relationship</label>
                                    <input id="next_of_kin_relationship" type="text" name="next_of_kin_relationship" value="{{ old('next_of_kin_relationship') }}" required>
                                </div>

                                <div class="field">
                                    <label for="next_of_kin_contact">Other Contacts</label>
                                    <input id="next_of_kin_contact" type="text" name="next_of_kin_contact" value="{{ old('next_of_kin_contact') }}" placeholder="Phone number or alternate contact" required>
                                </div>

                                <div class="field full">
                                    <label for="special_conditions_remark">Remark</label>
                                    <textarea id="special_conditions_remark" name="special_conditions_remark" rows="4" placeholder="Any other condition with direct bearing on communal living: allergies, epilepsy, twin status, orphan status, strong herbal use, or related notes.">{{ old('special_conditions_remark') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="form-section">
                        <div class="section-card">
                            <div class="section-header">
                                <div class="section-label">03</div>
                                <h2 class="section-title">Student Health Information</h2>
                                <p class="section-copy">This section mirrors the paper form so SSD can identify any health or support needs early in the accommodation process.</p>
                            </div>

                            <div class="field-grid">
                                <div class="field full">
                                    <span class="field-label">Do you have any physical disability?</span>
                                    <div class="choice-grid">
                                        <label class="choice-pill">
                                            <input type="radio" name="has_physical_disability" value="1" data-toggle-target="physical_disability_details"{{ old('has_physical_disability') === '1' ? ' checked' : '' }} required>
                                            <span>Yes</span>
                                        </label>
                                        <label class="choice-pill">
                                            <input type="radio" name="has_physical_disability" value="0" data-toggle-target="physical_disability_details"{{ old('has_physical_disability') === '0' ? ' checked' : '' }} required>
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="field full conditional-field" data-conditional-wrapper="physical_disability_details"{{ old('has_physical_disability') === '1' ? '' : ' hidden' }}>
                                    <label for="physical_disability_details">If yes, please specify</label>
                                    <textarea id="physical_disability_details" name="physical_disability_details" rows="3" data-conditional-input="physical_disability_details">{{ old('physical_disability_details') }}</textarea>
                                </div>

                                <div class="field">
                                    <span class="field-label">High blood pressure</span>
                                    <div class="choice-grid">
                                        <label class="choice-pill">
                                            <input type="radio" name="has_high_blood_pressure" value="1"{{ old('has_high_blood_pressure') === '1' ? ' checked' : '' }} required>
                                            <span>Yes</span>
                                        </label>
                                        <label class="choice-pill">
                                            <input type="radio" name="has_high_blood_pressure" value="0"{{ old('has_high_blood_pressure') === '0' ? ' checked' : '' }} required>
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="field">
                                    <span class="field-label">Diabetes</span>
                                    <div class="choice-grid">
                                        <label class="choice-pill">
                                            <input type="radio" name="has_diabetes" value="1"{{ old('has_diabetes') === '1' ? ' checked' : '' }} required>
                                            <span>Yes</span>
                                        </label>
                                        <label class="choice-pill">
                                            <input type="radio" name="has_diabetes" value="0"{{ old('has_diabetes') === '0' ? ' checked' : '' }} required>
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="field">
                                    <span class="field-label">Asthma</span>
                                    <div class="choice-grid">
                                        <label class="choice-pill">
                                            <input type="radio" name="has_asthma" value="1"{{ old('has_asthma') === '1' ? ' checked' : '' }} required>
                                            <span>Yes</span>
                                        </label>
                                        <label class="choice-pill">
                                            <input type="radio" name="has_asthma" value="0"{{ old('has_asthma') === '0' ? ' checked' : '' }} required>
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="chronic_illness_other">Other chronic illness</label>
                                    <input id="chronic_illness_other" type="text" name="chronic_illness_other" value="{{ old('chronic_illness_other') }}" placeholder="Optional">
                                </div>

                                <div class="field full">
                                    <span class="field-label">Are you on treatment for any chronic illness?</span>
                                    <div class="choice-grid">
                                        <label class="choice-pill">
                                            <input type="radio" name="on_chronic_treatment" value="1" data-toggle-target="treatment_frequency"{{ old('on_chronic_treatment') === '1' ? ' checked' : '' }} required>
                                            <span>Yes</span>
                                        </label>
                                        <label class="choice-pill">
                                            <input type="radio" name="on_chronic_treatment" value="0" data-toggle-target="treatment_frequency"{{ old('on_chronic_treatment') === '0' ? ' checked' : '' }} required>
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="field full conditional-field" data-conditional-wrapper="treatment_frequency"{{ old('on_chronic_treatment') === '1' ? '' : ' hidden' }}>
                                    <label for="treatment_frequency">If yes, how often do you seek treatment?</label>
                                    <textarea id="treatment_frequency" name="treatment_frequency" rows="3" data-conditional-input="treatment_frequency">{{ old('treatment_frequency') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="actions">
                        <div class="actions-copy">
                            Student accommodation allocation remains subject to SSD priority criteria and room availability. Submitting this form sends the application for accommodation review.
                        </div>
                        <button type="submit" class="submit-btn">Submit Hostel Application</button>
                    </div>
                </form>
            </div>
        </section>

        <aside class="guide-shell">
            <section class="guide-card guide-highlight">
                <h3>Form Guide</h3>
                <div class="meta-badges">
                    <span class="meta-badge">Campus: Lesotho</span>
                    <span class="meta-badge">Student type: New student</span>
                    <span class="meta-badge">Review flow: SSD accommodation</span>
                </div>
            </section>

            <section class="guide-card">
                <h4>Hostel Items Available</h4>
                <ul>
                    <li>Shared bedrooms with 4 students per room</li>
                    <li>Shared bathrooms</li>
                    <li>Bed frame without mattress</li>
                    <li>Shared kitchen and common room</li>
                    <li>Study hall</li>
                    <li>SSD toll-free helpline: 800 22077</li>
                </ul>
            </section>

            <section class="guide-card">
                <h4>Items To Bring</h4>
                <ul>
                    <li>Own bedding: mattress, blankets, sheets, and pillow</li>
                    <li>Own utensils: pots, cutlery, electric kettle, iron, bath basin, and bucket</li>
                    <li>Own food</li>
                    <li>Own heater approved for hostel use</li>
                    <li>Health booklet, broom, and one window curtain</li>
                    <li>M105.00 non-refundable application fee (M-Pesa or EcoCash)</li>
                    <li>M500.00 monthly rental at Standard Lesotho Bank (9 0 8 0 0 0 3 9 8 7 8 1 3), payable upon admission or before occupancy</li>
                    <li>Please submit all rental receipts to the Finance Department during registration</li>
                    <li>Hostel is located within campus, so there is no need for taxi fare</li>
                </ul>
            </section>

            <section class="guide-card guide-alert">
                <h4>Admission Note</h4>
                <p>The paper form states that accommodation allocation is based on priority criteria and room availability. Submission here does not guarantee placement until SSD completes the admission review.</p>
            </section>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const faculties = @json($limkokwingFaculties);
    const facultyDropdown = document.getElementById('faculty');
    const programmeDropdown = document.getElementById('programme');
    const nationalityDropdown = document.getElementById('nationality');
    const otherNationalityWrapper = document.querySelector('[data-nationality-other-wrapper]');
    const otherNationalityInput = document.getElementById('nationality_other');

    const normalizeValue = (value) => (value || '').toString().trim().toLowerCase();

    const optionExists = (field, value) => Array.from(field.options).some((option) => normalizeValue(option.value) === normalizeValue(value));

    const appendOption = (field, value, label, selected = false) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        option.selected = selected;
        field.appendChild(option);
    };

    const ensureOption = (field, value) => {
        if (!value || optionExists(field, value)) {
            return;
        }

        appendOption(field, value, value);
    };

    const findFacultyEntry = (value) => {
        const normalizedValue = normalizeValue(value);

        if (!normalizedValue) {
            return null;
        }

        return Object.entries(faculties).find(([facultyKey, faculty]) => {
            const aliases = [facultyKey, faculty.label].concat(faculty.aliases || []);

            return aliases.some((alias) => normalizeValue(alias) === normalizedValue);
        }) || null;
    };

    const syncFacultySelection = (value) => {
        const facultyEntry = findFacultyEntry(value);
        const facultyValue = facultyEntry ? facultyEntry[1].label : (value || '');

        ensureOption(facultyDropdown, facultyValue);
        facultyDropdown.value = facultyValue;

        return facultyValue;
    };

    const populateProgrammes = (facultyValue, selectedProgramme = null) => {
        const facultyEntry = findFacultyEntry(facultyValue);
        const programmes = facultyEntry ? facultyEntry[1].programmes || [] : [];
        const programmeValue = selectedProgramme === null
            ? (programmeDropdown.dataset.currentValue || programmeDropdown.value || '')
            : (selectedProgramme || '');

        programmeDropdown.innerHTML = '<option value="">Select Programme</option>';

        programmes.forEach((programme) => {
            appendOption(programmeDropdown, programme, programme, normalizeValue(programme) === normalizeValue(programmeValue));
        });

        ensureOption(programmeDropdown, programmeValue);

        if (programmeValue) {
            programmeDropdown.value = programmeValue;
        }
    };

    const initialFaculty = facultyDropdown.dataset.currentValue || facultyDropdown.value;
    const initialProgramme = programmeDropdown.dataset.currentValue || programmeDropdown.value;

    syncFacultySelection(initialFaculty);
    populateProgrammes(initialFaculty, initialProgramme);

    facultyDropdown.addEventListener('change', function () {
        programmeDropdown.dataset.currentValue = '';
        populateProgrammes(this.value, '');
    });

    const syncNationalityOtherField = () => {
        if (!nationalityDropdown || !otherNationalityWrapper || !otherNationalityInput) {
            return;
        }

        const shouldShow = nationalityDropdown.value === 'Other';
        otherNationalityWrapper.hidden = !shouldShow;
        otherNationalityInput.required = shouldShow;

        if (!shouldShow) {
            otherNationalityInput.value = '';
        }
    };

    if (nationalityDropdown) {
        nationalityDropdown.addEventListener('change', syncNationalityOtherField);
        syncNationalityOtherField();
    }

    const toggleConditionalField = (targetName, shouldShow) => {
        const wrapper = document.querySelector(`[data-conditional-wrapper="${targetName}"]`);
        const input = document.querySelector(`[data-conditional-input="${targetName}"]`);

        if (!wrapper || !input) {
            return;
        }

        wrapper.hidden = !shouldShow;
        input.required = shouldShow;

        if (!shouldShow) {
            input.value = '';
        }
    };

    const toggleTargets = new Set(
        Array.from(document.querySelectorAll('[data-toggle-target]')).map((radio) => radio.dataset.toggleTarget)
    );

    toggleTargets.forEach((targetName) => {
        const radios = Array.from(document.querySelectorAll(`[data-toggle-target="${targetName}"]`));
        const sync = () => {
            const checkedRadio = radios.find((radio) => radio.checked);
            toggleConditionalField(targetName, checkedRadio ? checkedRadio.value === '1' : false);
        };

        radios.forEach((radio) => radio.addEventListener('change', sync));
        sync();
    });
});
</script>

</body>
</html>
