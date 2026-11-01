<?php

use App\Models\CasisPendaftaran;
use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunJl(string $role, string $nama): User
{
    return User::factory()->create(['role' => $role, 'name' => $nama, 'is_active' => true]);
}

function ekskulJl(string $nama, string $tipe): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama, 'pembina' => 'Pak Uji', 'status' => 'aktif',
        'jenis_ekskul_id' => JenisEkskul::where('kode', $tipe)->value('id'),
    ]);
}

function periodeJl(Ekstrakurikuler $ekskul, string $status = 'rekrutmen'): OsisPeriode
{
    return OsisPeriode::create(['ekstrakurikuler_id' => $ekskul->id, 'nama' => '2026/2027', 'status' => $status]);
}

function judulJl(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

function isianJl(array $tambahan = []): array
{
    return array_merge(['angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'tahun_selesai' => '', 'status' => 'aktif'], $tambahan);
}

// OSIS (organisasi, jadi organisasi utama) + Paskibra (ekskul seleksi), masing-masing dengan periode rekrutmen terbuka
function duaJalurJl(): array
{
    $osis = ekskulJl('OSIS', 'organisasi');
    $paskibra = ekskulJl('Paskibra', 'seleksi');

    return ['osis' => $osis, 'paskibra' => $paskibra, 'periodeOsis' => periodeJl($osis), 'periodePaskibra' => periodeJl($paskibra), 'admin' => akunJl('admin', 'Admin Jalur'), 'siswa' => akunJl('siswa', 'Siswa Jalur')];
}

test('Kelola User menampilkan alat semua ekskul berseleksi sekaligus, tanpa pindah saringan', function () {
    $d = duaJalurJl();

    $this->actingAs($d['admin'])->get(route('users.index'))->assertOk()
        ->assertSee('Masukkan ke jalur CaSis OSIS')->assertSee('Kepengurusan OSIS')->assertSee('Catat pengurus OSIS')
        ->assertSee('Masukkan ke jalur Calon Anggota Paskibra')->assertSee('Keanggotaan Paskibra')->assertSee('Catat anggota Paskibra')
        ->assertSee('name="ekskul_id" value="'.$d['osis']->id.'"', false)
        ->assertSee('name="ekskul_id" value="'.$d['paskibra']->id.'"', false);
});

test('lencana di kolom nama menunjukkan keanggotaan dan seleksi di tiap ekskul berseleksi', function () {
    $d = duaJalurJl();

    PendaftaranEkskul::create([
        'user_id' => $d['siswa']->id, 'ekstrakurikuler_id' => $d['osis']->id, 'nama_pendaftar' => 'Siswa Jalur',
        'peran' => 'siswa', 'status' => 'diterima', 'status_anggota' => 'aktif', 'tahun_mulai' => 2026,
    ]);
    CasisPendaftaran::create(['osis_periode_id' => $d['periodePaskibra']->id, 'user_id' => $d['siswa']->id, 'status' => 'diajukan']);

    $this->actingAs($d['admin'])->get(route('users.index'))->assertOk()
        ->assertSee('Pengurus OSIS')->assertSee('Calon Anggota Paskibra: Diajukan');
});

test('pembina hanya melihat alat untuk ekskul berseleksi yang boleh dikelolanya', function () {
    $d = duaJalurJl();
    $pembinaOsis = akunJl('pembina', 'Pembina OSIS');
    EkskulPembina::create(['ekstrakurikuler_id' => $d['osis']->id, 'user_id' => $pembinaOsis->id, 'mulai' => now()->toDateString()]);

    $this->actingAs($pembinaOsis)->get(route('users.index'))->assertOk()
        ->assertSee('Catat pengurus OSIS')
        ->assertDontSee('Catat anggota Paskibra')
        ->assertDontSee('Masukkan ke jalur Calon Anggota Paskibra');
});

test('tanpa organisasi, alat tetap tersedia untuk ekskul seleksi', function () {
    $paskibra = ekskulJl('Paskibra', 'seleksi');
    periodeJl($paskibra);
    akunJl('siswa', 'Siswa Jalur');   // tombolnya muncul di baris siswa

    $this->actingAs(akunJl('admin', 'Admin Jalur'))->get(route('users.index'))->assertOk()->assertSee('Catat anggota Paskibra');
});

test('memasukkan siswa ke jalur seleksi ekskul pilihan', function () {
    $d = duaJalurJl();

    $this->actingAs($d['admin'])->post(route('casis.masukkan', $d['siswa']->id), ['ekskul_id' => $d['paskibra']->id])
        ->assertSessionHas('success');

    $casis = CasisPendaftaran::firstOrFail();

    expect($casis->osis_periode_id)->toBe($d['periodePaskibra']->id)
        ->and($casis->status)->toBe('draft')
        ->and($casis->didaftarkan_oleh)->toBe($d['admin']->id)
        ->and(judulJl($d['siswa']))->toContain('Kamu didaftarkan ke jalur Paskibra');
});

test('tanpa ekskul_id, siswa dimasukkan ke jalur organisasi utama', function () {
    $d = duaJalurJl();

    $this->actingAs($d['admin'])->post(route('casis.masukkan', $d['siswa']->id))->assertSessionHas('success');

    expect(CasisPendaftaran::firstOrFail()->osis_periode_id)->toBe($d['periodeOsis']->id)
        ->and(judulJl($d['siswa']))->toContain('Kamu didaftarkan ke jalur OSIS');
});

test('memasukkan ke jalur ditolak kalau periodenya belum dibuka atau siswa sudah anggota', function () {
    $d = duaJalurJl();
    $d['periodePaskibra']->update(['status' => 'berjalan']);

    $this->actingAs($d['admin'])->post(route('casis.masukkan', $d['siswa']->id), ['ekskul_id' => $d['paskibra']->id])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'Belum ada periode pendaftaran Calon Anggota yang dibuka'));

    PendaftaranEkskul::create([
        'user_id' => $d['siswa']->id, 'ekstrakurikuler_id' => $d['osis']->id, 'nama_pendaftar' => 'Siswa Jalur',
        'peran' => 'siswa', 'status' => 'diterima', 'status_anggota' => 'aktif', 'tahun_mulai' => 2026,
    ]);

    $this->actingAs($d['admin'])->post(route('casis.masukkan', $d['siswa']->id), ['ekskul_id' => $d['osis']->id])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah menjadi pengurus OSIS'));

    expect(CasisPendaftaran::count())->toBe(0);
});

test('mencatat anggota ekskul seleksi tanpa seleksi memakai sebutan anggota; organisasi memakai pengurus', function () {
    $d = duaJalurJl();

    $this->actingAs($d['admin'])->post(route('casis.pengurus', $d['siswa']->id), isianJl(['ekskul_id' => $d['paskibra']->id, 'catatan' => 'Lupa diinput']))
        ->assertSessionHas('success', fn ($m) => str_contains($m, 'sekarang anggota Paskibra angkatan 2024/2025'));

    $baris = PendaftaranEkskul::where('user_id', $d['siswa']->id)->firstOrFail();

    expect($baris->ekstrakurikuler_id)->toBe($d['paskibra']->id)
        ->and($baris->status)->toBe('diterima')
        ->and($baris->status_anggota)->toBe('aktif')
        ->and($baris->angkatan_ekskul)->toBe('2024/2025')
        ->and($baris->dibuat_susulan_oleh)->toBe($d['admin']->id)
        ->and(judulJl($d['siswa']))->toContain('Kamu menjadi anggota Paskibra');

    $lain = akunJl('siswa', 'Siswa Organisasi');
    $this->actingAs($d['admin'])->post(route('casis.pengurus', $lain->id), isianJl(['ekskul_id' => $d['osis']->id]))->assertSessionHas('success');

    expect(judulJl($lain))->toContain('Kamu menjadi pengurus OSIS');
});

test('angkatan susulan yang sama atau dua yang masih menjabat ditolak per ekskul, bukan lintas ekskul', function () {
    $d = duaJalurJl();
    $url = route('casis.pengurus', $d['siswa']->id);

    $this->actingAs($d['admin'])->post($url, isianJl(['ekskul_id' => $d['paskibra']->id]))->assertSessionHas('success');

    $this->actingAs($d['admin'])->post($url, isianJl(['ekskul_id' => $d['paskibra']->id]))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah tercatat di angkatan 2024/2025'));
    $this->actingAs($d['admin'])->post($url, isianJl(['ekskul_id' => $d['paskibra']->id, 'angkatan' => '2025/2026', 'tahun_mulai' => 2025]))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'anggota Paskibra yang masih menjabat'));

    // angkatan yang sama di ekskul lain (OSIS) tidak bentrok
    $this->actingAs($d['admin'])->post($url, isianJl(['ekskul_id' => $d['osis']->id]))->assertSessionHas('success');

    expect(PendaftaranEkskul::where('user_id', $d['siswa']->id)->count())->toBe(2);
});

