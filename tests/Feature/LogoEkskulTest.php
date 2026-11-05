<?php

use App\Models\EkskulAnggotaJabatan;
use App\Models\EkskulJabatan;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('public');
});

function akunLg(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulLg(string $nama = 'Palang Merah Remaja', array $tambahan = []): Ekstrakurikuler
{
    return Ekstrakurikuler::create(array_merge([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', 'ekskul')->value('id'),
    ], $tambahan));
}

function pembinaLg(Ekstrakurikuler $ekskul): User
{
    $pembina = akunLg('pembina');
    $ekskul->pembinas()->attach($pembina->id, ['mulai' => now()->toDateString()]);

    return $pembina;
}

// Siswa anggota aktif yang memegang jabatan bertanda "penanggung jawab anggota"
function penanggungJawabLg(Ekstrakurikuler $ekskul): User
{
    $siswa = akunLg();
    $pendaftaran = anggotaLg($ekskul, $siswa);
    $jabatan = EkskulJabatan::create([
        'ekstrakurikuler_id' => $ekskul->id,
        'nama' => 'Ketua',
        'varian' => '',
        'boleh_kelola_anggota' => true,
    ]);
    EkskulAnggotaJabatan::create([
        'pendaftaran_id' => $pendaftaran->id,
        'jabatan_id' => $jabatan->id,
        'periode_mulai' => 2026,
        'periode_selesai' => null,
    ]);

    return $siswa;
}

function anggotaLg(Ekstrakurikuler $ekskul, User $akun): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $akun->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $akun->name,
        'peran' => 'siswa',
        'status' => 'diterima',
        'status_anggota' => 'aktif',
    ]);
}

function logoLg(int $lebar = 300, int $tinggi = 300): UploadedFile
{
    return UploadedFile::fake()->image('logo.png', $lebar, $tinggi);
}

function unggahLogoLg($tes, User $akun, Ekstrakurikuler $ekskul, $berkas = null)
{
    return $tes->actingAs($akun)->patch(route('ekskul-logo.update', $ekskul->id), ['logo' => $berkas ?? logoLg()]);
}

// ---------------------------------------------------------------------------
// Siapa yang boleh mengubah logo
// ---------------------------------------------------------------------------

test('admin bisa menambahkan logo ekskul', function () {
    $ekskul = ekskulLg();

    unggahLogoLg($this, akunLg('admin'), $ekskul)->assertRedirect()->assertSessionHas('success');

    $ekskul->refresh();
    expect($ekskul->logo)->toStartWith("logo-ekskul/{$ekskul->id}-")->toEndWith('.png');
    Storage::disk('public')->assertExists($ekskul->logo);
    expect($ekskul->logo_url)->toContain('storage/'.$ekskul->logo);
});

test('pembina ekskul itu dan penanggung jawab anggota bisa mengganti logo', function () {
    $ekskul = ekskulLg();

    foreach ([pembinaLg($ekskul), penanggungJawabLg($ekskul)] as $akun) {
        unggahLogoLg($this, $akun, $ekskul)->assertRedirect()->assertSessionHasNoErrors();
        expect($ekskul->fresh()->logo)->not->toBeNull();
    }
});

