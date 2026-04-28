<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <style>
            body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; background: linear-gradient(135deg, #0f172a 0%, #111827 45%, #020617 100%); margin: 0; }
            .container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
            .card { width: 100%; max-width: 420px; background: #ffffff; border-radius: 1rem; padding: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.08); }
            .card h1 { margin: 0 0 1rem; font-size: 1.75rem; }
            .field { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
            .field label { font-weight: 600; font-size: 0.95rem; }
            .field input { width: 100%; border: 1px solid #d1d5db; border-radius: 0.75rem; padding: 0.85rem 1rem; font-size: 1rem; }
            .button { width: 100%; border: none; border-radius: 0.75rem; padding: 0.95rem 1rem; background: #1f2937; color: white; font-size: 1rem; font-weight: 600; cursor: pointer; }
            .button:hover { background: #111827; }
            .error { color: #b91c1c; margin-bottom: 1rem; }
            .note { margin-top: 1rem; color: #6b7280; font-size: 0.95rem; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1>Sign in to your account</h1>

                @if (session('status'))
                    <div class="error" style="color: #065f46;">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="error">{{ $errors->first() }}</div>
                @endif

                @if (session('login_email_suggestion'))
                    <div class="note" style="margin-top: 0; margin-bottom: 1rem; color: #92400e; background: #fffbeb; padding: 0.85rem 1rem; border-radius: 0.75rem;">
                        Did you mean <strong>{{ session('login_email_suggestion') }}</strong>?
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email or Student ID</label>
                        <input id="email" type="text" name="email" value="{{ old('email') }}" placeholder="name@example.com or 901xxxxxx" autocomplete="username" required autofocus>
                    </div>

                    <p class="note" style="margin-top: 0; margin-bottom: 1rem;">Continuing students can sign in with either their email address or student ID.</p>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required>
                    </div>

                    <button type="submit" class="button">Login</button>
                </form>

                <div class="text-center mt-4" style="text-align:center; margin-top:1rem;">
                    <p class="mb-0" style="margin-bottom:1.5rem;">Do you have an account?</p>
                    <a href="{{ route('register') }}" class="button" style="background: transparent; color: #1f2937; border: 1px solid #1f2937; margin-top: 0.75rem;">Create an Account</a>
                </div>
            </div>
        </div>
    </body>
</html>
