<?php

use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\KategoriEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunFt(string $role = 'admin'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulFt(string $nama, array $slugKategori = [], array $tambahan = [], string $kodeJenis = 'ekskul'): Ekstrakurikuler
{
    $ekskul = Ekstrakurikuler::create(array_merge([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', $kodeJenis)->value('id'),
    ], $tambahan));

    if ($slugKategori) {
        $ekskul->kategoris()->attach(KategoriEkskul::whereIn('slug', $slugKategori)->pluck('id')->all());
    }

    return $ekskul;
}

// Data bersama: Silat (Bela Diri + Seni & Olahraga, buka), Taekwondo (Bela Diri, ditutup manual),
// Robotik (Teknologi, buka), Voli (tanpa kategori, nonaktif)
function siapkanKatalogFt(): void
{
    ekskulFt('Silat Perguruan', ['bela-diri', 'seni-olahraga']);
    ekskulFt('Taekwondo Sekolah', ['bela-diri'], ['pendaftaran_dibuka' => false]);
    ekskulFt('Robotik Pintar', ['teknologi']);
    ekskulFt('Voli Putri', [], ['status' => 'nonaktif']);
}

// ---------------------------------------------------------------------------
// Pill kategori
// ---------------------------------------------------------------------------

test('pill kategori hanya untuk kategori yang punya ekskul, lengkap dengan jumlahnya', function () {
    siapkanKatalogFt();

    $this->actingAs(akunFt())->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Bela Diri (2)')
        ->assertSee('Seni & Olahraga (1)')
        ->assertSee('Teknologi (1)')
        ->assertDontSee('Kepemimpinan (');
});

test('menyaring katalog per kategori; ekskul berkategori ganda muncul di tiap kategorinya', function () {
    siapkanKatalogFt();
    $admin = akunFt();

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['kategori' => 'bela-diri']))
        ->assertOk()
        ->assertSee('Silat Perguruan')->assertSee('Taekwondo Sekolah')
        ->assertDontSee('Robotik Pintar')->assertDontSee('Voli Putri');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['kategori' => 'seni-olahraga']))
        ->assertOk()
        ->assertSee('Silat Perguruan')
        ->assertDontSee('Taekwondo Sekolah');
});

test('kategori yang tidak dikenal atau berbentuk array tidak menyaring apa pun', function () {
    siapkanKatalogFt();
    $admin = akunFt();

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['kategori' => 'tidak-ada']))
        ->assertOk()->assertSee('Silat Perguruan')->assertSee('Voli Putri');

    $this->actingAs($admin)->get('/ekstrakurikuler?kategori[]=bela-diri&status[]=buka')
        ->assertOk()->assertSee('Silat Perguruan')->assertSee('Voli Putri');
});

// ---------------------------------------------------------------------------
// Pill status pendaftaran
// ---------------------------------------------------------------------------

test('menyaring katalog berdasarkan status pendaftaran Buka dan Tutup', function () {
    siapkanKatalogFt();
    $admin = akunFt();

    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertSee('Buka (2)')->assertSee('Tutup (2)');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['status' => 'buka']))
        ->assertOk()
        ->assertSee('Silat Perguruan')->assertSee('Robotik Pintar')
        ->assertDontSee('Taekwondo Sekolah')->assertDontSee('Voli Putri');

    // ditutup manual dan ekskul nonaktif sama-sama dihitung Tutup
    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['status' => 'tutup']))
        ->assertOk()
        ->assertSee('Taekwondo Sekolah')->assertSee('Voli Putri')
        ->assertDontSee('Silat Perguruan')->assertDontSee('Robotik Pintar');
});

