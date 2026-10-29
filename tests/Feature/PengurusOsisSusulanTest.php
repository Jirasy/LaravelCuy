<?php

use App\Models\CasisPendaftaran;
use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Services\OsisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function osisOs(): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => 'OSIS',
        'pembina' => 'Bu Sari',
        'status' => 'aktif',
        'jenis' => 'osis',
    ]);
}

function akunOs(string $role = 'siswa', ?string $nama = null, bool $aktif = true): User
{
    return User::factory()->create(array_filter([
        'name' => $nama,
        'role' => $role,
        'is_active' => $aktif,
    ], fn ($v) => $v !== null));
}

function pembinaDiOs(Ekstrakurikuler $ekskul): User
{
    $pembina = akunOs('pembina');
    EkskulPembina::create(['ekstrakurikuler_id' => $ekskul->id, 'user_id' => $pembina->id, 'mulai' => now()->toDateString()]);

    return $pembina;
}

function dataPengurusOs(array $tambahan = []): array
{
    return array_merge(['angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'status' => 'aktif'], $tambahan);
}

function barisOsisOs(User $siswa, Ekstrakurikuler $osis)
{
    return PendaftaranEkskul::where('user_id', $siswa->id)->where('ekstrakurikuler_id', $osis->id)->orderBy('id')->get();
}

function judulNotifOs(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

// ---------------------------------------------------------------------------
// Catat susulan (tanpa CaSis)
// ---------------------------------------------------------------------------

test('admin mencatat pengurus angkatan lalu langsung, tanpa membuat data CaSis', function () {
    $osis = osisOs();
    $admin = akunOs('admin');
    $siswa = akunOs('siswa', 'Kakak Senior');

    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs(['catatan' => 'Lupa diinput tahun lalu']))
        ->assertSessionHas('success');

    $baris = barisOsisOs($siswa, $osis)->firstOrFail();

    expect($baris->status)->toBe('diterima')
        ->and($baris->status_anggota)->toBe('aktif')
        ->and($baris->angkatan_ekskul)->toBe('2024/2025')
        ->and($baris->tahun_mulai)->toBe(2024)
        ->and($baris->dibuat_susulan_oleh)->toBe($admin->id)
        ->and($baris->alasan_mendaftar)->toContain('Lupa diinput tahun lalu')
        ->and(CasisPendaftaran::count())->toBe(0)
        ->and(app(OsisService::class)->pengurusAktif($siswa))->toBeTrue()
        ->and(judulNotifOs($siswa))->toContain('Kamu menjadi pengurus OSIS');
});

test('pengurus yang sudah purna tugas tercatat sebagai riwayat, bukan anggota aktif', function () {
    $osis = osisOs();
    $siswa = akunOs('siswa', 'Alumni Pengurus');

    $this->actingAs(akunOs('admin'))->post(route('casis.pengurus', $siswa), dataPengurusOs([
        'angkatan' => '2023/2024', 'tahun_mulai' => 2023, 'tahun_selesai' => 2024, 'status' => 'selesai',
    ]))->assertSessionHas('success');

    $baris = barisOsisOs($siswa, $osis)->firstOrFail();

    expect($baris->status_anggota)->toBe('selesai')
        ->and($baris->tahun_selesai)->toBe(2024)
        ->and(app(OsisService::class)->pengurusAktif($siswa))->toBeFalse()
        ->and(PendaftaranEkskul::anggotaAktif()->count())->toBe(0)
        // riwayat purna tugas tidak menghalangi pendaftaran berikutnya
        ->and(PendaftaranEkskul::penghalangDaftar($siswa->id, $osis->id))->toBeNull()
        ->and(PendaftaranEkskul::idEkskulTerhalang($siswa->id)->all())->toBe([])
        ->and(judulNotifOs($siswa))->toContain('Riwayat kepengurusan OSIS dicatat');
});

test('satu siswa boleh punya beberapa angkatan: yang aktif plus riwayat yang sudah selesai', function () {
    $osis = osisOs();
    $siswa = akunOs();
    $admin = akunOs('admin');

    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs(['angkatan' => '2025/2026', 'tahun_mulai' => 2025]))->assertSessionHas('success');
    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs([
        'angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'tahun_selesai' => 2025, 'status' => 'selesai',
    ]))->assertSessionHas('success');

    expect(barisOsisOs($siswa, $osis)->pluck('status_anggota')->sort()->values()->all())->toBe(['aktif', 'selesai']);
});

