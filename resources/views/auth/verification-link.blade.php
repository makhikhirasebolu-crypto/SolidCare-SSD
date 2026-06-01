<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification - SolidCare SSD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.18), transparent 38%),
                linear-gradient(135deg, #0f172a 0%, #111827 45%, #020617 100%);
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: 460px;
            max-width: 100%;
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 15px;
            box-shadow: 0 18px 45px rgba(2, 6, 23, 0.45);
            padding: 34px 30px;
        }

        h1 {
            font-size: 26px;
            text-align: center;
            margin-bottom: 10px;
            color: #111827;
        }

        p {
            color: #4b5563;
            font-size: 15px;
            line-height: 1.55;
            margin-bottom: 16px;
            text-align: center;
        }

        .email {
            display: block;
            overflow-wrap: anywhere;
            color: #1d4ed8;
            font-weight: 700;
            margin-top: 4px;
        }

        .status {
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
            font-weight: 600;
            text-align: center;
        }

        .verify-button,
        button {
            display: block;
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(to right, #2563eb, #4f46e5);
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .copy-link {
            background: #e5e7eb;
            border-radius: 8px;
            color: #374151;
            font-size: 13px;
            line-height: 1.45;
            margin-top: 18px;
            overflow-wrap: anywhere;
            padding: 12px;
        }

        .secondary {
            margin-top: 12px;
        }

        .secondary button {
            background: transparent;
            color: #374151;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Account Created Successfully</h1>

        @if ($status ?? false)
            <div class="status">{{ $status }}</div>
        @endif

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <p>
            Verify this account for
            <span class="email">{{ $user->email }}</span>
        </p>

        <a href="{{ $verificationUrl }}" class="verify-button">Verify My Account</a>

        <div class="copy-link">{{ $verificationUrl }}</div>

        <form method="POST" action="{{ route('logout') }}" class="secondary">
            @csrf
            <button type="submit">Sign Out</button>
        </form>
    </main>
</body>
</html>
