<?php

use App\Models\EkskulPembina;
use App\Models\EkskulPengunduranDiri;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunKeanggotaan(string $role = 'siswa', ?string $nama = null): User
{
    return User::factory()->create(array_filter([
        'name' => $nama,
        'role' => $role,
        'is_active' => true,
    ]));
}

function ekskulKeanggotaan(string $nama = 'Futsal'): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Budi',
        'status' => 'aktif',
    ]);
}

function tugaskanPembinaKeanggotaan(User $pembina, Ekstrakurikuler $ekskul): EkskulPembina
{
    return EkskulPembina::create([
        'ekstrakurikuler_id' => $ekskul->id,
        'user_id' => $pembina->id,
        'mulai' => now()->toDateString(),
    ]);
}

function daftarKeanggotaan(User $siswa, Ekstrakurikuler $ekskul, string $status = 'diterima', string $statusAnggota = 'aktif'): PendaftaranEkskul
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

// Judul notifikasi in-app yang diterima user (untuk memeriksa siapa diberi tahu apa)
function judulNotifikasi(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

// ---------------------------------------------------------------------------
// 7a. Anti-double registration
// ---------------------------------------------------------------------------

test('siswa tidak bisa mendaftar lagi selama pendaftaran sebelumnya masih berlaku', function (string $status, string $statusAnggota, string $pesan) {
    $siswa = akunKeanggotaan();
    $ekskul = ekskulKeanggotaan();
    daftarKeanggotaan($siswa, $ekskul, $status, $statusAnggota);

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertSessionHas('error', fn ($pesanError) => str_contains($pesanError, $pesan));

    expect(PendaftaranEkskul::count())->toBe(1);
})->with([
    'masih menunggu' => ['pending', 'aktif', 'menunggu persetujuan'],
    'anggota aktif' => ['diterima', 'aktif', 'anggota aktif'],
    'pernah ditolak' => ['ditolak', 'aktif', 'tidak disetujui'],
    'pernah dikeluarkan' => ['diterima', 'dikeluarkan', 'dikeluarkan'],
]);

test('siswa yang pernah mengundurkan diri boleh mendaftar lagi', function () {
    $siswa = akunKeanggotaan();
    $ekskul = ekskulKeanggotaan();
    $lama = daftarKeanggotaan($siswa, $ekskul, 'diterima', 'keluar');

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertRedirect(route('pendaftaran.index'));

    // Riwayat lama tetap ada; yang baru menunggu persetujuan
    expect(PendaftaranEkskul::where('user_id', $siswa->id)->count())->toBe(2)
        ->and($lama->fresh()->status_anggota)->toBe('keluar')
        ->and(PendaftaranEkskul::where('user_id', $siswa->id)->where('status', 'pending')->exists())->toBeTrue();

    // Pendaftaran baru itu sendiri langsung menghalangi daftar ketiga kalinya
    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertSessionHas('error');
    expect(PendaftaranEkskul::where('user_id', $siswa->id)->count())->toBe(2);
});

test('pendaftaran yang dikirim bersamaan ditahan oleh kunci', function () {
    $siswa = akunKeanggotaan();
    $ekskul = ekskulKeanggotaan();

    $kunci = Cache::lock("daftar-ekskul:{$ekskul->id}:u{$siswa->id}", 10);
    expect($kunci->get())->toBeTrue();

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertSessionHas('error');
    expect(PendaftaranEkskul::count())->toBe(0);

    $kunci->release();

    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertRedirect(route('pendaftaran.index'));
    expect(PendaftaranEkskul::count())->toBe(1);
});

test('form pendaftaran tidak menawarkan ekskul yang masih punya pendaftaran berlaku', function () {
    $siswa = akunKeanggotaan();
    $terdaftar = ekskulKeanggotaan('Silat');
    ekskulKeanggotaan('Catur');
    daftarKeanggotaan($siswa, $terdaftar);

    $this->actingAs($siswa)->get(route('pendaftaran.create'))
        ->assertOk()
        ->assertSee('Catur')
        ->assertDontSee('Silat');

    $this->actingAs($siswa)->get(route('pendaftaran.create', ['ekstrakurikuler_id' => $terdaftar->id]))
        ->assertRedirect(route('ekstrakurikuler.show', $terdaftar->id))
        ->assertSessionHas('error');
});

// ---------------------------------------------------------------------------
// 7b. Siswa mengundurkan diri (butuh persetujuan pembina/admin)
// ---------------------------------------------------------------------------

test('siswa mengajukan keluar dengan alasan, baru resmi keluar setelah pembina menyetujui', function () {
    $siswa = akunKeanggotaan();
    $pembina = akunKeanggotaan('pembina');
    $ekskul = ekskulKeanggotaan();
    tugaskanPembinaKeanggotaan($pembina, $ekskul);
    $pendaftaran = daftarKeanggotaan($siswa, $ekskul);

    // Alasan wajib dan minimal 10 karakter
    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'capek'])
        ->assertSessionHasErrors('alasan');
    expect(EkskulPengunduranDiri::count())->toBe(0);

    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Jadwalnya bentrok dengan les tambahan.'])
        ->assertSessionHas('success');

    $pengajuan = EkskulPengunduranDiri::firstOrFail();
    expect($pengajuan->status)->toBe('menunggu')
        ->and($pengajuan->peran)->toBe('anggota')
        ->and($pengajuan->jenis)->toBe('mengundurkan_diri')
        ->and(judulNotifikasi($pembina))->toContain('Pengajuan keluar ekskul');

    // Sebelum disetujui, siswa tetap anggota aktif
    expect($pendaftaran->fresh()->status_anggota)->toBe('aktif');

    $this->actingAs($pembina)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])
        ->assertSessionHas('success');

    expect($pengajuan->fresh()->status)->toBe('disetujui')
        ->and($pendaftaran->fresh()->status_anggota)->toBe('keluar')
        ->and($pendaftaran->fresh()->tanggal_keluar)->not->toBeNull()
        ->and(judulNotifikasi($siswa))->toContain('Pengunduran diri disetujui');

    // Sudah keluar -> boleh mendaftar lagi (aturan 7a)
    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertRedirect(route('pendaftaran.index'));
});

