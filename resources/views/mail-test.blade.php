<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Send Test Email</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .panel { width: 100%; max-width: 520px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); }
        h1 { margin: 0 0 18px; font-size: 24px; }
        label { display: block; margin-bottom: 6px; font-weight: 700; }
        input, textarea { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; padding: 11px 12px; font: inherit; }
        textarea { min-height: 140px; resize: vertical; }
        .field { margin-bottom: 16px; }
        .button { width: 100%; border: 0; border-radius: 6px; padding: 12px 14px; background: #1f2937; color: #fff; font-weight: 700; cursor: pointer; }
        .notice { margin-bottom: 16px; padding: 12px; border-radius: 6px; }
        .success { background: #ecfdf5; color: #065f46; }
        .error { background: #fef2f2; color: #991b1b; }
    </style>
</head>
<body>
    <main class="page">
        <section class="panel">
            <h1>Send Test Email</h1>

            @if (session('status'))
                <div class="notice success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="notice error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('mail.test.send') }}">
                @csrf

                <div class="field">
                    <label for="to">Recipient Email</label>
                    <input id="to" name="to" type="email" value="{{ old('to') }}" required>
                </div>

                <div class="field">
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject', 'SolidCare SSD test email') }}" maxlength="150" required>
                </div>

                <div class="field">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" maxlength="2000" required>{{ old('message', 'This is a test email from SolidCare SSD.') }}</textarea>
                </div>

                <button class="button" type="submit">Send Email</button>
            </form>
        </section>
    </main>
</body>
</html>
