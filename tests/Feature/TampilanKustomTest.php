<?php

use App\Models\User;
use App\Support\TampilanKustom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('public');
});

function penggunaTampilan(string $role = 'siswa', array $atribut = []): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true] + $atribut);
}

/**
 * Isian formulir Pengaturan yang valid. Setiap tes cukup menimpa bagian yang diuji.
 *
 * @param  array<string, mixed>  $timpa
 * @return array<string, mixed>
 */
function isianTampilan(array $timpa = []): array
{
    return $timpa + [
        'palet' => 'aurora',
        'navbar_gaya' => 'melayang',
        'kartu_gaya' => 'kaca',
        'kartu_aksen' => 'ikut_palet',
        'wallpaper_tipe' => 'default',
        'wallpaper_mode' => 'tutupi',
        'aksen_tipe' => 'preset',
        'aksen_mode' => 'tutupi',
    ];
}

function gambarGifTampilan(string $nama = 'animasi.gif'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($nama, base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));
}

/**
 * Skrip di <head> yang memasang kelas wallpaper ke <html>. CSS statis di halaman juga menyebut nama-nama kelas ini,
 * jadi hanya skrip inilah yang membuktikan kelas benar-benar dipasang.
 */
function skripKelasWallpaper(string ...$kelas): string
{
    return 'classList.add(...'.json_encode($kelas).');';
}

/** Elemen kartu profil dengan kelasnya (CSS statis juga menyebut "pk-kustom", jadi cek atribut class-nya). */
function kartuBerkelas(string $kelas): string
{
    return 'class="pk '.$kelas.'"';
}

function simpanTampilan(User $user, array $timpa = []): TestResponse
{
    return test()->actingAs($user)->put(route('pengaturan.tampilan'), isianTampilan($timpa));
}

// ---------- Halaman & penyimpanan ----------

test('halaman pengaturan menampilkan pilihan wallpaper dan aksen', function () {
    $this->actingAs(penggunaTampilan())
        ->get(route('pengaturan.edit'))
        ->assertOk()
        ->assertSee('name="wallpaper_tipe"', false)
        ->assertSee('name="aksen_tipe"', false)
        ->assertSee('name="wallpaper_url"', false)
        ->assertSee('name="aksen_gambar"', false);
});

