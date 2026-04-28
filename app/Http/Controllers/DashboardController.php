<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            return view('admin.dashboard');
        }

        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        return view('dashboard');
    }

    public function home()
    {
        return $this->index();
    }
}