test('pembina ekskul lain, anggota biasa, dan siswa lain tidak boleh mengubah logo', function () {
    $ekskul = ekskulLg();
    $lain = ekskulLg('Robotik');

    $anggotaBiasa = akunLg();
    anggotaLg($ekskul, $anggotaBiasa);

    // Jabatan tanpa tanda penanggung jawab tidak cukup
    $pemegangJabatan = akunLg();
    $pendaftaran = anggotaLg($ekskul, $pemegangJabatan);
    $jabatan = EkskulJabatan::create(['ekstrakurikuler_id' => $ekskul->id, 'nama' => 'Sekretaris', 'varian' => '', 'boleh_kelola_anggota' => false]);
    EkskulAnggotaJabatan::create(['pendaftaran_id' => $pendaftaran->id, 'jabatan_id' => $jabatan->id, 'periode_mulai' => 2026, 'periode_selesai' => null]);

    foreach ([pembinaLg($lain), $anggotaBiasa, $pemegangJabatan, akunLg()] as $akun) {
        unggahLogoLg($this, $akun, $ekskul)->assertForbidden();
    }

    expect($ekskul->fresh()->logo)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('penanggung jawab ekskul lain tidak bisa mengubah logo ekskul ini', function () {
    $ekskul = ekskulLg();
    $lain = ekskulLg('Robotik');

    unggahLogoLg($this, penanggungJawabLg($lain), $ekskul)->assertForbidden();
    expect($ekskul->fresh()->logo)->toBeNull();
});

test('tamu yang belum login diarahkan ke halaman masuk', function () {
    $ekskul = ekskulLg();

    $this->patch(route('ekskul-logo.update', $ekskul->id), ['logo' => logoLg()])->assertRedirect(route('login'));
    $this->delete(route('ekskul-logo.destroy', $ekskul->id))->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Validasi berkas
// ---------------------------------------------------------------------------

test('berkas yang bukan gambar, SVG, terlalu besar, atau terlalu kecil ditolak', function () {
    $ekskul = ekskulLg();
    $admin = akunLg('admin');

    $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><script>alert(1)</script></svg>');

    $ditolak = [
        'pdf' => UploadedFile::fake()->create('logo.pdf', 50, 'application/pdf'),
        'svg' => $svg,
        'terlalu besar' => UploadedFile::fake()->image('besar.png', 300, 300)->size(3000),
        'terlalu kecil' => UploadedFile::fake()->image('kecil.png', 32, 32),
    ];

    foreach ($ditolak as $alasan => $berkas) {
        unggahLogoLg($this, $admin, $ekskul, $berkas)->assertSessionHasErrors('logo');
    }

    $this->actingAs($admin)->patch(route('ekskul-logo.update', $ekskul->id), [])->assertSessionHasErrors('logo');

    expect($ekskul->fresh()->logo)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

// ---------------------------------------------------------------------------
// Pengolahan gambar
// ---------------------------------------------------------------------------

test('transparansi PNG tetap terjaga, tidak berubah jadi latar putih', function () {
    $ekskul = ekskulLg();

    $gd = imagecreatetruecolor(200, 200);
    imagealphablending($gd, false);
    imagesavealpha($gd, true);
    imagefill($gd, 0, 0, imagecolorallocatealpha($gd, 0, 0, 0, 127));
    imagefilledellipse($gd, 100, 100, 120, 120, imagecolorallocatealpha($gd, 220, 30, 30, 0));
    $sementara = tempnam(sys_get_temp_dir(), 'logo').'.png';
    imagepng($gd, $sementara);
    imagedestroy($gd);

    unggahLogoLg($this, akunLg('admin'), $ekskul, new UploadedFile($sementara, 'logo.png', 'image/png', null, true))->assertSessionHasNoErrors();

    $hasil = imagecreatefromstring(Storage::disk('public')->get($ekskul->fresh()->logo));
    $sudut = imagecolorsforindex($hasil, imagecolorat($hasil, 2, 2));
    $tengah = imagecolorsforindex($hasil, imagecolorat($hasil, 100, 100));

    expect($sudut['alpha'])->toBe(127);   // pojok tetap transparan penuh
    expect($tengah['alpha'])->toBe(0);    // isi logo tetap utuh
    expect($tengah['red'])->toBeGreaterThan(200);
});

test('gambar besar dikecilkan ke 512 px, gambar kecil tidak diperbesar', function () {
    $ekskul = ekskulLg();
    $admin = akunLg('admin');

    unggahLogoLg($this, $admin, $ekskul, logoLg(1200, 800))->assertSessionHasNoErrors();
    [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($ekskul->fresh()->logo));
    expect([$w, $h])->toBe([512, 341]);

    unggahLogoLg($this, $admin, $ekskul, logoLg(200, 200))->assertSessionHasNoErrors();
    [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($ekskul->fresh()->logo));
    expect([$w, $h])->toBe([200, 200]);
});

// ---------------------------------------------------------------------------
// Mengganti dan menghapus
// ---------------------------------------------------------------------------

test('mengganti logo menghapus berkas lama dan memakai nama baru', function () {
    $ekskul = ekskulLg();
    $admin = akunLg('admin');

    unggahLogoLg($this, $admin, $ekskul);
    $lama = $ekskul->fresh()->logo;

    unggahLogoLg($this, $admin, $ekskul);
    $baru = $ekskul->fresh()->logo;

    expect($baru)->not->toBe($lama);
    Storage::disk('public')->assertMissing($lama);
    Storage::disk('public')->assertExists($baru);
    expect(Storage::disk('public')->allFiles())->toHaveCount(1);
});

test('menghapus logo membuang berkas dan mengosongkan kolom, hanya untuk yang berhak', function () {
    $ekskul = ekskulLg();
    unggahLogoLg($this, akunLg('admin'), $ekskul);
    $path = $ekskul->fresh()->logo;

    $this->actingAs(akunLg())->delete(route('ekskul-logo.destroy', $ekskul->id))->assertForbidden();
    Storage::disk('public')->assertExists($path);

    $this->actingAs(pembinaLg($ekskul))->delete(route('ekskul-logo.destroy', $ekskul->id))->assertRedirect()->assertSessionHas('success');

    expect($ekskul->fresh()->logo)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('menghapus ekskul ikut menghapus berkas logonya', function () {
    $ekskul = ekskulLg();
    unggahLogoLg($this, akunLg('admin'), $ekskul);
    $path = $ekskul->fresh()->logo;
    Storage::disk('public')->assertExists($path);

    $ekskul->fresh()->delete();

    Storage::disk('public')->assertMissing($path);
});

// ---------------------------------------------------------------------------
// Monogram cadangan
// ---------------------------------------------------------------------------

test('monogram memakai singkatan, atau huruf awal dua kata pertama, maksimal tiga huruf', function () {
    expect(Ekstrakurikuler::make(['nama_ekstrakurikuler' => 'Palang Merah Remaja', 'nama_singkatan' => 'pmr'])->monogram)->toBe('PMR');
    expect(Ekstrakurikuler::make(['nama_ekstrakurikuler' => 'Palang Merah Remaja'])->monogram)->toBe('PM');
    expect(Ekstrakurikuler::make(['nama_ekstrakurikuler' => 'Pramuka'])->monogram)->toBe('P');
    expect(Ekstrakurikuler::make(['nama_ekstrakurikuler' => 'Seni Tari Tradisional', 'nama_singkatan' => 'Tari Tradisi'])->monogram)->toBe('TAR');
    expect(Ekstrakurikuler::make(['nama_ekstrakurikuler' => '  -- Karya Ilmiah --  '])->monogram)->toBe('KI');
    expect(Ekstrakurikuler::make(['nama_ekstrakurikuler' => '???'])->monogram)->toBe('?');
});

test('warna monogram tetap untuk nama yang sama dan berasal dari palet', function () {
    $a = Ekstrakurikuler::make(['nama_ekstrakurikuler' => 'Palang Merah Remaja']);
    $b = Ekstrakurikuler::make(['nama_ekstrakurikuler' => 'PALANG MERAH REMAJA']);

    expect($a->warna_monogram)->toBe($b->warna_monogram)->and(Ekstrakurikuler::WARNA_MONOGRAM)->toContain($a->warna_monogram);
});

// ---------------------------------------------------------------------------
// Tampilan
// ---------------------------------------------------------------------------

test('katalog menampilkan monogram kalau belum ada logo, dan gambar logo kalau sudah ada', function () {
    $ekskul = ekskulLg('Palang Merah Remaja', ['nama_singkatan' => 'PMR']);
    $siswa = akunLg();

    $this->actingAs($siswa)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('aria-label="Logo Palang Merah Remaja"', false)
        ->assertSee('>PMR</span>', false)
        ->assertDontSee('storage/logo-ekskul/', false);

    unggahLogoLg($this, akunLg('admin'), $ekskul);

    $this->actingAs($siswa)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('storage/'.$ekskul->fresh()->logo, false)
        ->assertSee('alt="Logo Palang Merah Remaja"', false);
});

test('dasar putih logo ditulis inline, tidak lewat kelas bg-white yang ditimpa tema gelap', function () {
    // Tema aplikasi menimpa .bg-white (semi-transparan / gelap), sehingga logo berlatar transparan
    // seperti palang merah nyaris tak terlihat. Warna dasar harus inline supaya tidak ikut tertimpa.
    $ekskul = ekskulLg();
    unggahLogoLg($this, akunLg('admin'), $ekskul);

    $html = $this->actingAs(akunLg())->get(route('ekstrakurikuler.index'))->assertOk()->getContent();
    preg_match('/<span[^>]*background:#fff[^>]*>\s*<img src="[^"]*storage\/logo-ekskul\/[^"]*"/s', $html, $cocok);

    expect($cocok)->not->toBeEmpty();
    expect($cocok[0])->not->toContain('bg-white');
});

test('form logo di halaman detail hanya muncul untuk yang berhak', function () {
    $ekskul = ekskulLg();
    $rute = route('ekskul-logo.update', $ekskul->id);

    $berhak = [akunLg('admin'), pembinaLg($ekskul), penanggungJawabLg($ekskul)];
    foreach ($berhak as $akun) {
        $this->actingAs($akun)->get(route('ekstrakurikuler.show', $ekskul->id))
            ->assertOk()->assertSee($rute, false)->assertSee('Tambah logo');
    }

    $anggota = akunLg();
    anggotaLg($ekskul, $anggota);
    foreach ([akunLg(), $anggota, pembinaLg(ekskulLg('Robotik'))] as $akun) {
        $this->actingAs($akun)->get(route('ekstrakurikuler.show', $ekskul->id))
            ->assertOk()->assertDontSee($rute, false)->assertDontSee('Tambah logo');
    }
});

test('setelah logo terpasang, form menawarkan ganti dan hapus', function () {
    $ekskul = ekskulLg();
    $admin = akunLg('admin');
    unggahLogoLg($this, $admin, $ekskul);

    $this->actingAs($admin)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()
        ->assertSee('Ganti logo')
        ->assertSee(route('ekskul-logo.destroy', $ekskul->id), false);
});

// ---------------------------------------------------------------------------
// Logo web (identitas sekolah)
// ---------------------------------------------------------------------------

test('berkas logo web dan favicon tersedia dan bukan berkas kosong', function () {
    foreach (['images/logo-sekolah.svg', 'favicon.svg', 'favicon.ico'] as $berkas) {
        expect(public_path($berkas))->toBeFile();
        expect(filesize(public_path($berkas)))->toBeGreaterThan(0);
    }

    expect(file_get_contents(public_path('images/logo-sekolah.svg')))->not->toContain('<script');
});

test('halaman masuk memuat logo, nama sekolah, dan ikon tab', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('SMKS Mahaputra Cerdas Utama')
        ->assertSee(asset('images/logo-sekolah.svg'), false)
        ->assertSee(asset('favicon.svg'), false)
        ->assertSee(asset('favicon.ico'), false)
        ->assertSee('Masuk - Ekstrakurikuler SMKS MCU');

    $this->get(route('register'))->assertOk()->assertSee('SMKS Mahaputra Cerdas Utama')->assertSee(asset('images/logo-sekolah.svg'), false);
});

test('navbar memuat logo sekolah yang mengarah ke katalog', function () {
    $this->actingAs(akunLg())->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('class="nav-brand"', false)
        ->assertSee(asset('images/logo-sekolah.svg'), false)
        ->assertSee('SMKS MCU');
});

test('identitas sekolah bisa diganti lewat konfigurasi tanpa menyentuh tampilan', function () {
    config(['sekolah.nama' => 'SMK Contoh Bangsa', 'sekolah.singkatan' => 'SMKCB', 'sekolah.logo' => 'images/logo-lain.png']);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('SMK Contoh Bangsa')
        ->assertSee(asset('images/logo-lain.png'), false)
        ->assertSee('Masuk - Ekstrakurikuler SMKCB')
        ->assertDontSee('SMKS Mahaputra Cerdas Utama');
});
