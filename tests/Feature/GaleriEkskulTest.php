<?php

use App\Models\EkskulFoto;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
});

function penggunaGaleri(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulGaleri(string $nama = 'Fotografi'): Ekstrakurikuler
{
    return Ekstrakurikuler::create(['nama_ekstrakurikuler' => $nama, 'pembina' => 'Bu Sari', 'status' => 'aktif']);
}

function anggotaGaleri(User $siswa, Ekstrakurikuler $ekskul): PendaftaranEkskul
{
    return PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'status' => 'diterima',
    ]);
}

function fotoGaleri(Ekstrakurikuler $ekskul, ?User $pengunggah, bool $disetujui, string $kategori = 'latihan'): EkskulFoto
{
    $path = "galeri/{$ekskul->id}/".Str::random(10).'.jpg';
    Storage::disk('local')->put($path, 'isi-foto-uji');

    return EkskulFoto::create([
        'ekstrakurikuler_id' => $ekskul->id,
        'diunggah_oleh' => $pengunggah?->id,
        'berkas' => $path,
        'nama_berkas' => 'foto.jpg',
        'mime' => 'image/jpeg',
        'kategori' => $kategori,
        'diambil_pada' => now()->toDateString(),
        'disetujui' => $disetujui,
    ]);
}

function bukaGaleri(Ekstrakurikuler $ekskul, array $query = []): string
{
    return route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'galeri'] + $query);
}

// ---------- Melihat galeri ----------

test('siswa yang bukan anggota bisa membuka galeri dan hanya melihat foto yang disetujui', function () {
    $ekskul = ekskulGaleri();
    $pengunggah = penggunaGaleri();
    $tampil = fotoGaleri($ekskul, $pengunggah, disetujui: true);
    fotoGaleri($ekskul, $pengunggah, disetujui: false);

    $this->actingAs(penggunaGaleri())->get(bukaGaleri($ekskul))
        ->assertOk()
        ->assertViewHas('fotos', fn ($fotos) => $fotos->pluck('id')->all() === [$tampil->id])
        ->assertViewHas('bisaUnggah', false)
        ->assertDontSee('Khusus anggota ekskul ini');
});

test('pengunjung bukan anggota tidak melihat formulir unggah, tapi diajak mendaftar', function () {
    $ekskul = ekskulGaleri();

    $this->actingAs(penggunaGaleri())->get(bukaGaleri($ekskul))
        ->assertOk()
        ->assertDontSee('Unggah foto kegiatan')
        ->assertSee('Tertarik? Daftar Sekarang');
});

test('pengunjung tidak melihat antrean foto menunggu persetujuan milik orang lain', function () {
    $ekskul = ekskulGaleri();
    fotoGaleri($ekskul, penggunaGaleri(), disetujui: false);

    $this->actingAs(penggunaGaleri())->get(bukaGaleri($ekskul))
        ->assertViewHas('fotoMenunggu', fn ($daftar) => $daftar->isEmpty())
        ->assertViewHas('fotoSaya', fn ($daftar) => $daftar->isEmpty());
});

test('anggota aktif melihat formulir unggah dan fotonya sendiri yang masih menunggu', function () {
    $ekskul = ekskulGaleri();
    $anggota = penggunaGaleri();
    anggotaGaleri($anggota, $ekskul);
    $menunggu = fotoGaleri($ekskul, $anggota, disetujui: false);

    $this->actingAs($anggota)->get(bukaGaleri($ekskul))
        ->assertOk()
        ->assertViewHas('bisaUnggah', true)
        ->assertViewHas('fotoSaya', fn ($daftar) => $daftar->pluck('id')->all() === [$menunggu->id])
        ->assertSee('Unggah foto kegiatan')
        ->assertDontSee('Tertarik? Daftar Sekarang');
});

test('pengelola melihat antrean persetujuan', function () {
    $ekskul = ekskulGaleri();
    $menunggu = fotoGaleri($ekskul, penggunaGaleri(), disetujui: false);

    $this->actingAs(penggunaGaleri('admin'))->get(bukaGaleri($ekskul))
        ->assertOk()
        ->assertViewHas('bisaUnggah', true)
        ->assertViewHas('fotoMenunggu', fn ($daftar) => $daftar->pluck('id')->all() === [$menunggu->id]);
});

test('filter kategori tetap bekerja untuk pengunjung', function () {
    $ekskul = ekskulGaleri();
    $pengunggah = penggunaGaleri();
    fotoGaleri($ekskul, $pengunggah, disetujui: true, kategori: 'latihan');
    $lomba = fotoGaleri($ekskul, $pengunggah, disetujui: true, kategori: 'lomba');

    $this->actingAs(penggunaGaleri())->get(bukaGaleri($ekskul, ['kategori' => 'lomba']))
        ->assertViewHas('fotos', fn ($fotos) => $fotos->pluck('id')->all() === [$lomba->id])
        ->assertViewHas('kategoriDipilih', 'lomba');
});

// ---------- Berkas foto ----------

test('pengguna login yang bukan anggota bisa memuat foto yang sudah disetujui', function () {
    $ekskul = ekskulGaleri();
    $foto = fotoGaleri($ekskul, penggunaGaleri(), disetujui: true);

    $this->actingAs(penggunaGaleri())->get(route('galeri.berkas', $foto))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');
});

test('foto yang belum disetujui tidak bisa dimuat oleh pengguna lain', function () {
    $ekskul = ekskulGaleri();
    $foto = fotoGaleri($ekskul, penggunaGaleri(), disetujui: false);

    $this->actingAs(penggunaGaleri())->get(route('galeri.berkas', $foto))->assertForbidden();
});

test('foto yang belum disetujui tetap bisa dimuat oleh pengunggahnya dan pengelola', function () {
    $ekskul = ekskulGaleri();
    $pengunggah = penggunaGaleri();
    $foto = fotoGaleri($ekskul, $pengunggah, disetujui: false);

    $this->actingAs($pengunggah)->get(route('galeri.berkas', $foto))->assertOk();
    $this->actingAs(penggunaGaleri('admin'))->get(route('galeri.berkas', $foto))->assertOk();
});

test('tamu yang belum login tidak bisa memuat foto walau sudah disetujui', function () {
    $foto = fotoGaleri(ekskulGaleri(), penggunaGaleri(), disetujui: true);

    $this->get(route('galeri.berkas', $foto))->assertRedirect(route('login'));
});

// ---------- Yang tetap terkunci ----------

test('pengunjung bukan anggota tidak bisa mengunggah foto', function () {
    $ekskul = ekskulGaleri();

    $this->actingAs(penggunaGaleri())->post(route('galeri.store', $ekskul->id), [])->assertForbidden();

    expect(EkskulFoto::count())->toBe(0);
});

test('pengunjung tidak bisa menyetujui atau menghapus foto orang lain', function () {
    $ekskul = ekskulGaleri();
    $foto = fotoGaleri($ekskul, penggunaGaleri(), disetujui: false);
    $pengunjung = penggunaGaleri();

    $this->actingAs($pengunjung)->patch(route('galeri.setujui', $foto))->assertForbidden();
    $this->actingAs($pengunjung)->delete(route('galeri.destroy', $foto))->assertForbidden();

    expect($foto->fresh()->disetujui)->toBeFalse();
});

test('tab lain seperti pengumuman tetap khusus anggota', function () {
    $ekskul = ekskulGaleri();

    $this->actingAs(penggunaGaleri())
        ->get(route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'pengumuman']))
        ->assertOk()
        ->assertSee('Khusus anggota ekskul ini');
});
