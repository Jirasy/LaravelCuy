<?php

use App\Models\CasisPendaftaran;
use App\Models\CasisReview;
use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\SeleksiTahap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunHi(string $role, string $nama): User
{
    return User::factory()->create(['role' => $role, 'name' => $nama, 'is_active' => true]);
}

function organisasiHi(string $nama, ?Ekstrakurikuler $induk = null): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama, 'pembina' => 'Pak Uji', 'status' => 'aktif',
        'jenis_ekskul_id' => JenisEkskul::where('kode', 'organisasi')->value('id'),
        'induk_id' => $induk?->id,
    ]);
}

function pembinaHi(Ekstrakurikuler $ekskul, string $nama): User
{
    $pembina = akunHi('pembina', $nama);
    EkskulPembina::create(['ekstrakurikuler_id' => $ekskul->id, 'user_id' => $pembina->id, 'mulai' => now()->toDateString()]);

    return $pembina;
}

function anggotaHi(Ekstrakurikuler $ekskul, User $siswa): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id, 'ekstrakurikuler_id' => $ekskul->id, 'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa', 'status' => 'diterima', 'status_anggota' => 'aktif', 'tahun_mulai' => 2026,
    ]);
}

function judulHi(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

// MPK di atas OSIS: pembina MPK, pembina OSIS, satu pembina organisasi lain, admin, anggota MPK, dan satu pendaftar OSIS
function duniaHi(string $statusPeriode = 'seleksi'): array
{
    $mpk = organisasiHi('MPK');
    $osis = organisasiHi('OSIS', $mpk);
    $periode = OsisPeriode::create(['ekstrakurikuler_id' => $osis->id, 'nama' => '2026/2027', 'status' => $statusPeriode]);
    $calon = akunHi('siswa', 'Calon CaSis');
    $casis = CasisPendaftaran::create(['osis_periode_id' => $periode->id, 'user_id' => $calon->id, 'motivasi' => str_repeat('Ingin ikut. ', 6), 'status' => 'diajukan']);
    $anggotaMpk = akunHi('siswa', 'Anggota MPK Uji');
    anggotaHi($mpk, $anggotaMpk);

    return [
        'mpk' => $mpk, 'osis' => $osis, 'periode' => $periode, 'calon' => $calon, 'casis' => $casis, 'anggotaMpk' => $anggotaMpk,
        'admin' => akunHi('admin', 'Admin Hi'),
        'pembinaMpk' => pembinaHi($mpk, 'Pembina MPK'),
        'pembinaOsis' => pembinaHi($osis, 'Pembina OSIS'),
        'pembinaLain' => pembinaHi(organisasiHi('Organisasi Lain'), 'Pembina Lain'),
    ];
}

// ---------------------------------------------------------------------------
// Rantai induk
// ---------------------------------------------------------------------------

test('idLeluhur menelusuri induk, induk dari induk, dan aman dari lingkaran', function () {
    $a = organisasiHi('Dewan Tertinggi');
    $b = organisasiHi('MPK', $a);
    $c = organisasiHi('OSIS', $b);

    expect($c->idLeluhur())->toBe([$b->id, $a->id])
        ->and($b->idLeluhur())->toBe([$a->id])
        ->and($a->idLeluhur())->toBe([]);

    // data rusak (lingkaran lewat query langsung) tidak membuat perulangan tanpa henti
    Ekstrakurikuler::whereKey($a->id)->update(['induk_id' => $c->id]);

    expect($c->fresh()->idLeluhur())->toBe([$b->id, $a->id]);
});

// ---------------------------------------------------------------------------
// Hak pembina induk
// ---------------------------------------------------------------------------

test('pembina MPK bisa membuka dan mengelola seleksi OSIS; pembina organisasi lain dan siswa tidak', function () {
    $d = duniaHi();

    $this->actingAs($d['pembinaMpk'])->get(route('casis.index', $d['osis']->id))->assertOk()->assertSee('Calon CaSis')->assertSee('Tambah tahap seleksi');
    $this->actingAs($d['pembinaOsis'])->get(route('casis.index', $d['osis']->id))->assertOk();
    $this->actingAs($d['pembinaLain'])->get(route('casis.index', $d['osis']->id))->assertForbidden();
    $this->actingAs($d['anggotaMpk'])->get(route('casis.index', $d['osis']->id))->assertForbidden();
});

test('hak tidak mengalir ke atas: pembina OSIS tidak bisa mengelola seleksi MPK', function () {
    $d = duniaHi();
    $periodeMpk = OsisPeriode::create(['ekstrakurikuler_id' => $d['mpk']->id, 'nama' => '2026/2027', 'status' => 'rekrutmen']);

    $this->actingAs($d['pembinaOsis'])->get(route('casis.index', $d['mpk']->id))->assertForbidden();
    $this->actingAs($d['pembinaOsis'])->patch(route('periode.status', $periodeMpk->id), ['aksi' => 'maju'])->assertForbidden();
});

test('pembina MPK membuat periode, mengatur senior, dan menggerakkan status periode OSIS', function () {
    $d = duniaHi('rekrutmen');
    $d['periode']->update(['status' => 'selesai']);

    $this->actingAs($d['pembinaMpk'])->post(route('periode.store', $d['osis']->id), ['nama' => '2027/2028'])->assertSessionHas('success');

    $baru = OsisPeriode::where('nama', '2027/2028')->firstOrFail();

    $this->actingAs($d['pembinaMpk'])->post(route('periode.senior.store', $baru->id), ['user_id' => $d['anggotaMpk']->id])->assertSessionHas('success');
    expect($baru->seniors()->pluck('users.id')->all())->toBe([$d['anggotaMpk']->id]);

    $this->actingAs($d['pembinaMpk'])->delete(route('periode.senior.destroy', [$baru->id, $d['anggotaMpk']->id]))->assertSessionHas('success');
    expect($baru->seniors()->count())->toBe(0);

    // pembina organisasi lain tetap ditolak
    $this->actingAs($d['pembinaLain'])->post(route('periode.store', $d['osis']->id), ['nama' => '2028/2029'])->assertForbidden();
});

test('senior boleh berasal dari OSIS sendiri atau MPK, tapi tidak dari luar keduanya', function () {
    $d = duniaHi();
    $luar = akunHi('siswa', 'Siswa Luar');
    $pengurusOsis = akunHi('siswa', 'Pengurus OSIS Uji');
    anggotaHi($d['osis'], $pengurusOsis);

    $url = route('periode.senior.store', $d['periode']->id);

    $this->actingAs($d['admin'])->post($url, ['user_id' => $luar->id])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'Senior harus pengurus OSIS yang masih aktif (atau anggota organisasi di atasnya)'));
    $this->actingAs($d['admin'])->post($url, ['user_id' => $pengurusOsis->id])->assertSessionHas('success');
    $this->actingAs($d['admin'])->post($url, ['user_id' => $d['anggotaMpk']->id])->assertSessionHas('success');

    expect($d['periode']->seniors()->count())->toBe(2);
});

