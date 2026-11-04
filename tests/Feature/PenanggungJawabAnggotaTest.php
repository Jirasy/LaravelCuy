<?php

use App\Models\EkskulAnggotaJabatan;
use App\Models\EkskulJabatan;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunPj(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulPj(string $nama = 'Robotik Uji', string $kodeJenis = 'ekskul'): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Uji',
        'status' => 'aktif',
        'jenis_ekskul_id' => (int) JenisEkskul::where('kode', $kodeJenis)->value('id'),
    ]);
}

function pendaftarPj(Ekstrakurikuler $ekskul, User $akun, string $status = 'diterima', string $statusAnggota = 'aktif', array $tambahan = []): PendaftaranEkskul
{
    return PendaftaranEkskul::create(array_merge([
        'user_id' => $akun->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $akun->name,
        'peran' => 'siswa',
        'status' => $status,
        'status_anggota' => $statusAnggota,
    ], $tambahan));
}

// Anggota yang memegang sebuah jabatan; $bendera = jabatan itu bertanda "penanggung jawab anggota"
function seniorPj(Ekstrakurikuler $ekskul, bool $bendera = true, string $statusAnggota = 'aktif', ?int $periodeSelesai = null, string $namaJabatan = 'Ketua'): User
{
    $senior = akunPj();
    $pendaftaran = pendaftarPj($ekskul, $senior, 'diterima', $statusAnggota);
    $jabatan = EkskulJabatan::create([
        'ekstrakurikuler_id' => $ekskul->id,
        'nama' => $namaJabatan,
        'varian' => '',
        'boleh_kelola_anggota' => $bendera,
    ]);
    EkskulAnggotaJabatan::create([
        'pendaftaran_id' => $pendaftaran->id,
        'jabatan_id' => $jabatan->id,
        'periode_mulai' => 2026,
        'periode_selesai' => $periodeSelesai,
    ]);

    return $senior;
}

function pembinaPj(Ekstrakurikuler $ekskul): User
{
    $pembina = akunPj('pembina');
    $ekskul->pembinas()->attach($pembina->id, ['mulai' => now()->toDateString()]);

    return $pembina;
}

// ---------------------------------------------------------------------------
// Menandai jabatan sebagai penanggung jawab (hanya admin dan pembina ekskul)
// ---------------------------------------------------------------------------

test('pembina menandai jabatan sebagai penanggung jawab anggota, bawaannya mati, dan bisa dimatikan lagi', function () {
    $ekskul = ekskulPj();
    $pembina = pembinaPj($ekskul);

    $this->actingAs($pembina)->post(route('ekskul-jabatan.store', $ekskul->id), ['nama' => 'Ketua', 'boleh_kelola_anggota' => '1'])
        ->assertSessionHasNoErrors();
    $this->actingAs($pembina)->post(route('ekskul-jabatan.store', $ekskul->id), ['nama' => 'Sekretaris'])
        ->assertSessionHasNoErrors();

    $ketua = EkskulJabatan::where('nama', 'Ketua')->firstOrFail();
    expect($ketua->boleh_kelola_anggota)->toBeTrue()
        ->and(EkskulJabatan::where('nama', 'Sekretaris')
        ->firstOrFail()
        ->boleh_kelola_anggota)->toBeFalse();

    $this->actingAs($pembina)->put(route('ekskul-jabatan.update', $ketua->id), ['nama' => 'Ketua'])
        ->assertSessionHasNoErrors();
    expect($ketua->fresh()->boleh_kelola_anggota)->toBeFalse();
});