test('warna polos untuk wallpaper tersimpan dengan huruf kecil', function () {
    $user = penggunaTampilan();

    simpanTampilan($user, ['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#A1B2C3'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($user->fresh())
        ->wallpaper_tipe->toBe('polos')
        ->wallpaper_warna1->toBe('#a1b2c3');
});

test('gradasi wallpaper dan aksen tersimpan', function () {
    $user = penggunaTampilan();

    simpanTampilan($user, [
        'wallpaper_tipe' => 'gradasi', 'wallpaper_warna1' => '#112233', 'wallpaper_warna2' => '#445566',
        'aksen_tipe' => 'gradasi', 'aksen_warna1' => '#aa0000', 'aksen_warna2' => '#00aa00',
    ])->assertSessionHasNoErrors();

    expect($user->fresh())
        ->wallpaper_tipe->toBe('gradasi')
        ->wallpaper_warna2->toBe('#445566')
        ->aksen_tipe->toBe('gradasi')
        ->aksen_warna2->toBe('#00aa00');
});

test('link gambar https tersimpan untuk wallpaper dan aksen, lengkap dengan mode ulang untuk pola batik', function () {
    $user = penggunaTampilan();

    simpanTampilan($user, [
        'wallpaper_tipe' => 'url', 'wallpaper_url' => 'https://contoh.com/batik-parang.png', 'wallpaper_mode' => 'ulang',
        'aksen_tipe' => 'url', 'aksen_url' => 'https://contoh.com/batik-kawung.webp', 'aksen_mode' => 'ulang',
    ])->assertSessionHasNoErrors();

    expect($user->fresh())
        ->wallpaper_url->toBe('https://contoh.com/batik-parang.png')
        ->wallpaper_mode->toBe('ulang')
        ->aksen_url->toBe('https://contoh.com/batik-kawung.webp')
        ->aksen_mode->toBe('ulang');
});

test('foto wallpaper dan gambar aksen diunggah ke penyimpanan publik', function () {
    $user = penggunaTampilan();

    simpanTampilan($user, [
        'wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->image('latar.jpg', 200, 120),
        'aksen_tipe' => 'foto', 'aksen_gambar' => UploadedFile::fake()->image('batik.png', 64, 64),
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->wallpaper_tipe)->toBe('foto')
        ->and($user->aksen_tipe)->toBe('foto');

    Storage::disk('public')->assertExists($user->wallpaper);
    Storage::disk('public')->assertExists($user->aksen_gambar);
    expect($user->wallpaper)->toStartWith('wallpapers/')
        ->and($user->aksen_gambar)->toStartWith('aksen/');
});

test('mengganti foto menghapus berkas lama', function () {
    $user = penggunaTampilan();

    simpanTampilan($user, ['wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->image('a.jpg')]);
    $lama = $user->fresh()->wallpaper;

    simpanTampilan($user, ['wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->image('b.jpg')]);
    $baru = $user->fresh()->wallpaper;

    expect($baru)->not->toBe($lama);
    Storage::disk('public')->assertMissing($lama);
    Storage::disk('public')->assertExists($baru);
});

test('menyimpan tipe foto tanpa mengunggah ulang tetap memakai foto yang sudah ada', function () {
    $user = penggunaTampilan();
    simpanTampilan($user, ['wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->image('a.jpg')]);
    $path = $user->fresh()->wallpaper;

    simpanTampilan($user, ['wallpaper_tipe' => 'foto', 'navbar_gaya' => 'solid'])->assertSessionHasNoErrors();

    expect($user->fresh())->wallpaper->toBe($path)->navbar_gaya->toBe('solid');
    Storage::disk('public')->assertExists($path);
});

test('gif diterima sebagai wallpaper dan ditandai animasi saat dirender', function () {
    $user = penggunaTampilan();

    simpanTampilan($user, ['wallpaper_tipe' => 'foto', 'wallpaper' => gambarGifTampilan()])
        ->assertSessionHasNoErrors();

    $this->actingAs($user->fresh())
        ->get(route('profil.show'))
        ->assertOk()
        ->assertSee(skripKelasWallpaper('has-wall', 'wall-anim'), false);
});

// ---------- Validasi ----------

test('isian tampilan yang tidak valid ditolak', function (array $timpa, string $kolom) {
    $user = penggunaTampilan();

    simpanTampilan($user, $timpa)->assertSessionHasErrors($kolom);

    expect($user->fresh())->wallpaper_tipe->toBe('default')->aksen_tipe->toBe('preset');
})->with([
    'warna wallpaper bukan hex' => [['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => 'merah'], 'wallpaper_warna1'],
    'warna wallpaper tiga digit' => [['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#fff'], 'wallpaper_warna1'],
    'warna wallpaper kosong' => [['wallpaper_tipe' => 'polos'], 'wallpaper_warna1'],
    'suntikan css di warna wallpaper' => [['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#123456;}body{display:none'], 'wallpaper_warna1'],
    'gradasi wallpaper tanpa warna kedua' => [['wallpaper_tipe' => 'gradasi', 'wallpaper_warna1' => '#123456'], 'wallpaper_warna2'],
    'warna aksen bukan hex' => [['aksen_tipe' => 'polos', 'aksen_warna1' => 'biru'], 'aksen_warna1'],
    'gradasi aksen tanpa warna kedua' => [['aksen_tipe' => 'gradasi', 'aksen_warna1' => '#123456'], 'aksen_warna2'],
    'tipe wallpaper tidak dikenal' => [['wallpaper_tipe' => 'video'], 'wallpaper_tipe'],
    'tipe aksen tidak dikenal' => [['aksen_tipe' => 'video'], 'aksen_tipe'],
    'mode wallpaper tidak dikenal' => [['wallpaper_mode' => 'meregang'], 'wallpaper_mode'],
    'link wallpaper kosong' => [['wallpaper_tipe' => 'url'], 'wallpaper_url'],
    'link wallpaper http' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'http://contoh.com/a.png'], 'wallpaper_url'],
    'link wallpaper javascript' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'javascript:alert(1)'], 'wallpaper_url'],
    'link wallpaper memuat spasi' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'https://contoh.com/a b.png'], 'wallpaper_url'],
    'link wallpaper memuat kutip' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'https://contoh.com/a"onerror=alert(1)'], 'wallpaper_url'],
    'link wallpaper memuat kurung' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'https://contoh.com/a(b)'], 'wallpaper_url'],
    'link wallpaper terlalu panjang' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'https://contoh.com/'.str_repeat('a', 500)], 'wallpaper_url'],
    'link aksen kosong' => [['aksen_tipe' => 'url'], 'aksen_url'],
    'link aksen bukan https' => [['aksen_tipe' => 'url', 'aksen_url' => 'ftp://contoh.com/a.png'], 'aksen_url'],
    'tipe foto wallpaper tanpa berkas' => [['wallpaper_tipe' => 'foto'], 'wallpaper'],
    'tipe foto aksen tanpa berkas' => [['aksen_tipe' => 'foto'], 'aksen_gambar'],
    'wallpaper bukan gambar' => [['wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')], 'wallpaper'],
    'wallpaper svg' => [['wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')], 'wallpaper'],
    'wallpaper terlalu besar' => [['wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->image('besar.jpg')->size(5000)], 'wallpaper'],
    'aksen svg' => [['aksen_tipe' => 'foto', 'aksen_gambar' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>')], 'aksen_gambar'],
]);

test('isian yang tidak dipakai tipe terpilih tidak ikut divalidasi', function () {
    $user = penggunaTampilan();

    // Pengguna beralih ke tipe default setelah sempat mengetik link yang salah di panel lain
    simpanTampilan($user, ['wallpaper_tipe' => 'default', 'wallpaper_url' => ''])->assertSessionHasNoErrors();
});

// ---------- Render ----------

test('wallpaper polos dirender sebagai variabel css dengan kontras yang sudah disesuaikan', function () {
    $user = penggunaTampilan('siswa', ['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#ff0000']);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertOk()
        ->assertSee(skripKelasWallpaper('has-wall-warna'), false)
        ->assertSee('--wall-warna-terang: #', false)
        ->assertSee('--wall-warna-gelap: #', false)
        // Merah murni terlalu gelap untuk teks gelap, jadi tidak boleh dicetak apa adanya di mode terang
        ->assertDontSee('--wall-warna-terang: #ff0000', false);
});

test('gradasi wallpaper dirender sebagai linear-gradient', function () {
    $user = penggunaTampilan('siswa', ['wallpaper_tipe' => 'gradasi', 'wallpaper_warna1' => '#ffe4e6', 'wallpaper_warna2' => '#e0f2fe']);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertSee('--wall-warna-terang: linear-gradient(135deg, #ffe4e6, #e0f2fe)', false);
});

test('wallpaper dari link dan mode ulang dirender', function () {
    $user = penggunaTampilan('siswa', [
        'wallpaper_tipe' => 'url',
        'wallpaper_url' => 'https://contoh.com/batik.png',
        'wallpaper_mode' => 'ulang',
    ]);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertSee('--wall: url("https://contoh.com/batik.png");', false)
        ->assertSee(skripKelasWallpaper('has-wall', 'wall-ulang'), false);
});

test('wallpaper gif dari link ditandai animasi', function () {
    $user = penggunaTampilan('siswa', ['wallpaper_tipe' => 'url', 'wallpaper_url' => 'https://contoh.com/hujan.gif']);

    $this->actingAs($user)->get(route('profil.show'))->assertSee(skripKelasWallpaper('has-wall', 'wall-anim'), false);
});

test('pengguna tanpa wallpaper kustom tidak mendapat variabel wallpaper', function () {
    $this->actingAs(penggunaTampilan())
        ->get(route('profil.show'))
        ->assertOk()
        ->assertDontSee('--wall:', false)
        ->assertDontSee('--wall-warna-terang:', false)
        ->assertDontSee('classList.add(...', false)
        ->assertSee(kartuBerkelas('pk-kaca'), false);
});

test('aksen gradasi merender kartu penuh dengan latar gradasi', function () {
    $user = penggunaTampilan('siswa', ['aksen_tipe' => 'gradasi', 'aksen_warna1' => '#1e3a8a', 'aksen_warna2' => '#7f1d1d']);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertSee(kartuBerkelas('pk-kustom'), false)
        ->assertSee('--pk-latar: linear-gradient(135deg, #1e3a8a, #7f1d1d)', false);
});

test('aksen dari link dilapisi gelap supaya teks tetap terbaca dan mode ulang dipakai untuk pola batik', function () {
    $user = penggunaTampilan('siswa', [
        'aksen_tipe' => 'url',
        'aksen_url' => 'https://contoh.com/batik.png',
        'aksen_mode' => 'ulang',
    ]);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertSee(kartuBerkelas('pk-kustom'), false)
        ->assertSee('url(&quot;https://contoh.com/batik.png&quot;) top left / auto repeat', false);
});

test('aksen warna dirender untuk seluruh situs bersama kelas wallpaper', function () {
    $user = penggunaTampilan('siswa', [
        'wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#ffe4e6',
        'aksen_tipe' => 'gradasi', 'aksen_warna1' => '#1e3a8a', 'aksen_warna2' => '#7f1d1d',
    ]);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertSee(skripKelasWallpaper('has-wall-warna', 'has-aksen-warna'), false)
        ->assertSee('--ak-1-rgb: 30 58 138;', false)
        ->assertSee('--ak-3-rgb: 127 29 29;', false)
        ->assertSee('--wall-warna-terang:', false);
});

test('aksen gambar dirender sebagai variabel latar untuk sapaan dan pita navbar', function () {
    $user = penggunaTampilan('siswa', ['aksen_tipe' => 'url', 'aksen_url' => 'https://contoh.com/batik.png', 'aksen_mode' => 'ulang']);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertSee(skripKelasWallpaper('has-aksen-gambar'), false)
        ->assertSee('--aksen-gambar: url("https://contoh.com/batik.png") top left / auto repeat;', false);
});

test('halaman katalog dengan sapaan memuat aksen situs', function () {
    $user = penggunaTampilan('siswa', ['aksen_tipe' => 'polos', 'aksen_warna1' => '#0f766e']);

    $this->actingAs($user)
        ->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Halo, ', false)
        ->assertSee(skripKelasWallpaper('has-aksen-warna'), false)
        ->assertSee('--ak-1-rgb: 15 118 110;', false);
});

test('pengguna tanpa aksen kustom tidak mendapat variabel aksen situs', function () {
    $this->actingAs(penggunaTampilan('siswa', ['kartu_aksen' => 'rose']))
        ->get(route('profil.show'))
        ->assertDontSee('--ak-1-rgb:', false)
        ->assertDontSee('--aksen-gambar:', false)
        ->assertDontSee('"has-aksen', false);
});

test('aksen polos mengikuti gaya kartu dan memakai warna pilihan', function () {
    $user = penggunaTampilan('siswa', ['aksen_tipe' => 'polos', 'aksen_warna1' => '#0e7490', 'kartu_gaya' => 'kaca']);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertSee(kartuBerkelas('pk-kaca'), false)
        ->assertSee('--pk-rgb: 14 116 144', false)
        ->assertDontSee(kartuBerkelas('pk-kustom'), false);
});

test('nilai tersimpan yang berbahaya tidak pernah dirender', function (array $atribut) {
    $user = penggunaTampilan('siswa', $atribut);

    $respons = $this->actingAs($user)->get(route('profil.show'))->assertOk();

    $respons->assertDontSee('alert(1)', false)
        ->assertDontSee('</style><script>', false)
        ->assertDontSee('onerror', false);
})->with([
    'warna wallpaper' => [['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#123456;}</style><script>alert(1)</script>']],
    'gradasi wallpaper' => [['wallpaper_tipe' => 'gradasi', 'wallpaper_warna1' => '#123456', 'wallpaper_warna2' => 'red;}</style><script>alert(1)</script>']],
    'link wallpaper' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'https://x.com/a");}</style><script>alert(1)</script>']],
    'link wallpaper http' => [['wallpaper_tipe' => 'url', 'wallpaper_url' => 'javascript:alert(1)']],
    'berkas wallpaper' => [['wallpaper_tipe' => 'foto', 'wallpaper' => '../x"><script>alert(1)</script>']],
    'warna aksen' => [['aksen_tipe' => 'polos', 'aksen_warna1' => '#123456" onerror="alert(1)']],
    'link aksen' => [['aksen_tipe' => 'url', 'aksen_url' => 'https://x.com/a" onerror="alert(1)']],
    'berkas aksen' => [['aksen_tipe' => 'foto', 'aksen_gambar' => 'a" onerror="alert(1)']],
]);

