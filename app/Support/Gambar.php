<?php

namespace App\Support;

// Membuat thumbnail memakai GD bawaan PHP. Kalau GD tidak aktif atau gambarnya terlalu besar,
// dikembalikan false dan galeri memakai foto asli (tetap jalan, hanya lebih berat).
class Gambar
{
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