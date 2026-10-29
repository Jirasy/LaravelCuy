<?php

namespace App\Support;

// Password sementara acak yang kuat dan enak dibacakan/diketik: tanpa karakter yang mudah tertukar
// (0 O 1 l I). Selalu memuat huruf besar, huruf kecil, angka, dan simbol (memenuhi aturan PasswordKuat).
class PasswordAcak
{
    private const BESAR  = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    private const KECIL  = 'abcdefghijkmnpqrstuvwxyz';
    private const ANGKA  = '23456789';
    private const SIMBOL = '!@#$%&*?';

    public static function buat(int $panjang = 14): string
    {
        $panjang = max(12, $panjang);
        $semua   = self::BESAR . self::KECIL . self::ANGKA . self::SIMBOL;

        $karakter = [self::ambil(self::BESAR), self::ambil(self::KECIL), self::ambil(self::ANGKA), self::ambil(self::SIMBOL)];
        while (count($karakter) < $panjang) {
            $karakter[] = self::ambil($semua);
        }

        // Kocok (Fisher-Yates) memakai random_int agar posisi karakter wajib tidak bisa ditebak
        for ($i = count($karakter) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$karakter[$i], $karakter[$j]] = [$karakter[$j], $karakter[$i]];
        }

        return implode('', $karakter);
    }

    private static function ambil(string $himpunan): string
    {
        return $himpunan[random_int(0, strlen($himpunan) - 1)];
    }
}
