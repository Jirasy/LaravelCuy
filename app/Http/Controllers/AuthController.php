<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Memproses autentikasi
    public function login(Request $request)
    {
        // Validasi inputan
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username / NIS wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Melakukan login menggunakan guard kustom 'eks_guard'
        if (Auth::guard('eks_guard')->attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::guard('eks_guard')->user();

            // Redirect berdasarkan peran (role) pengguna
            switch ($user->peran) {
                case 'Admin':
                    return redirect()->route('admin.dashboard');
                case 'Siswa':
                    return redirect()->route('siswa.dashboard');
                case 'Pembina':
                    return redirect()->route('pembina.dashboard');
                default:
                    Auth::guard('eks_guard')->logout();
                    return back()->withErrors(['username' => 'Peran pengguna tidak valid.']);
            }
        }

        // Jika login gagal
        return back()->withErrors([
            'username' => 'Username/NIS atau password salah.',
        ])->onlyInput('username');
    }

    // Memproses logout
    public function logout(Request $request)
    {
        Auth::guard('eks_guard')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}