test('nilai tersimpan yang rusak membuat tampilan kembali ke bawaan, bukan error', function () {
    $user = penggunaTampilan('siswa', ['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => 'bukan-warna', 'aksen_tipe' => 'url', 'aksen_url' => 'http://tidak-aman']);

    $this->actingAs($user)
        ->get(route('profil.show'))
        ->assertOk()
        ->assertDontSee('classList.add(...', false)
        ->assertDontSee('--wall-warna-terang:', false)
        ->assertSee(kartuBerkelas('pk-kaca'), false);
});

test('pengguna lain melihat kartu profil dengan aksen milik pemiliknya, bukan miliknya sendiri', function () {
    $pemilik = penggunaTampilan('siswa', ['aksen_tipe' => 'gradasi', 'aksen_warna1' => '#1e3a8a', 'aksen_warna2' => '#7f1d1d']);
    $pengintip = penggunaTampilan();

    $this->actingAs($pengintip)
        ->get(route('profil.show', $pemilik))
        ->assertSee('--pk-latar: linear-gradient(135deg, #1e3a8a, #7f1d1d)', false);
});

// ---------- Pratinjau ----------

test('pratinjau mengembalikan hasil render tanpa menyimpan apa pun', function () {
    $user = penggunaTampilan();

    $this->actingAs($user)
        ->postJson(route('pengaturan.pratinjau'), ['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#ffe4e6', 'aksen_tipe' => 'gradasi', 'aksen_warna1' => '#1e3a8a', 'aksen_warna2' => '#7f1d1d'])
        ->assertOk()
        ->assertJsonPath('wallpaper.kelas.0', 'has-wall-warna')
        ->assertJsonPath('kartu.kelas', 'pk-kustom')
        ->assertJsonPath('kartu.style', '--pk-latar: linear-gradient(135deg, #1e3a8a, #7f1d1d)');

    expect($user->fresh())->wallpaper_tipe->toBe('default')->aksen_tipe->toBe('preset');
});

test('pratinjau menyertakan aksen untuk seluruh situs', function () {
    $this->actingAs(penggunaTampilan())
        ->postJson(route('pengaturan.pratinjau'), ['aksen_tipe' => 'polos', 'aksen_warna1' => '#0f766e'])
        ->assertOk()
        ->assertJsonPath('aksen.kelas', ['has-aksen-warna'])
        ->assertJsonPath('aksen.css', fn (string $css) => str_contains($css, '--ak-1-rgb: 15 118 110;'));

    $this->actingAs(penggunaTampilan())
        ->postJson(route('pengaturan.pratinjau'), ['aksen_tipe' => 'foto', 'aksen_berkas' => 'gambar'])
        ->assertOk()
        ->assertJsonPath('aksen.kelas', ['has-aksen-gambar'])
        ->assertJsonPath('aksen.css', fn (string $css) => str_contains($css, 'https://pratinjau.invalid/berkas'));
});

test('pratinjau memakai hitungan kontras yang sama dengan tampilan sebenarnya', function () {
    $user = penggunaTampilan('siswa', ['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#ff0000']);

    $pratinjau = $this->actingAs($user)
        ->postJson(route('pengaturan.pratinjau'), ['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#ff0000'])
        ->json('wallpaper.css');

    expect(TampilanKustom::wallpaper($user)['css'])->toBe($pratinjau);
});

test('pratinjau berkas gif yang baru dipilih memakai alamat sementara bertanda animasi', function () {
    $this->actingAs(penggunaTampilan())
        ->postJson(route('pengaturan.pratinjau'), ['wallpaper_tipe' => 'foto', 'wallpaper_berkas' => 'gif'])
        ->assertOk()
        ->assertJsonPath('wallpaper.css', '--wall: url("https://pratinjau.invalid/berkas.gif");')
        ->assertJsonPath('wallpaper.kelas', ['has-wall', 'wall-anim']);
});

test('pratinjau gambar biasa yang baru dipilih tidak ditandai animasi', function () {
    $this->actingAs(penggunaTampilan())
        ->postJson(route('pengaturan.pratinjau'), ['aksen_tipe' => 'foto', 'aksen_berkas' => 'gambar'])
        ->assertOk()
        ->assertJsonPath('kartu.kelas', 'pk-kustom')
        ->assertJsonPath('wallpaper.kelas', []);
});

test('pratinjau menolak nilai berbahaya dengan hasil kosong, tanpa error', function () {
    $this->actingAs(penggunaTampilan())
        ->postJson(route('pengaturan.pratinjau'), ['wallpaper_tipe' => 'url', 'wallpaper_url' => 'javascript:alert(1)', 'aksen_tipe' => 'polos', 'aksen_warna1' => 'x;}'])
        ->assertOk()
        ->assertJsonPath('wallpaper.css', '')
        ->assertJsonPath('kartu.kelas', 'pk-kaca');
});

test('pratinjau mengabaikan isian berbentuk array tanpa error', function () {
    $this->actingAs(penggunaTampilan())
        ->postJson(route('pengaturan.pratinjau'), ['wallpaper_tipe' => ['polos'], 'wallpaper_warna1' => [['#ffffff']], 'aksen_tipe' => ['x' => 'y'], 'kartu_gaya' => ['kaca'], 'kartu_aksen' => ['indigo']])
        ->assertOk();
});

test('pratinjau tidak bisa dipakai untuk mengubah atribut selain tampilan', function () {
    $user = penggunaTampilan('siswa');

    $this->actingAs($user)
        ->postJson(route('pengaturan.pratinjau'), ['role' => 'admin', 'is_active' => false, 'wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#ffffff'])
        ->assertOk();

    expect($user->fresh())->role->toBe('siswa')->is_active->toBeTrue();
});

test('pratinjau menolak tamu', function () {
    $this->postJson(route('pengaturan.pratinjau'), ['wallpaper_tipe' => 'polos'])->assertUnauthorized();
});

// ---------- Hapus ----------

test('hapus wallpaper menghapus berkas dan mengembalikan tipe ke default', function () {
    $user = penggunaTampilan();
    simpanTampilan($user, ['wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->image('a.jpg')]);
    $path = $user->fresh()->wallpaper;

    $this->actingAs($user)->delete(route('pengaturan.wallpaper.hapus'))->assertRedirect();

    expect($user->fresh())->wallpaper->toBeNull()->wallpaper_tipe->toBe('default');
    Storage::disk('public')->assertMissing($path);
});

test('hapus aksen menghapus gambar dan mengembalikan tipe ke preset', function () {
    $user = penggunaTampilan();
    simpanTampilan($user, ['aksen_tipe' => 'foto', 'aksen_gambar' => UploadedFile::fake()->image('batik.png')]);
    $path = $user->fresh()->aksen_gambar;

    $this->actingAs($user)->delete(route('pengaturan.aksen.hapus'))->assertRedirect();

    expect($user->fresh())->aksen_gambar->toBeNull()->aksen_tipe->toBe('preset');
    Storage::disk('public')->assertMissing($path);
});

test('hapus wallpaper tidak mengubah tipe lain yang sedang dipakai', function () {
    $user = penggunaTampilan('siswa', ['wallpaper_tipe' => 'polos', 'wallpaper_warna1' => '#ffffff']);

    $this->actingAs($user)->delete(route('pengaturan.wallpaper.hapus'))->assertRedirect();

    expect($user->fresh())->wallpaper_tipe->toBe('polos');
});

test('menghapus akun pengguna ikut membersihkan wallpaper dan gambar aksennya', function () {
    $admin = penggunaTampilan('admin');
    $siswa = penggunaTampilan();
    simpanTampilan($siswa, [
        'wallpaper_tipe' => 'foto', 'wallpaper' => UploadedFile::fake()->image('a.jpg'),
        'aksen_tipe' => 'foto', 'aksen_gambar' => UploadedFile::fake()->image('b.png'),
    ]);
    $siswa->refresh();
    [$wall, $aksen] = [$siswa->wallpaper, $siswa->aksen_gambar];

    $this->actingAs($admin)->delete(route('users.destroy', $siswa))->assertRedirect();

    $this->assertModelMissing($siswa);
    Storage::disk('public')->assertMissing($wall);
    Storage::disk('public')->assertMissing($aksen);
});
