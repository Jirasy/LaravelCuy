<?php

namespace App\Support;

// NISN disimpan dan diperlakukan sebagai TEKS (kolom string/VARCHAR), bukan angka: boleh diawali nol ("0051234567") dan
// boleh memakai pemisah seperti titik, strip, atau garis miring ("2526.0.10.001"). Satu tempat untuk aturan formatnya.
class Nisn
{
    // 5-30 karakter: huruf/angka, boleh ada . - / di tengah, diawali dan diakhiri huruf/angka
    public const POLA = '/^(?=.{5,30}$)[A-Za-z0-9](?:[A-Za-z0-9.\-\/]*[A-Za-z0-9])$/';

    public const PESAN = 'NISN hanya boleh huruf, angka, titik, strip, atau garis miring (5-30 karakter), mis. 0051234567 atau 2526.0.10.001. Kosongkan kalau belum ada.';

    // Rapikan isian: buang spasi (termasuk spasi tak terlihat), dan ".0" sisa pembacaan sel angka Excel ("3001234567.0").
    // Titik pemisah yang sebenarnya ("2526.0.10.001") dibiarkan apa adanya. Kosong = null.
    public static function bersihkan(?string $nilai): ?string
    {
        $nisn = preg_replace('/[\s\x{00A0}\x{200B}\x{FEFF}]+/u', '', (string) $nilai) ?? '';

        if (preg_match('/^(\d+)\.0+$/', $nisn, $m)) {
            $nisn = $m[1];
        }

        return $nisn === '' ? null : $nisn;
    }

    public static function valid(?string $nisn): bool
    {
        return $nisn !== null && preg_match(self::POLA, $nisn) === 1;
    }

    // Dari berkas daftar siswa: format harus valid dan memuat minimal $minDigit angka, supaya nama atau
    // keterangan yang salah kolom tidak dianggap NISN.
    public static function dariBerkas(string $sel, int $minDigit = 4): ?string
    {
        $nisn = self::bersihkan($sel);

        if (! self::valid($nisn) || preg_match_all('/\d/', $nisn) < $minDigit) {
            return null;
        }

        return $nisn;
    }
}