test('pengajuan keluar ditolak butuh catatan dan siswa tetap anggota aktif', function () {
    $siswa = akunKeanggotaan();
    $pembina = akunKeanggotaan('pembina');
    $ekskul = ekskulKeanggotaan();
    tugaskanPembinaKeanggotaan($pembina, $ekskul);
    $pendaftaran = daftarKeanggotaan($siswa, $ekskul);

    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Mau fokus ke pelajaran dulu.']);
    $pengajuan = EkskulPengunduranDiri::firstOrFail();

    $this->actingAs($pembina)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'tolak'])
        ->assertSessionHasErrors('catatan');
    expect($pengajuan->fresh()->status)->toBe('menunggu');

    $this->actingAs($pembina)->patch(route('pengunduran.putuskan', $pengajuan->id), [
        'keputusan' => 'tolak',
        'catatan' => 'Masih dibutuhkan untuk lomba bulan depan.',
    ])->assertSessionHas('success');

    expect($pengajuan->fresh()->status)->toBe('ditolak')
        ->and($pendaftaran->fresh()->status_anggota)->toBe('aktif')
        ->and(judulNotifikasi($siswa))->toContain('Pengunduran diri ditolak');

    // Pengajuan yang sudah diputuskan tidak bisa diputuskan lagi
    $this->actingAs($pembina)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])
        ->assertForbidden();
});

test('tidak boleh ada dua pengajuan keluar yang menunggu, dan pengaju bisa membatalkan', function () {
    $siswa = akunKeanggotaan();
    $ekskul = ekskulKeanggotaan();
    daftarKeanggotaan($siswa, $ekskul);

    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Alasan pertama yang cukup panjang.']);
    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Alasan kedua yang juga panjang.'])
        ->assertSessionHas('error');
    expect(EkskulPengunduranDiri::count())->toBe(1);

    $this->actingAs($siswa)->delete(route('pengunduran.batal', EkskulPengunduranDiri::firstOrFail()->id))
        ->assertSessionHas('success');
    expect(EkskulPengunduranDiri::count())->toBe(0);
});