test('isian kepengurusan yang tidak valid ditolak dan tidak menyimpan apa pun', function (array $isian) {
    $osis = osisOs();
    $siswa = akunOs();

    $this->actingAs(akunOs('admin'))->post(route('casis.pengurus', $siswa), dataPengurusOs($isian))
        ->assertSessionHas('error');

    expect(PendaftaranEkskul::count())->toBe(0);
})->with([
    'angkatan kosong' => [['angkatan' => '']],
    'angkatan terlalu panjang' => [['angkatan' => str_repeat('2', 21)]],
    'tahun mulai kosong' => [['tahun_mulai' => '']],
    'tahun mulai bukan angka' => [['tahun_mulai' => 'dua ribu']],
    'tahun mulai jauh di masa depan' => [['tahun_mulai' => 2999]],
    'tahun selesai sebelum mulai' => [['tahun_mulai' => 2025, 'tahun_selesai' => 2024]],
    'purna tugas tanpa tahun selesai' => [['status' => 'selesai']],
    'status tidak dikenal' => [['status' => 'keluar']],
    'catatan terlalu panjang' => [['catatan' => str_repeat('x', 201)]],
]);

test('angkatan yang sama tidak bisa dicatat dua kali, dan tidak boleh ada dua yang masih menjabat', function () {
    $osis = osisOs();
    $siswa = akunOs();
    $admin = akunOs('admin');

    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs())->assertSessionHas('success');

    // angkatan sama
    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs(['status' => 'selesai', 'tahun_selesai' => 2025]))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah tercatat di angkatan 2024/2025'));

    // angkatan lain tapi masih menjabat juga
    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs(['angkatan' => '2025/2026']))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'masih menjabat'));

    expect(barisOsisOs($siswa, $osis))->toHaveCount(1);
});

test('mencatat susulan menyelesaikan CaSis yang berjalan hanya bila statusnya masih menjabat', function () {
    $osis = osisOs();
    $periode = OsisPeriode::create(['ekstrakurikuler_id' => $osis->id, 'nama' => '2026/2027', 'status' => 'rekrutmen']);
    $admin = akunOs('admin');

    $aktif = akunOs('siswa', 'Masih Menjabat');
    $purna = akunOs('siswa', 'Sudah Purna');
    CasisPendaftaran::create(['osis_periode_id' => $periode->id, 'user_id' => $aktif->id, 'status' => 'draft']);
    CasisPendaftaran::create(['osis_periode_id' => $periode->id, 'user_id' => $purna->id, 'status' => 'draft']);

    $this->actingAs($admin)->post(route('casis.pengurus', $aktif), dataPengurusOs());
    $this->actingAs($admin)->post(route('casis.pengurus', $purna), dataPengurusOs(['status' => 'selesai', 'tahun_selesai' => 2025]));

    expect(CasisPendaftaran::where('user_id', $aktif->id)->value('status'))->toBe('diterima')
        ->and(CasisPendaftaran::where('user_id', $purna->id)->value('status'))->toBe('draft');
});

