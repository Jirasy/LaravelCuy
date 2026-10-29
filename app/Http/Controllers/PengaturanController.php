<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\PasswordKuat;
use App\Support\TampilanKustom;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Pengaturan akun: tampilan (wallpaper, navbar, kartu profil) dan keamanan (password).
// Data profil (foto, nama, nomor HP, bio) ada di ProfilPenggunaController.
// Role TIDAK bisa diubah di sini.
class PengaturanController extends Controller
{
    public const PALET = ['aurora', 'sunset', 'ocean', 'sakura', 'forest'];

    // Alamat sementara untuk pratinjau berkas yang baru dipilih di peramban (belum tersimpan). JS menggantinya dengan blob URL.
    public const ALAMAT_PRATINJAU = 'https://pratinjau.invalid/berkas';

    public function edit(Request $request)
    {
        return view('pengaturan.edit', ['user' => $request->user()]);
    }

    public function updateTampilan(Request $request)
    {
        $user = $request->user();

        $data = $request->validate($this->aturanTampilan($request, $user), $this->pesanTampilan());

        $kolom = Arr::except($data, ['wallpaper', 'aksen_gambar']);

        // Hex disimpan huruf kecil supaya seragam
        foreach (['wallpaper_warna1', 'wallpaper_warna2', 'aksen_warna1', 'aksen_warna2'] as $warna) {
            if (isset($kolom[$warna])) {
                $kolom[$warna] = strtolower($kolom[$warna]);
            }
        }

        $user->fill($kolom);

        if ($request->hasFile('wallpaper')) {
            $this->hapusFile($user->wallpaper);
            $user->wallpaper = $request->file('wallpaper')->store('wallpapers', 'public');
        }

        if ($request->hasFile('aksen_gambar')) {
            $this->hapusFile($user->aksen_gambar);
            $user->aksen_gambar = $request->file('aksen_gambar')->store('aksen', 'public');
        }

        $user->save();

        return back()->with('success', 'Tampilan berhasil disimpan.');
    }

