<?php

use App\Models\Ekstrakurikuler;
use App\Models\JenisEkskul;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunKt(string $role = 'siswa', ?string $nama = null): User
{
    return User::factory()->create(array_filter(['name' => $nama, 'role' => $role, 'is_active' => true], fn ($v) => $v !== null));
}

function tipeKt(string $kode): int
{
    return (int) JenisEkskul::where('kode', $kode)->value('id');
}

function judulNotifKt(User $user): array
{
    return $user->notifications()->get()->map(fn ($n) => $n->data['judul'] ?? null)->all();
}

function komunitasKt(User $pembuat, string $nama = 'Komunitas Fotografi'): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => '-',
        'status' => 'aktif',
        'jenis_ekskul_id' => tipeKt('komunitas'),
        'dibuat_oleh' => $pembuat->id,
    ]);
}

test('siswa membuat komunitas: belum resmi, ia menjadi anggota pertama, admin diberi tahu', function () {
    $siswa = akunKt('siswa', 'Pendiri Muda');
    $admin = akunKt('admin');

    $this->actingAs($siswa)->get(route('komunitas.create'))->assertOk()->assertSee('Buat Komunitas');

    $this->actingAs($siswa)->post(route('komunitas.store'), ['nama_ekstrakurikuler' => 'Komunitas Fotografi', 'deskripsi' => 'Belajar memotret bersama.'])
        ->assertSessionHas('success');

    $ekskul = Ekstrakurikuler::firstOrFail();
    $anggota = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskul->id)->firstOrFail();

    expect($ekskul->isKomunitas())->toBeTrue()
        ->and($ekskul->dibuat_oleh)->toBe($siswa->id)
        ->and($ekskul->diresmikan_pada)->toBeNull()
        ->and($anggota->user_id)->toBe($siswa->id)
        ->and($anggota->status)->toBe('diterima')
        ->and($anggota->status_anggota)->toBe('aktif')
        ->and(judulNotifKt($admin))->toContain('Komunitas baru menunggu peresmian');
});

test('pembina membuat komunitas dan otomatis menjadi pembinanya', function () {
    $pembina = akunKt('pembina');

    $this->actingAs($pembina)->post(route('komunitas.store'), ['nama_ekstrakurikuler' => 'Komunitas Robotik'])->assertSessionHas('success');

    $ekskul = Ekstrakurikuler::firstOrFail();

    expect($ekskul->isKomunitas())->toBeTrue()
        ->and($ekskul->pembinaAktif()->pluck('users.id')->all())->toBe([$pembina->id])
        ->and($ekskul->pembina)->toBe($pembina->name);
});

