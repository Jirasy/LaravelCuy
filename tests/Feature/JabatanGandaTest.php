<?php

use App\Models\EkskulAnggotaJabatan;
use App\Models\EkskulJabatan;
use App\Models\EkskulPembina;
use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function akunJg(string $role = 'siswa'): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

function ekskulJg(string $nama = 'Futsal', string $jenis = 'ekskul'): Ekstrakurikuler
{
    return Ekstrakurikuler::create([
        'nama_ekstrakurikuler' => $nama,
        'pembina' => 'Pak Budi',
        'status' => 'aktif',
        'jenis' => $jenis,
    ]);
}

function anggotaJg(Ekstrakurikuler $ekskul, string $status = 'diterima'): PendaftaranEkskul
{
    $siswa = akunJg();

    return PendaftaranEkskul::create([
        'user_id' => $siswa->id,
        'ekstrakurikuler_id' => $ekskul->id,
        'nama_pendaftar' => $siswa->name,
        'peran' => 'siswa',
        'status' => $status,
        'status_anggota' => 'aktif',
    ]);
}

function jabatanJg(Ekstrakurikuler $ekskul, string $nama, ?int $kuota = null): EkskulJabatan
{
    return EkskulJabatan::create(['ekstrakurikuler_id' => $ekskul->id, 'nama' => $nama, 'kuota' => $kuota]);
}

function berikanJg($test, User $oleh, PendaftaranEkskul $anggota, array $jabatanIds, array $tambahan = [])
{
    return $test->actingAs($oleh)->post(route('anggota-jabatan.store', $anggota->id), array_merge([
        'jabatan_ids' => $jabatanIds,
        'periode_mulai' => date('Y'),
    ], $tambahan));
}

function namaJabatanAktifJg(PendaftaranEkskul $anggota): array
{
    return $anggota->fresh()->jabatanAktif->map->label_lengkap->sort()->values()->all();
}

test('admin memberi satu anggota beberapa jabatan sekaligus', function () {
    $ekskul = ekskulJg();
    $anggota = anggotaJg($ekskul);
    $ketua = jabatanJg($ekskul, 'Ketua');
    $sekretaris = jabatanJg($ekskul, 'Sekretaris');
    $humas = jabatanJg($ekskul, 'Koordinator Humas');

    berikanJg($this, akunJg('admin'), $anggota, [$ketua->id, $sekretaris->id, $humas->id])
        ->assertRedirect(route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'anggota']))
        ->assertSessionHas('success')
        ->assertSessionMissing('error');

    expect(namaJabatanAktifJg($anggota))->toBe(['Ketua', 'Koordinator Humas', 'Sekretaris'])
        ->and($anggota->fresh()->jabatan_label)->toContain('Ketua')->toContain('Sekretaris')->toContain('Koordinator Humas')
        ->and(EkskulAnggotaJabatan::where('pendaftaran_id', $anggota->id)->count())->toBe(3);
});

test('jabatan yang sudah dipegang dilewati dan tidak dobel, yang baru tetap diberikan', function () {
    $ekskul = ekskulJg();
    $anggota = anggotaJg($ekskul);
    $ketua = jabatanJg($ekskul, 'Ketua');
    $bendahara = jabatanJg($ekskul, 'Bendahara');

    berikanJg($this, akunJg('admin'), $anggota, [$ketua->id]);

    berikanJg($this, akunJg('admin'), $anggota, [$ketua->id, $bendahara->id])
        ->assertSessionHas('success')
        ->assertSessionHas('error');

    expect(session('error'))->toContain('Ketua')->toContain('sudah dipegang')
        ->and(namaJabatanAktifJg($anggota))->toBe(['Bendahara', 'Ketua'])
        ->and(EkskulAnggotaJabatan::where('pendaftaran_id', $anggota->id)->count())->toBe(2);
});

test('jabatan yang kuotanya penuh dilewati tapi jabatan lain dalam permintaan yang sama tetap diberikan', function () {
    $ekskul = ekskulJg();
    $pemegangLama = anggotaJg($ekskul);
    $anggota = anggotaJg($ekskul);
    $ketua = jabatanJg($ekskul, 'Ketua', kuota: 1);
    $bendahara = jabatanJg($ekskul, 'Bendahara');

    berikanJg($this, akunJg('admin'), $pemegangLama, [$ketua->id])->assertSessionHas('success');

    berikanJg($this, akunJg('admin'), $anggota, [$ketua->id, $bendahara->id])
        ->assertSessionHas('success')
        ->assertSessionHas('error');

    expect(session('error'))->toContain('Ketua')->toContain('kuota')
        ->and(session('success'))->toContain('Bendahara')
        ->and(namaJabatanAktifJg($anggota))->toBe(['Bendahara'])
        ->and(namaJabatanAktifJg($pemegangLama))->toBe(['Ketua']);
});

test('mencabut satu jabatan tidak mengganggu jabatan lain milik anggota yang sama', function () {
    $ekskul = ekskulJg();
    $anggota = anggotaJg($ekskul);
    $ketua = jabatanJg($ekskul, 'Ketua');
    $sekretaris = jabatanJg($ekskul, 'Sekretaris');
    $admin = akunJg('admin');

    berikanJg($this, $admin, $anggota, [$ketua->id, $sekretaris->id]);

    $penetapanKetua = EkskulAnggotaJabatan::where('pendaftaran_id', $anggota->id)->where('jabatan_id', $ketua->id)->first();

    $this->actingAs($admin)->delete(route('anggota-jabatan.destroy', $penetapanKetua->id))->assertSessionHas('success');

    expect(namaJabatanAktifJg($anggota))->toBe(['Sekretaris'])
        ->and($penetapanKetua->fresh()->periode_selesai)->not->toBeNull()
        ->and(EkskulAnggotaJabatan::where('pendaftaran_id', $anggota->id)->count())->toBe(2); // riwayat tetap tersimpan
});

