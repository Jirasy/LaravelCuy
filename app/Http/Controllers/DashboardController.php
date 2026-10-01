<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin()
    {
        $user = Auth::guard('eks_guard')->user();
        return view('admin.dashboard', compact('user'));
    }

    public function siswa()
    {
        $user = Auth::guard('eks_guard')->user();
        return view('siswa.dashboard', compact('user'));
    }

    public function pembina()
    {
        $user = Auth::guard('eks_guard')->user();
        return view('pembina.dashboard', compact('user'));
    }
}