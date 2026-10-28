<?php

use App\Models\User;
use App\Support\TampilanKustom;

// ---------- Validasi warna ----------

test('warna hex enam digit diterima', function (string $warna) {
    expect(TampilanKustom::warnaValid($warna))->toBeTrue();
})->with(['#000000', '#ffffff', '#A1b2C3', '#6366f1']);

test('warna selain hex enam digit ditolak', function (mixed $warna) {
    expect(TampilanKustom::warnaValid($warna))->toBeFalse();
})->with([
    'tanpa tanda pagar' => 'ffffff',
    'tiga digit' => '#fff',
    'nama warna' => 'red',
    'bukan hex' => '#12345g',
    'terlalu panjang' => '#1234567',
    'baris baru di akhir' => "#123456\n",
    'suntikan css' => '#123456;}body{display:none',
    'null' => null,
    'array' => [['#123456']],
]);

// ---------- Validasi URL ----------

test('url https yang aman diterima', function (string $url) {
    expect(TampilanKustom::urlValid($url))->toBeTrue();
})->with([
    'https://contoh.com/batik.png',
    'https://cdn.contoh.com/a/b/pola.webp?ukuran=besar&v=2',
    'https://contoh.com/animasi.gif',
]);

test('url yang berbahaya atau bukan https ditolak', function (mixed $url) {
    expect(TampilanKustom::urlValid($url))->toBeFalse();
})->with([
    'http biasa' => 'http://contoh.com/a.png',
    'javascript' => 'javascript:alert(1)',
    'data uri' => 'data:image/png;base64,AAAA',
    'protokol relatif' => '//contoh.com/a.png',
    'spasi' => 'https://contoh.com/a b.png',
    'kutip ganda' => 'https://contoh.com/a"onerror=alert(1)',
    'kutip tunggal' => "https://contoh.com/a'b",
    'kurung buka' => 'https://contoh.com/a(b',
    'kurung tutup' => 'https://contoh.com/a)b',
    'tanda kurung sudut' => 'https://contoh.com/</style><script>alert(1)</script>',
    'backslash' => 'https://contoh.com/a\\b',
    'backtick' => 'https://contoh.com/a`b',
    'baris baru di akhir' => "https://contoh.com/a.png\n",
    'baris baru di tengah' => "https://contoh.com/a\n.png",
    'terlalu panjang' => 'https://contoh.com/'.str_repeat('a', 500),
    'null' => null,
    'array' => [['https://contoh.com/a.png']],
]);

// ---------- Deteksi GIF ----------

test('animasi mengenali berkas gif dan bukan yang lain', function (string $sumber, bool $gif) {
    expect(TampilanKustom::animasi($sumber))->toBe($gif);
})->with([
    ['wallpapers/abc.gif', true],
    ['https://contoh.com/a.GIF', true],
    ['https://contoh.com/a.gif?x=1', true],
    ['https://contoh.com/a.gif#frame', true],
    ['wallpapers/abc.png', false],
    ['https://contoh.com/agif', false],
    ['https://contoh.com/a.gifx', false],
]);

// ---------- Kontras ----------

/**
 * Sampel warna: 5 tingkat tiap kanal RGB (125 warna) mencakup hitam, putih, dan warna jenuh.
 *
 * @return list<string>
 */
function sampelWarna(): array
{
    $tingkat = [0, 64, 128, 192, 255];
    $hasil = [];

    foreach ($tingkat as $r) {
        foreach ($tingkat as $g) {
            foreach ($tingkat as $b) {
                $hasil[] = sprintf('#%02x%02x%02x', $r, $g, $b);
            }
        }
    }

    return $hasil;
}

test('latar aksen untuk teks putih selalu memenuhi kontras 4,5:1', function () {
    foreach (sampelWarna() as $warna) {
        $hasil = TampilanKustom::sesuaikanUntukTeksPutih($warna);

        expect(TampilanKustom::kontras($hasil, '#ffffff'))->toBeGreaterThanOrEqual(4.5, "gagal untuk {$warna} -> {$hasil}");
    }
});

