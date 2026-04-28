<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your New Password</title>
    <style>
        * { box-sizing: border-box; margin:0; padding:0; }
        body { min-height:100vh; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#eef2ff; display:flex; align-items:center; justify-content:center; padding:20px; }
        .card { width:100%; max-width:420px; background:#fff; border-radius:18px; padding:32px; box-shadow:0 25px 50px rgba(15,23,42,.08); }
        h1 { color:#111827; margin-bottom:6px; }
        p { color:#475569; margin-bottom:22px; line-height:1.6; }
        label { display:block; margin-bottom:8px; color:#334155; font-weight:600; }
        input { width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:12px; margin-bottom:18px; background:#f8fafc; }
        input:focus { border-color:#6366f1; outline:none; background:#fff; box-shadow:0 0 0 4px rgba(99,102,241,.12); }
        button { width:100%; padding:14px 18px; background:#4f46e5; color:#fff; border:none; border-radius:12px; font-weight:700; cursor:pointer; }
        button:hover { background:#4338ca; }
        .message { margin-bottom:18px; padding:15px 16px; border-radius:14px; background:#f8fafc; color:#334155; border:1px solid #cbd5e1; }
        .errors { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Update Your Password</h1>
        <p>Your temporary password has expired. Set a new secure password to continue to your SolidCare SSD account.</p>

        @if($errors->any())
            <div class="message errors">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.temporary.update') }}">
            @csrf
            <label for="password">New Password</label>
            <input id="password" name="password" type="password" required>

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <button type="submit">Save New Password</button>
        </form>
    </div>
</body>
</html>