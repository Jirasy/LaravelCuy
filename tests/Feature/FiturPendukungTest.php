<?php

use App\Models\EkskulPembina;
use App\Models\EkskulTugas;
use App\Models\EkskulTugasPenerima;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunFp(string $role = 'siswa', ?string $nama = null): User
{
    return User::factory()->create(array_filter([
        'name' => $nama,
        'role' => $role,
        'is_active' => true,
    ]));
}

function ekskulFp(string $nama = 'Futsal'): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Budi',
        'status' => 'aktif',
    ]);
}

function pembinaFp(Ekstrakurikuler $ekskul, ?string $nama = null): User
{
    $pembina = akunFp('pembina', $nama);

    EkskulPembina::create([
        'ekstrakurikuler_id' => $ekskul->id,
        'user_id' => $pembina->id,
        'mulai' => now()->toDateString(),
    ]);

    return $pembina;
}

function anggotaFp(User $siswa, Ekstrakurikuler $ekskul, string $status = 'diterima'): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'status' => $status,
        'status_anggota' => 'aktif',
    ]);
}

function judulNotifFp(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

// ---------------------------------------------------------------------------
// Task (navbar pesan/task)
// ---------------------------------------------------------------------------

test('siswa membuat task hanya untuk dirinya sendiri, penerima lain diabaikan', function () {
    $siswa = akunFp();
    $teman = akunFp();

    $this->actingAs($siswa)->post(route('tugas.store'), [
        'judul' => 'Beli seragam',
        'penerima' => [$teman->id],
    ])->assertSessionHas('success');
    

    $tugas = EkskulTugas::firstOrFail();

    expect($tugas->dibuat_oleh)->toBe($siswa->id)
        ->and(EkskulTugasPenerima::where('tugas_id', $tugas->id)->pluck('user_id')->all())->toBe([$siswa->id])
        ->and(judulNotifFp($teman))->toBe([]);
});

test('judul task wajib diisi', function () {
    $this->actingAs(akunFp())->post(route('tugas.store'), ['judul' => ''])
        ->assertSessionHasErrors('judul');

    expect(EkskulTugas::count())->toBe(0);
});

test('pembina memberi task ke semua anggota aktif ekskulnya dan mereka mendapat notifikasi', function () {
    $ekskul = ekskulFp();
    $pembina = pembinaFp($ekskul);
    $a = akunFp();
    $b = akunFp();
    $menunggu = akunFp();
    anggotaFp($a, $ekskul);
    anggotaFp($b, $ekskul);
    anggotaFp($menunggu, $ekskul, 'pending');

    $this->actingAs($pembina)->post(route('tugas.store'), [
        'judul' => 'Latihan passing',
        'ekstrakurikuler_id' => $ekskul->id,
        'target' => 'semua_anggota',
    ])->assertSessionHas('success');

    $penerima = EkskulTugasPenerima::pluck('user_id')->sort()->values()->all();

    expect($penerima)->toBe(collect([$a->id, $b->id])->sort()->values()->all())
        ->and(judulNotifFp($a))->toContain('Task baru')
        ->and(judulNotifFp($b))->toContain('Task baru')
        ->and(judulNotifFp($menunggu))->toBe([])
        ->and(judulNotifFp($pembina))->toBe([]);
});

test('pembina tidak bisa memberi task ke ekskul yang bukan binaannya', function () {
    $milikku = ekskulFp('Futsal');
    $lain = ekskulFp('Basket');
    $pembina = pembinaFp($milikku);
    anggotaFp(akunFp(), $lain);

    $this->actingAs($pembina)->post(route('tugas.store'), [
        'judul' => 'Tidak boleh',
        'ekstrakurikuler_id' => $lain->id,
        'target' => 'semua_anggota',
    ])->assertForbidden();

    expect(EkskulTugas::count())->toBe(0);
});

test('hanya penerima yang boleh mengubah status task miliknya', function () {
    $pemilik = akunFp();
    $orangLain = akunFp();

    $tugas = EkskulTugas::create(['judul' => 'Tugas uji', 'dibuat_oleh' => $pemilik->id]);
    $penerima = EkskulTugasPenerima::create(['tugas_id' => $tugas->id, 'user_id' => $pemilik->id]);

    $this->actingAs($orangLain)->patch(route('tugas.status', $penerima), ['status' => 'selesai'])
        ->assertForbidden();

    $this->actingAs($pemilik)->patch(route('tugas.status', $penerima), ['status' => 'selesai']);

    expect($penerima->fresh()->status)->toBe('selesai')
        ->and($penerima->fresh()->selesai_pada)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Kontrol pendaftaran (buka/tutup)
// ---------------------------------------------------------------------------

test('pembina menutup pendaftaran lalu tombol siswa berubah jadi Pendaftaran Ditutup', function () {
    $ekskul = ekskulFp();
    $pembina = pembinaFp($ekskul);
    $siswa = akunFp();

    expect($ekskul->fresh()->label_pendaftaran)->toBe('Daftar Sekarang');

    $this->actingAs($pembina)->patch(route('pendaftaran-kontrol.update', $ekskul), ['pendaftaran_dibuka' => 0])
        ->assertSessionHas('success');

    expect($ekskul->fresh()->label_pendaftaran)->toBe('Pendaftaran Ditutup');

    // Aturan dijaga di server, bukan hanya teks tombol
    $this->actingAs($siswa)->post(route('pendaftaran.store'), ['ekstrakurikuler_id' => $ekskul->id])
        ->assertSessionHas('error', fn ($pesan) => str_contains($pesan, 'Pendaftaran Ditutup'));

    expect(PendaftaranEkskul::count())->toBe(0);
});

test('siswa dan pembina ekskul lain tidak bisa membuka atau menutup pendaftaran', function () {
    $ekskul = ekskulFp('Futsal');
    $lain = ekskulFp('Basket');
    $pembinaLain = pembinaFp($lain);

    $this->actingAs(akunFp())->patch(route('pendaftaran-kontrol.update', $ekskul), ['pendaftaran_dibuka' => 0])
        ->assertForbidden();
    $this->actingAs($pembinaLain)->patch(route('pendaftaran-kontrol.update', $ekskul), ['pendaftaran_dibuka' => 0])
        ->assertForbidden();

    expect($ekskul->fresh()->pendaftaran_dibuka)->toBeTrue();
});

test('kuota penuh menampilkan label Kuota Penuh', function () {
    $ekskul = ekskulFp();
    $ekskul->update(['kuota' => 1]);
    anggotaFp(akunFp(), $ekskul);

    expect($ekskul->fresh()->label_pendaftaran)->toBe('Kuota Penuh');
});

// ---------------------------------------------------------------------------
// Laporan (cetak / PDF / Excel)
// ---------------------------------------------------------------------------

test('pembina ekskul bisa membuka laporan cetak anggota yang berisi anggota aktif', function () {
    $ekskul = ekskulFp();
    $pembina = pembinaFp($ekskul);
    anggotaFp(akunFp('siswa', 'Andi Pratama'), $ekskul);
    anggotaFp(akunFp('siswa', 'Calon Menunggu'), $ekskul, 'pending');

    $this->actingAs($pembina)->get(route('laporan.unduh', [$ekskul, 'anggota', 'cetak']))
        ->assertOk()
        ->assertSee('Andi Pratama')
        ->assertDontSee('Calon Menunggu');
});

test('laporan hanya untuk admin atau pembina ekskul itu sendiri', function () {
    $ekskul = ekskulFp('Futsal');
    $lain = ekskulFp('Basket');
    $pembinaLain = pembinaFp($lain);

    $this->actingAs(akunFp())->get(route('laporan.unduh', [$ekskul, 'anggota', 'cetak']))->assertForbidden();
    $this->actingAs($pembinaLain)->get(route('laporan.unduh', [$ekskul, 'anggota', 'cetak']))->assertForbidden();
    $this->actingAs(akunFp('admin'))->get(route('laporan.unduh', [$ekskul, 'absensi', 'cetak']))->assertOk();
});

test('jenis atau format laporan yang tidak dikenal ditolak', function () {
    $ekskul = ekskulFp();
    $admin = akunFp('admin');

    $this->actingAs($admin)->get("/ekstrakurikuler/{$ekskul->id}/laporan/rahasia/cetak")->assertNotFound();
    $this->actingAs($admin)->get("/ekstrakurikuler/{$ekskul->id}/laporan/anggota/docx")->assertNotFound();
});

test('ekspor PDF mengunduh berkas PDF', function () {
    $ekskul = ekskulFp();
    anggotaFp(akunFp(), $ekskul);

    $this->actingAs(akunFp('admin'))->get(route('laporan.unduh', [$ekskul, 'anggota', 'pdf']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
})->skip(! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class), 'barryvdh/laravel-dompdf belum terpasang');

test('ekspor Excel mengunduh berkas, atau memberi petunjuk kalau paketnya belum dipasang', function () {
    $ekskul = ekskulFp();
    anggotaFp(akunFp(), $ekskul);

    $respons = $this->actingAs(akunFp('admin'))->get(route('laporan.unduh', [$ekskul, 'anggota', 'excel']));

    if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
        $respons->assertOk();
    } else {
        $respons->assertSessionHas('error', fn ($pesan) => str_contains($pesan, 'maatwebsite/excel'));
    }
});

// ---------------------------------------------------------------------------
// Kelola User: eksklusif admin & pembina
// ---------------------------------------------------------------------------

test('Kelola User hanya bisa dibuka admin dan pembina', function () {
    $this->get(route('users.index'))->assertRedirect(route('login'));

    $this->actingAs(akunFp('siswa'))->get(route('users.index'))->assertForbidden();
    $this->actingAs(akunFp('admin'))->get(route('users.index'))->assertOk();
    $this->actingAs(akunFp('pembina'))->get(route('users.index'))->assertOk();
});

test('hanya admin yang boleh mengubah role user', function () {
    $target = akunFp('siswa');

    $this->actingAs(akunFp('pembina'))->patch(route('users.updateRole', $target), ['role' => 'admin'])
        ->assertForbidden();

    expect($target->fresh()->role)->toBe('siswa');
});
