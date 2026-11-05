<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Support\Gambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Logo ekskul: dipasang, diganti, atau dihapus oleh admin, pembina ekskul itu, atau penanggung jawab anggota
// (hak dicek lewat Gate "ubahLogo", bukan lewat peran, supaya siswa penanggung jawab tetap bisa).
// Disimpan di disk "public" karena logo tampil di katalog untuk semua orang yang login.
class EkskulLogoController extends Controller
{
    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('ubahLogo', $ekstrakurikuler);

        // "image" dan "mimes" sengaja tidak menyertakan SVG: SVG bisa memuat skrip.
        $request->validate([
            'logo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=64,min_height=64'],
        ], [
            'logo.required'   => 'Pilih berkas logo terlebih dahulu.',
            'logo.uploaded'   => 'Logo gagal diunggah. Kemungkinan ukurannya melebihi batas server (upload_max_filesize dan post_max_size di php.ini).',
            'logo.image'      => 'Berkas harus berupa gambar.',
            'logo.mimes'      => 'Logo harus berformat PNG, JPG, atau WEBP.',
            'logo.max'        => 'Ukuran logo maksimal 2 MB.',
            'logo.dimensions' => 'Logo terlalu kecil. Minimal 64 × 64 piksel (disarankan 512 × 512).',
        ]);

        $file = $request->file('logo');
        $png  = Gambar::logo($file->getRealPath());

        // Nama acak setiap kali: logo baru langsung tampil tanpa tersangkut cache peramban
        $nama = "logo-ekskul/{$ekstrakurikuler->id}-".Str::lower(Str::random(8));
        $path = $png !== null
            ? "{$nama}.png"
            : "{$nama}.".$file->guessExtension();

        if ($png !== null) {
            Storage::disk('public')->put($path, $png);
        } else {
            // GD tidak tersedia / gagal: simpan berkas aslinya (sudah lolos validasi gambar di atas)
            Storage::disk('public')->putFileAs('logo-ekskul', $file, basename($path));
        }

        $lama = $ekstrakurikuler->logo;
        $ekstrakurikuler->update(['logo' => $path]);

        if ($lama) {
            Storage::disk('public')->delete($lama);
        }

        return back()->with('success', 'Logo '.$ekstrakurikuler->nama_tampil.' berhasil disimpan.');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        Gate::authorize('ubahLogo', $ekstrakurikuler);

        if ($ekstrakurikuler->logo) {
            Storage::disk('public')->delete($ekstrakurikuler->logo);
            $ekstrakurikuler->update(['logo' => null]);
        }

        return back()->with('success', 'Logo dihapus. Katalog kembali memakai monogram.');
    }
}