test('admin tidak membuat komunitas: ekskul buatan admin langsung resmi', function () {
    $admin = akunKt('admin');

    $this->actingAs($admin)->get(route('komunitas.create'))->assertRedirect(route('ekstrakurikuler.create'));
    $this->actingAs($admin)->post(route('komunitas.store'), ['nama_ekstrakurikuler' => 'Komunitas Admin'])->assertForbidden();

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('nama komunitas wajib diisi', function () {
    $this->actingAs(akunKt('siswa'))->post(route('komunitas.store'), ['nama_ekstrakurikuler' => ''])
        ->assertSessionHasErrors('nama_ekstrakurikuler');

    expect(Ekstrakurikuler::count())->toBe(0);
});

test('admin meresmikan komunitas dan memilih tipe akhirnya; pembuat diberi tahu', function () {
    $siswa = akunKt('siswa', 'Pendiri Muda');
    $komunitas = komunitasKt($siswa);
    $admin = akunKt('admin');

    $this->actingAs($admin)->post(route('komunitas.resmikan', $komunitas->id), ['jenis_ekskul_id' => tipeKt('seleksi')])
        ->assertSessionHas('success');

    $komunitas->refresh();

    expect($komunitas->isKomunitas())->toBeFalse()
        ->and($komunitas->jenis_ekskul_id)->toBe(tipeKt('seleksi'))
        ->and($komunitas->memakaiCasis())->toBeTrue()
        ->and($komunitas->diresmikan_pada)->not->toBeNull()
        ->and($komunitas->diresmikan_oleh)->toBe($admin->id)
        ->and(judulNotifKt($siswa))->toContain('Komunitas diresmikan');
});

test('hanya admin yang bisa meresmikan, hanya untuk komunitas, dan hanya ke tipe resmi yang aktif', function () {
    $siswa = akunKt('siswa');
    $komunitas = komunitasKt($siswa);
    $admin = akunKt('admin');

    $this->actingAs($siswa)->post(route('komunitas.resmikan', $komunitas->id), ['jenis_ekskul_id' => tipeKt('ekskul')])->assertForbidden();
    $this->actingAs(akunKt('pembina'))->post(route('komunitas.resmikan', $komunitas->id), ['jenis_ekskul_id' => tipeKt('ekskul')])->assertForbidden();

    $this->actingAs($admin)->post(route('komunitas.resmikan', $komunitas->id), ['jenis_ekskul_id' => tipeKt('komunitas')])->assertSessionHas('error');
    $this->actingAs($admin)->post(route('komunitas.resmikan', $komunitas->id), [])->assertSessionHasErrors('jenis_ekskul_id');
    expect($komunitas->fresh()->isKomunitas())->toBeTrue();

    // ekskul yang sudah resmi tidak bisa diresmikan lagi
    $resmi = Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Futsal', 'pembina' => 'x', 'status' => 'aktif']);
    $this->actingAs($admin)->post(route('komunitas.resmikan', $resmi->id), ['jenis_ekskul_id' => tipeKt('seleksi')])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah resmi'));
});

test('tipe "hanya satu" menghalangi peresmian kalau sudah dimiliki ekskul lain', function () {
    JenisEkskul::create(['kode' => 'tunggal', 'nama' => 'Tunggal', 'alur_pendaftaran' => 'langsung', 'kategori' => 'ekskul', 'hanya_satu' => true, 'aktif' => true, 'bawaan' => false, 'urutan' => 30]);
    Ekstrakurikuler::create(['nama_ekstrakurikuler' => 'Sudah Ada', 'pembina' => 'x', 'status' => 'aktif', 'jenis_ekskul_id' => tipeKt('tunggal')]);
    $komunitas = komunitasKt(akunKt('siswa'));

    $this->actingAs(akunKt('admin'))->post(route('komunitas.resmikan', $komunitas->id), ['jenis_ekskul_id' => tipeKt('tunggal')])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'hanya boleh dimiliki satu ekskul'));

    expect($komunitas->fresh()->isKomunitas())->toBeTrue();
});

test('komunitas tidak diresmikan lewat form edit, dan tombol Resmikan hanya terlihat admin', function () {
    $komunitas = komunitasKt(akunKt('siswa'));
    $admin = akunKt('admin');

    $this->actingAs($admin)->put(route('ekstrakurikuler.update', $komunitas->id), [
        'nama_ekstrakurikuler' => 'Komunitas Fotografi',
        'pembina' => 'Bu Guru',
        'status' => 'aktif',
        'jenis_ekskul_id' => tipeKt('ekskul'),
        'hari' => ['senin'],
        'jadwal' => ['senin' => ['jam_mulai' => '15:00', 'jam_selesai' => '17:00']],
    ])->assertSessionHasErrors('jenis_ekskul_id');
    expect($komunitas->fresh()->isKomunitas())->toBeTrue();

    $this->actingAs($admin)->get(route('ekstrakurikuler.show', $komunitas->id))->assertOk()->assertSee('belum resmi')->assertSee('Resmikan');
    $this->actingAs(akunKt('siswa'))->get(route('ekstrakurikuler.show', $komunitas->id))->assertOk()->assertSee('belum resmi')->assertDontSee('Resmikan');
});

test('katalog menandai komunitas sebagai belum resmi', function () {
    komunitasKt(akunKt('siswa'), 'Komunitas Fotografi');

    $this->actingAs(akunKt('siswa'))->get(route('ekstrakurikuler.index'))
        ->assertOk()->assertSee('Komunitas Fotografi')->assertSee('belum resmi')->assertSee('Buat Komunitas');
});