test('yang bukan anggota aktif tidak bisa mengajukan keluar, dan siswa tidak bisa memutuskan pengajuan', function () {
    $siswa = akunKeanggotaan();
    $lain = akunKeanggotaan();
    $ekskul = ekskulKeanggotaan();
    daftarKeanggotaan($siswa, $ekskul);

    $this->actingAs($lain)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Saya bukan anggota ekskul ini.'])
        ->assertSessionHas('error');
    expect(EkskulPengunduranDiri::count())->toBe(0);

    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Mau pindah ke ekskul lain.']);
    $pengajuan = EkskulPengunduranDiri::firstOrFail();

    $this->actingAs($siswa)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])
        ->assertForbidden();
    $this->actingAs($lain)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])
        ->assertForbidden();
});

test('pembina ekskul lain tidak bisa memutuskan pengajuan anggota ekskul ini', function () {
    $siswa = akunKeanggotaan();
    $pembinaLain = akunKeanggotaan('pembina');
    $ekskul = ekskulKeanggotaan('Futsal');
    tugaskanPembinaKeanggotaan($pembinaLain, ekskulKeanggotaan('Catur'));
    daftarKeanggotaan($siswa, $ekskul);

    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Mau pindah ke ekskul lain.']);
    $pengajuan = EkskulPengunduranDiri::firstOrFail();

    $this->actingAs($pembinaLain)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])
        ->assertForbidden();
    expect($pengajuan->fresh()->status)->toBe('menunggu');
});

// ---------------------------------------------------------------------------
// 7c. Pembina mengundurkan diri (disetujui admin, admin mencari pengganti)
// ---------------------------------------------------------------------------

test('pembina keluar harus disetujui admin dan admin diingatkan mencari pengganti', function () {
    $admin = akunKeanggotaan('admin');
    $adminLain = akunKeanggotaan('admin');
    $pembina = akunKeanggotaan('pembina');
    $pembinaLain = akunKeanggotaan('pembina');
    $ekskul = ekskulKeanggotaan('Futsal');
    tugaskanPembinaKeanggotaan($pembina, $ekskul);
    tugaskanPembinaKeanggotaan($pembinaLain, ekskulKeanggotaan('Catur'));

    // Alasan wajib
    $this->actingAs($pembina)->post(route('pengunduran.pembina', $ekskul->id), ['alasan' => 'pindah'])
        ->assertSessionHasErrors('alasan');

    $this->actingAs($pembina)->post(route('pengunduran.pembina', $ekskul->id), ['alasan' => 'Dipindahtugaskan ke sekolah lain.'])
        ->assertSessionHas('success');

    $pengajuan = EkskulPengunduranDiri::firstOrFail();
    expect($pengajuan->peran)->toBe('pembina')
        ->and(judulNotifikasi($admin))->toContain('Pembina mengajukan keluar');

    // Pembina (termasuk dirinya sendiri) tidak boleh memutuskan; hanya admin
    $this->actingAs($pembina)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])->assertForbidden();
    $this->actingAs($pembinaLain)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])->assertForbidden();

    $this->actingAs($admin)->patch(route('pengunduran.putuskan', $pengajuan->id), ['keputusan' => 'setuju'])
        ->assertSessionHas('success');

    expect(EkskulPembina::where('ekstrakurikuler_id', $ekskul->id)->where('user_id', $pembina->id)->first()->selesai)->not->toBeNull()
        ->and(judulNotifikasi($pembina))->toContain('Pengunduran diri disetujui')
        ->and(judulNotifikasi($adminLain))->toContain('Butuh pembina pengganti');

    // Ekskul tanpa pembina muncul sebagai tugas admin
    $this->actingAs($adminLain)->get(route('pengunduran.index'))
        ->assertOk()
        ->assertSee('Futsal');
});