test('senior tidak bisa menaikkan hak sendiri: mengatur jabatan, memberi jabatan, mengeluarkan anggota, dan keanggotaan tetap terlarang', function () {
    $ekskul = ekskulPj();
    $senior = seniorPj($ekskul);
    $anggota = pendaftarPj($ekskul, akunPj());
    $jabatan = EkskulJabatan::where('ekstrakurikuler_id', $ekskul->id)->firstOrFail();

    $this->actingAs($senior)->post(route('ekskul-jabatan.store', $ekskul->id), ['nama' => 'Wakil', 'boleh_kelola_anggota' => '1'])->assertForbidden();
    $this->actingAs($senior)->put(route('ekskul-jabatan.update', $jabatan->id), ['nama' => 'Ketua', 'boleh_kelola_anggota' => '1'])->assertForbidden();
    $this->actingAs($senior)->post(route('anggota-jabatan.store', $anggota->id), ['jabatan_ids' => [$jabatan->id], 'periode_mulai' => 2026])->assertForbidden();
    $this->actingAs($senior)->post(route('pengunduran.keluarkan', $anggota->id), ['alasan' => 'tes'])->assertForbidden();
    $this->actingAs($senior)->patch(route('keanggotaan.update', $anggota->id), ['angkatan_ekskul' => '5'])->assertForbidden();

    expect($anggota->fresh()->status_anggota)->toBe('aktif');
});

// ---------------------------------------------------------------------------
// Kuota dan buka/tutup pendaftaran
// ---------------------------------------------------------------------------

test('senior penanggung jawab mengatur kuota dan buka/tutup pendaftaran ekskulnya', function () {
    $ekskul = ekskulPj();
    $senior = seniorPj($ekskul);

    $this->actingAs($senior)->patch(route('pendaftaran-kontrol.update', $ekskul->id), ['pendaftaran_dibuka' => 0, 'kuota' => 20])
        ->assertSessionHasNoErrors();

    $ekskul->refresh();
    expect($ekskul->kuota)->toBe(20)
        ->and($ekskul->pendaftaran_dibuka)->toBeFalse();
});

test('yang bukan penanggung jawab ditolak mengatur kuota', function (Closure $siapkan) {
    $ekskul = ekskulPj();
    $akun = $siapkan($ekskul);

    $this->actingAs($akun)->patch(route('pendaftaran-kontrol.update', $ekskul->id), ['pendaftaran_dibuka' => 0, 'kuota' => 5])
        ->assertForbidden();

    expect($ekskul->fresh()->kuota)->toBeNull();
})->with([
    'anggota biasa tanpa jabatan' => [fn (Ekstrakurikuler $e) => tap(akunPj(), fn ($a) => pendaftarPj($e, $a))],
    'pemegang jabatan yang tidak bertanda' => [fn (Ekstrakurikuler $e) => seniorPj($e, bendera: false)],
    'siswa yang bukan anggota' => [fn (Ekstrakurikuler $e) => akunPj()],
    'senior yang sudah keluar dari ekskul' => [fn (Ekstrakurikuler $e) => seniorPj($e, statusAnggota: 'keluar')],
    'senior yang jabatannya sudah berakhir' => [fn (Ekstrakurikuler $e) => seniorPj($e, periodeSelesai: 2025)],
    'senior di ekskul lain' => [fn (Ekstrakurikuler $e) => seniorPj(ekskulPj('Ekskul Lain Uji'))],
    'pembina ekskul lain' => [fn (Ekstrakurikuler $e) => pembinaPj(ekskulPj('Ekskul Lain Uji'))],
]);

test('admin dan pembina ekskul itu tetap bisa mengatur kuota', function () {
    $ekskul = ekskulPj();

    $this->actingAs(akunPj('admin'))->patch(route('pendaftaran-kontrol.update', $ekskul->id), ['pendaftaran_dibuka' => 1, 'kuota' => 30])->assertSessionHasNoErrors();
    expect($ekskul->fresh()->kuota)->toBe(30);

    $this->actingAs(pembinaPj($ekskul))->patch(route('pendaftaran-kontrol.update', $ekskul->id), ['pendaftaran_dibuka' => 1, 'kuota' => 40])->assertSessionHasNoErrors();
    expect($ekskul->fresh()->kuota)->toBe(40);
});

