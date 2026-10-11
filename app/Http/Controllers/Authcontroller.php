<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('ekstrakurikuler.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('ekstrakurikuler.index'));
        }

        // Password benar tapi akun dinonaktifkan: beri pesan yang jelas
        $user = User::where('email', $credentials['email'])->first();
        if ($user && !$user->is_active && Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Akunmu dinonaktifkan. Hubungi admin.'])
                ->onlyInput('email');
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('ekstrakurikuler.index');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'nisn'     => 'required|string|max:30|unique:users,nisn',
            'kelas'    => 'required|string|max:50',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Role SELALU siswa saat daftar sendiri. Admin/pembina diatur oleh admin.
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'nisn'     => $data['nisn'],
            'kelas'    => $data['kelas'],
            'password' => $data['password'], // di-hash otomatis oleh cast 'hashed'
            'role'     => 'siswa',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('ekstrakurikuler.index')->with('success', 'Akun berhasil dibuat. Selamat datang!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}