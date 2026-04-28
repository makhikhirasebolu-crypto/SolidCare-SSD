<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Dashboard - SolidCare SSD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #eef2ff;
                color: #1f2937;
            }
            .page-header {
                padding: 2rem 1rem 1rem;
                text-align: center;
            }
            .page-header h1 {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
            }
            .page-header p {
                color: #475569;
                font-size: 1rem;
            }
            .services-section {
                padding: 2rem 0 4rem;
            }
            .card {
                border-radius: 1rem;
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            }
            .card-body {
                min-height: 240px;
            }
            .btn-custom {
                border-radius: 0.85rem;
                padding: 0.75rem 1rem;
            }
            footer {
                text-align: center;
                padding: 1.5rem 0;
                color: #64748b;
            }
        </style>
    </head>
    <body>
        <div class="page-header">
            <h1>Admin Dashboard</h1>
            <p>Signed in as admin. Access support services and management tools.</p>
            <button type="button" class="btn btn-outline-secondary btn-custom mt-3" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </button>
        </div>

        <div class="container services-section">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <h4 class="text-primary">Support Service</h4>
                            <p>Register and access all support services including accommodation requests, clinic appointments, and counselling.</p>
                            <a href="{{ route('register') }}" class="btn btn-sm btn-primary btn-custom">Register Now</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <h4 class="text-primary">Create User</h4>
                            <p>Add new users for any supported role with a temporary password that expires in 2 days.</p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary btn-custom">Add User</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <h4 class="text-success">🏥 Clinic</h4>
                            <p>Manage clinic records, track student visits, and provide professional healthcare support.</p>
                            <a href="{{ route('login') }}" class="btn btn-sm btn-success btn-custom">Nurse Login</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <h4 class="text-warning">💬 Counselling</h4>
                            <p>Provide emotional and academic support through structured counseling sessions.</p>
                            <a href="{{ route('login') }}" class="btn btn-sm btn-warning btn-custom text-white">Counselor Login</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-0">
                        <div class="card-body text-center">
                            <h4 class="text-info">🏠 Accommodation</h4>
                            <p>Allocate and manage student housing, room assignments, and residential records.</p>
                            <a href="{{ route('accommodation') }}" class="btn btn-sm btn-info btn-custom text-white">Access Accommodation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            &copy; 2026 SolidCare SSD. All rights reserved.
        </footer>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </body>
</html>