// ---------------------------------------------------------------------------
// Menyetujui / menolak pendaftar
// ---------------------------------------------------------------------------

test('senior penanggung jawab menyetujui dan menolak pendaftar yang menunggu', function () {
    $ekskul = ekskulPj();
    $senior = seniorPj($ekskul);
    $diterima = pendaftarPj($ekskul, akunPj(), 'pending');
    $ditolak = pendaftarPj($ekskul, akunPj(), 'pending');

    $this->actingAs($senior)->patch(route('pendaftaran.updateStatus', $diterima->id), ['status' => 'diterima', 'alasan' => ''])
        ->assertRedirect(route('pendaftaran.index'));
    $this->actingAs($senior)->patch(route('pendaftaran.updateStatus', $ditolak->id), ['status' => 'ditolak', 'alasan' => 'Kuota latihan penuh'])
        ->assertRedirect(route('pendaftaran.index'));

    expect($diterima->fresh()->status)->toBe('diterima')
        ->and($diterima->fresh()->tahun_mulai)->not->toBeNull()
        ->and($ditolak->fresh()->status)->toBe('ditolak')
        ->and($ditolak->fresh()->alasan)->toBe('Kuota latihan penuh');
});

test('senior hanya memutuskan pendaftar yang menunggu: tidak mengubah yang sudah diputuskan atau mengembalikan ke menunggu', function () {
    $ekskul = ekskulPj();
    $senior = seniorPj($ekskul);
    $sudahDiterima = pendaftarPj($ekskul, akunPj(), 'diterima');
    $menunggu = pendaftarPj($ekskul, akunPj(), 'pending');

    // anggota yang sudah diterima tidak boleh "ditolak" (itu sama saja mengeluarkan)
    $this->actingAs($senior)->patch(route('pendaftaran.updateStatus', $sudahDiterima->id), ['status' => 'ditolak', 'alasan' => 'x'])->assertForbidden();
    expect($sudahDiterima->fresh()->status)->toBe('diterima');

    // mengembalikan ke "menunggu" hanya hak admin/pembina
    $this->actingAs($senior)->patch(route('pendaftaran.updateStatus', $menunggu->id), ['status' => 'pending', 'alasan' => ''])
        ->assertSessionHas('error');
    expect($menunggu->fresh()->status)->toBe('pending');
});

test('senior tidak memutuskan pendaftaran miliknya sendiri', function () {
    $ekskul = ekskulPj();
    $senior = seniorPj($ekskul);
    // pendaftaran tambahan atas nama senior sendiri yang masih menunggu
    $milikSendiri = pendaftarPj($ekskul, $senior, 'pending');

    $this->actingAs($senior)->patch(route('pendaftaran.updateStatus', $milikSendiri->id), ['status' => 'diterima', 'alasan' => ''])->assertForbidden();

    expect($milikSendiri->fresh()->status)->toBe('pending');
});

test('senior tidak memutuskan pendaftar ekskul lain', function () {
    $ekskul = ekskulPj();
    $lain = ekskulPj('Ekskul Lain Uji');
    $senior = seniorPj($ekskul);
    $pendaftarLain = pendaftarPj($lain, akunPj(), 'pending');

    $this->actingAs($senior)->patch(route('pendaftaran.updateStatus', $pendaftarLain->id), ['status' => 'diterima', 'alasan' => ''])->assertForbidden();

    expect($pendaftarLain->fresh()->status)->toBe('pending');
});

test('di ekskul berseleksi senior tidak memutuskan pendaftar lewat jalur ini', function () {
    $ekskul = ekskulPj('Paskibra Uji', 'seleksi');
    $senior = seniorPj($ekskul);
    $calon = pendaftarPj($ekskul, akunPj(), 'pending');

    $this->actingAs($senior)->patch(route('pendaftaran.updateStatus', $calon->id), ['status' => 'diterima', 'alasan' => ''])->assertForbidden();

    expect($calon->fresh()->status)->toBe('pending');
});

