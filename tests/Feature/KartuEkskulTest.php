<?php

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function kartuAkun(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function kartuEkskul(string $nama): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Budi',
        'status' => 'aktif',
    ]);
}

function kartuDaftar(User $siswa, Ekstrakurikuler $ekskul, string $status, string $statusAnggota): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'status' => $status,
        'status_anggota' => $statusAnggota,
    ]);
}

// ---------------------------------------------------------------------------
// Kartu ekskul untuk siswa
// ---------------------------------------------------------------------------

test('kartu ekskul yang sudah diikuti siswa menampilkan status dan Lihat Informasi, bukan tombol daftar', function (string $status, string $statusAnggota, string $teksStatus) {
    $siswa = kartuAkun();
    kartuDaftar($siswa, kartuEkskul('Futsal'), $status, $statusAnggota);

    $this->actingAs($siswa)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee($teksStatus)
        ->assertSee('Lihat Informasi')
        ->assertDontSee('Daftar Sekarang')
        ->assertDontSee('Daftar Lagi');
})->with([
    'anggota aktif' => ['diterima', 'aktif', 'Kamu anggota aktif'],
    'menunggu persetujuan' => ['pending', 'aktif', 'Pendaftaranmu menunggu persetujuan'],
    'pendaftaran ditolak' => ['ditolak', 'aktif', 'Pendaftaranmu tidak disetujui'],
    'dikeluarkan' => ['diterima', 'dikeluarkan', 'Kamu dikeluarkan dari ekskul ini'],
]);

test('ekskul yang belum diikuti tetap bisa didaftari, dan yang pernah mengundurkan diri jadi Daftar Lagi', function () {
    $siswa = kartuAkun();
    kartuEkskul('Futsal');
    kartuDaftar($siswa, kartuEkskul('Catur'), 'diterima', 'keluar');

    $this->actingAs($siswa)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Daftar Sekarang')
        ->assertSee('Daftar Lagi');
});

test('hanya ekskul yang sudah diikuti yang berubah jadi kartu informasi', function () {
    $siswa = kartuAkun();
    kartuDaftar($siswa, kartuEkskul('Futsal'), 'diterima', 'aktif');
    kartuEkskul('Catur');

    // Futsal = kartu informasi, Catur = masih kartu daftar
    $this->actingAs($siswa)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Lihat Informasi')
        ->assertSee('Daftar Sekarang');
});

// ---------------------------------------------------------------------------
// Admin & pembina bukan siswa: tidak mendaftar ekskul
// ---------------------------------------------------------------------------

test('admin dan pembina tidak melihat tombol daftar di katalog, halaman ekskul, maupun daftar pendaftaran', function (string $role) {
    $akun = kartuAkun($role);
    $ekskul = kartuEkskul('Futsal');

    $this->actingAs($akun)->get(route('ekstrakurikuler.index'))
        ->assertOk()
        ->assertSee('Lihat Detail')
        ->assertDontSee('Daftar Sekarang');

    $this->actingAs($akun)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()
        ->assertDontSee('Daftar Sekarang');

    $this->actingAs($akun)->get(route('pendaftaran.index'))
        ->assertOk()
        ->assertDontSee('+ Daftar Baru');
})->with(['admin', 'pembina']);

test('siswa melihat tombol Daftar Baru di daftar pendaftarannya', function () {
    $this->actingAs(kartuAkun())->get(route('pendaftaran.index'))
        ->assertOk()
        ->assertSee('+ Daftar Baru');
});

test('admin dan pembina tidak bisa membuka maupun mengirim form pendaftaran', function (string $role) {
    $akun = kartuAkun($role);
    $ekskul = kartuEkskul('Futsal');

    $this->actingAs($akun)->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $ekskul->id]))
        ->assertRedirect(route('pendaftaran.index'))
        ->assertSessionHas('error');

    $this->actingAs($akun)->post(route('pendaftaran.store'), [
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => 'Siapa Saja',
        'peran' => 'siswa',
        'kelas' => 'XII PPLG',
    ])
        ->assertRedirect(route('pendaftaran.index'))
        ->assertSessionHas('error');

    expect(PendaftaranEkskul::count())->toBe(0);
})->with(['admin', 'pembina']);
