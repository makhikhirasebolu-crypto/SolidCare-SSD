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
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const yearLeaderSection = document.getElementById('yearleader-fields');
            const passwordSection = document.getElementById('password-fields');
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');

            yearLeaderSection.style.display = role === 'yearleader' ? 'block' : 'none';
            passwordSection.style.display = role === 'student' ? 'none' : 'block';
            password.required = role !== 'student';
            passwordConfirmation.required = role !== 'student';
        }

        window.addEventListener('DOMContentLoaded', function () {
            document.getElementById('role').addEventListener('change', toggleRoleFields);
            toggleRoleFields();
        });
    </script>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div>
                <h1>Create User</h1>
                <p>Add staff users, or approve a student email so the student can complete registration.</p>
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
                    <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
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
                    <input id="faculty" name="faculty" type="text" value="{{ old('faculty') }}" placeholder="Faculty name">

                    <label for="class">Class</label>
                    <input id="class" name="class" type="text" value="{{ old('class') }}" placeholder="Class name or section">

                    <label for="year">Year</label>
                    <input id="year" name="year" type="text" value="{{ old('year') }}" placeholder="Academic year">
                </div>

                <button type="submit">Create User</button>
            </form>
        </div>
    </div>
</body>
</html>
