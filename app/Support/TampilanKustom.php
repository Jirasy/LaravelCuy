<?php

namespace App\Support;

use App\Models\User;
use Closure;

/**
 * Tampilan kustom pengguna: wallpaper dan aksen kartu profil (warna polos, gradasi, gambar, pola batik).
 *
 * Dua hal dijaga di sini:
 * 1. Keamanan: warna dan URL dari pengguna dicek ketat (juga saat dirender) supaya tidak bisa
 *    menyuntikkan CSS atau HTML ke halaman.
 * 2. Keterbacaan: warna pilihan pengguna digeser otomatis (hue tetap) sampai teks bawaan tetap terbaca.
 */
class TampilanKustom
{
    public const TIPE_WALLPAPER = ['default', 'polos', 'gradasi', 'foto', 'url'];

    public const TIPE_AKSEN = ['preset', 'polos', 'gradasi', 'foto', 'url'];

    public const MODE = ['tutupi', 'ulang'];

    // #rrggbb. Modifier D: "$" hanya cocok di ujung string, bukan sebelum baris baru terakhir.
    public const POLA_WARNA = '/^#[0-9a-fA-F]{6}$/D';

    // https saja; tanpa spasi, kutip, kurung, backslash, tanda kurung sudut, atau backtick (cegah suntikan CSS/HTML). Modifier D: lihat POLA_WARNA.
    public const POLA_URL = '#^https://[^\s"\'()\\\\<>`]+$#D';

    // Teks di atas wallpaper: gray-600 (mode terang) harus tetap >= 4,5:1 dan slate-400 (mode gelap) juga
    private const LUMINANSI_MIN_TERANG = 0.6;

    private const LUMINANSI_MAKS_GELAP = 0.04;

    // Teks putih di atas aksen: syarat WCAG AA 4,5:1 ditambah sedikit cadangan
    private const KONTRAS_TEKS_PUTIH = 4.6;

    // Lapisan gelap di atas gambar aksen supaya teks putih tetap terbaca (gambar putih pun lolos 4,5:1)
    private const LAPISAN_GAMBAR = 'linear-gradient(rgba(15, 23, 42, .6), rgba(15, 23, 42, .6))';

    // ---------- Validasi ----------

    public static function warnaValid(mixed $nilai): bool
    {
        return is_string($nilai) && preg_match(self::POLA_WARNA, $nilai) === 1;
    }

    public static function urlValid(mixed $nilai): bool
    {
        return is_string($nilai) && strlen($nilai) <= 500 && preg_match(self::POLA_URL, $nilai) === 1;
    }

    // Path hasil store() Laravel, mis. "aksen/abc123.jpg"
    private static function pathAman(mixed $nilai): bool
    {
        return is_string($nilai) && preg_match('#^[\w\-./]+$#D', $nilai) === 1 && ! str_contains($nilai, '..');
    }

    public static function animasi(string $urlAtauPath): bool
    {
        return preg_match('/\.gif(?:$|[?#])/i', $urlAtauPath) === 1;
    }