    // Pratinjau langsung di halaman Pengaturan (wallpaper, aksen situs, kartu profil). Memakai TampilanKustom yang sama dengan
    // tampilan sebenarnya, jadi warna yang disesuaikan kontrasnya persis sama. Tidak ada yang disimpan.
    public function pratinjau(Request $request): JsonResponse
    {
        $isian = collect($request->only([
            'wallpaper_tipe', 'wallpaper_warna1', 'wallpaper_warna2', 'wallpaper_url', 'wallpaper_mode',
            'aksen_tipe', 'aksen_warna1', 'aksen_warna2', 'aksen_url', 'aksen_mode',
            'kartu_gaya', 'kartu_aksen',
        ]))->filter(fn ($nilai) => is_string($nilai))->all();

        $contoh = $request->user()->replicate();
        $contoh->forceFill($isian);

        // Berkas yang baru dipilih (belum diunggah) dipratinjau lewat alamat sementara; ".gif" menandai animasi
        foreach (['wallpaper' => ['wallpaper_tipe', 'wallpaper_url'], 'aksen' => ['aksen_tipe', 'aksen_url']] as $nama => [$tipe, $url]) {
            $jenis = $request->input("{$nama}_berkas");

            if ($contoh->{$tipe} === 'foto' && in_array($jenis, ['gif', 'gambar'], true)) {
                $contoh->{$tipe} = 'url';
                $contoh->{$url} = self::ALAMAT_PRATINJAU.($jenis === 'gif' ? '.gif' : '');
            }
        }

        return response()->json([
            'wallpaper' => TampilanKustom::wallpaper($contoh),
            'aksen' => TampilanKustom::aksen($contoh),
            'kartu' => TampilanKustom::kartu($contoh),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'string', 'confirmed', 'different:current_password', new PasswordKuat($request->user()->name, $request->user()->email)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // di-hash oleh cast; password sementara dianggap sudah diganti
        $request->user()->update(['password' => $request->password, 'must_change_password' => false]);

        // Password sementara yang tersimpan untuk admin tidak berlaku lagi -> hapus
        \App\Models\KredensialSementara::where('user_id', $request->user()->id)->delete();

        return back()->with('success', 'Password berhasil diganti.');
    }

    public function hapusWallpaper(Request $request)
    {
        $user = $request->user();
        $this->hapusFile($user->wallpaper);
        $user->wallpaper = null;

        if ($user->wallpaper_tipe === 'foto') {
            $user->wallpaper_tipe = 'default';
        }

        $user->save();

        return back()->with('success', 'Wallpaper dikembalikan ke warna palet.');
    }

    public function hapusAksen(Request $request)
    {
        $user = $request->user();
        $this->hapusFile($user->aksen_gambar);
        $user->aksen_gambar = null;

        if ($user->aksen_tipe === 'foto') {
            $user->aksen_tipe = 'preset';
        }

        $user->save();

        return back()->with('success', 'Gambar aksen dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function aturanTampilan(Request $request, User $user): array
    {
        $warna = ['nullable', 'regex:'.TampilanKustom::POLA_WARNA];

        $linkGambar = function (string $atribut, mixed $nilai, Closure $gagal): void {
            if ($nilai !== null && $nilai !== '' && ! TampilanKustom::urlValid($nilai)) {
                $gagal('Link gambar harus diawali https://, maksimal 500 karakter, dan tidak boleh memuat spasi, tanda kutip, atau kurung.');
            }
        };

        $jenisGambar = ['image', 'mimes:jpg,jpeg,png,webp,gif'];

        return [
            'palet' => ['required', Rule::in(self::PALET)],
            'navbar_gaya' => ['required', Rule::in(User::NAVBAR_GAYA)],
            'kartu_gaya' => ['required', Rule::in(User::KARTU_GAYA)],
            'kartu_aksen' => ['required', Rule::in(array_merge(['ikut_palet'], array_keys(User::AKSEN_RGB)))],

            'wallpaper_tipe' => ['required', Rule::in(TampilanKustom::TIPE_WALLPAPER)],
            'wallpaper_warna1' => array_merge($warna, ['required_if:wallpaper_tipe,polos,gradasi']),
            'wallpaper_warna2' => array_merge($warna, ['required_if:wallpaper_tipe,gradasi']),
            'wallpaper_url' => ['nullable', 'required_if:wallpaper_tipe,url', $linkGambar],
            'wallpaper_mode' => ['required', Rule::in(TampilanKustom::MODE)],
            'wallpaper' => array_merge(
                ['nullable', Rule::requiredIf(fn () => $request->input('wallpaper_tipe') === 'foto' && ! $user->wallpaper)],
                $jenisGambar,
                ['max:4096']
            ),

            'aksen_tipe' => ['required', Rule::in(TampilanKustom::TIPE_AKSEN)],
            'aksen_warna1' => array_merge($warna, ['required_if:aksen_tipe,polos,gradasi']),
            'aksen_warna2' => array_merge($warna, ['required_if:aksen_tipe,gradasi']),
            'aksen_url' => ['nullable', 'required_if:aksen_tipe,url', $linkGambar],
            'aksen_mode' => ['required', Rule::in(TampilanKustom::MODE)],
            'aksen_gambar' => array_merge(
                ['nullable', Rule::requiredIf(fn () => $request->input('aksen_tipe') === 'foto' && ! $user->aksen_gambar)],
                $jenisGambar,
                ['max:4096']
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function pesanTampilan(): array
    {
        return [
            'wallpaper.required' => 'Pilih berkas foto atau GIF untuk wallpaper.',
            'wallpaper.image' => 'File wallpaper harus berupa gambar.',
            'wallpaper.mimes' => 'Wallpaper harus berformat JPG, PNG, WEBP, atau GIF.',
            'wallpaper.max' => 'Ukuran wallpaper maksimal 4 MB.',
            'aksen_gambar.required' => 'Pilih berkas gambar untuk aksen kartu.',
            'aksen_gambar.image' => 'File aksen harus berupa gambar.',
            'aksen_gambar.mimes' => 'Gambar aksen harus berformat JPG, PNG, WEBP, atau GIF.',
            'aksen_gambar.max' => 'Ukuran gambar aksen maksimal 4 MB.',
            'wallpaper_warna1.required_if' => 'Pilih warna wallpaper.',
            'wallpaper_warna2.required_if' => 'Pilih warna kedua untuk gradasi wallpaper.',
            'wallpaper_warna1.regex' => 'Warna wallpaper tidak valid.',
            'wallpaper_warna2.regex' => 'Warna kedua wallpaper tidak valid.',
            'wallpaper_url.required_if' => 'Isi link gambar untuk wallpaper.',
            'aksen_warna1.required_if' => 'Pilih warna aksen kartu.',
            'aksen_warna2.required_if' => 'Pilih warna kedua untuk gradasi aksen.',
            'aksen_warna1.regex' => 'Warna aksen tidak valid.',
            'aksen_warna2.regex' => 'Warna kedua aksen tidak valid.',
            'aksen_url.required_if' => 'Isi link gambar untuk aksen kartu.',
        ];
    }

    private function hapusFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