test('organisasi tanpa induk: senior hanya dari organisasi itu, dengan pesan tanpa tambahan', function () {
    $mandiri = organisasiHi('Organisasi Mandiri');
    $periode = OsisPeriode::create(['ekstrakurikuler_id' => $mandiri->id, 'nama' => '2026/2027', 'status' => 'seleksi']);
    $luar = akunHi('siswa', 'Siswa Luar');

    $this->actingAs(akunHi('admin', 'Admin Hi'))->post(route('periode.senior.store', $periode->id), ['user_id' => $luar->id])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'yang masih aktif.') && ! str_contains($m, 'organisasi di atasnya'));
});

test('daftar calon senior memuat anggota MPK dengan keterangan asalnya', function () {
    $d = duniaHi();

    $this->actingAs($d['pembinaMpk'])->get(route('casis.index', $d['osis']->id))->assertOk()
        ->assertSee('Anggota MPK Uji')->assertSee(' - MPK');
});

test('pembina MPK ikut menilai dan memutuskan hasil tahap seleksi OSIS', function () {
    $d = duniaHi();
    SeleksiTahap::create(['osis_periode_id' => $d['periode']->id, 'urutan' => 1, 'nama' => 'Wawancara']);

    $this->actingAs($d['admin'])->post(route('casis.review', $d['casis']->id), ['skor' => 80, 'rekomendasi' => 'lolos'])->assertSessionHas('success');
    $this->actingAs($d['pembinaMpk'])->post(route('casis.review', $d['casis']->id), ['skor' => 85, 'rekomendasi' => 'lolos'])->assertSessionHas('success');
    $this->actingAs($d['pembinaOsis'])->post(route('casis.review', $d['casis']->id), ['skor' => 90, 'rekomendasi' => 'lolos'])->assertSessionHas('success');

    // penilaian pembina MPK tercatat sebagai unsur pembina
    expect(CasisReview::where('reviewer_id', $d['pembinaMpk']->id)->value('tipe'))->toBe('pembina');

    $this->actingAs($d['pembinaMpk'])->patch(route('casis.putuskan', $d['casis']->id), ['keputusan' => 'lolos'])->assertSessionHas('success');

    expect($d['casis']->fresh()->status)->toBe('diterima')
        ->and(PendaftaranEkskul::where('user_id', $d['calon']->id)->where('ekstrakurikuler_id', $d['osis']->id)->exists())->toBeTrue();
});

