<?php

namespace App\Support;

// Membuat thumbnail memakai GD bawaan PHP. Kalau GD tidak aktif atau gambarnya terlalu besar,
// dikembalikan false dan galeri memakai foto asli (tetap jalan, hanya lebih berat).
class Gambar
{
    // Mengolah logo unggahan menjadi PNG yang bersih: sisi terpanjang dibatasi $sisi px (tidak pernah diperbesar),
    // transparansi dipertahankan (beda dengan thumbnail() yang berlatar putih), dan karena digambar ulang dari piksel,
    // EXIF serta data tersembunyi di dalam berkas ikut terbuang. Mengembalikan isi PNG, atau null kalau GD tidak bisa
    // memprosesnya (pemanggil lalu menyimpan berkas aslinya yang sudah lolos validasi gambar).
    public static function logo(string $sumber, int $sisi = 512): ?string
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagepng')) {
            return null;
        }

        $info = @getimagesize($sumber);
        if (! $info) {
            return null;
        }

        [$w, $h] = $info;

        // Batas yang sama dengan thumbnail(): cegah kehabisan memori pada gambar berukuran raksasa
        if ($w < 1 || $h < 1 || $w * $h > 25_000_000) {
            return null;
        }

        $gambar = match ($info[2]) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($sumber) : false,
            IMAGETYPE_PNG  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($sumber) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sumber) : false,
            default        => false,
        };

        if (! $gambar) {
            return null;
        }

        $skala = min(1, $sisi / max($w, $h));
        $lebar = max(1, (int) round($w * $skala));
        $tinggi = max(1, (int) round($h * $skala));

        $hasil = imagecreatetruecolor($lebar, $tinggi);
        imagealphablending($hasil, false);
        imagesavealpha($hasil, true);
        imagefill($hasil, 0, 0, imagecolorallocatealpha($hasil, 0, 0, 0, 127));
        imagecopyresampled($hasil, $gambar, 0, 0, 0, 0, $lebar, $tinggi, $w, $h);

        ob_start();
        $berhasil = imagepng($hasil, null, 9);
        $png = ob_get_clean();

        imagedestroy($gambar);
        imagedestroy($hasil);

        return $berhasil && $png !== false && $png !== '' ? $png : null;
    }

    public static function thumbnail(string $sumber, string $tujuan, int $lebar = 480): bool
    {
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
            return false;
        }

        $info = @getimagesize($sumber);
        if (!$info) {
            return false;
        }

        [$w, $h] = $info;
        $tipe = $info[2];

        // Cegah kehabisan memori pada foto resolusi sangat tinggi (> 25 megapiksel)
        if ($w < 1 || $h < 1 || $w * $h > 25_000_000) {
            return false;
        }

        $gambar = match ($tipe) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($sumber) : false,
            IMAGETYPE_PNG  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($sumber) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sumber) : false,
            default        => false,
        };

        if (!$gambar) {
            return false;
        }

        // Foto dari HP sering disimpan miring dan hanya ditandai lewat EXIF: putar sesuai tandanya
        if ($tipe === IMAGETYPE_JPEG && function_exists('exif_read_data') && function_exists('imagerotate')) {
            $exif  = @exif_read_data($sumber);
            $sudut = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;

            if ($sudut) {
                $putar = imagerotate($gambar, $sudut, 0);
                if ($putar) {
                    imagedestroy($gambar);
                    $gambar = $putar;
                }
            }

            $w = imagesx($gambar);
            $h = imagesy($gambar);
        }

        $baruLebar  = min($lebar, $w);
        $baruTinggi = max(1, (int) round($h * $baruLebar / $w));

        $kecil = imagecreatetruecolor($baruLebar, $baruTinggi);
        imagefill($kecil, 0, 0, imagecolorallocate($kecil, 255, 255, 255));   // latar putih untuk PNG/WEBP transparan
        imagecopyresampled($kecil, $gambar, 0, 0, 0, 0, $baruLebar, $baruTinggi, $w, $h);

        $berhasil = imagejpeg($kecil, $tujuan, 80);

        imagedestroy($gambar);
        imagedestroy($kecil);

        return (bool) $berhasil;
    }
}