test('siswa biasa, anggota biasa, dan pembina ekskul lain tidak bisa memutuskan pendaftar', function () {
    $ekskul = ekskulPj();
    $calon = pendaftarPj($ekskul, akunPj(), 'pending');
    $anggotaBiasa = akunPj();
    pendaftarPj($ekskul, $anggotaBiasa);

    foreach ([akunPj(), $anggotaBiasa, pembinaPj(ekskulPj('Ekskul Lain Uji'))] as $akun) {
        $this->actingAs($akun)->patch(route('pendaftaran.updateStatus', $calon->id), ['status' => 'diterima', 'alasan' => ''])->assertForbidden();
    }

    expect($calon->fresh()->status)->toBe('pending');
});

// ---------------------------------------------------------------------------
// Tampilan
// ---------------------------------------------------------------------------

test('halaman pendaftaran senior: hanya pendaftar yang menunggu di ekskulnya, tanpa NISN orang lain', function () {
    $ekskul = ekskulPj();
    $senior = seniorPj($ekskul);
    pendaftarPj($ekskul, akunPj(), 'pending', 'aktif', ['nama_pendaftar' => 'Calon Menunggu Uji', 'nisn' => '9988776655']);
    pendaftarPj($ekskul, akunPj(), 'diterima', 'aktif', ['nama_pendaftar' => 'Anggota Lama Uji']);
    pendaftarPj(ekskulPj('Ekskul Lain Uji'), akunPj(), 'pending', 'aktif', ['nama_pendaftar' => 'Calon Ekskul Lain Uji']);

    $this->actingAs($senior)->get(route('pendaftaran.index'))
        ->assertOk()
        ->assertSee('Calon Menunggu Uji')
        ->assertSee('Pendaftaran Saya')
        ->assertSee('penanggung jawab anggota')
        ->assertDontSee('9988776655')
        ->assertDontSee('Anggota Lama Uji')
        ->assertDontSee('Calon Ekskul Lain Uji');

    // admin tetap melihat semuanya, termasuk NISN
    $this->actingAs(akunPj('admin'))->get(route('pendaftaran.index'))
        ->assertOk()
        ->assertSee('Calon Menunggu Uji')
        ->assertSee('9988776655')
        ->assertSee('Anggota Lama Uji');
});

test('siswa biasa tidak melihat pendaftar orang lain di halaman pendaftaran', function () {
    $ekskul = ekskulPj();
    $biasa = akunPj();
    pendaftarPj($ekskul, $biasa);
    pendaftarPj($ekskul, akunPj(), 'pending', 'aktif', ['nama_pendaftar' => 'Calon Menunggu Uji']);

    $this->actingAs($biasa)->get(route('pendaftaran.index'))
        ->assertOk()
        ->assertDontSee('Calon Menunggu Uji')
        ->assertDontSee('penanggung jawab anggota');
});

test('panel kuota di halaman ekskul tampil untuk senior penanggung jawab, bukan untuk anggota biasa', function () {
    $ekskul = ekskulPj();
    $senior = seniorPj($ekskul);
    $biasa = akunPj();
    pendaftarPj($ekskul, $biasa);
    $aksi = route('pendaftaran-kontrol.update', $ekskul->id);

    $this->actingAs($senior)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()->assertSee($aksi, false);

    $this->actingAs($biasa)->get(route('ekstrakurikuler.show', $ekskul->id))
        ->assertOk()->assertDontSee($aksi, false);
});

test('form jabatan punya pilihan penanggung jawab anggota, dan kartu jabatan menandainya', function () {
    $ekskul = ekskulPj();
    seniorPj($ekskul);
    $pembina = pembinaPj($ekskul);

    $this->actingAs($pembina)->get(route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'struktur']))
        ->assertOk()
        ->assertSee('name="boleh_kelola_anggota"', false)
        ->assertSee('Penanggung jawab anggota');
});