test('status Buka/Tutup di filter sama persis dengan aturan pendaftaran_terbuka di model, termasuk ekskul seleksi', function () {
    siapkanKatalogFt();
    $paskibra = ekskulFt('Paskibra Garuda', [], [], 'seleksi');

    // ekskul berseleksi tanpa periode rekrutmen yang dibuka = tutup
    expect($paskibra->fresh()->pendaftaran_terbuka)->toBeFalse();

    // Catatan: kartu "Rekrutmen" di atas katalog selalu menampilkan ekskul seleksi apa pun saringannya,
    // jadi di sini yang diperiksa jumlah pill dan kartu ekskul biasa.
    $admin = akunFt();
    $this->actingAs($admin)->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertSee('Buka (2)')->assertSee('Tutup (3)');

    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['status' => 'buka']))
        ->assertOk()->assertSee('Silat Perguruan')->assertDontSee('Taekwondo Sekolah');
    $this->actingAs($admin)->get(route('ekstrakurikuler.index', ['status' => 'tutup']))
        ->assertOk()->assertSee('Taekwondo Sekolah')->assertDontSee('Silat Perguruan');
});

// ---------------------------------------------------------------------------
// Gabungan dan tautan
// ---------------------------------------------------------------------------

test('kategori dan status digabung, dan jumlah di pill mengikuti saringan kelompok lain', function () {
    siapkanKatalogFt();

    $this->actingAs(akunFt())->get(route('ekstrakurikuler.index', ['kategori' => 'bela-diri', 'status' => 'buka']))
        ->assertOk()
        ->assertSee('Silat Perguruan')
        ->assertDontSee('Taekwondo Sekolah')
        ->assertDontSee('Robotik Pintar')
        // Bela Diri yang buka tinggal 1; Teknologi buka = 1 tetapi tidak beririsan dengan kategori terpilih
        ->assertSee('Bela Diri (1)')
        ->assertSee('Buka (1)')
        ->assertSee('Tutup (1)');
});

test('tautan pill mempertahankan saringan kelompok lain dan mematikan saringan yang sedang aktif', function () {
    siapkanKatalogFt();

    $html = $this->actingAs(akunFt())
        ->get(route('ekstrakurikuler.index', ['kategori' => 'bela-diri', 'status' => 'buka']))
        ->assertOk();

    // pill kategori lain membawa status=buka
    $html->assertSee('kategori=teknologi&amp;status=buka', false);
    // pill Buka yang aktif mematikan status saja, kategori tetap
    $html->assertSee('href="'.route('ekstrakurikuler.index', ['kategori' => 'bela-diri']).'"', false);
    // ada tombol reset karena ada saringan aktif
    $html->assertSee('Reset filter');
});

test('tanpa saringan aktif tidak ada tombol reset', function () {
    siapkanKatalogFt();

    $this->actingAs(akunFt())->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertDontSee('Reset filter');
});

test('saringan yang tidak menghasilkan apa pun menampilkan pesan kosong dengan tautan reset', function () {
    siapkanKatalogFt();

    $this->actingAs(akunFt())->get(route('ekstrakurikuler.index', ['kategori' => 'teknologi', 'status' => 'tutup']))
        ->assertOk()
        ->assertSee('Tidak ada ekskul yang cocok dengan saringan ini')
        ->assertSee('Reset filter');
});

test('saringan juga bisa dipakai siswa dan digabung dengan saringan tipe yang sudah ada', function () {
    siapkanKatalogFt();
    ekskulFt('Komunitas Beladiri', ['bela-diri'], [], 'komunitas');

    $this->actingAs(akunFt('siswa'))
        ->get(route('ekstrakurikuler.index', ['jenis' => 'ekskul', 'kategori' => 'bela-diri']))
        ->assertOk()
        ->assertSee('Silat Perguruan')->assertSee('Taekwondo Sekolah')
        ->assertDontSee('Komunitas Beladiri');

    $this->actingAs(akunFt('siswa'))
        ->get(route('ekstrakurikuler.index', ['jenis' => 'komunitas', 'kategori' => 'bela-diri']))
        ->assertOk()
        ->assertSee('Komunitas Beladiri')
        ->assertDontSee('Silat Perguruan');
});

test('katalog kosong tidak menampilkan baris saringan', function () {
    $this->actingAs(akunFt())->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertDontSee('saringan-katalog')
        ->assertSee('Belum ada data ekstrakurikuler');
});