test('jabatan hanya bisa diberikan ke anggota yang pendaftarannya sudah disetujui', function () {
    $ekskul = ekskulJg();
    $pendaftar = anggotaJg($ekskul, 'pending');
    $ketua = jabatanJg($ekskul, 'Ketua');

    berikanJg($this, akunJg('admin'), $pendaftar, [$ketua->id])->assertSessionHas('error');

    expect(EkskulAnggotaJabatan::count())->toBe(0);
});

test('input pemberian jabatan yang tidak valid ditolak dan tidak mengubah data', function () {
    $ekskul = ekskulJg();
    $lain = ekskulJg('Basket');
    $anggota = anggotaJg($ekskul);
    $ketua = jabatanJg($ekskul, 'Ketua');
    $jabatanEkskulLain = jabatanJg($lain, 'Ketua');
    $admin = akunJg('admin');

    berikanJg($this, $admin, $anggota, [])->assertSessionHasErrors('jabatan_ids');
    berikanJg($this, $admin, $anggota, [$jabatanEkskulLain->id])->assertSessionHasErrors('jabatan_ids.0');
    berikanJg($this, $admin, $anggota, [$ketua->id, $jabatanEkskulLain->id])->assertSessionHasErrors('jabatan_ids.1');
    berikanJg($this, $admin, $anggota, [$ketua->id, $ketua->id])->assertSessionHasErrors('jabatan_ids.0');
    berikanJg($this, $admin, $anggota, [$ketua->id], ['periode_mulai' => 1800])->assertSessionHasErrors('periode_mulai');

    expect(EkskulAnggotaJabatan::count())->toBe(0);
});

test('hanya admin dan pembina ekskul itu yang boleh memberi jabatan', function () {
    $ekskul = ekskulJg();
    $anggota = anggotaJg($ekskul);
    $ketua = jabatanJg($ekskul, 'Ketua');

    berikanJg($this, akunJg('siswa'), $anggota, [$ketua->id])->assertForbidden();

    $pembinaLain = akunJg('pembina');
    EkskulPembina::create(['ekstrakurikuler_id' => ekskulJg('Basket')->id, 'user_id' => $pembinaLain->id, 'mulai' => now()->toDateString()]);
    berikanJg($this, $pembinaLain, $anggota, [$ketua->id])->assertForbidden();

    expect(EkskulAnggotaJabatan::count())->toBe(0);

    $pembinaIni = akunJg('pembina');
    EkskulPembina::create(['ekstrakurikuler_id' => $ekskul->id, 'user_id' => $pembinaIni->id, 'mulai' => now()->toDateString()]);
    berikanJg($this, $pembinaIni, $anggota, [$ketua->id])->assertSessionHas('success');

    expect(namaJabatanAktifJg($anggota))->toBe(['Ketua']);
});

test('OSIS memakai mekanisme yang sama: pengurus bisa memegang lebih dari satu jabatan', function () {
    $osis = ekskulJg('OSIS', 'osis');
    $pengurus = anggotaJg($osis);
    $ketuaOsis = jabatanJg($osis, 'Ketua OSIS');
    $humas = jabatanJg($osis, 'Koordinator Humas');
    $admin = akunJg('admin');

    berikanJg($this, $admin, $pengurus, [$ketuaOsis->id, $humas->id])->assertSessionHas('success');

    expect(namaJabatanAktifJg($pengurus))->toBe(['Ketua OSIS', 'Koordinator Humas']);

    $this->actingAs($admin)->get(route('ekstrakurikuler.show', ['id' => $osis->id, 'tab' => 'anggota']))
        ->assertOk()
        ->assertSee('Ketua OSIS')
        ->assertSee('Koordinator Humas');
});

test('form beri jabatan di tab anggota berupa checkbox yang hanya menawarkan jabatan yang belum dipegang', function () {
    $ekskul = ekskulJg();
    $anggota = anggotaJg($ekskul);
    $ketua = jabatanJg($ekskul, 'Ketua');
    $bendahara = jabatanJg($ekskul, 'Bendahara');
    $admin = akunJg('admin');

    $halaman = fn () => $this->actingAs($admin)->get(route('ekstrakurikuler.show', ['id' => $ekskul->id, 'tab' => 'anggota']));

    $halaman()->assertOk()
        ->assertSee('name="jabatan_ids[]" value="'.$ketua->id.'"', false)
        ->assertSee('name="jabatan_ids[]" value="'.$bendahara->id.'"', false)
        ->assertSee('boleh lebih dari satu');

    berikanJg($this, $admin, $anggota, [$ketua->id]);

    // Ketua sudah dipegang, jadi tidak ditawarkan lagi; Bendahara masih
    $halaman()->assertOk()
        ->assertDontSee('name="jabatan_ids[]" value="'.$ketua->id.'"', false)
        ->assertSee('name="jabatan_ids[]" value="'.$bendahara->id.'"', false);

    berikanJg($this, $admin, $anggota, [$bendahara->id]);

    $halaman()->assertOk()->assertSee('sudah memegang semua jabatan yang ada');
});
