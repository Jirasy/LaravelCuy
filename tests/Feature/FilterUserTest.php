<?php

use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Support\KelasSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunFl(string $role, string $nama, ?string $kelas = null): User
{
    return User::factory()->create(['role' => $role, 'name' => $nama, 'kelas' => $kelas, 'is_active' => true]);
}

function ekskulFl(string $nama): Ekstrakurikuler
{
    return Ekstrakurikuler::create(['nama_ekstrakurikuler' => $nama, 'pembina' => 'Pak Uji', 'status' => 'aktif']);
}

function anggotaFl(Ekstrakurikuler $ekskul, User $siswa, string $statusAnggota = 'aktif'): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id, 'ekstrakurikuler_id' => $ekskul->id, 'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa', 'kelas' => $siswa->kelas, 'status' => 'diterima', 'status_anggota' => $statusAnggota, 'tahun_mulai' => 2026,
    ]);
}

// Data: 5 siswa dengan kelas/jurusan berbeda + 1 pembina + 1 admin (nama sengaja unik supaya mudah dicari di halaman)
function datasetFl(): array
{
    return [
        'admin' => akunFl('admin', 'Admin Penguji'),
        'pembina' => akunFl('pembina', 'Pembina Garuda'),
        'a' => akunFl('siswa', 'Andi Sepuluh Pplg', 'X PPLG'),
        'b' => akunFl('siswa', 'Bayu Sebelas Pplg', 'XI PPLG'),
        'c' => akunFl('siswa', 'Citra Sebelas Tkj', 'XI TKJ 1'),
        'd' => akunFl('siswa', 'Dinda Duabelas Tkj', 'XII TKJ'),
        'e' => akunFl('siswa', 'Eka Tanpa Kelas', null),
    ];
}

function tampakFl($respons, array $ada, array $tidakAda): void
{
    foreach ($ada as $nama) {
        $respons->assertSee($nama);
    }
    foreach ($tidakAda as $nama) {
        $respons->assertDontSee($nama);
    }
}

// ---------------------------------------------------------------------------
// Kelola User
// ---------------------------------------------------------------------------

test('tanpa saringan semua user tampil', function () {
    $d = datasetFl();

    tampakFl($this->actingAs($d['admin'])->get(route('users.index'))->assertOk(),
        ['Andi Sepuluh Pplg', 'Bayu Sebelas Pplg', 'Citra Sebelas Tkj', 'Dinda Duabelas Tkj', 'Eka Tanpa Kelas', 'Pembina Garuda'], []);
});

test('saringan kelas (tingkat) menampilkan semua jurusan di tingkat itu', function () {
    $d = datasetFl();

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['kelas' => 'XI']))->assertOk(),
        ['Bayu Sebelas Pplg', 'Citra Sebelas Tkj'],
        ['Andi Sepuluh Pplg', 'Dinda Duabelas Tkj', 'Eka Tanpa Kelas', 'Pembina Garuda']);
});

test('saringan jurusan tidak terpengaruh tingkat atau nomor rombel, dan bisa digabung dengan kelas', function () {
    $d = datasetFl();

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['jurusan' => 'TKJ']))->assertOk(),
        ['Citra Sebelas Tkj', 'Dinda Duabelas Tkj'], ['Andi Sepuluh Pplg', 'Bayu Sebelas Pplg']);

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['kelas' => 'XI', 'jurusan' => 'TKJ']))->assertOk(),
        ['Citra Sebelas Tkj'], ['Bayu Sebelas Pplg', 'Dinda Duabelas Tkj']);
});

test('kelas yang tidak ada hasilnya kosong, bukan semua user', function () {
    $d = datasetFl();

    $respons = $this->actingAs($d['admin'])->get(route('users.index', ['kelas' => 'XII', 'jurusan' => 'PPLG']))->assertOk();

    tampakFl($respons, ['Tidak ada user yang cocok'], ['Andi Sepuluh Pplg', 'Bayu Sebelas Pplg', 'Citra Sebelas Tkj', 'Dinda Duabelas Tkj']);
});

test('saringan role', function () {
    $d = datasetFl();

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['role' => 'pembina']))->assertOk(),
        ['Pembina Garuda'], ['Andi Sepuluh Pplg', 'Eka Tanpa Kelas']);

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['role' => 'siswa', 'kelas' => 'X']))->assertOk(),
        ['Andi Sepuluh Pplg'], ['Bayu Sebelas Pplg', 'Pembina Garuda']);

    // role yang tidak dikenal diabaikan (tidak menyaring)
    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['role' => 'asal']))->assertOk(),
        ['Andi Sepuluh Pplg', 'Pembina Garuda'], []);
});

test('saringan ekskul mencakup anggota aktif dan pembinanya, bukan anggota yang sudah keluar', function () {
    $d = datasetFl();
    $futsal = ekskulFl('Futsal Garuda');
    $voli = ekskulFl('Voli Garuda');

    anggotaFl($futsal, $d['a']);
    anggotaFl($futsal, $d['d'], 'keluar');
    anggotaFl($voli, $d['b']);
    EkskulPembina::create(['ekstrakurikuler_id' => $futsal->id, 'user_id' => $d['pembina']->id, 'mulai' => now()->toDateString()]);

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['ekskul' => $futsal->id]))->assertOk(),
        ['Andi Sepuluh Pplg', 'Pembina Garuda'],
        ['Bayu Sebelas Pplg', 'Dinda Duabelas Tkj', 'Citra Sebelas Tkj']);

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['ekskul' => $voli->id]))->assertOk(),
        ['Bayu Sebelas Pplg'], ['Andi Sepuluh Pplg', 'Pembina Garuda']);
});

