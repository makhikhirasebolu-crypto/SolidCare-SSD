<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SolidCare SSD - Student Registration</title>
    <style>
        /* Reset & Body */
        * { box-sizing: border-box; margin:0; padding:0; }
        html, body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.18), transparent 38%),
                linear-gradient(135deg, #0f172a 0%, #111827 45%, #020617 100%);
            display: flex;
            flex-direction: column;
        }

        /* Container for form */
        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        form.card {
            background: #f8fafc;
            padding: 40px 30px;
            border-radius: 15px;
            width: 400px;
            max-width: 95%;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 18px 45px rgba(2, 6, 23, 0.45);
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Headings */
        .card h1 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .card h2 {
            text-align: center;
            color: #555;
            font-size: 18px;
            margin-bottom: 25px;
        }

        /* Labels & Inputs */
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 14px;
            background: #f1f3f6;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 8px rgba(102,126,234,0.5);
            outline: none;
            background: #fff;
        }

        /* Radio group */
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }

        .radio-group label {
            font-weight: normal;
        }

        /* Button */
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #667eea, #5a67d8);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        button:active {
            transform: scale(0.98);
        }

        /* Error box */
        .error-box {
            background: #ffe6e6;
            color: #cc0000;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .error-box p { margin: 5px 0; }

        .account-note {
            display: none;
            background: #e0f2fe;
            color: #075985;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 15px 10px;
            background: rgba(2, 6, 23, 0.7);
            color: #e5e7eb;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 450px) {
            .card {
                width: 95%;
                padding: 30px 20px;
            }
        }
    </style>

    <script>
        function toggleFields() {
            const email = document.getElementById('email');
            const studentFields = document.getElementById('student_fields');
            const studentType = document.getElementById('student_type');
            const studentIdField = document.getElementById('student_id_field');
            const idNumberField = document.getElementById('id_number_field');
            const adminNotice = document.getElementById('admin_notice');
            const isAdminEmail = /^[a-z]+\.[a-z]+@limkokwing\.ac\.ls$/i.test((email.value || '').trim());

            studentFields.style.display = isAdminEmail ? 'none' : 'block';
            adminNotice.style.display = isAdminEmail ? 'block' : 'none';
            studentType.required = !isAdminEmail;

            if (isAdminEmail) {
                studentIdField.style.display = 'none';
                idNumberField.style.display = 'none';
                return;
            }

            studentIdField.style.display = (studentType.value === 'continuing') ? 'block' : 'none';
            idNumberField.style.display = (studentType.value === 'new') ? 'block' : 'none';
        }

        function toggleDisabilityDetails() {
            const radios = document.getElementsByName('disability');
            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('disability_details').style.display = (this.value === 'yes') ? 'block' : 'none';
                });
            });

            const selected = document.querySelector('input[name="disability"]:checked');
            if (selected) {
                document.getElementById('disability_details').style.display = (selected.value === 'yes') ? 'block' : 'none';
            }
        }

        window.onload = function() {
            document.getElementById('email').addEventListener('input', toggleFields);
            document.getElementById('student_type').addEventListener('change', toggleFields);
            toggleFields();
            toggleDisabilityDetails();
        }
    </script>
</head>
<body>

<div class="container">
    <form method="POST" action="{{ route('register.store') }}" class="card">
        @csrf

        <h1>SolidCare SSD</h1>
        <h2>Student Account Registration</h2>
        <p style="color:#555; text-align:center; margin-bottom:20px; font-size:14px;">Register as a student to get access to accommodation and student services.</p>

        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <label for="name">Name</label>
        <input type="text" name="name" placeholder="Full Name" value="{{ old('name') }}" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required>

        <label for="password">Password</label>
        <input type="password" name="password" placeholder="Password" required>

        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

        <div id="admin_notice" class="account-note">This email will be registered as an admin account.</div>

        <div id="student_fields">
            <label for="student_type">Select Type</label>
            <select name="student_type" id="student_type" onchange="toggleFields()" required>
                <option value="">Select Type</option>
                <option value="continuing" {{ old('student_type')=='continuing'?'selected':'' }}>Continuing Student</option>
                <option value="new" {{ old('student_type')=='new'?'selected':'' }}>New Student</option>
            </select>

            <div id="student_id_field" style="display:none;">
                <label>Student ID (if continuing)</label>
                <input type="text" name="student_id" placeholder="901xxxxxx" value="{{ old('student_id') }}">
            </div>

            <div id="id_number_field" style="display:none;">
                <label>National ID (if new)</label>
                <input type="text" name="id_number" placeholder="Identity Number" value="{{ old('id_number') }}">
            </div>
        </div>

        <label>Do you have a disability?</label>
        <div class="radio-group">
            <label><input type="radio" name="disability" value="no" {{ old('disability', 'no') === 'no' ? 'checked' : '' }}> No</label>
            <label><input type="radio" name="disability" value="yes" {{ old('disability') === 'yes' ? 'checked' : '' }}> Yes</label>
        </div>

        <div id="disability_details" style="display:none;">
            <label>Disability Details</label>
            <textarea name="disability_details" rows="3" placeholder="Provide details if yes">{{ old('disability_details') }}</textarea>
        </div>

        <button type="submit">Register</button>
    </form>
</div>

<footer>
    &copy; 2026 SolidCare SSD | Student Support System
</footer>

</body>
</html>