test('wallpaper mode terang selalu cukup terang untuk teks gray-600', function () {
    foreach (sampelWarna() as $warna) {
        $hasil = TampilanKustom::sesuaikanTerang($warna);

        expect(TampilanKustom::kontras($hasil, '#4b5563'))->toBeGreaterThanOrEqual(4.5, "gagal untuk {$warna} -> {$hasil}");
    }
});

test('wallpaper mode gelap selalu cukup gelap untuk teks slate-400', function () {
    foreach (sampelWarna() as $warna) {
        $hasil = TampilanKustom::sesuaikanGelap($warna);

        expect(TampilanKustom::kontras($hasil, '#94a3b8'))->toBeGreaterThanOrEqual(4.5, "gagal untuk {$warna} -> {$hasil}");
    }
});

test('warna yang sudah memenuhi syarat tidak diubah, hanya dijadikan huruf kecil', function () {
    expect(TampilanKustom::sesuaikanTerang('#FFFFFF'))->toBe('#ffffff')
        ->and(TampilanKustom::sesuaikanGelap('#000000'))->toBe('#000000')
        ->and(TampilanKustom::sesuaikanUntukTeksPutih('#1E293B'))->toBe('#1e293b');
});

test('warna yang terlalu terang untuk teks putih digelapkan, bukan diganti warna lain', function () {
    $hasil = TampilanKustom::sesuaikanUntukTeksPutih('#fde68a');

    expect($hasil)->not->toBe('#fde68a');

    // Kanal merah tetap yang terbesar: warna kuning tetap terasa kuning, hanya lebih gelap
    [$r, $g, $b] = TampilanKustom::komponen($hasil);
    expect($r)->toBeGreaterThanOrEqual($g)->and($g)->toBeGreaterThan($b);
});

test('luminansi dan kontras mengikuti rumus WCAG', function () {
    expect(TampilanKustom::luminansi('#000000'))->toBe(0.0)
        ->and(TampilanKustom::luminansi('#ffffff'))->toEqualWithDelta(1.0, 0.0001)
        ->and(TampilanKustom::kontras('#000000', '#ffffff'))->toEqualWithDelta(21.0, 0.01)
        ->and(TampilanKustom::kontras('#777777', '#777777'))->toEqualWithDelta(1.0, 0.0001);
});

// ---------- Aksen seluruh situs ----------

/**
 * @param  array<string, mixed>  $atribut
 */
function penggunaAksenUnit(array $atribut): User
{
    return new User($atribut);
}

/**
 * @return list<string> warna hex hasil dari variabel --ak-N-rgb pada css aksen
 */
function warnaDariCssAksen(string $css): array
{
    preg_match_all('/--ak-\d-rgb: (\d+) (\d+) (\d+);/', $css, $cocok, PREG_SET_ORDER);

    return array_map(fn (array $m) => sprintf('#%02x%02x%02x', $m[1], $m[2], $m[3]), $cocok);
}

test('aksen warna polos menghasilkan tiga variabel dan kelas penanda', function () {
    // #0F766E sudah cukup gelap untuk teks putih, jadi warna pertama dipakai apa adanya (huruf besar jadi kecil)
    $hasil = TampilanKustom::aksen(penggunaAksenUnit(['aksen_tipe' => 'polos', 'aksen_warna1' => '#0F766E']));

    expect($hasil['kelas'])->toBe(['has-aksen-warna'])
        ->and(warnaDariCssAksen($hasil['css']))->toHaveCount(3)
        ->and($hasil['css'])->toContain('--ak-1-rgb: 15 118 110;');
});