test('hanya admin atau pembina OSIS yang bisa mencatat, dan hanya untuk siswa aktif', function () {
    $osis = osisOs();
    $lain = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $siswa = akunOs();

    $this->actingAs(akunOs('siswa'))->post(route('casis.pengurus', $siswa), dataPengurusOs())->assertForbidden();
    $this->actingAs(pembinaDiOs($lain))->post(route('casis.pengurus', $siswa), dataPengurusOs())->assertForbidden();
    expect(PendaftaranEkskul::count())->toBe(0);

    // pembina OSIS boleh
    $this->actingAs(pembinaDiOs($osis))->post(route('casis.pengurus', $siswa), dataPengurusOs())->assertSessionHas('success');

    // target bukan siswa / akun nonaktif ditolak
    $admin = akunOs('admin');
    $this->actingAs($admin)->post(route('casis.pengurus', akunOs('pembina')), dataPengurusOs())->assertSessionHas('error');
    $this->actingAs($admin)->post(route('casis.pengurus', akunOs('siswa', null, false)), dataPengurusOs())->assertSessionHas('error');
    expect(PendaftaranEkskul::count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Koreksi data kepengurusan
// ---------------------------------------------------------------------------

test('admin mengoreksi angkatan, tahun, dan status pengurus yang sudah tercatat', function () {
    $osis = osisOs();
    $siswa = akunOs();
    $admin = akunOs('admin');

    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs(['angkatan' => '2023/2024', 'tahun_mulai' => 2023]));
    $baris = barisOsisOs($siswa, $osis)->firstOrFail();

    $this->actingAs($admin)->patch(route('casis.pengurus.koreksi', $baris), [
        'angkatan' => '2022/2023', 'tahun_mulai' => 2022, 'tahun_selesai' => 2023, 'status' => 'selesai',
    ])->assertSessionHas('success');

    $baris->refresh();

    expect($baris->angkatan_ekskul)->toBe('2022/2023')
        ->and($baris->tahun_mulai)->toBe(2022)
        ->and($baris->tahun_selesai)->toBe(2023)
        ->and($baris->status_anggota)->toBe('selesai')
        ->and(app(OsisService::class)->pengurusAktif($siswa))->toBeFalse()
        ->and(judulNotifOs($siswa))->toContain('Data kepengurusan OSIS dikoreksi');
});

test('koreksi tidak boleh membuat angkatan kembar atau dua yang masih menjabat', function () {
    $osis = osisOs();
    $siswa = akunOs();
    $admin = akunOs('admin');

    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs(['angkatan' => '2025/2026', 'tahun_mulai' => 2025]));
    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs([
        'angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'tahun_selesai' => 2025, 'status' => 'selesai',
    ]));
    [$aktif, $lama] = [barisOsisOs($siswa, $osis)->firstWhere('status_anggota', 'aktif'), barisOsisOs($siswa, $osis)->firstWhere('status_anggota', 'selesai')];

    // riwayat lama dipaksa jadi angkatan yang sama dengan yang aktif
    $this->actingAs($admin)->patch(route('casis.pengurus.koreksi', $lama), ['angkatan' => '2025/2026', 'tahun_mulai' => 2024, 'tahun_selesai' => 2025, 'status' => 'selesai'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah tercatat di angkatan'));

    // riwayat lama dipaksa jadi aktif padahal sudah ada yang aktif
    $this->actingAs($admin)->patch(route('casis.pengurus.koreksi', $lama), ['angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'status' => 'aktif'])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'masih menjabat'));

    // mengoreksi dirinya sendiri tanpa mengubah angkatan tetap boleh
    $this->actingAs($admin)->patch(route('casis.pengurus.koreksi', $aktif), ['angkatan' => '2025/2026', 'tahun_mulai' => 2026, 'status' => 'aktif'])
        ->assertSessionHas('success');

    expect($lama->fresh()->status_anggota)->toBe('selesai')
        ->and($aktif->fresh()->tahun_mulai)->toBe(2026);
});

test('koreksi hanya untuk pengurus OSIS yang diterima, dan hanya oleh yang berhak', function () {
    $osis = osisOs();
    $futsal = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $siswa = akunOs();
    $admin = akunOs('admin');
    $isian = ['angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'status' => 'aktif'];

    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs());
    $baris = barisOsisOs($siswa, $osis)->firstOrFail();

    $this->actingAs(akunOs('siswa'))->patch(route('casis.pengurus.koreksi', $baris), $isian)->assertForbidden();
    $this->actingAs(pembinaDiOs($futsal))->patch(route('casis.pengurus.koreksi', $baris), $isian)->assertForbidden();

    // baris ekskul lain dan baris yang sudah keluar tidak bisa dikoreksi lewat jalur OSIS
    $barisFutsal = PendaftaranEkskul::create(['user_id' => $siswa->id, 'ekstrakurikuler_id' => $futsal->id, 'nama_pendaftar' => $siswa->name, 'peran' => 'siswa', 'status' => 'diterima', 'status_anggota' => 'aktif']);
    $barisKeluar = PendaftaranEkskul::create(['user_id' => akunOs()->id, 'ekstrakurikuler_id' => $osis->id, 'nama_pendaftar' => 'Mantan', 'peran' => 'siswa', 'status' => 'diterima', 'status_anggota' => 'keluar']);

    $this->actingAs($admin)->patch(route('casis.pengurus.koreksi', $barisFutsal), $isian)->assertNotFound();
    $this->actingAs($admin)->patch(route('casis.pengurus.koreksi', $barisKeluar), $isian)->assertNotFound();
});

// ---------------------------------------------------------------------------
// Dampak ke bagian lain
// ---------------------------------------------------------------------------

test('senior yang baru diinput (angkatan lalu, masih menjabat) bisa ditunjuk sebagai penilai CaSis', function () {
    $osis = osisOs();
    $admin = akunOs('admin');
    $senior = akunOs('siswa', 'Senior Telat Input');
    $periode = OsisPeriode::create(['ekstrakurikuler_id' => $osis->id, 'nama' => '2026/2027', 'status' => 'rekrutmen']);

    $this->actingAs($admin)->post(route('periode.senior.store', $periode), ['user_id' => $senior->id])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'pengurus OSIS yang masih aktif'));

    $this->actingAs($admin)->post(route('casis.pengurus', $senior), dataPengurusOs(['angkatan' => '2025/2026', 'tahun_mulai' => 2025]));

    $this->actingAs($admin)->post(route('periode.senior.store', $periode), ['user_id' => $senior->id])
        ->assertSessionHas('success');

    expect($periode->seniors()->pluck('users.id')->all())->toBe([$senior->id]);
});

test('baris riwayat yang dibuat belakangan tidak mengalahkan baris aktif yang lebih lama', function () {
    $osis = osisOs();
    $siswa = akunOs();
    $admin = akunOs('admin');

    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs(['angkatan' => '2025/2026', 'tahun_mulai' => 2025]));
    $aktif = barisOsisOs($siswa, $osis)->firstOrFail();

    // riwayat susulan dibuat BELAKANGAN (id dan created_at lebih baru)
    $this->travel(1)->hours();
    $this->actingAs($admin)->post(route('casis.pengurus', $siswa), dataPengurusOs([
        'angkatan' => '2024/2025', 'tahun_mulai' => 2024, 'tahun_selesai' => 2025, 'status' => 'selesai',
    ]));

    expect(PendaftaranEkskul::where('user_id', $siswa->id)->relevanDulu()->first()->id)->toBe($aktif->id);

    // halaman ekskul memakai baris yang aktif: siswa tetap dianggap anggota (tidak diberi label "sudah selesai")
    $this->actingAs($siswa)->get(route('ekstrakurikuler.show', $osis->id))
        ->assertOk()
        ->assertDontSee('sudah selesai');
});

test('siswa yang seluruh kepengurusannya sudah selesai melihat keterangan masa jabat selesai', function () {
    $osis = osisOs();
    $siswa = akunOs();

    $this->actingAs(akunOs('admin'))->post(route('casis.pengurus', $siswa), dataPengurusOs([
        'angkatan' => '2023/2024', 'tahun_mulai' => 2023, 'tahun_selesai' => 2024, 'status' => 'selesai',
    ]));

    $this->actingAs($siswa)->get(route('ekstrakurikuler.show', $osis->id))
        ->assertOk()
        ->assertSee('Masa kepengurusanmu')
        ->assertSee('2023/2024');
});

test('halaman riwayat menampilkan Purna tugas beserta angkatannya', function () {
    $osis = osisOs();
    $siswa = akunOs('siswa', 'Alumni Pengurus');

    $this->actingAs(akunOs('admin'))->post(route('casis.pengurus', $siswa), dataPengurusOs([
        'angkatan' => '2023/2024', 'tahun_mulai' => 2023, 'tahun_selesai' => 2024, 'status' => 'selesai',
    ]));

    $this->actingAs(akunOs('admin'))->get(route('riwayat.show', $siswa))
        ->assertOk()
        ->assertSee('Purna tugas')
        ->assertSee('2023/2024');
});

test('Kelola User menampilkan form kepengurusan OSIS hanya untuk yang berhak', function () {
    $osis = osisOs();
    $futsal = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'Pak Budi', 'status' => 'aktif']);
    $siswa = akunOs('siswa', 'Siswa Contoh');

    $this->actingAs(akunOs('admin'))->post(route('casis.pengurus', $siswa), dataPengurusOs());

    $this->actingAs(akunOs('admin'))->get(route('users.index'))
        ->assertOk()
        ->assertSee('Kepengurusan OSIS')
        ->assertSee('Catat pengurus OSIS')
        ->assertSee('2024/2025')                       // koreksi + saran angkatan
        ->assertSee('id="angkatan-osis"', false);

    $this->actingAs(pembinaDiOs($futsal))->get(route('users.index'))
        ->assertOk()
        ->assertDontSee('Kepengurusan OSIS');
});
