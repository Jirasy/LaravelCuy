<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\PasswordKuat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Pengaturan akun: tampilan (wallpaper, navbar, kartu profil) dan keamanan (password).
// Data profil (foto, nama, nomor HP, bio) ada di ProfilPenggunaController.
// Role TIDAK bisa diubah di sini.
class PengaturanController extends Controller
{
    public const PALET = ['aurora', 'sunset', 'ocean', 'sakura', 'forest'];

    public function edit(Request $request)
    {
        return view('pengaturan.edit', ['user' => $request->user()]);
    }

    public function updateTampilan(Request $request)
    {
        $data = $request->validate([
            'palet'       => ['required', Rule::in(self::PALET)],
            'navbar_gaya' => ['required', Rule::in(User::NAVBAR_GAYA)],
            'kartu_gaya'  => ['required', Rule::in(User::KARTU_GAYA)],
            'kartu_aksen' => ['required', Rule::in(array_merge(['ikut_palet'], array_keys(User::AKSEN_RGB)))],
            'wallpaper'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'wallpaper.image' => 'File wallpaper harus berupa gambar.',
            'wallpaper.mimes' => 'Wallpaper harus berformat JPG, PNG, atau WEBP.',
            'wallpaper.max'   => 'Ukuran wallpaper maksimal 4 MB.',
        ]);

        $user = $request->user();
        $user->palet       = $data['palet'];
        $user->navbar_gaya = $data['navbar_gaya'];
        $user->kartu_gaya  = $data['kartu_gaya'];
        $user->kartu_aksen = $data['kartu_aksen'];

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