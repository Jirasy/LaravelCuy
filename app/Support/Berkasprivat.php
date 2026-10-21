<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Berkas materi dan PR disimpan di disk PRIVAT (gak bisa dibuka lewat URL langsung),
// dan cuma disajiin lewat controller yang udah menecek hak akses.
class BerkasPrivat
{
    // Simpan dengan nama acak. Mengembalikan kolom: berkas, nama_berkas, mime
    public static function simpan(UploadedFile $file, string $folder): array
    {
        $asli = preg_replace('/[^\w\-. ]+/u', '_', $file->getClientOriginalName());

        return [
            'berkas'      => $file->store($folder, 'local'),
            'nama_berkas' => Str::limit($asli, 120, ''),
            'mime'        => $file->getMimeType() ?: 'application/octet-stream',
        ];
    }

    public static function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    // $langsung = tampil di browser (video/foto/PDF). Selain itu diunduh.
    public static function kirim(string $path, ?string $nama, ?string $mime, bool $langsung)
    {
        abort_unless(Storage::disk('local')->exists($path), 404, 'Berkas tidak ditemukan.');

        $penuh   = Storage::disk('local')->path($path);
        $headers = [
            'Content-Type'           => $mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return $langsung
            ? response()->file($penuh, $headers)                       // mendukung seek video (Range)
            : response()->download($penuh, $nama ?: basename($path), $headers);
    }
}