test('aksen gradasi memakai warna pertama, tengah, dan kedua', function () {
    $hasil = TampilanKustom::aksen(penggunaAksenUnit(['aksen_tipe' => 'gradasi', 'aksen_warna1' => '#1e3a8a', 'aksen_warna2' => '#7f1d1d']));

    // Kedua warna sudah cukup gelap untuk teks putih, jadi tidak diubah; warna tengah adalah campurannya
    expect(warnaDariCssAksen($hasil['css']))->toBe(['#1e3a8a', '#4f2c54', '#7f1d1d']);
});

test('semua warna aksen situs memenuhi kontras 4,5:1 dengan teks putih', function () {
    foreach (sampelWarna() as $warna) {
        foreach (warnaDariCssAksen(TampilanKustom::aksen(penggunaAksenUnit(['aksen_tipe' => 'polos', 'aksen_warna1' => $warna]))['css']) as $hasil) {
            expect(TampilanKustom::kontras($hasil, '#ffffff'))->toBeGreaterThanOrEqual(4.5, "gagal untuk polos {$warna} -> {$hasil}");
        }

        $gradasi = TampilanKustom::aksen(penggunaAksenUnit(['aksen_tipe' => 'gradasi', 'aksen_warna1' => $warna, 'aksen_warna2' => '#ffffff']));

        foreach (warnaDariCssAksen($gradasi['css']) as $hasil) {
            expect(TampilanKustom::kontras($hasil, '#ffffff'))->toBeGreaterThanOrEqual(4.5, "gagal untuk gradasi {$warna} -> {$hasil}");
        }
    }
});

test('aksen gambar dari link menjadi latar sapaan, mode ulang untuk batik', function () {
    $tutupi = TampilanKustom::aksen(penggunaAksenUnit(['aksen_tipe' => 'url', 'aksen_url' => 'https://contoh.com/batik.png', 'aksen_mode' => 'tutupi']));
    $ulang = TampilanKustom::aksen(penggunaAksenUnit(['aksen_tipe' => 'url', 'aksen_url' => 'https://contoh.com/batik.png', 'aksen_mode' => 'ulang']));

    expect($tutupi)->toBe(['kelas' => ['has-aksen-gambar'], 'css' => '--aksen-gambar: url("https://contoh.com/batik.png") center / cover no-repeat;'])
        ->and($ulang['css'])->toBe('--aksen-gambar: url("https://contoh.com/batik.png") top left / auto repeat;');
});

test('aksen yang tidak lengkap atau berbahaya diabaikan', function (array $atribut) {
    expect(TampilanKustom::aksen(penggunaAksenUnit($atribut)))->toBe(['kelas' => [], 'css' => '']);
})->with([
    'bawaan' => [['aksen_tipe' => 'preset', 'aksen_warna1' => '#ffffff']],
    'tipe kosong' => [[]],
    'polos tanpa warna' => [['aksen_tipe' => 'polos']],
    'polos warna rusak' => [['aksen_tipe' => 'polos', 'aksen_warna1' => '#12345;}']],
    'gradasi tanpa warna kedua' => [['aksen_tipe' => 'gradasi', 'aksen_warna1' => '#123456']],
    'link http' => [['aksen_tipe' => 'url', 'aksen_url' => 'http://contoh.com/a.png']],
    'link berbahaya' => [['aksen_tipe' => 'url', 'aksen_url' => 'https://x.com/a");}</style><script>alert(1)</script>']],
    'berkas tidak ada' => [['aksen_tipe' => 'foto']],
    'berkas path berbahaya' => [['aksen_tipe' => 'foto', 'aksen_gambar' => '../../etc/passwd']],
]);

test('warna aksen yang terlalu terang digelapkan, bukan dipakai apa adanya', function () {
    $hasil = TampilanKustom::aksen(penggunaAksenUnit(['aksen_tipe' => 'polos', 'aksen_warna1' => '#fde68a']));

    expect($hasil['css'])->not->toContain('--ak-1-rgb: 253 230 138;')
        ->and(TampilanKustom::kontras(warnaDariCssAksen($hasil['css'])[0], '#ffffff'))->toBeGreaterThanOrEqual(4.5);
});