    // ---------- Warna & kontras ----------

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function komponen(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    // Luminansi relatif WCAG (0 = hitam, 1 = putih)
    public static function luminansi(string $hex): float
    {
        [$r, $g, $b] = array_map(function (int $c): float {
            $s = $c / 255;

            return $s <= 0.03928 ? $s / 12.92 : (($s + 0.055) / 1.055) ** 2.4;
        }, self::komponen($hex));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    public static function kontras(string $hexA, string $hexB): float
    {
        $a = self::luminansi($hexA);
        $b = self::luminansi($hexB);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

    // $bagian = 0 menghasilkan $hex, 1 menghasilkan $target
    public static function campur(string $hex, string $target, float $bagian): string
    {
        $dari = self::komponen($hex);
        $ke = self::komponen($target);

        return sprintf('#%02x%02x%02x', ...array_map(
            fn (int $a, int $b) => (int) round($a + ($b - $a) * $bagian),
            $dari,
            $ke
        ));
    }

    // Mode terang: wallpaper dipucatkan sampai teks gelap di atasnya terbaca
    public static function sesuaikanTerang(string $hex): string
    {
        return self::geserKe($hex, '#ffffff', fn (string $h) => self::luminansi($h) >= self::LUMINANSI_MIN_TERANG);
    }

    // Mode gelap: wallpaper dituakan sampai teks terang di atasnya terbaca
    public static function sesuaikanGelap(string $hex): string
    {
        return self::geserKe($hex, '#000000', fn (string $h) => self::luminansi($h) <= self::LUMINANSI_MAKS_GELAP);
    }

    // Latar aksen kartu: digelapkan sampai teks putih memenuhi kontras
    public static function sesuaikanUntukTeksPutih(string $hex): string
    {
        return self::geserKe($hex, '#000000', fn (string $h) => self::kontras($h, '#ffffff') >= self::KONTRAS_TEKS_PUTIH);
    }

    // Geser warna sedikit demi sedikit menuju $target, berhenti di titik pertama yang memenuhi syarat
    private static function geserKe(string $hex, string $target, Closure $cukup): string
    {
        $hex = strtolower($hex);

        if ($cukup($hex)) {
            return $hex;
        }

        $rendah = 0.0;
        $tinggi = 1.0;   // $target sendiri (putih/hitam) selalu memenuhi syarat

        for ($i = 0; $i < 14; $i++) {
            $tengah = ($rendah + $tinggi) / 2;

            if ($cukup(self::campur($hex, $target, $tengah))) {
                $tinggi = $tengah;
            } else {
                $rendah = $tengah;
            }
        }

        return self::campur($hex, $target, $tinggi);
    }

    // ---------- Wallpaper ----------

    /**
     * Kelas untuk <html> dan deklarasi variabel CSS untuk :root. Nilai selalu lolos validasi, jadi aman dicetak apa adanya.
     *
     * @return array{kelas: list<string>, css: string}
     */
    public static function wallpaper(User $user): array
    {
        $kosong = ['kelas' => [], 'css' => ''];
        $ulang = $user->wallpaper_mode === 'ulang' ? ['wall-ulang'] : [];

        switch ($user->wallpaper_tipe) {
            case 'polos':
                if (! self::warnaValid($user->wallpaper_warna1)) {
                    return $kosong;
                }

                return [
                    'kelas' => ['has-wall-warna'],
                    'css' => sprintf(
                        '--wall-warna-terang: %s; --wall-warna-gelap: %s;',
                        self::sesuaikanTerang($user->wallpaper_warna1),
                        self::sesuaikanGelap($user->wallpaper_warna1)
                    ),
                ];

            case 'gradasi':
                if (! self::warnaValid($user->wallpaper_warna1) || ! self::warnaValid($user->wallpaper_warna2)) {
                    return $kosong;
                }

                return [
                    'kelas' => ['has-wall-warna'],
                    'css' => sprintf(
                        '--wall-warna-terang: linear-gradient(135deg, %s, %s); --wall-warna-gelap: linear-gradient(135deg, %s, %s);',
                        self::sesuaikanTerang($user->wallpaper_warna1),
                        self::sesuaikanTerang($user->wallpaper_warna2),
                        self::sesuaikanGelap($user->wallpaper_warna1),
                        self::sesuaikanGelap($user->wallpaper_warna2)
                    ),
                ];

            case 'foto':
                $sumber = self::pathAman($user->wallpaper) ? asset('storage/'.$user->wallpaper) : null;
                break;

            case 'url':
                $sumber = self::urlValid($user->wallpaper_url) ? $user->wallpaper_url : null;
                break;

            default:
                $sumber = null;
        }

        if ($sumber === null) {
            return $kosong;
        }

        return [
            'kelas' => array_merge(['has-wall'], $ulang, self::animasi($sumber) ? ['wall-anim'] : []),
            'css' => sprintf('--wall: url("%s");', $sumber),
        ];
    }

    // ---------- Aksen kartu profil ----------

    /**
     * Kelas dan style inline untuk kartu profil.
     * Warna polos mengikuti gaya kartu pilihan; gradasi dan gambar memakai kartu penuh (pk-kustom) berteks putih.
     *
     * @return array{kelas: string, style: string}
     */
    public static function kartu(User $user): array
    {
        $gaya = in_array($user->kartu_gaya, User::KARTU_GAYA, true) ? $user->kartu_gaya : 'kaca';
        $preset = [
            'kelas' => "pk-{$gaya}",
            'style' => '--pk-rgb: '.(User::AKSEN_RGB[$user->kartu_aksen ?? 'ikut_palet'] ?? 'var(--p1-rgb)'),
        ];
        $ulang = $user->aksen_mode === 'ulang';

        switch ($user->aksen_tipe) {
            case 'polos':
                if (! self::warnaValid($user->aksen_warna1)) {
                    return $preset;
                }

                // Gaya "gradasi" memakai teks putih, jadi warnanya perlu digelapkan seperlunya
                $hex = $gaya === 'gradasi' ? self::sesuaikanUntukTeksPutih($user->aksen_warna1) : strtolower($user->aksen_warna1);

                return ['kelas' => "pk-{$gaya}", 'style' => '--pk-rgb: '.implode(' ', self::komponen($hex))];

            case 'gradasi':
                if (! self::warnaValid($user->aksen_warna1) || ! self::warnaValid($user->aksen_warna2)) {
                    return $preset;
                }

                return [
                    'kelas' => 'pk-kustom',
                    'style' => sprintf(
                        '--pk-latar: linear-gradient(135deg, %s, %s)',
                        self::sesuaikanUntukTeksPutih($user->aksen_warna1),
                        self::sesuaikanUntukTeksPutih($user->aksen_warna2)
                    ),
                ];

            case 'foto':
            case 'url':
                $sumber = self::sumberAksen($user);
                break;

            default:
                $sumber = null;
        }

        if ($sumber === null) {
            return $preset;
        }

        return [
            'kelas' => 'pk-kustom',
            'style' => sprintf('--pk-latar: %s, %s', self::LAPISAN_GAMBAR, self::latarGambar($sumber, $ulang)),
        ];
    }

    // ---------- Aksen seluruh situs ----------

    /**
     * Aksen untuk seluruh situs: sapaan, kartu rekrutmen OSIS, navbar, dan tombol. Kartu profil tetap diatur kartu().
     * Warna polos atau gradasi menggantikan warna palet (--p1-rgb sampai --p3-rgb, lewat --ak-1-rgb sampai --ak-3-rgb).
     * Gambar atau pola mengisi latar sapaan dan kartu OSIS (--aksen-gambar) dan menjadi pita motif di tepi navbar.
     * Warna disesuaikan supaya teks putih di atasnya (tombol) tetap terbaca.
     *
     * @return array{kelas: list<string>, css: string}
     */
    public static function aksen(User $user): array
    {
        $kosong = ['kelas' => [], 'css' => ''];

        switch ($user->aksen_tipe) {
            case 'polos':
                if (! self::warnaValid($user->aksen_warna1)) {
                    return $kosong;
                }

                $dasar = strtolower($user->aksen_warna1);
                $warna = [$dasar, self::campur($dasar, '#ffffff', 0.2), self::campur($dasar, '#000000', 0.2)];
                break;

            case 'gradasi':
                if (! self::warnaValid($user->aksen_warna1) || ! self::warnaValid($user->aksen_warna2)) {
                    return $kosong;
                }

                $awal = strtolower($user->aksen_warna1);
                $akhir = strtolower($user->aksen_warna2);
                $warna = [$awal, self::campur($awal, $akhir, 0.5), $akhir];
                break;

            case 'foto':
            case 'url':
                $sumber = self::sumberAksen($user);

                if ($sumber === null) {
                    return $kosong;
                }

                return [
                    'kelas' => ['has-aksen-gambar'],
                    'css' => sprintf('--aksen-gambar: %s;', self::latarGambar($sumber, $user->aksen_mode === 'ulang')),
                ];

            default:
                return $kosong;
        }

        $css = [];

        foreach (array_values($warna) as $urutan => $hex) {
            $css[] = sprintf('--ak-%d-rgb: %s;', $urutan + 1, implode(' ', self::komponen(self::sesuaikanUntukTeksPutih($hex))));
        }

        return ['kelas' => ['has-aksen-warna'], 'css' => implode(' ', $css)];
    }

    // Sumber gambar aksen yang sudah lolos validasi (berkas unggahan atau link https), atau null
    private static function sumberAksen(User $user): ?string
    {
        return match ($user->aksen_tipe) {
            'foto' => self::pathAman($user->aksen_gambar) ? asset('storage/'.$user->aksen_gambar) : null,
            'url' => self::urlValid($user->aksen_url) ? $user->aksen_url : null,
            default => null,
        };
    }

    // Nilai properti "background" untuk satu gambar: tutupi seluruh area, atau diulang (pola batik)
    private static function latarGambar(string $sumber, bool $ulang): string
    {
        return sprintf('url("%s") %s', $sumber, $ulang ? 'top left / auto repeat' : 'center / cover no-repeat');
    }
}
