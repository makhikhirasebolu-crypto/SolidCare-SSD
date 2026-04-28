<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Student Portal') - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            :root {
                --page-bg-start: #071a2d;
                --page-bg-end: #102b45;
                --panel-bg: rgba(8, 20, 35, 0.74);
                --panel-border: rgba(255, 255, 255, 0.08);
                --card-bg: #f8fafc;
                --card-alt: #eef6ff;
                --text-main: #102033;
                --text-soft: #5b6b80;
                --accent: #1d7ef2;
                --accent-deep: #0f4fa8;
                --success-bg: #ddf7e7;
                --success-text: #146c43;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 30%),
                    linear-gradient(135deg, var(--page-bg-start) 0%, var(--page-bg-end) 100%);
                color: #e6edf6;
            }

            .student-shell {
                width: min(1180px, calc(100% - 28px));
                margin: 18px auto;
                min-height: calc(100vh - 36px);
                background: var(--panel-bg);
                border: 1px solid var(--panel-border);
                border-radius: 28px;
                box-shadow: 0 24px 70px rgba(0, 0, 0, 0.3);
                overflow: hidden;
                backdrop-filter: blur(10px);
            }

            .student-topbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                padding: 1.25rem 1.5rem;
                border-bottom: 1px solid var(--panel-border);
            }

            .student-brand {
                display: flex;
                flex-direction: column;
                gap: 0.2rem;
            }

            .student-brand strong {
                font-size: 1.2rem;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .student-brand span {
                color: #b8c8da;
                font-size: 0.95rem;
            }

            .topbar-actions {
                display: flex;
                gap: 0.75rem;
                align-items: center;
            }

            .topbar-link,
            .topbar-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.8rem 1.1rem;
                border-radius: 999px;
                text-decoration: none;
                font-weight: 700;
                border: 1px solid rgba(255, 255, 255, 0.12);
                transition: transform 0.2s ease, background 0.2s ease;
            }

            .topbar-link {
                color: #eff6ff;
                background: rgba(255, 255, 255, 0.06);
            }

            .topbar-button {
                color: #eff6ff;
                background: transparent;
                cursor: pointer;
            }

            .topbar-link:hover,
            .topbar-button:hover {
                transform: translateY(-1px);
                background: rgba(255, 255, 255, 0.12);
            }

            .student-content {
                padding: 1.5rem;
            }

            @media (max-width: 768px) {
                .student-shell {
                    width: min(100% - 16px, 100%);
                    margin: 8px auto;
                    min-height: calc(100vh - 16px);
                    border-radius: 20px;
                }

                .student-topbar {
                    flex-direction: column;
                    align-items: stretch;
                }

                .topbar-actions {
                    flex-direction: column;
                }

                .topbar-link,
                .topbar-button {
                    width: 100%;
                }

                .student-content {
                    padding: 1rem;
                }
            }
        </style>
        @stack('styles')
    </head>
    <body>
        <div class="student-shell">
            <div class="student-topbar">
                <div class="student-brand">
                    @php
                        $portalLabel = trim($__env->yieldContent('portal_label')) ?: (($user->name ?? 'Student') . ' Student Support Portal');
                        $showLogoutAction = trim($__env->yieldContent('show_logout_action')) !== '0';
                    @endphp
                    <strong>SolidCare SSD</strong>
                    <span>{{ $portalLabel }}</span>
                </div>

                <div class="topbar-actions">
                    <a href="{{ route('home') }}" class="topbar-link">Back to Home</a>
                    @if ($showLogoutAction)
                        <button
                            type="button"
                            class="topbar-button"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        >
                            Logout
                        </button>
                    @endif
                </div>
            </div>

            <main class="student-content">
                @yield('content')
            </main>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </body>
</html>
