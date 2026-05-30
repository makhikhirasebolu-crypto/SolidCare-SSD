<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Dashboard - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" crossorigin="anonymous">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #0f172a;
                color: #e5e7eb;
            }

            .page-header {
                padding: 2rem 1rem 1rem;
                text-align: center;
            }

            .page-header h1 {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
            }

            .page-header p {
                color: #cbd5e1;
                font-size: 1rem;
            }

            .services-section {
                padding: 2rem 0 4rem;
            }

            .card {
                background: #111827;
                border-radius: 0.5rem;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
                color: #e5e7eb;
            }

            .card-body {
                min-height: 180px;
            }

            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }

            .report-table th {
                background: #020617;
                color: #f8fafc;
                font-size: 0.78rem;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .report-table {
                border-color: #334155;
            }

            .report-table tbody tr {
                border-color: #334155;
            }

            .report-table tbody tr:hover {
                background: #1e293b;
            }

            .report-table td {
                color: #f8fafc;
                font-weight: 500;
            }

            .report-panel {
                background: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 0.75rem;
                overflow: hidden;
            }

            .report-panel .report-table {
                margin-bottom: 0;
            }

            .report-panel .report-table th {
                background: #e2e8f0;
                color: #0f172a;
            }

            .report-panel .report-table td {
                color: #0f172a;
            }

            .report-panel .report-table tbody tr:hover {
                background: #e0f2fe;
            }

            .role-badge {
                background: #e0f2fe;
                border-radius: 999px;
                color: #075985;
                display: inline-block;
                font-size: 0.82rem;
                font-weight: 700;
                padding: 0.35rem 0.7rem;
            }

            .form-label {
                color: #e5e7eb;
                font-weight: 700;
            }

            .form-control,
            .form-select {
                background: #020617;
                border-color: #334155;
                border-radius: 0.75rem;
                color: #e5e7eb;
                padding: 0.75rem 0.875rem;
            }

            .form-control:focus,
            .form-select:focus {
                background: #020617;
                border-color: #60a5fa;
                box-shadow: 0 0 0 0.25rem rgba(96, 165, 250, 0.18);
                color: #e5e7eb;
            }

            .text-muted {
                color: #94a3b8 !important;
            }

            .directory-card {
                background:
                    radial-gradient(circle at 18% 12%, rgba(59, 130, 246, 0.18), transparent 30%),
                    radial-gradient(circle at 86% 92%, rgba(14, 165, 233, 0.14), transparent 28%),
                    rgba(15, 23, 42, 0.96);
                border: 1px solid rgba(56, 189, 248, 0.22);
                border-radius: 1.25rem;
                box-shadow: 0 28px 70px rgba(0, 0, 0, 0.48);
                overflow: hidden;
            }

            .directory-inner {
                padding: 2rem;
            }

            .directory-hero {
                align-items: flex-end;
                border-bottom: 1px solid rgba(56, 189, 248, 0.35);
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                justify-content: space-between;
                margin-bottom: 1.5rem;
                padding-bottom: 1.25rem;
            }

            .directory-title {
                color: #f8fafc;
                font-size: clamp(2rem, 5vw, 3.8rem);
                font-weight: 800;
                letter-spacing: 0;
                line-height: 1;
                margin-bottom: 0.65rem;
            }

            .directory-subtitle {
                align-items: center;
                color: #cbd5e1;
                display: flex;
                flex-wrap: wrap;
                gap: 0.55rem;
                margin-bottom: 0;
            }

            .verified-chip {
                align-items: center;
                background: rgba(59, 130, 246, 0.16);
                border: 1px solid rgba(96, 165, 250, 0.28);
                border-radius: 999px;
                color: #bfdbfe;
                display: inline-flex;
                font-size: 0.85rem;
                font-weight: 700;
                gap: 0.35rem;
                padding: 0.3rem 0.75rem;
            }

            .directory-count {
                background: linear-gradient(145deg, #1e293b, #020617);
                border: 1px solid rgba(148, 163, 184, 0.16);
                border-radius: 999px;
                min-width: 150px;
                padding: 0.75rem 1.5rem;
                text-align: center;
            }

            .directory-count strong {
                color: #7dd3fc;
                display: block;
                font-size: 2.5rem;
                font-weight: 800;
                line-height: 1;
            }

            .directory-count span {
                color: #94a3b8;
                display: block;
                font-size: 0.74rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                margin-top: 0.25rem;
                text-transform: uppercase;
            }

            .directory-table-wrap {
                overflow-x: auto;
            }

            .directory-table {
                border-collapse: separate;
                border-spacing: 0 0.75rem;
                margin-bottom: 0;
                min-width: 1040px;
                width: 100%;
            }

            .directory-table th {
                background: rgba(15, 23, 42, 0.72);
                border-bottom: 1px solid rgba(148, 163, 184, 0.18);
                color: #bfdbfe;
                font-size: 0.86rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                padding: 1rem;
                text-transform: uppercase;
            }

            .directory-table td {
                background: rgba(30, 41, 59, 0.8);
                color: #e2e8f0;
                padding: 1rem;
                vertical-align: middle;
            }

            .directory-table tbody tr td:first-child {
                border-bottom-left-radius: 1rem;
                border-top-left-radius: 1rem;
            }

            .directory-table tbody tr td:last-child {
                border-bottom-right-radius: 1rem;
                border-top-right-radius: 1rem;
            }

            .directory-table tbody tr:hover td {
                background: rgba(37, 99, 235, 0.28);
            }

            .member-name {
                align-items: center;
                display: flex;
                gap: 0.9rem;
                min-width: 220px;
            }

            .avatar {
                align-items: center;
                background: linear-gradient(145deg, #075985, #0f172a);
                border-radius: 999px;
                color: #fff;
                display: inline-flex;
                flex: 0 0 auto;
                font-weight: 800;
                height: 44px;
                justify-content: center;
                width: 44px;
            }

            .name-text {
                color: #f8fafc;
                font-size: 1.05rem;
                font-weight: 800;
            }

            .email-cell {
                color: #bfdbfe;
                font-family: Consolas, 'Courier New', monospace;
                font-size: 0.94rem;
                word-break: break-word;
            }

            .directory-role {
                align-items: center;
                border: 1px solid rgba(59, 130, 246, 0.45);
                border-radius: 999px;
                display: inline-flex;
                font-size: 0.86rem;
                font-weight: 800;
                gap: 0.4rem;
                padding: 0.45rem 0.85rem;
                white-space: nowrap;
            }

            .role-senior-nurse-officer { background: rgba(6, 182, 212, 0.16); border-color: #06b6d4; color: #a5f3fc; }
            .role-psychologist { background: rgba(168, 85, 247, 0.16); border-color: #a855f7; color: #e9d5ff; }
            .role-yearleader { background: rgba(34, 197, 94, 0.16); border-color: #22c55e; color: #bbf7d0; }
            .role-warden { background: rgba(249, 115, 22, 0.16); border-color: #f97316; color: #fed7aa; }
            .role-executive { background: rgba(236, 72, 153, 0.16); border-color: #ec4899; color: #fbcfe8; }

            .date-cell {
                color: #e2e8f0;
                white-space: nowrap;
            }

            .temporary-password-cell {
                min-width: 190px;
            }

            .temporary-password-value {
                background: rgba(2, 6, 23, 0.82);
                border: 1px solid rgba(125, 211, 252, 0.32);
                border-radius: 0.6rem;
                color: #bae6fd;
                display: inline-block;
                font-family: Consolas, 'Courier New', monospace;
                font-size: 0.9rem;
                font-weight: 800;
                padding: 0.35rem 0.55rem;
                word-break: break-word;
            }

            .temporary-password-status {
                color: #cbd5e1;
                display: block;
                font-size: 0.78rem;
                font-weight: 700;
                margin-top: 0.35rem;
            }

            .temporary-password-muted {
                color: #94a3b8;
                font-weight: 800;
            }

            .delete-member-btn {
                align-items: center;
                background: rgba(220, 38, 38, 0.16);
                border: 1px solid rgba(248, 113, 113, 0.65);
                border-radius: 999px;
                color: #fecaca;
                display: inline-flex;
                font-size: 0.84rem;
                font-weight: 800;
                gap: 0.35rem;
                padding: 0.45rem 0.85rem;
                transition: 0.18s ease;
                white-space: nowrap;
            }

            .delete-member-btn:hover {
                background: #dc2626;
                color: #fff;
            }

            .reissue-password-btn {
                align-items: center;
                background: rgba(14, 165, 233, 0.16);
                border: 1px solid rgba(125, 211, 252, 0.65);
                border-radius: 999px;
                color: #bae6fd;
                display: inline-flex;
                font-size: 0.78rem;
                font-weight: 800;
                gap: 0.35rem;
                margin-top: 0.45rem;
                padding: 0.4rem 0.7rem;
                transition: 0.18s ease;
                white-space: nowrap;
            }

            .reissue-password-btn:hover {
                background: #0284c7;
                color: #fff;
            }

            .directory-footer {
                align-items: center;
                border-top: 1px solid rgba(148, 163, 184, 0.18);
                color: #94a3b8;
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                justify-content: space-between;
                margin-top: 1.5rem;
                padding-top: 1.25rem;
            }

            .member-analysis {
                background: rgba(15, 23, 42, 0.62);
                border: 1px solid rgba(148, 163, 184, 0.18);
                border-radius: 1rem;
                margin-top: 1.25rem;
                padding: 1rem;
            }

            .member-analysis h3 {
                color: #f8fafc;
                font-size: 1rem;
                font-weight: 800;
                margin-bottom: 0.85rem;
            }

            .analysis-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .analysis-pill {
                background: rgba(59, 130, 246, 0.14);
                border: 1px solid rgba(96, 165, 250, 0.26);
                border-radius: 999px;
                color: #dbeafe;
                font-weight: 800;
                padding: 0.55rem 0.9rem;
            }

            .pulse-dot {
                background: #10b981;
                border-radius: 50%;
                box-shadow: 0 0 8px #2dd4bf;
                display: inline-block;
                height: 10px;
                margin-right: 0.45rem;
                width: 10px;
            }

            @media (max-width: 768px) {
                .directory-inner {
                    padding: 1.25rem;
                }

                .directory-count {
                    width: 100%;
                }
            }

            footer {
                text-align: center;
                padding: 1.5rem 0;
                color: #94a3b8;
            }
        </style>
        <script>
            const limkokwingFaculties = @json(config('limkokwing.faculties', []));
            const oldClassValue = @json(old('class'));
            const oldYearValue = @json(old('year'));

            function toggleRoleFields() {
                const role = document.getElementById('role');
                const yearLeaderSection = document.getElementById('yearleader-fields');

                if (!role || !yearLeaderSection) {
                    return;
                }

                yearLeaderSection.style.display = role.value === 'yearleader' ? 'block' : 'none';
            }

            function populateClassOptions() {
                const facultySelect = document.getElementById('faculty');
                const classSelect = document.getElementById('class');

                if (!facultySelect || !classSelect) {
                    return;
                }

                const selectedFaculty = Object.values(limkokwingFaculties).find(function (faculty) {
                    return faculty.label === facultySelect.value;
                });
                const facultyGroups = selectedFaculty ? [selectedFaculty] : Object.values(limkokwingFaculties);
                const selectedValue = classSelect.dataset.currentValue || oldClassValue || '';

                classSelect.innerHTML = '<option value="">Select class/programme</option>';

                facultyGroups.forEach(function (faculty) {
                    const programmes = faculty.programmes || [];
                    const optionParent = selectedFaculty ? classSelect : document.createElement('optgroup');

                    if (!selectedFaculty) {
                        optionParent.label = faculty.label;
                    }

                    programmes.forEach(function (programme) {
                        const option = document.createElement('option');
                        option.value = programme;
                        option.textContent = programme;

                        if (programme === selectedValue) {
                            option.selected = true;
                        }

                        optionParent.appendChild(option);
                    });

                    if (!selectedFaculty && programmes.length > 0) {
                        classSelect.appendChild(optionParent);
                    }
                });

                classSelect.dataset.currentValue = '';
                populateYearOptions();
            }

            function programmeYearCount(programme) {
                const normalizedProgramme = (programme || '').toLowerCase();

                if (!normalizedProgramme) {
                    return 0;
                }

                if (normalizedProgramme.includes('certificate')) {
                    return 2;
                }

                if (normalizedProgramme.includes('diploma') || normalizedProgramme.includes('associate')) {
                    return 3;
                }

                return 4;
            }

            function populateYearOptions() {
                const classSelect = document.getElementById('class');
                const yearSelect = document.getElementById('year');

                if (!classSelect || !yearSelect) {
                    return;
                }

                const selectedValue = yearSelect.dataset.currentValue || oldYearValue || '';
                const yearCount = programmeYearCount(classSelect.value) || 4;

                yearSelect.innerHTML = '<option value="">Select year</option>';

                for (let year = 1; year <= yearCount; year += 1) {
                    const option = document.createElement('option');
                    option.value = String(year);
                    option.textContent = String(year);

                    if (option.value === selectedValue) {
                        option.selected = true;
                    }

                    yearSelect.appendChild(option);
                }

                yearSelect.dataset.currentValue = '';
            }

            window.addEventListener('DOMContentLoaded', function () {
                const role = document.getElementById('role');
                const faculty = document.getElementById('faculty');
                const classSelect = document.getElementById('class');

                if (role) {
                    role.addEventListener('change', toggleRoleFields);
                    toggleRoleFields();
                }

                if (faculty) {
                    faculty.addEventListener('change', populateClassOptions);
                    populateClassOptions();
                }

                if (classSelect) {
                    classSelect.addEventListener('change', populateYearOptions);
                }
            });
        </script>
    </head>
    <body>
        @php
            $roleLabels = [
                'executive' => 'Executive',
                'ssd_assistant_1' => 'SSD Assistant 1',
                'ssd_assistant_2' => 'SSD Assistant 2',
                'psychologist' => 'Psychologist',
                'senior_nurse_officer' => 'Senior Nurse Officer',
                'warden' => 'Warden',
                'yearleader' => 'Year Leader',
                'admin' => 'System Admin',
            ];
            $roleIcons = [
                'admin' => 'fa-user-shield',
                'executive' => 'fa-crown',
                'psychologist' => 'fa-brain',
                'senior_nurse_officer' => 'fa-user-md',
                'warden' => 'fa-shield-alt',
                'yearleader' => 'fa-chalkboard-teacher',
            ];
        @endphp

        <div class="page-header">
            <h1>Admin Dashboard</h1>
            <p>Signed in as admin. Manage staff users and review added members.</p>
            <button type="button" class="btn btn-outline-secondary btn-custom mt-3" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </button>
        </div>

        <div class="container services-section">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <h4 class="text-primary">Create User</h4>
                            <p>Add staff users with a temporary password that expires in 2 days.</p>
                            <a href="{{ route('dashboard', ['create_user' => 1]) }}" class="btn btn-sm btn-primary btn-custom">Create User</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <h4 class="text-success">Members Report</h4>
                            <p>Display added members and their assigned roles.</p>
                            <a href="{{ route('dashboard', ['members_report' => 1]) }}" class="btn btn-sm btn-success btn-custom">Generate Report</a>
                        </div>
                    </div>
                </div>
            </div>

            @if($showCreateUserForm)
                <div class="card border-0 mt-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h4 class="mb-1">Create User</h4>
                                <p class="text-muted mb-0">Add staff users to SolidCare SSD.</p>
                            </div>
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary btn-custom">Done</a>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.users.store') }}">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Temporary Password</label>
                                    <input id="password" name="password" type="password" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                                </div>

                                <div class="col-md-12">
                                    <label for="role" class="form-label">Role</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="">Select role</option>
                                        <option value="executive" {{ old('role') === 'executive' ? 'selected' : '' }}>Executive</option>
                                        <option value="ssd_assistant_1" {{ old('role') === 'ssd_assistant_1' ? 'selected' : '' }}>SSD Assistant 1</option>
                                        <option value="ssd_assistant_2" {{ old('role') === 'ssd_assistant_2' ? 'selected' : '' }}>SSD Assistant 2</option>
                                        <option value="psychologist" {{ old('role') === 'psychologist' ? 'selected' : '' }}>Psychologist</option>
                                        <option value="senior_nurse_officer" {{ old('role') === 'senior_nurse_officer' ? 'selected' : '' }}>Senior Nurse Officer</option>
                                        <option value="warden" {{ old('role') === 'warden' ? 'selected' : '' }}>Warden</option>
                                        <option value="yearleader" {{ old('role') === 'yearleader' ? 'selected' : '' }}>Year Leader</option>
                                    </select>
                                </div>

                                <div id="yearleader-fields" class="col-12" style="display:none;">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="faculty" class="form-label">Faculty</label>
                                            <select id="faculty" name="faculty" class="form-select">
                                                <option value="">Select faculty</option>
                                                @foreach (config('limkokwing.faculties', []) as $faculty)
                                                    <option value="{{ $faculty['label'] }}" {{ old('faculty') === $faculty['label'] ? 'selected' : '' }}>
                                                        {{ $faculty['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="class" class="form-label">Class/Programme</label>
                                            <select id="class" name="class" class="form-select" data-current-value="{{ old('class') }}">
                                                <option value="">Select class/programme</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="year" class="form-label">Year</label>
                                            <select id="year" name="year" class="form-select" data-current-value="{{ old('year') }}">
                                                <option value="">Select year</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-custom">Create User</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($showMembersReport)
                <div class="directory-card mt-4">
                    <div class="directory-inner">
                        <div class="directory-hero">
                            <div class="directory-title-block">
                                <h2 class="directory-title"><i class="fas fa-users me-2"></i>Added Members</h2>
                                <p class="directory-subtitle">
                                    <i class="fas fa-id-badge text-info"></i>
                                    Staff members and admins currently registered in <strong>SolidCare SSD</strong>
                                    <span class="verified-chip"><i class="fas fa-check-circle"></i> verified roster</span>
                                </p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="directory-count">
                                    <strong>{{ $members->count() }}</strong>
                                    <span>Active Members</span>
                                </div>
                                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary btn-custom">Done</a>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if($members->isEmpty())
                            <div class="alert alert-info mb-0">No members have been added yet.</div>
                        @else
                            @php
                                $memberRoleCounts = $members->countBy('role');
                            @endphp

                            <div class="directory-table-wrap">
                                <table class="directory-table">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-user me-1"></i>Name</th>
                                            <th><i class="fas fa-envelope me-1"></i>Email</th>
                                            <th><i class="fas fa-briefcase me-1"></i>Role</th>
                                            <th><i class="fas fa-calendar-alt me-1"></i>Added On</th>
                                            <th><i class="fas fa-key me-1"></i>Temporary Password</th>
                                            <th><i class="fas fa-user-slash me-1"></i>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($members as $member)
                                            @php
                                                $initials = collect(explode(' ', trim($member->name)))
                                                    ->filter()
                                                    ->take(2)
                                                    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                                    ->implode('');
                                                $roleClass = 'role-' . str_replace('_', '-', $member->role);
                                                $roleIcon = $roleIcons[$member->role] ?? 'fa-user-tie';
                                                $temporaryPasswordIsActive = $member->password_temporary
                                                    && $member->temporary_password_expires_at
                                                    && $member->temporary_password_expires_at->isFuture();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="member-name">
                                                        <span class="avatar">{{ $initials ?: 'U' }}</span>
                                                        <span class="name-text">{{ $member->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="email-cell">{{ $member->email }}</td>
                                                <td>
                                                    <span class="directory-role {{ $roleClass }}">
                                                        <i class="fas {{ $roleIcon }}"></i>
                                                        {{ $roleLabels[$member->role] ?? \Illuminate\Support\Str::headline($member->role) }}
                                                    </span>
                                                </td>
                                                <td class="date-cell"><i class="far fa-calendar-check me-1"></i>{{ optional($member->created_at)->format('M j, Y') }}</td>
                                                <td class="temporary-password-cell">
                                                    @if($temporaryPasswordIsActive && $member->temporary_password_plain)
                                                        <span class="temporary-password-value">{{ $member->temporary_password_plain }}</span>
                                                        <span class="temporary-password-status">
                                                            Expires {{ $member->temporary_password_expires_at->format('M j, Y g:i A') }}
                                                        </span>
                                                    @elseif($member->password_temporary && $member->temporary_password_expires_at && $member->temporary_password_expires_at->isPast())
                                                        <span class="temporary-password-muted">Expired</span>
                                                        <span class="temporary-password-status">
                                                            Expired {{ $member->temporary_password_expires_at->format('M j, Y g:i A') }}
                                                        </span>
                                                    @elseif($member->password_temporary)
                                                        <span class="temporary-password-muted">Not available</span>
                                                        <span class="temporary-password-status">Created before password display was enabled.</span>
                                                        @if($temporaryPasswordIsActive)
                                                            <form method="POST" action="{{ route('admin.users.temporary-password.reissue', $member) }}" onsubmit="return confirm('The original password cannot be recovered. Reissue a new temporary password for this member?');">
                                                                @csrf
                                                                <button type="submit" class="reissue-password-btn">
                                                                    <i class="fas fa-rotate"></i>
                                                                    Reissue visible password
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        @if($member->is_system_admin)
                                                            <span class="temporary-password-muted">Not applicable</span>
                                                            <span class="temporary-password-status">System admin account.</span>
                                                        @else
                                                            <span class="temporary-password-muted">Changed by user</span>
                                                            <span class="temporary-password-status">Temporary password no longer applies.</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($member->is_system_admin)
                                                        @if(auth('admin')->id() === $member->id)
                                                            <span class="temporary-password-muted">Protected</span>
                                                        @else
                                                            <form method="POST" action="{{ route('admin.admins.delete', $member) }}" onsubmit="return confirm('Delete this admin? This admin will no longer have access to the system.');">
                                                                @csrf
                                                                <button type="submit" class="delete-member-btn">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        <form method="POST" action="{{ route('admin.users.delete', $member) }}" onsubmit="return confirm('Delete this member? This member will no longer have access to the system.');">
                                                            @csrf
                                                            <button type="submit" class="delete-member-btn">
                                                                <i class="fas fa-trash-alt"></i>
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="member-analysis">
                                <h3>Members Analysis</h3>
                                <div class="analysis-grid">
                                    @foreach($memberRoleCounts as $role => $count)
                                        <span class="analysis-pill">
                                            {{ $count }} {{ $roleLabels[$role] ?? \Illuminate\Support\Str::headline($role) }}{{ $count === 1 ? '' : 's' }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="directory-footer">
                                <span><span class="pulse-dot"></span><strong>SolidCare SSD</strong> staff directory is active.</span>
                                <span>{{ $members->count() }} members currently listed.</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <footer>
            &copy; 2026 SolidCare SSD. All rights reserved.
        </footer>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </body>
</html>