test('senior dari MPK bisa membuka dan menilai pendaftar OSIS', function () {
    $d = duniaHi();
    SeleksiTahap::create(['osis_periode_id' => $d['periode']->id, 'urutan' => 1, 'nama' => 'Wawancara']);

    $this->actingAs($d['pembinaMpk'])->post(route('periode.senior.store', $d['periode']->id), ['user_id' => $d['anggotaMpk']->id])->assertSessionHas('success');

    $this->actingAs($d['anggotaMpk'])->get(route('casis.show', $d['casis']->id))->assertOk()->assertSee('Calon CaSis');
    $this->actingAs($d['anggotaMpk'])->post(route('casis.review', $d['casis']->id), ['skor' => 70, 'rekomendasi' => 'lolos'])->assertSessionHas('success');

    expect(CasisReview::where('reviewer_id', $d['anggotaMpk']->id)->value('tipe'))->toBe('senior');
});

// ---------------------------------------------------------------------------
// Kabar & pengingat
// ---------------------------------------------------------------------------

test('pembina MPK diberi tahu saat ada pendaftar baru di OSIS dan saat siswa mundur', function () {
    $d = duniaHi('rekrutmen');
    $siswa = akunHi('siswa', 'Pendaftar Baru');

    $this->actingAs($siswa)->post(route('casis.simpan', $d['osis']->id), ['aksi' => 'ajukan', 'motivasi' => str_repeat('Ingin berorganisasi dengan baik. ', 2)])
        ->assertSessionHas('success');

    expect(judulHi($d['pembinaMpk']))->toContain('Pendaftar CaSis baru')
        ->and(judulHi($d['pembinaOsis']))->toContain('Pendaftar CaSis baru')
        ->and(judulHi($d['pembinaLain']))->not->toContain('Pendaftar CaSis baru');

    $this->actingAs($siswa)->post(route('casis.mundur', $d['osis']->id))->assertSessionHas('success');

    expect(judulHi($d['pembinaMpk']))->toContain('CaSis mengundurkan diri');
});

test('pengingat jadwal seleksi OSIS juga sampai ke pembina MPK', function () {
    $d = duniaHi();
    SeleksiTahap::create([
        'osis_periode_id' => $d['periode']->id, 'urutan' => 1, 'nama' => 'Wawancara',
        'mulai_pada' => now()->addHours(10), 'selesai_pada' => now()->addHours(11), 'tempat' => 'Aula',
    ]);

    Artisan::call('seleksi:ingatkan');

    expect(judulHi($d['pembinaMpk']))->toContain('Pengingat seleksi: Wawancara')
        ->and(judulHi($d['pembinaOsis']))->toContain('Pengingat seleksi: Wawancara')
        ->and(judulHi($d['pembinaLain']))->not->toContain('Pengingat seleksi: Wawancara');
});

// ---------------------------------------------------------------------------
// Kelola User & tampilan
// ---------------------------------------------------------------------------

test('pembina MPK memakai alat Kelola User untuk OSIS dan MPK', function () {
    $d = duniaHi('rekrutmen');
    $siswa = akunHi('siswa', 'Siswa Alat');

    $this->actingAs($d['pembinaMpk'])->get(route('users.index'))->assertOk()
        ->assertSee('Catat pengurus OSIS')->assertSee('Catat pengurus MPK');

    $this->actingAs($d['pembinaMpk'])->post(route('casis.pengurus', $siswa->id), [
        'ekskul_id' => $d['osis']->id, 'angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'tahun_selesai' => '', 'status' => 'aktif',
    ])->assertSessionHas('success');

    expect(PendaftaranEkskul::where('user_id', $siswa->id)->value('ekstrakurikuler_id'))->toBe($d['osis']->id);

    // pembina organisasi lain tidak punya alat itu
    $this->actingAs($d['pembinaLain'])->get(route('users.index'))->assertOk()->assertDontSee('Catat pengurus OSIS')->assertDontSee('Catat pengurus MPK');
    $this->actingAs($d['pembinaLain'])->post(route('casis.pengurus', akunHi('siswa', 'Siswa Lain')->id), [
        'ekskul_id' => $d['osis']->id, 'angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'status' => 'aktif',
    ])->assertForbidden();
});

test('halaman OSIS menjelaskan peran organisasi induknya dan tombol kelola seleksi muncul untuk pembina MPK', function () {
    $d = duniaHi();

    $this->actingAs($d['pembinaMpk'])->get(route('ekstrakurikuler.show', $d['osis']->id))->assertOk()
        ->assertSee('Di bawah MPK')->assertSee('ikut mengelola dan menilai seleksi di sini')->assertSee('Kelola seleksi');

    // di halaman MPK sendiri tidak ada catatan itu (MPK tidak punya induk)
    $this->actingAs($d['pembinaMpk'])->get(route('ekstrakurikuler.show', $d['mpk']->id))->assertOk()->assertDontSee('ikut mengelola dan menilai seleksi di sini');
});
