<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>SolidCare SSD | Student Support Dashboard</title>
        <!-- Google Fonts + Font Awesome -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            html {
                width: 100%;
                overflow-x: hidden;
            }

            body {
                font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
                background: #0a0f1c;
                color: #f1f5f9;
                line-height: 1.5;
                overflow-x: hidden;
            }

            /* premium animated background */
            body::before {
                content: "";
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: radial-gradient(circle at 15% 25%, rgba(45, 85, 255, 0.1), transparent 55%),
                            radial-gradient(circle at 85% 70%, rgba(240, 180, 41, 0.12), transparent 60%),
                            linear-gradient(135deg, #0a1122 0%, #0e1a2f 100%);
                z-index: -2;
            }

            body::after {
                content: "";
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.7' numOctaves='1' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.035'/%3E%3C/svg%3E");
                pointer-events: none;
                z-index: -1;
            }

            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: #0f1a24; border-radius: 10px; }
            ::-webkit-scrollbar-thumb { background: #f0b429; border-radius: 10px; }

            .app-container {
                width: 100%;
                max-width: 1400px;
                margin: 0 auto;
                padding: 0 clamp(16px, 4vw, 32px);
            }

            /* ========== HEADER (modern glass) ========== */
            .site-header {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                padding: 24px 0 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                margin-bottom: 36px;
                backdrop-filter: blur(6px);
            }
            .brand-group {
                display: flex;
                align-items: center;
                gap: 14px;
                flex-wrap: wrap;
                min-width: 0;
            }
            .brand-icon {
                font-size: 2rem;
                color: #f0b429;
                filter: drop-shadow(0 0 8px rgba(240,180,41,0.3));
            }
            .brand-name {
                font-weight: 800;
                font-size: clamp(1.25rem, 5vw, 1.8rem);
                letter-spacing: -0.02em;
                background: linear-gradient(135deg, #ffffff, #e2e8f0);
                background-clip: text;
                -webkit-background-clip: text;
                color: transparent;
            }
            .campus-badge {
                background: rgba(240, 180, 41, 0.18);
                padding: 5px 12px;
                border-radius: 60px;
                font-size: 0.7rem;
                font-weight: 600;
                border: 1px solid rgba(240, 180, 41, 0.4);
                color: #f0b429;
            }
            .header-actions {
                display: flex;
                gap: 20px;
                align-items: center;
                flex-wrap: wrap;
            }
            .user-welcome {
                display: inline-flex;
                align-items: center;
                background: rgba(255,255,255,0.05);
                padding: 8px 20px;
                border-radius: 40px;
                font-size: 0.9rem;
                backdrop-filter: blur(8px);
            }
            .user-welcome i {
                margin-right: 8px;
                color: #f0b429;
            }
            .btn-logout {
                background: rgba(220, 38, 38, 0.2);
                border: 1px solid rgba(255,255,255,0.12);
                border-radius: 40px;
                padding: 8px 22px;
                font-weight: 600;
                font-size: 0.85rem;
                color: #ffe1e1;
                cursor: pointer;
                transition: all 0.25s;
            }
            .btn-logout:hover {
                background: rgba(220, 38, 38, 0.45);
                transform: translateY(-2px);
                border-color: #f0b429;
            }

            /* ========== HERO PANEL ========== */
            .hero-panel {
                background: linear-gradient(105deg, rgba(12, 28, 45, 0.7), rgba(5, 15, 25, 0.6));
                backdrop-filter: blur(12px);
                border-radius: 48px;
                border: 1px solid rgba(255,255,255,0.08);
                padding: 48px 56px;
                margin-bottom: 56px;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                gap: 32px;
                box-shadow: 0 28px 50px -18px black;
                overflow: hidden;
            }
            .hero-text {
                flex: 1 1 420px;
                min-width: 0;
            }
            .hero-tag {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 2px;
                background: rgba(240,180,41,0.2);
                display: inline-block;
                padding: 5px 14px;
                border-radius: 30px;
                margin-bottom: 18px;
                color: #f0b429;
            }
            .hero-main-title {
                font-size: clamp(2.2rem, 5vw, 3.7rem);
                font-weight: 800;
                line-height: 1.2;
                margin-bottom: 18px;
                background: linear-gradient(to right, #fff, #ccd6f0);
                background-clip: text;
                -webkit-background-clip: text;
                color: transparent;
            }
            .hero-description {
                font-size: 1rem;
                color: #bdd0f0;
                max-width: 540px;
                margin-bottom: 28px;
            }
            .hero-stats {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 20px;
                max-width: 620px;
            }
            .stat-block {
                display: flex;
                align-items: center;
                gap: 8px;
                min-width: 0;
            }
            .stat-digit {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                flex-shrink: 0;
                font-weight: 800;
                font-size: clamp(1.05rem, 3vw, 1.5rem);
                color: #f0b429;
            }
            .hero-illustration {
                flex: 1 1 280px;
                min-width: 180px;
                text-align: center;
            }
            .glass-card-illus {
                background: rgba(255,255,240,0.02);
                border-radius: 36px;
                padding: 24px 16px;
                border: 1px solid rgba(240,180,41,0.2);
                box-shadow: 0 15px 35px -12px rgba(0,0,0,0.5);
            }
            .hero-logo-lockup {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                gap: 0.9rem;
                width: min(100%, 360px);
            }
            .hero-logo-panel {
                width: 100%;
                background: #18181c;
                color: #f8f7f2;
                padding: 1rem 1.3rem 0.8rem;
                border: 1px solid rgba(255,255,255,0.16);
                box-shadow: 0 14px 28px rgba(0,0,0,0.32);
                text-align: left;
            }
            .hero-logo-title {
                font-family: Georgia, 'Times New Roman', serif;
                font-size: clamp(1.9rem, 3.8vw, 2.65rem);
                line-height: 0.88;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }
            .hero-logo-subtitle {
                margin-top: 0.4rem;
                font-family: Georgia, 'Times New Roman', serif;
                font-size: 0.9rem;
                line-height: 1;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }
            .hero-logo-country {
                display: inline-flex;
                align-items: center;
                gap: 0.8rem;
                width: 100%;
                color: #f5e9c9;
                font-family: Georgia, 'Times New Roman', serif;
                font-size: 1.3rem;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                justify-content: center;
            }
            .hero-logo-country::before,
            .hero-logo-country::after {
                content: "";
                flex: 1 1 2rem;
                max-width: 4rem;
                height: 1px;
                background: rgba(240,180,41,0.55);
            }

            /* flash messages */
            .flash-zone {
                margin-bottom: 32px;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .flash-message {
                background: rgba(0,0,0,0.55);
                backdrop-filter: blur(12px);
                border-radius: 24px;
                padding: 14px 20px;
                border-left: 5px solid;
                font-weight: 500;
                animation: fadeSlide 0.3s ease;
            }
            .flash-message.success { border-left-color: #2ecc71; background: rgba(46,204,113,0.12); color: #e0ffe6; }
            .flash-message.error { border-left-color: #e74c3c; background: rgba(231,76,60,0.12); color: #ffe6e6; }
            .flash-message.info { border-left-color: #3498db; background: rgba(52,152,219,0.12); color: #e3f0ff; }

            /* service cards - modern luxurious */
            .services-header {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                margin-bottom: 32px;
                flex-wrap: wrap;
            }
            .services-header h2 {
                font-size: 2rem;
                font-weight: 700;
                letter-spacing: -0.3px;
            }
            .services-header h2 span {
                color: #f0b429;
            }
            .service-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(min(100%, 310px), 1fr));
                gap: 32px;
                margin-bottom: 70px;
            }
            .service-card {
                background: rgba(18, 32, 48, 0.7);
                backdrop-filter: blur(10px);
                border-radius: 38px;
                padding: 2rem;
                transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
                border: 1px solid rgba(255,255,245,0.07);
                box-shadow: 0 15px 32px -12px rgba(0,0,0,0.5);
                display: flex;
                flex-direction: column;
            }
            .service-card:hover {
                transform: translateY(-8px);
                border-color: rgba(240, 180, 41, 0.5);
                background: rgba(28, 48, 72, 0.85);
                box-shadow: 0 28px 44px -16px rgba(0,0,0,0.6);
            }
            .card-icon-lg {
                font-size: 2.8rem;
                color: #f0b429;
                margin-bottom: 22px;
            }
            .service-card h3 {
                font-size: 1.7rem;
                font-weight: 700;
                margin-bottom: 12px;
            }
            .service-card p {
                color: #cbdff5;
                font-size: 0.9rem;
                margin-bottom: 28px;
                line-height: 1.5;
                flex: 1;
            }
            .card-action {
                margin-top: auto;
            }
            .btn-service {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                background: linear-gradient(95deg, #f0b429, #da9e1a);
                padding: 10px 28px;
                border-radius: 60px;
                font-weight: 700;
                color: #0e1a24;
                text-decoration: none;
                transition: all 0.2s;
                font-size: 0.85rem;
                border: none;
                cursor: pointer;
            }
            .btn-service i {
                transition: transform 0.2s;
            }
            .btn-service:hover i {
                transform: translateX(5px);
            }
            .btn-disabled {
                background: #2c3e4e;
                color: #b9d0ef;
                cursor: not-allowed;
                pointer-events: none;
                opacity: 0.7;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 28px;
                border-radius: 60px;
                font-weight: 600;
                font-size: 0.85rem;
            }

            /* FULL FOOTER */
            .main-footer {
                margin-top: 40px;
                border-top: 1px solid rgba(255,255,255,0.08);
                padding: 48px 0 32px;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 40px;
            }
            .footer-col {
                flex: 1;
                min-width: 170px;
            }
            .footer-brand {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 800;
                font-size: 1.3rem;
                margin-bottom: 18px;
            }
            .footer-brand i {
                color: #f0b429;
            }
            .footer-about {
                color: #90a5c5;
                font-size: 0.85rem;
                max-width: 260px;
                line-height: 1.5;
            }
            .footer-col h4 {
                font-size: 1rem;
                margin-bottom: 20px;
                letter-spacing: 0.5px;
                font-weight: 600;
            }
            .footer-links {
                list-style: none;
            }
            .footer-links li {
                margin-bottom: 12px;
            }
            .footer-links a {
                color: #b4c8ec;
                text-decoration: none;
                transition: 0.2s;
                font-size: 0.85rem;
            }
            .footer-links a:hover {
                color: #f0b429;
                padding-left: 5px;
            }
            .social-links {
                display: flex;
                gap: 18px;
                margin-top: 20px;
            }
            .social-links a {
                background: rgba(255,255,255,0.04);
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.25s;
                color: #eef3ff;
            }
            .social-links a:hover {
                background: #f0b429;
                color: #0a0f1c;
                transform: translateY(-3px);
            }
            .copyright-bar {
                text-align: center;
                margin-top: 48px;
                padding-top: 22px;
                border-top: 1px solid rgba(255,255,255,0.05);
                font-size: 0.75rem;
                color: #6c88a8;
            }

            @keyframes fadeSlide {
                from { opacity: 0; transform: translateY(-12px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @media (max-width: 880px) {
                .hero-panel {
                    padding: 32px 28px;
                    flex-direction: column;
                    text-align: center;
                    align-items: stretch;
                    border-radius: 34px;
                }
                .hero-description { margin-inline: auto; }
                .hero-stats { justify-content: center; }
                .site-header { flex-direction: column; gap: 16px; align-items: stretch; }
                .header-actions { justify-content: space-between; }
                .hero-illustration { width: 100%; }
                .glass-card-illus { width: 100%; max-width: 390px; margin: 0 auto; }
                .service-card { border-radius: 28px; }
            }

            @media (max-width: 560px) {
                .site-header {
                    padding: 18px 0 16px;
                    margin-bottom: 24px;
                }
                .brand-group,
                .header-actions {
                    justify-content: center;
                }
                .header-actions {
                    gap: 10px;
                }
                .user-welcome,
                .btn-logout {
                    flex: 1 1 auto;
                    min-width: 0;
                    text-align: center;
                    justify-content: center;
                }
                .hero-panel {
                    padding: 24px 18px;
                    gap: 24px;
                    margin-bottom: 42px;
                    border-radius: 28px;
                }
                .hero-main-title {
                    font-size: clamp(1.85rem, 9vw, 2.45rem);
                }
                .hero-description {
                    font-size: 0.92rem;
                }
                .hero-stats {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 10px;
                }
                .stat-block {
                    flex-direction: column;
                    justify-content: flex-start;
                    gap: 4px;
                    font-size: 0.82rem;
                    line-height: 1.25;
                    text-align: center;
                }
                .stat-digit {
                    flex-direction: column;
                    gap: 2px;
                    line-height: 1.1;
                }
                .glass-card-illus {
                    padding: 20px 14px;
                    border-radius: 28px;
                }
                .hero-logo-lockup {
                    width: min(100%, 300px);
                }
                .hero-logo-panel {
                    padding: 0.85rem 1rem 0.75rem;
                }
                .hero-logo-title {
                    font-size: clamp(1.55rem, 7vw, 1.9rem);
                }
                .hero-logo-subtitle {
                    font-size: 0.72rem;
                }
                .hero-logo-country {
                    gap: 0.6rem;
                    font-size: 1.05rem;
                }
                .services-header {
                    gap: 8px;
                    margin-bottom: 22px;
                }
                .services-header h2 {
                    font-size: 1.55rem;
                }
                .service-grid {
                    gap: 20px;
                    margin-bottom: 48px;
                }
                .service-card {
                    padding: 1.35rem;
                    border-radius: 24px;
                }
                .service-card h3 {
                    font-size: 1.35rem;
                }
                .btn-service,
                .btn-disabled {
                    width: 100%;
                    justify-content: center;
                    padding-inline: 16px;
                    text-align: center;
                }
            }
        </style>
    </head>
    <body>
        @php
            $dashboardUser = auth()->guard('web')->user();
            $academicSupportRoles = ['yearleader', 'executive', 'ssd_assistant_1', 'ssd_assistant_2'];
            $clinicAccessRoles = ['executive', 'ssd_assistant_1', 'ssd_assistant_2', 'senior_nurse_officer'];
            $canAccessAcademicSupports = $dashboardUser && in_array($dashboardUser->role, $academicSupportRoles, true);
            $canAccessClinic = $dashboardUser && in_array($dashboardUser->role, $clinicAccessRoles, true);
            $canManageCounselling = $dashboardUser && $dashboardUser->role === 'psychologist';
            $canAccessCounselling = $dashboardUser && ($canManageCounselling || ($dashboardUser->role === 'student' && $dashboardUser->student_type === 'continuing'));
            $userName = $dashboardUser ? ($dashboardUser->name ?? ($dashboardUser->email ?? 'Member')) : 'Guest';
        @endphp

        <div class="app-container">
            <!-- Header with full identity -->
            <header class="site-header">
                <div class="brand-group">
                    <i class="fas fa-shield-virus brand-icon"></i>
                    <div class="brand-name">SolidCare | SSD</div>
                    <div class="campus-badge"><i class="fas fa-map-marker-alt"></i> Limkokwing Lesotho</div>
                </div>
                <div class="header-actions">
                    <div class="user-welcome">
                        <i class="fas fa-user-graduate"></i> {{ ucfirst($userName) }}
                    </div>
                    <button class="btn-logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-door-open"></i> Logout
                    </button>
                </div>
            </header>

            <!-- Hero / welcome area -->
            <div class="hero-panel">
                <div class="hero-text">
                    <div class="hero-tag"><i class="fas fa-hands-helping"></i> holistic student support</div>
                    <h1 class="hero-main-title">Your well-being,<br>our priority.</h1>
                    <p class="hero-description">Centralized hub for academic guidance, health, counselling, and accommodation — designed to empower every student.</p>
                    <div class="hero-stats">
                        <div class="stat-block"><span class="stat-digit"><i class="fas fa-calendar-check"></i> 24/7</span> <span>Access</span></div>
                        <div class="stat-block"><span class="stat-digit"><i class="fas fa-chalkboard-user"></i> 4+</span> <span>Core Services</span></div>
                        <div class="stat-block"><span class="stat-digit"><i class="fas fa-arrow-trend-up"></i> Fast</span> <span>Referrals</span></div>
                    </div>
                </div>
                <div class="hero-illustration">
                    <div class="glass-card-illus">
                        <div class="hero-logo-lockup" aria-label="Limkokwing University of Creative Technology Lesotho">
                            <div class="hero-logo-panel">
                                <div class="hero-logo-title">Limkokwing<br>University</div>
                                <div class="hero-logo-subtitle">Of Creative Technology</div>
                            </div>
                            <div class="hero-logo-country">Lesotho</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash messages (preserve all session types) -->
            @if (session('status') || session('success') || session('error'))
                <div class="flash-zone">
                    @if (session('status'))
                        <div class="flash-message info"><i class="fas fa-bell"></i> {{ session('status') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="flash-message success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="flash-message error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
                    @endif
                </div>
            @endif

            <!-- Services grid (magnificent cards) -->
            <div class="services-header">
                <h2>Integrated <span>Support Services</span></h2>
                <p style="color:#bdd8ff;"><i class="fas fa-sync-alt"></i> real-time dashboard</p>
            </div>

            <div class="service-grid">
                <!-- Academic Supports -->
                <div class="service-card">
                    <div class="card-icon-lg"><i class="fas fa-chalkboard-user"></i></div>
                    <h3>Academic Supports</h3>
                    <p>oversee student academic progress within their year group, coordinate support interventions, monitor performance trends, and collaborate with staff to improve student outcomes.</p>
                    <div class="card-action">
                        @if ($canAccessAcademicSupports)
                            <a href="{{ route('academic.referrals') }}" class="btn-service">Open Academic Desk <i class="fas fa-arrow-right"></i></a>
                        @else
                            <span class="btn-disabled"><i class="fas fa-lock"></i> Authorized staff only</span>
                        @endif
                    </div>
                </div>

                <!-- Clinic -->
                <div class="service-card">
                    <div class="card-icon-lg"><i class="fas fa-stethoscope"></i></div>
                    <h3>Health & Clinic</h3>
                    <p>provides comprehensive healthcare support by maintaining accurate medical records, managing student visits, delivering professional medical care, and ensuring continuous health monitoring and wellness management.</p>
                    <div class="card-action">
                        @if ($canAccessClinic)
                            <a href="{{ route('clinic') }}" class="btn-service">Access Clinic <i class="fas fa-arrow-right"></i></a>
                        @else
                            <span class="btn-disabled"><i class="fas fa-lock"></i> Authorized staff only</span>
                        @endif
                    </div>
                </div>

                <!-- Counselling -->
                <div class="service-card">
                    <div class="card-icon-lg"><i class="fas fa-comment-dots"></i></div>
                    <h3>Guidance Counselling</h3>
                    <p>
                        @if ($canManageCounselling)
                            Review requests, schedule sessions, mark attendance & provide professional follow-up.
                        @else
                            a dedicated system that supports students’ psychological health by enabling counselling requests, session bookings, and progress tracking, while psychologists ensure professional care, monitoring, and follow-up.
                        @endif
                    </p>
                    <div class="card-action">
                        @if ($canAccessCounselling)
                            <a href="{{ route('counselling') }}" class="btn-service">{{ $canManageCounselling ? 'Manage Appointments' : 'Book Counselling' }} <i class="fas fa-arrow-right"></i></a>
                        @else
                            <span class="btn-disabled"><i class="fas fa-lock"></i> Continuing Students / Psychologist only</span>
                        @endif
                    </div>
                </div>

                <!-- Accommodation -->
                <div class="service-card">
                    <div class="card-icon-lg"><i class="fas fa-building"></i></div>
                    <h3>Accommodation</h3>
                    <p>streamlines student housing by managing applications, optimizing room allocation, monitoring occupancy, and maintaining accurate, secure residence records.</p>
                    <div class="card-action">
                        <a href="{{ route('accommodation') }}" class="btn-service">Manage Housing <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Full featured footer -->
            <footer class="main-footer">
                <div class="footer-col">
                    <div class="footer-brand">
                        <i class="fas fa-hand-holding-heart"></i> <span>SolidCare SSD</span>
                    </div>
                    <p class="footer-about">Committed to student success and mental wellness at Limkokwing University of Creative Technology, Lesotho.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-angle-right"></i> Academic Desk</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Clinic Appointments</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Counselling Hub</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Accommodation Portal</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Student Resources</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-angle-right"></i> Emergency Contacts</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Wellness Guide</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Privacy & Policies</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> IT Support</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Office</h4>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> ssd@limkokwing.ac.ls</li>
                        <li><i class="fas fa-phone-alt"></i> +266 2231 4567</li>
                        <li><i class="fas fa-clock"></i> Mon-Fri: 8:30 - 17:00</li>
                        <li><i class="fas fa-map-pin"></i> Maseru, Lesotho Campus</li>
                    </ul>
                </div>
            </footer>
            <div class="copyright-bar">
                &copy; {{ date('Y') }} SolidCare Student Support Directorate — Limkokwing University. All rights reserved. <i class="fas fa-heart" style="color:#f0b429;"></i> inclusive excellence
            </div>
        </div>

        <!-- Hidden logout form (original functionality untouched) -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <!-- Micro animation (purely visual, no functional override) -->
        <script>
            document.querySelectorAll('.btn-service').forEach(btn => {
                if(!btn.classList.contains('btn-disabled')) {
                    btn.addEventListener('click', (e) => {
                        // just subtle ripple effect, does not affect original href
                        let ripple = document.createElement('span');
                        ripple.style.position = 'absolute';
                        ripple.style.background = 'rgba(0,0,0,0.2)';
                        ripple.style.borderRadius = '50%';
                        ripple.style.width = '28px';
                        ripple.style.height = '28px';
                        ripple.style.transform = 'scale(0)';
                        ripple.style.transition = 'transform 0.3s';
                        ripple.style.pointerEvents = 'none';
                        btn.style.position = 'relative';
                        btn.style.overflow = 'hidden';
                        btn.appendChild(ripple);
                        setTimeout(() => ripple.remove(), 300);
                    });
                }
            });
        </script>
    </body>
</html>