test('mencatat anggota yang masih menjabat menyelesaikan pendaftaran seleksinya di ekskul itu saja', function () {
    $d = duaJalurJl();
    $dipaskibra = CasisPendaftaran::create(['osis_periode_id' => $d['periodePaskibra']->id, 'user_id' => $d['siswa']->id, 'status' => 'draft']);
    $diOsis = CasisPendaftaran::create(['osis_periode_id' => $d['periodeOsis']->id, 'user_id' => $d['siswa']->id, 'status' => 'draft']);

    $this->actingAs($d['admin'])->post(route('casis.pengurus', $d['siswa']->id), isianJl(['ekskul_id' => $d['paskibra']->id]))->assertSessionHas('success');

    expect($dipaskibra->fresh()->status)->toBe('diterima')->and($diOsis->fresh()->status)->toBe('draft');
});

test('koreksi kepengurusan bekerja untuk ekskul seleksi dan memakai sebutan keanggotaan', function () {
    $d = duaJalurJl();
    $baris = PendaftaranEkskul::create([
        'user_id' => $d['siswa']->id, 'ekstrakurikuler_id' => $d['paskibra']->id, 'nama_pendaftar' => 'Siswa Jalur',
        'peran' => 'siswa', 'status' => 'diterima', 'status_anggota' => 'aktif', 'angkatan_ekskul' => '2023/2024', 'tahun_mulai' => 2023,
    ]);

    $this->actingAs($d['admin'])->patch(route('casis.pengurus.koreksi', $baris->id), isianJl(['angkatan' => '2024/2025', 'status' => 'selesai', 'tahun_selesai' => 2025]))
        ->assertSessionHas('success', fn ($m) => str_contains($m, 'keanggotaan Paskibra'));

    expect($baris->fresh()->angkatan_ekskul)->toBe('2024/2025')
        ->and($baris->fresh()->status_anggota)->toBe('selesai')
        ->and(judulJl($d['siswa']))->toContain('Data keanggotaan Paskibra dikoreksi');
});

