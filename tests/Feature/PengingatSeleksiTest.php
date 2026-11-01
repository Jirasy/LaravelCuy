<?php

use App\Models\CasisPendaftaran;
use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\OsisPeriode;
use App\Models\SeleksiPengingat;
use App\Models\SeleksiTahap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunPg(string $role, string $nama): User
{
    return User::factory()->create(['role' => $role, 'name' => $nama, 'is_active' => true]);
}

// Skenario dasar: ekskul seleksi + periode berstatus seleksi + admin, pembina, senior, dan satu pendaftar yang diajukan
function skenarioPg(string $statusPeriode = 'seleksi', string $namaEkskul = 'Paskibra Garuda'): array
{
    $ekskul = Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $namaEkskul, 'pembina' => 'Pak Uji', 'status' => 'aktif',
        'jenis_ekskul_id' => JenisEkskul::where('kode', 'seleksi')->value('id'),
    ]);
    $periode = OsisPeriode::create(['ekstrakurikuler_id' => $ekskul->id, 'nama' => '2026/2027', 'status' => $statusPeriode]);

    $pembina = akunPg('pembina', 'Pembina Uji');
    EkskulPembina::create(['ekstrakurikuler_id' => $ekskul->id, 'user_id' => $pembina->id, 'mulai' => now()->toDateString()]);

    $senior = akunPg('siswa', 'Senior Uji');
    $periode->seniors()->attach($senior->id);

    $siswa = akunPg('siswa', 'Calon Uji');
    $casis = CasisPendaftaran::create(['osis_periode_id' => $periode->id, 'user_id' => $siswa->id, 'motivasi' => str_repeat('Ingin ikut. ', 6), 'status' => 'diajukan']);

    return ['ekskul' => $ekskul, 'periode' => $periode, 'admin' => akunPg('admin', 'Admin Uji'), 'pembina' => $pembina, 'senior' => $senior, 'siswa' => $siswa, 'casis' => $casis];
}

function tahapPg(OsisPeriode $periode, int $urutan, string $nama, $mulai = null): SeleksiTahap
{
    return SeleksiTahap::create([
        'osis_periode_id' => $periode->id, 'urutan' => $urutan, 'nama' => $nama,
        'mulai_pada' => $mulai, 'selesai_pada' => $mulai ? $mulai->copy()->addHour() : null, 'tempat' => $mulai ? 'Aula' : null,
    ]);
}

function pengingatPg(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? '')->filter(fn ($j) => str_starts_with($j, 'Pengingat seleksi'))->values()->all();
}

function jalankanPg(): void
{
    Artisan::call('seleksi:ingatkan');
}

test('tahap yang mulai dalam 24 jam diingatkan ke pendaftar dan semua penilai, sekali saja', function () {
    $d = skenarioPg();
    tahapPg($d['periode'], 1, 'Tes tulis', now()->addHours(20));

    jalankanPg();

    foreach (['siswa', 'admin', 'pembina', 'senior'] as $peran) {
        expect(pengingatPg($d[$peran]))->toBe(['Pengingat seleksi: Tes tulis']);
    }

    $pesan = $d['siswa']->notifications()->first()->data['pesan'];
    expect($pesan)->toContain('Dalam 24 jam ke depan')->toContain('Paskibra Garuda')->toContain('Aula');
    expect($d['admin']->notifications()->first()->data['pesan'])->toContain('untuk 1 pendaftar');

    // dijalankan lagi: tidak ada pengingat ganda
    jalankanPg();
    jalankanPg();

    expect(pengingatPg($d['siswa']))->toHaveCount(1)->and(pengingatPg($d['admin']))->toHaveCount(1);
});

test('tahap yang masih lebih dari 24 jam lagi belum diingatkan, lalu diingatkan setelah masuk jendela', function () {
    $d = skenarioPg();
    tahapPg($d['periode'], 1, 'Wawancara', now()->addHours(30));

    jalankanPg();
    expect(pengingatPg($d['siswa']))->toBe([]);

    $this->travel(7)->hours();
    jalankanPg();

    expect(pengingatPg($d['siswa']))->toBe(['Pengingat seleksi: Wawancara']);
});

test('tahap yang sudah dekat (kurang dari 1 jam) hanya mendapat satu pengingat, bukan dua', function () {
    $d = skenarioPg();
    $tahap = tahapPg($d['periode'], 1, 'Praktik', now()->addMinutes(40));

    jalankanPg();
    jalankanPg();

    expect(pengingatPg($d['siswa']))->toHaveCount(1)
        ->and($d['siswa']->notifications()->first()->data['pesan'])->toContain('Kurang dari 1 jam lagi')
        ->and(SeleksiPengingat::where('seleksi_tahap_id', $tahap->id)->pluck('jenis')->sort()->values()->all())->toBe(['1j', '24j']);
});

