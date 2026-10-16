<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('is_active')->orderBy('role')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'nisn'     => ['nullable', 'string', 'max:30', Rule::unique('users', 'nisn')->ignore($user->id)],
            'kelas'    => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
            'angkatan_sekolah' => 'nullable|string|max:20',
            'tahun_lulus'      => 'nullable|integer|min:1990|max:' . (date('Y') + 10),
        ]);

        $data['is_alumni'] = $request->boolean('is_alumni');

        // Password hanya diganti kalau diisi
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data); // password di-hash otomatis oleh cast 'hashed'

        return redirect()->route('users.index')->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:admin,pembina,siswa',
        ]);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Kamu tidak bisa mengubah role akunmu sendiri.');
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', "Role {$user->name} diubah menjadi {$data['role']}.");
    }

    // Aktifkan <-> nonaktifkan
    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Kamu tidak bisa menonaktifkan akunmu sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $teks = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$teks}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $nama = $user->name;
        Storage::disk('public')->delete(array_filter([$user->foto, $user->wallpaper])); // bersihkan file milik user
        $user->delete();

        return back()->with('success', "Akun {$nama} berhasil dihapus.");
    }
}