test('saringan "belum ikut ekskul" hanya untuk siswa tanpa keanggotaan aktif', function () {
    $d = datasetFl();
    $futsal = ekskulFl('Futsal Garuda');
    anggotaFl($futsal, $d['a']);
    anggotaFl($futsal, $d['d'], 'keluar');

    tampakFl($this->actingAs($d['admin'])->get(route('users.index', ['ekskul' => 'tanpa']))->assertOk(),
        ['Bayu Sebelas Pplg', 'Citra Sebelas Tkj', 'Dinda Duabelas Tkj', 'Eka Tanpa Kelas'],
        ['Andi Sepuluh Pplg', 'Pembina Garuda']);
});

test('semua saringan bisa digabung, dan pembina juga bisa memakainya', function () {
    $d = datasetFl();
    $futsal = ekskulFl('Futsal Garuda');
    anggotaFl($futsal, $d['a']);
    anggotaFl($futsal, $d['b']);
    anggotaFl($futsal, $d['c']);

    tampakFl($this->actingAs($d['pembina'])->get(route('users.index', ['role' => 'siswa', 'ekskul' => $futsal->id, 'kelas' => 'XI', 'jurusan' => 'PPLG']))->assertOk(),
        ['Bayu Sebelas Pplg'], ['Andi Sepuluh Pplg', 'Citra Sebelas Tkj']);
});

test('halaman menampilkan jumlah hasil, pilihan saringan dari data, dan tautan reset hanya saat saringan aktif', function () {
    $d = datasetFl();

    $this->actingAs($d['admin'])->get(route('users.index'))
        ->assertOk()->assertSee('Kelas XII')->assertSee('>TKJ<', false)->assertSee('Siswa belum ikut ekskul')->assertDontSee('>Reset<', false);

    $this->actingAs($d['admin'])->get(route('users.index', ['kelas' => 'XI']))
        ->assertOk()->assertSee('Menampilkan')->assertSee('>Reset<', false)
        ->assertSee('<strong>2</strong> dari 7 user', false);
});

// ---------------------------------------------------------------------------
// Tab Anggota di halaman ekskul
// ---------------------------------------------------------------------------

test('tab Anggota bisa disaring per kelas, jurusan, dan nama', function () {
    $d = datasetFl();
    $futsal = ekskulFl('Futsal Garuda');
    foreach (['a', 'b', 'c', 'd'] as $k) {
        anggotaFl($futsal, $d[$k]);
    }

    $url = fn (array $q) => route('ekstrakurikuler.show', array_merge([$futsal->id], ['tab' => 'anggota'], $q));

    tampakFl($this->actingAs($d['admin'])->get($url(['kelas' => 'XI']))->assertOk(),
        ['Bayu Sebelas Pplg', 'Citra Sebelas Tkj'], ['Andi Sepuluh Pplg', 'Dinda Duabelas Tkj']);

    tampakFl($this->actingAs($d['admin'])->get($url(['jurusan' => 'PPLG']))->assertOk(),
        ['Andi Sepuluh Pplg', 'Bayu Sebelas Pplg'], ['Citra Sebelas Tkj', 'Dinda Duabelas Tkj']);

    tampakFl($this->actingAs($d['admin'])->get($url(['cari' => 'dinda']))->assertOk(),
        ['Dinda Duabelas Tkj'], ['Andi Sepuluh Pplg', 'Bayu Sebelas Pplg']);

    tampakFl($this->actingAs($d['admin'])->get($url(['kelas' => 'XII', 'jurusan' => 'PPLG']))->assertOk(),
        ['Tidak ada anggota yang cocok'], ['Andi Sepuluh Pplg', 'Dinda Duabelas Tkj']);
});

// ---------------------------------------------------------------------------
// Pemecah kelas
// ---------------------------------------------------------------------------

test('KelasSiswa memecah tingkat dan jurusan dari teks kelas', function (?string $kelas, ?string $tingkat, ?string $jurusan) {
    expect(KelasSiswa::tingkat($kelas))->toBe($tingkat)->and(KelasSiswa::jurusan($kelas))->toBe($jurusan);
})->with([
    ['X', 'X', null],
    ['XI', 'XI', null],
    ['XI PPLG', 'XI', 'PPLG'],
    ['xi  pplg', 'XI', 'PPLG'],
    ['XII TKJ 2', 'XII', 'TKJ'],
    ['XI-PPLG-1', 'XI', 'PPLG'],
    ['XI IPA 1', 'XI', 'IPA'],
    ['10 RPL', '10', 'RPL'],
    ['PPLG', null, 'PPLG'],
    [null, null, null],
    ['', null, null],
]);

test('KelasSiswa menyusun pilihan tingkat berurutan dan jurusan unik', function () {
    $kelas = ['XII', 'X', 'XI PPLG', 'XI', 'XII TKJ 2', 'XI PPLG 1'];

    expect(KelasSiswa::daftarTingkat($kelas))->toBe(['X', 'XI', 'XII'])
        ->and(KelasSiswa::daftarJurusan($kelas))->toBe(['PPLG', 'TKJ'])
        ->and(KelasSiswa::yangCocok($kelas, 'XI', 'PPLG'))->toBe(['XI PPLG', 'XI PPLG 1'])
        ->and(KelasSiswa::yangCocok($kelas, null, 'TKJ'))->toBe(['XII TKJ 2'])
        ->and(KelasSiswa::yangCocok($kelas, 'X', 'PPLG'))->toBe([]);
});