test('pengingat 24 jam lalu 1 jam: dua pengingat berbeda untuk tahap yang sama', function () {
    $d = skenarioPg();
    tahapPg($d['periode'], 1, 'Tes tulis', now()->addHours(20));

    jalankanPg();
    $this->travel(19)->hours();
    $this->travel(30)->minutes();
    jalankanPg();

    $pesan = $d['siswa']->notifications()->get()->pluck('data.pesan')->all();

    expect(pengingatPg($d['siswa']))->toHaveCount(2)
        ->and(collect($pesan)->contains(fn ($p) => str_contains($p, 'Dalam 24 jam ke depan')))->toBeTrue()
        ->and(collect($pesan)->contains(fn ($p) => str_contains($p, 'Kurang dari 1 jam lagi')))->toBeTrue();
});

test('hanya pendaftar yang sedang berada di tahap itu yang diingatkan', function () {
    $d = skenarioPg();
    $satu = tahapPg($d['periode'], 1, 'Tes tulis');
    tahapPg($d['periode'], 2, 'Wawancara', now()->addHours(10));

    // satu pendaftar lagi: belum lolos tahap 1; pendaftar utama sudah lolos tahap 1 (sekarang di tahap 2)
    $belum = akunPg('siswa', 'Calon Belum Lolos');
    CasisPendaftaran::create(['osis_periode_id' => $d['periode']->id, 'user_id' => $belum->id, 'motivasi' => str_repeat('Ingin ikut. ', 6), 'status' => 'diajukan']);
    $d['casis']->hasils()->create(['seleksi_tahap_id' => $satu->id, 'hasil' => 'lolos']);

    jalankanPg();

    expect(pengingatPg($d['siswa']))->toBe(['Pengingat seleksi: Wawancara'])
        ->and(pengingatPg($belum))->toBe([])
        ->and($d['admin']->notifications()->first()->data['pesan'])->toContain('untuk 1 pendaftar');
});

test('tidak ada pengingat tanpa jadwal, untuk jadwal yang sudah lewat, periode yang sudah berjalan, atau pendaftar yang sudah ditolak', function () {
    $d = skenarioPg();
    tahapPg($d['periode'], 1, 'Tanpa jadwal');
    tahapPg($d['periode'], 2, 'Sudah lewat', now()->subHours(2));

    $berjalan = skenarioPg('berjalan', 'Ekskul Berjalan');
    tahapPg($berjalan['periode'], 1, 'Periode selesai', now()->addHours(5));

    $ditolak = skenarioPg('seleksi', 'Ekskul Ditolak');
    $ditolak['casis']->update(['status' => 'ditolak']);
    tahapPg($ditolak['periode'], 1, 'Pendaftar ditolak', now()->addHours(5));

    jalankanPg();

    foreach ([$d, $berjalan, $ditolak] as $skenario) {
        expect(pengingatPg($skenario['siswa']))->toBe([])->and(pengingatPg($skenario['admin']))->toBe([]);
    }
});

test('tahap tanpa pendaftar tidak ditandai terkirim, jadi tetap diingatkan begitu ada pendaftar', function () {
    $d = skenarioPg();
    $d['casis']->update(['status' => 'draft']);
    $tahap = tahapPg($d['periode'], 1, 'Tes tulis', now()->addHours(10));

    jalankanPg();
    expect(SeleksiPengingat::count())->toBe(0);

    $d['casis']->update(['status' => 'diajukan']);
    jalankanPg();

    expect(pengingatPg($d['siswa']))->toHaveCount(1)->and(SeleksiPengingat::where('seleksi_tahap_id', $tahap->id)->pluck('jenis')->all())->toBe(['24j']);
});

test('mengubah jadwal tahap menghapus catatan pengingat, jadi jadwal baru diingatkan lagi', function () {
    $d = skenarioPg();
    $tahap = tahapPg($d['periode'], 1, 'Tes tulis', now()->addHours(20));

    jalankanPg();
    expect(pengingatPg($d['siswa']))->toHaveCount(1);

    $this->actingAs($d['admin'])->patch(route('tahap.update', $tahap->id), [
        'nama' => 'Tes tulis',
        'mulai_pada' => now()->addHours(10)->format('Y-m-d H:i'),
        'selesai_pada' => now()->addHours(11)->format('Y-m-d H:i'),
        'tempat' => 'Lab Komputer',
    ])->assertSessionHas('success');

    expect(SeleksiPengingat::count())->toBe(0);

    jalankanPg();

    expect(pengingatPg($d['siswa']))->toHaveCount(2)
        ->and($d['siswa']->notifications()->get()->pluck('data.pesan')->contains(fn ($p) => str_contains($p, 'Lab Komputer')))->toBeTrue();
});

test('perintah seleksi:ingatkan terdaftar di penjadwal', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())->toContain('seleksi:ingatkan');
});
