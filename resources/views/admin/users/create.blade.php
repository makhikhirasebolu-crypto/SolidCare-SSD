<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Admin</title>
    <style>
        * { box-sizing: border-box; margin:0; padding:0; }
        html, body { min-height:100%; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#eef2ff; }
        .page { max-width: 680px; margin: 40px auto; padding: 24px; }
        .card { background:#fff; border-radius:18px; padding:32px; box-shadow:0 25px 50px rgba(15,23,42,.08); }
        h1, h2 { color:#1f2937; }
        h1 { margin-bottom: 8px; }
        p { color:#475569; margin-bottom:24px; }
        label { display:block; font-weight:600; margin-bottom:8px; color:#334155; }
        input, select, textarea { width:100%; padding:12px 14px; border-radius:12px; border:1px solid #cbd5e1; margin-bottom:18px; background:#f8fafc; transition:.2s; }
        input:focus, select:focus, textarea:focus { border-color:#6366f1; box-shadow:0 0 0 4px rgba(99,102,241,.12); outline:none; background:#fff; }
        .radio-group { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
        .radio-group label { font-weight:500; }
        button { width:100%; padding:14px 18px; border:none; border-radius:12px; background:#4f46e5; color:#fff; font-weight:700; cursor:pointer; transition:.2s; }
        button:hover { transform: translateY(-1px); background:#4338ca; }
        .message { margin-bottom:18px; padding:16px 18px; border-radius:14px; }
        .success { background:#ecfdf5; color:#166534; border:1px solid #d1fae5; }
        .error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
        .topbar a { color:#4f46e5; text-decoration:none; font-weight:700; }
    </style>
    <script>
        const limkokwingFaculties = @json(config('limkokwing.faculties', []));
        const oldClassValue = @json(old('class'));
        const oldYearValue = @json(old('year'));

        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const yearLeaderSection = document.getElementById('yearleader-fields');

            yearLeaderSection.style.display = role === 'yearleader' ? 'block' : 'none';
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
            document.getElementById('role').addEventListener('change', toggleRoleFields);
            document.getElementById('faculty').addEventListener('change', populateClassOptions);
            document.getElementById('class').addEventListener('change', populateYearOptions);
            toggleRoleFields();
            populateClassOptions();
        });
    </script>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div>
                <h1>Create User</h1>
                <p>Add staff users to SolidCare SSD.</p>
            </div>
            <a href="{{ route('dashboard') }}">Back to Dashboard</a>
        </div>

        <div class="card">
            @if(session('success'))
                <div class="message success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="message error">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>

                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>

                <div id="password-fields">
                    <label for="password">Temporary Password</label>
                    <input id="password" name="password" type="password" required>

                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select role</option>
                    <option value="executive" {{ old('role') === 'executive' ? 'selected' : '' }}>Executive</option>
                    <option value="ssd_assistant_1" {{ old('role') === 'ssd_assistant_1' ? 'selected' : '' }}>SSD Assistant 1</option>
                    <option value="ssd_assistant_2" {{ old('role') === 'ssd_assistant_2' ? 'selected' : '' }}>SSD Assistant 2</option>
                    <option value="psychologist" {{ old('role') === 'psychologist' ? 'selected' : '' }}>Psychologist</option>
                    <option value="senior_nurse_officer" {{ old('role') === 'senior_nurse_officer' ? 'selected' : '' }}>Senior Nurse Officer</option>
                    <option value="warden" {{ old('role') === 'warden' ? 'selected' : '' }}>Warden</option>
                    <option value="yearleader" {{ old('role') === 'yearleader' ? 'selected' : '' }}>Year Leader</option>
                </select>

                <div id="yearleader-fields" style="display:none;">
                    <label for="faculty">Faculty</label>
                    <select id="faculty" name="faculty">
                        <option value="">Select faculty</option>
                        @foreach (config('limkokwing.faculties', []) as $faculty)
                            <option value="{{ $faculty['label'] }}" {{ old('faculty') === $faculty['label'] ? 'selected' : '' }}>
                                {{ $faculty['label'] }}
                            </option>
                        @endforeach
                    </select>

                    <label for="class">Class/Programme</label>
                    <select id="class" name="class" data-current-value="{{ old('class') }}">
                        <option value="">Select class/programme</option>
                    </select>

                    <label for="year">Year</label>
                    <select id="year" name="year" data-current-value="{{ old('year') }}">
                        <option value="">Select year</option>
                    </select>
                </div>

                <button type="submit">Create User</button>
            </form>
        </div>
    </div>
</body>
</html>
