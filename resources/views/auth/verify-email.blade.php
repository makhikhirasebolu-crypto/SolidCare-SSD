<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - SolidCare SSD</title>
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
            width: 440px;
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

        .status.error {
            background: #fee2e2;
            color: #991b1b;
        }

        button {
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(to right, #2563eb, #4f46e5);
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .logout {
            margin-top: 12px;
        }

        .logout button {
            background: transparent;
            color: #374151;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Verify Your Email</h1>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="status error">{{ session('error') }}</div>
        @endif

        <p>
            We are sending a verification link to
            <span class="email">{{ $user->email }}</span>
        </p>
        <p>Open that email and confirm your address before continuing to SolidCare SSD. If it is not in your inbox, check spam or resend it.</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="logout">
            @csrf
            <button type="submit">Sign Out</button>
        </form>
    </main>
</body>
</html>
