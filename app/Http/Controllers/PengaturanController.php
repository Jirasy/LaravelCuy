<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rules\PasswordKuat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Pengaturan pribadi: bisa dipakai SEMUA user. Role TIDAK bisa diubah di sini.
class PengaturanController extends Controller
{
    public const PALET = ['aurora', 'sunset', 'ocean', 'sakura', 'forest'];

    public function edit(Request $request)
    {
        return view('pengaturan.edit', ['user' => $request->user()]);
    }

    public function updateProfil(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'angkatan_sekolah' => 'nullable|string|max:20',
        ], [
            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.max'   => 'Ukuran foto maksimal 2 MB.',
        ]);

        $user = $request->user();
        $user->name = $data['name'];
        $user->angkatan_sekolah = $data['angkatan_sekolah'] ?? null;

        if ($request->hasFile('foto')) {
            $this->hapusFile($user->foto);
            $user->foto = $request->file('foto')->store('avatars', 'public');
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateTampilan(Request $request)
    {
        $data = $request->validate([
            'palet'     => ['required', Rule::in(self::PALET)],
            'wallpaper' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'wallpaper.image' => 'File wallpaper harus berupa gambar.',
            'wallpaper.mimes' => 'Wallpaper harus berformat JPG, PNG, atau WEBP.',
            'wallpaper.max'   => 'Ukuran wallpaper maksimal 4 MB.',
        ]);

        $user = $request->user();
        $user->palet = $data['palet'];

        if ($request->hasFile('wallpaper')) {
            $this->hapusFile($user->wallpaper);
            $user->wallpaper = $request->file('wallpaper')->store('wallpapers', 'public');
        }

        $user->save();

        return back()->with('success', 'Tampilan berhasil disimpan.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => ['required', 'string', 'confirmed', 'different:current_password', new PasswordKuat($request->user()->name, $request->user()->email)],
        ], [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.different'                => 'Password baru harus berbeda dari password saat ini.',
            'password.confirmed'                => 'Konfirmasi password baru tidak cocok.',
        ]);

        // di-hash oleh cast; password sementara dianggap sudah diganti
        $request->user()->update(['password' => $request->password, 'must_change_password' => false]);

        return back()->with('success', 'Password berhasil diganti.');
    }

    public function hapusFoto(Request $request)
    {
        $user = $request->user();
        $this->hapusFile($user->foto);
        $user->foto = null;
        $user->save();

        return back()->with('success', 'Foto profil dihapus.');
    }

    public function hapusWallpaper(Request $request)
    {
        $user = $request->user();
        $this->hapusFile($user->wallpaper);
        $user->wallpaper = null;
        $user->save();

        return back()->with('success', 'Wallpaper dikembalikan ke warna palet.');
    }

    private function hapusFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}