test('yang bukan pembina bertugas di ekskul itu tidak bisa mengajukan keluar sebagai pembina', function () {
    $pembinaLain = akunKeanggotaan('pembina');
    $ekskul = ekskulKeanggotaan('Futsal');
    tugaskanPembinaKeanggotaan($pembinaLain, ekskulKeanggotaan('Catur'));

    $this->actingAs($pembinaLain)->post(route('pengunduran.pembina', $ekskul->id), ['alasan' => 'Mau keluar dari ekskul ini.'])
        ->assertForbidden();
    expect(EkskulPengunduranDiri::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// 7d. Admin/pembina mengeluarkan siswa dengan alasan
// ---------------------------------------------------------------------------

test('pembina mengeluarkan siswa dengan alasan, lalu siswa tidak bisa mendaftar sendiri lagi', function () {
    $siswa = akunKeanggotaan();
    $pembina = akunKeanggotaan('pembina');
    $ekskul = ekskulKeanggotaan();
    tugaskanPembinaKeanggotaan($pembina, $ekskul);
    $pendaftaran = daftarKeanggotaan($siswa, $ekskul);

    $this->actingAs($pembina)->post(route('pengunduran.keluarkan', $pendaftaran->id), ['alasan' => 'pendek'])
        ->assertSessionHasErrors('alasan');
    expect($pendaftaran->fresh()->status_anggota)->toBe('aktif');

    $this->actingAs($pembina)->post(route('pengunduran.keluarkan', $pendaftaran->id), ['alasan' => 'Sering absen tanpa keterangan selama sebulan.'])
        ->assertSessionHas('success');

    $catatan = EkskulPengunduranDiri::firstOrFail();
    expect($pendaftaran->fresh()->status_anggota)->toBe('dikeluarkan')
        ->and($catatan->jenis)->toBe('dikeluarkan')
        ->and($catatan->status)->toBe('disetujui')
        ->and($catatan->alasan)->toContain('Sering absen')
        ->and(judulNotifikasi($siswa))->toContain('Dikeluarkan dari ekskul');

    // Tidak bisa dikeluarkan dua kali
    $this->actingAs($pembina)->post(route('pengunduran.keluarkan', $pendaftaran->id), ['alasan' => 'Alasan yang kedua kalinya.'])
        ->assertSessionHas('error');
    expect(EkskulPengunduranDiri::count())->toBe(1);

    // Dikeluarkan tidak bisa daftar sendiri; harus lewat admin/pembina
    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertSessionHas('error');
    expect(PendaftaranEkskul::count())->toBe(1);
});

test('hanya admin atau pembina ekskul itu yang bisa mengeluarkan anggota', function () {
    $siswa = akunKeanggotaan();
    $pembinaLain = akunKeanggotaan('pembina');
    $admin = akunKeanggotaan('admin');
    $ekskul = ekskulKeanggotaan('Futsal');
    tugaskanPembinaKeanggotaan($pembinaLain, ekskulKeanggotaan('Catur'));
    $pendaftaran = daftarKeanggotaan($siswa, $ekskul);
    $alasan = ['alasan' => 'Melanggar tata tertib ekskul berulang kali.'];

    $this->actingAs($siswa)->post(route('pengunduran.keluarkan', $pendaftaran->id), $alasan)->assertForbidden();
    $this->actingAs($pembinaLain)->post(route('pengunduran.keluarkan', $pendaftaran->id), $alasan)->assertForbidden();
    expect($pendaftaran->fresh()->status_anggota)->toBe('aktif');

    $this->actingAs($admin)->post(route('pengunduran.keluarkan', $pendaftaran->id), $alasan)->assertSessionHas('success');
    expect($pendaftaran->fresh()->status_anggota)->toBe('dikeluarkan');
});

test('mengeluarkan anggota menutup pengajuan keluarnya yang masih menunggu', function () {
    $siswa = akunKeanggotaan();
    $pembina = akunKeanggotaan('pembina');
    $ekskul = ekskulKeanggotaan();
    tugaskanPembinaKeanggotaan($pembina, $ekskul);
    $pendaftaran = daftarKeanggotaan($siswa, $ekskul);

    $this->actingAs($siswa)->post(route('pengunduran.anggota', $ekskul->id), ['alasan' => 'Mau fokus ke pelajaran dulu.']);
    expect(EkskulPengunduranDiri::where('status', 'menunggu')->count())->toBe(1);

    $this->actingAs($pembina)->post(route('pengunduran.keluarkan', $pendaftaran->id), ['alasan' => 'Tidak pernah hadir latihan sama sekali.']);

    expect(EkskulPengunduranDiri::where('status', 'menunggu')->count())->toBe(0)
        ->and(EkskulPengunduranDiri::where('jenis', 'mengundurkan_diri')->first()->catatan_keputusan)->toContain('Ditutup otomatis');
});