test('pembina hanya bisa mengelola jalur ekskulnya sendiri', function () {
    $d = duaJalurJl();
    $pembinaOsis = akunJl('pembina', 'Pembina OSIS');
    EkskulPembina::create(['ekstrakurikuler_id' => $d['osis']->id, 'user_id' => $pembinaOsis->id, 'mulai' => now()->toDateString()]);

    $this->actingAs($pembinaOsis)->post(route('casis.masukkan', $d['siswa']->id), ['ekskul_id' => $d['paskibra']->id])->assertForbidden();
    $this->actingAs($pembinaOsis)->post(route('casis.pengurus', $d['siswa']->id), isianJl(['ekskul_id' => $d['paskibra']->id]))->assertForbidden();
    $this->actingAs($pembinaOsis)->post(route('casis.masukkan', $d['siswa']->id), ['ekskul_id' => $d['osis']->id])->assertSessionHas('success');

    $pembinaPaskibra = akunJl('pembina', 'Pembina Paskibra');
    EkskulPembina::create(['ekstrakurikuler_id' => $d['paskibra']->id, 'user_id' => $pembinaPaskibra->id, 'mulai' => now()->toDateString()]);
    $lain = akunJl('siswa', 'Siswa Lain');

    $this->actingAs($pembinaPaskibra)->post(route('casis.pengurus', $lain->id), isianJl(['ekskul_id' => $d['paskibra']->id]))->assertSessionHas('success');

    expect(PendaftaranEkskul::where('user_id', $lain->id)->value('ekstrakurikuler_id'))->toBe($d['paskibra']->id);
});

test('ekskul_id yang bukan ekskul berseleksi ditolak, begitu pula koreksi baris ekskul biasa', function () {
    $d = duaJalurJl();
    $biasa = ekskulJl('Futsal', 'ekskul');

    $this->actingAs($d['admin'])->post(route('casis.masukkan', $d['siswa']->id), ['ekskul_id' => $biasa->id])->assertNotFound();
    $this->actingAs($d['admin'])->post(route('casis.pengurus', $d['siswa']->id), isianJl(['ekskul_id' => $biasa->id]))->assertNotFound();

    $baris = PendaftaranEkskul::create([
        'user_id' => $d['siswa']->id, 'ekstrakurikuler_id' => $biasa->id, 'nama_pendaftar' => 'Siswa Jalur',
        'peran' => 'siswa', 'status' => 'diterima', 'status_anggota' => 'aktif', 'tahun_mulai' => 2025,
    ]);

    $this->actingAs($d['admin'])->patch(route('casis.pengurus.koreksi', $baris->id), isianJl())->assertNotFound();
});
